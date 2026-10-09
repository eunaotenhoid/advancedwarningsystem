<?php
/**
 *
 * Advanced Warnings extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2013 rxu, 2024-2026 eunaumtenhoid
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace eunaumtenhoid\advancedwarningsystem\controller\ucp;

/**
 * Controller for UCP AWS Appeal submission
 */
class ucp_aws_appeal
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\db\tools\tools_interface */
	protected $db_tools;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/** @var string */
	protected $table_prefix;

	/** @var string */
	protected $rules_table;

	public function __construct(
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\db\tools\tools_interface $db_tools,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\request\request $request,
		$phpbb_root_path,
		$php_ext,
		$table_prefix,
		$rules_table = ''
	) {
		$this->config = $config;
		$this->db = $db;
		$this->db_tools = $db_tools;
		$this->template = $template;
		$this->user = $user;
		$this->request = $request;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->table_prefix = $table_prefix;
		$this->rules_table = $rules_table ?: $this->table_prefix . 'aws_warning_rules';
	}

	public function handle($u_action = '')
	{
		$warning_id = $this->request->variable('warn_id', 0);

		// Security check
		$sql = 'SELECT w.*, l.log_data
			FROM ' . $this->table_prefix . 'warnings w
			LEFT JOIN ' . LOG_TABLE . ' l ON l.log_id = w.log_id
			WHERE w.warning_id = ' . (int) $warning_id . '
				AND w.user_id = ' . (int) $this->user->data['user_id'];
		$result = $this->db->sql_query($sql);
		$warn_data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$warn_data || $warn_data['warning_appeal_status'] != 0 || !$this->config['aws_enable_appeals'])
		{
			if ($this->request->is_ajax())
			{
				$response = new \phpbb\json_response(['success' => false, 'message' => $this->user->lang['NO_WARNING']]);
				$response->send();
				exit;
			}
			trigger_error('NO_WARNING');
		}

		$log_data = (isset($warn_data['log_data'])) ? unserialize($warn_data['log_data']) : [];
		$rule_title = (isset($log_data['title'])) ? $log_data['title'] : '';

		// Fetch rules to check if appeal is allowed
		$allow_appeal = true;
		if ($rule_title && $this->db_tools->sql_table_exists($this->rules_table))
		{
			$sql = "SELECT rule_allow_appeal FROM " . $this->rules_table . " WHERE rule_title = '" . $this->db->sql_escape($rule_title) . "'";
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			if ($row)
			{
				$allow_appeal = (bool) $row['rule_allow_appeal'];
			}
			$this->db->sql_freeresult($result);
		}

		if (!$allow_appeal)
		{
			if ($this->request->is_ajax())
			{
				$response = new \phpbb\json_response(['success' => false, 'message' => $this->user->lang['RULE_CANNOT_APPEAL']]);
				$response->send();
				exit;
			}
			trigger_error('RULE_CANNOT_APPEAL');
		}

		$s_hidden_fields = build_hidden_fields([
			'warn_id' => $warning_id,
		]);

		$u_confirm_action = append_sid("{$this->phpbb_root_path}ucp.{$this->php_ext}", 'i=-eunaumtenhoid-advancedwarningsystem-ucp-main_module&mode=appeal&warn_id=' . $warning_id);

		// Handle AJAX submission (SweetAlert)
		if ($this->request->is_ajax() && $this->request->is_set_post('appeal_reason'))
		{
			// Verify CSRF
			if (!check_form_key('aws_appeal'))
			{
				$response = new \phpbb\json_response(['success' => false, 'message' => $this->user->lang['FORM_INVALID']]);
				$response->send();
				exit;
			}

			$appeal_reason = $this->request->variable('appeal_reason', '', true);

			if (!$appeal_reason)
			{
				$response = new \phpbb\json_response(['success' => false, 'message' => $this->user->lang['APPEAL_REASON_EMPTY']]);
				$response->send();
				exit;
			}

			$sql_ary = [
				'warning_appeal_status' => 1,
				'warning_appeal_reason' => $appeal_reason,
				'warning_appeal_time'   => time(),
			];

			$sql = 'UPDATE ' . $this->table_prefix . 'warnings SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE warning_id = ' . (int) $warning_id;
			$this->db->sql_query($sql);

			if (!function_exists('add_log'))
			{
				include($this->phpbb_root_path . 'includes/functions_admin.' . $this->php_ext);
			}
			add_log('user', (int) $this->user->data['user_id'], 'LOG_APPEAL_REQUESTED', $rule_title ?: $this->user->lang['WARNING']);

			$response = new \phpbb\json_response(['success' => true, 'message' => $this->user->lang['APPEAL_SUBMITTED_SUCCESS']]);
			$response->send();
			exit;
		}

		if (confirm_box(true))
		{
			$appeal_reason = $this->request->variable('appeal_reason', '', true);

			if (!$appeal_reason)
			{
				trigger_error('APPEAL_REASON_EMPTY', E_USER_WARNING);
			}

			if (!check_form_key('aws_appeal'))
			{
				trigger_error('FORM_INVALID');
			}

			$sql_ary = [
				'warning_appeal_status' => 1,
				'warning_appeal_reason' => $appeal_reason,
				'warning_appeal_time'   => time(),
			];

			$sql = 'UPDATE ' . $this->table_prefix . 'warnings SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE warning_id = ' . (int) $warning_id;
			$this->db->sql_query($sql);

			if (!function_exists('add_log'))
			{
				include($this->phpbb_root_path . 'includes/functions_admin.' . $this->php_ext);
			}
			add_log('user', (int) $this->user->data['user_id'], 'LOG_APPEAL_REQUESTED', $rule_title ?: $this->user->lang['WARNING']);

			$meta_url = append_sid("{$this->phpbb_root_path}ucp.{$this->php_ext}", 'i=-eunaumtenhoid-advancedwarningsystem-ucp-main_module&mode=warning_list');
			meta_refresh(3, $meta_url);
			trigger_error($this->user->lang['APPEAL_SUBMITTED_SUCCESS'] . '<br /><br />' . sprintf($this->user->lang['RETURN_PAGE'], '<a href="' . $meta_url . '">', '</a>'));
		}
		else
		{
			add_form_key('aws_appeal');
			confirm_box(false, 'REQUEST_APPEAL', $s_hidden_fields, '@eunaumtenhoid_advancedwarningsystem/ucp_aws_appeal_form.html', $u_confirm_action);
		}
	}
}
