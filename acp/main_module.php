<?php
/**
 *
 * Advanced Warnings extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2024-2026 eunaumtenhoid
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace eunaumtenhoid\advancedwarningsystem\acp;

/**
 * Rules management module.
 */
class main_module
{
	protected $config;
	protected $db;
	protected $db_tools;
	protected $template;
	protected $user;
	protected $request;
	protected $cache;
	protected $table_prefix;
	protected $rules_table;

	public $u_action;
	public $tpl_name;
	public $page_title;

	/**
	 * Constructor
	 */
	public function __construct(
		$config = null,
		$db = null,
		$db_tools = null,
		$template = null,
		$user = null,
		$request = null,
		$cache = null,
		$table_prefix = null,
		$rules_table = ''
	) {
		global $phpbb_container;

		// If the first argument is p_master (old style loading)
		if (is_object($config) && !($config instanceof \phpbb\config\config))
		{
			$p_master = $config;
			$this->u_action = isset($p_master->u_action) ? $p_master->u_action : '';
			$this->config = $phpbb_container->get('config');
		}
		else
		{
			$this->config = $config ?: $phpbb_container->get('config');
		}

		$this->db = $db ?: $phpbb_container->get('dbal.conn');
		$this->db_tools = $db_tools ?: $phpbb_container->get('dbal.tools');
		$this->template = $template ?: $phpbb_container->get('template');
		$this->user = $user ?: $phpbb_container->get('user');
		$this->request = $request ?: $phpbb_container->get('request');
		$this->cache = $cache ?: $phpbb_container->get('cache.driver');
		$this->table_prefix = $table_prefix ?: $phpbb_container->getParameter('core.table_prefix');
		$this->rules_table = $rules_table ?: $this->table_prefix . 'aws_warning_rules';
	}

	public function main($id, $mode)
	{
		global $phpEx;
		$this->user->add_lang_ext('eunaumtenhoid/advancedwarningsystem', ['common', 'acp', 'info_acp_aws']);

		$this->tpl_name = 'acp_warning_rules';
		$this->page_title = 'ACP_WARN_RULES';

		$action = $this->request->variable('action', 'list');
		$id = $this->request->variable('id', 0);

		switch ($action)
		{
			case 'add':
			case 'edit':
				$rule = [
					'rule_id'                => 0,
					'rule_title'             => '',
					'rule_duration'          => 0,
					'rule_points'            => 0,
					'rule_is_warning'        => 0,
					'rule_is_locked'         => 0,
					'rule_allow_appeal'      => 1,
					'rule_appeal_convert_to' => '',
					'rule_description'       => '',
				];

				if ($action == 'edit' && $id)
				{
					$sql = 'SELECT * FROM ' . $this->get_rules_table() . ' WHERE rule_id = ' . (int) $id;
					$result = $this->db->sql_query($sql);
					$rule = $this->db->sql_fetchrow($result);
					$this->db->sql_freeresult($result);

					if (!$rule)
					{
						trigger_error($this->user->lang['NO_RULE'] . adm_back_link($this->u_action), E_USER_WARNING);
					}
				}

				$this->template->assign_vars([
					'S_EDIT'                 => true,
					'RULE_ID'                => $rule['rule_id'],
					'RULE_TITLE'             => $rule['rule_title'],
					'RULE_DURATION'          => $rule['rule_duration'],
					'RULE_POINTS'            => $rule['rule_points'],
					'RULE_IS_WARNING'        => $rule['rule_is_warning'],
					'RULE_IS_LOCKED'         => $rule['rule_is_locked'],
					'RULE_ALLOW_APPEAL'      => $rule['rule_allow_appeal'] ?? 1,
					'RULE_APPEAL_CONVERT_TO' => $rule['rule_appeal_convert_to'] ?? '',
					'RULE_DESCRIPTION'       => $rule['rule_description'],
					'S_APPEALS_ENABLED'      => (bool) $this->config['aws_enable_appeals'],
					'U_ACTION'               => $this->u_action . '&amp;action=save&amp;id=' . $rule['rule_id'],
					'U_BACK'                 => append_sid("index.$phpEx", 'i=acp_board&amp;mode=settings'),
				]);

				$sql = 'SELECT rule_title FROM ' . $this->get_rules_table() . ' ORDER BY rule_title ASC';
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					if ($row['rule_title'] != $rule['rule_title'])
					{
						$this->template->assign_block_vars('convert_rules', [
							'TITLE'    => $row['rule_title'],
							'SELECTED' => ($row['rule_title'] == ($rule['rule_appeal_convert_to'] ?? '')) ? true : false,
						]);
					}
				}
				$this->db->sql_freeresult($result);
			break;

			case 'save':
				if (!check_form_key('acp_warn_rules'))
				{
					trigger_error('FORM_INVALID' . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$rule_title = $this->request->variable('rule_title', '', true);
				$rule_duration = $this->request->variable('rule_duration', 0);
				$rule_points = $this->request->variable('rule_points', 0);
				$rule_is_warning = $this->request->variable('rule_is_warning', 0);
				$rule_is_locked = $this->request->variable('rule_is_locked', 0);
				$rule_allow_appeal = $this->request->variable('rule_allow_appeal', 0);
				$rule_appeal_convert_to = $this->request->variable('rule_appeal_convert_to', '', true);
				$rule_description = $this->request->variable('rule_description', '', true);

				if (!$rule_title)
				{
					trigger_error('EMPTY_RULE_TITLE' . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$sql_ary = [
					'rule_title'             => $rule_title,
					'rule_duration'          => (int) $rule_duration,
					'rule_points'            => (int) $rule_points,
					'rule_is_warning'        => (int) $rule_is_warning,
					'rule_is_locked'         => (int) $rule_is_locked,
					'rule_allow_appeal'      => (int) $rule_allow_appeal,
					'rule_appeal_convert_to' => $rule_appeal_convert_to,
					'rule_description'       => $rule_description,
				];

				if ($id)
				{
					$sql = 'UPDATE ' . $this->get_rules_table() . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE rule_id = ' . (int) $id;
					$this->db->sql_query($sql);
				}
				else
				{
					$sql = 'INSERT INTO ' . $this->get_rules_table() . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
					$this->db->sql_query($sql);
				}

				$back_link = append_sid("index.$phpEx", 'i=acp_board&amp;mode=settings');
				trigger_error($this->user->lang['RULE_SAVED'] . adm_back_link($back_link));
			break;

			case 'delete':
				if (confirm_box(true))
				{
					$sql = 'DELETE FROM ' . $this->get_rules_table() . ' WHERE rule_id = ' . (int) $id;
					$this->db->sql_query($sql);

					if ($this->request->is_ajax())
					{
						$response = new \phpbb\json_response(['success' => true]);
						$response->send();
						exit;
					}

					$back_link = append_sid("index.$phpEx", 'i=acp_board&amp;mode=settings');
					trigger_error($this->user->lang['RULE_DELETED'] . adm_back_link($back_link));
				}
				else
				{
					confirm_box(false, $this->user->lang['CONFIRM_OPERATION'], build_hidden_fields([
						'i'      => '-eunaumtenhoid-advancedwarningsystem-acp-main_module',
						'mode'   => 'main',
						'action' => 'delete',
						'id'     => $id,
					]));
				}
			break;

			case 'resync':
				if (confirm_box(true))
				{
					// 1. Reset all user warnings to 0
					$this->db->sql_query('UPDATE ' . USERS_TABLE . ' SET user_warnings = 0');

					// 2. Sum only active warnings points (warning_status = 1) per user
					$sql = 'SELECT user_id, SUM(warning_points) AS total_points
						FROM ' . WARNINGS_TABLE . '
						WHERE warning_status = 1
						GROUP BY user_id';
					$result = $this->db->sql_query($sql);

					while ($row = $this->db->sql_fetchrow($result))
					{
						$uid = (int) $row['user_id'];
						$pts = (int) $row['total_points'];
						$this->db->sql_query('UPDATE ' . USERS_TABLE . " SET user_warnings = $pts WHERE user_id = $uid");
					}
					$this->db->sql_freeresult($result);

					$back_link = append_sid("index.$phpEx", 'i=acp_board&amp;mode=settings');
					trigger_error($this->user->lang['AWS_RESYNC_SUCCESS'] . adm_back_link($back_link));
				}
				else
				{
					confirm_box(false, $this->user->lang['AWS_RESYNC_CONFIRM'], build_hidden_fields([
						'i'      => '-eunaumtenhoid-advancedwarningsystem-acp-main_module',
						'mode'   => 'main',
						'action' => 'resync',
					]));
				}
			break;

			case 'move_up':
			case 'move_down':
				$this->move_rule($id, $action);
				if ($this->request->is_ajax())
				{
					$response = new \phpbb\json_response(['success' => true]);
					$response->send();
					exit;
				}
				$back_link = append_sid("index.$phpEx", 'i=acp_board&amp;mode=settings');
				redirect($back_link);
			break;

			default:
				$rules_data = [];
				if ($this->db_tools->sql_table_exists($this->get_rules_table()))
				{
					$sql = 'SELECT * FROM ' . $this->get_rules_table() . ' ORDER BY rule_order ASC, rule_id ASC';
					$result = $this->db->sql_query($sql);
					$rules_data = $this->db->sql_fetchrowset($result);
					$this->db->sql_freeresult($result);
				}

				$total_rules = count($rules_data);
				foreach ($rules_data as $i => $row)
				{
					$this->template->assign_block_vars('rules', [
						'ID'          => $row['rule_id'],
						'TITLE'       => $row['rule_title'],
						'DURATION'    => $row['rule_duration'],
						'POINTS'      => $row['rule_points'],
						'S_FIRST_ROW' => ($i === 0) ? true : false,
						'S_LAST_ROW'  => ($i === $total_rules - 1) ? true : false,
						'U_EDIT'      => $this->u_action . '&amp;action=edit&amp;id=' . $row['rule_id'],
						'U_DELETE'    => $this->u_action . '&amp;action=delete&amp;id=' . $row['rule_id'],
						'U_MOVE_UP'   => $this->u_action . '&amp;action=move_up&amp;id=' . $row['rule_id'],
						'U_MOVE_DOWN' => $this->u_action . '&amp;action=move_down&amp;id=' . $row['rule_id'],
						'ICON_MOVE_UP'   => $this->user->img('icon_move_up', 'MOVE_UP'),
						'ICON_MOVE_DOWN' => $this->user->img('icon_move_down', 'MOVE_DOWN'),
					]);
				}

				$this->template->assign_vars([
					'U_ADD'                   => $this->u_action . '&amp;action=add',
					'U_BACK'                  => append_sid("index.$phpEx", 'i=acp_board&amp;mode=settings'),
					'ICON_MOVE_UP'            => $this->user->img('icon_move_up', 'MOVE_UP'),
					'ICON_MOVE_UP_DISABLED'   => $this->user->img('icon_move_up_disabled', 'MOVE_UP'),
					'ICON_MOVE_DOWN'          => $this->user->img('icon_move_down', 'MOVE_DOWN'),
					'ICON_MOVE_DOWN_DISABLED' => $this->user->img('icon_move_down_disabled', 'MOVE_DOWN'),
					'ICON_EDIT'               => $this->user->img('icon_edit', 'EDIT'),
					'ICON_DELETE'             => $this->user->img('icon_delete', 'DELETE'),
				]);
			break;
		}

		add_form_key('acp_warn_rules');
	}

	private function get_rules_table()
	{
		return $this->rules_table;
	}

	/**
	 * Move a rule up or down
	 */
	private function move_rule($id, $action, $retry = true)
	{
		$sql = 'SELECT rule_order FROM ' . $this->get_rules_table() . ' WHERE rule_id = ' . (int) $id;
		$result = $this->db->sql_query($sql);
		$current_order = (int) $this->db->sql_fetchfield('rule_order');
		$this->db->sql_freeresult($result);

		$op = ($action == 'move_up') ? '<' : '>';
		$order = ($action == 'move_up') ? 'DESC' : 'ASC';

		$sql = 'SELECT rule_id, rule_order FROM ' . $this->get_rules_table() . "
			WHERE rule_order $op $current_order
			ORDER BY rule_order $order";
		$result = $this->db->sql_query_limit($sql, 1);
		$target = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($target)
		{
			// Swap orders
			$sql = 'UPDATE ' . $this->get_rules_table() . ' SET rule_order = ' . $target['rule_order'] . ' WHERE rule_id = ' . (int) $id;
			$this->db->sql_query($sql);

			$sql = 'UPDATE ' . $this->get_rules_table() . ' SET rule_order = ' . $current_order . ' WHERE rule_id = ' . (int) $target['rule_id'];
			$this->db->sql_query($sql);
		}
		else if ($retry)
		{
			$this->resequence_rules();
			$this->move_rule($id, $action, false);
		}
	}

	private function resequence_rules()
	{
		$sql = 'SELECT rule_id FROM ' . $this->get_rules_table() . ' ORDER BY rule_order ASC, rule_id ASC';
		$result = $this->db->sql_query($sql);
		$i = 0;
		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->db->sql_query('UPDATE ' . $this->get_rules_table() . ' SET rule_order = ' . ($i * 10) . ' WHERE rule_id = ' . (int) $row['rule_id']);
			$i++;
		}
		$this->db->sql_freeresult($result);
	}
}
