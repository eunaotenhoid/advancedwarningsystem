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
 * Controller for UCP AWS Warning List
 */
class ucp_aws_warning_list
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
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->table_prefix = $table_prefix;
		$this->rules_table = $rules_table ?: $this->table_prefix . 'aws_warning_rules';
	}

	public function handle($u_action = '')
	{
		if (!function_exists('phpbb_get_user_avatar'))
		{
			include($this->phpbb_root_path . 'includes/functions.' . $this->php_ext);
		}

		// Fetch rules that allow appeal
		$rules_appeal = [];

		if ($this->db_tools->sql_table_exists($this->rules_table))
		{
			$sql = 'SELECT rule_title, rule_allow_appeal FROM ' . $this->rules_table;
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$rules_appeal[$row['rule_title']] = (bool) $row['rule_allow_appeal'];
			}
			$this->db->sql_freeresult($result);
		}

		// Fetch warnings for the current user
		$sql = 'SELECT w.warning_id, w.warning_points, w.warning_time, w.warning_end, w.warning_status, w.post_id, w.warning_type, w.warning_hide_mod,
				w.warning_appeal_status, w.warning_appeal_reason, w.warning_appeal_time,
				m.user_id AS mod_user_id, m.username AS mod_username, m.user_colour AS mod_user_colour,
				l.log_data
			FROM ' . $this->table_prefix . 'warnings w
			LEFT JOIN ' . LOG_TABLE . ' l ON l.log_id = w.log_id
			LEFT JOIN ' . USERS_TABLE . ' m ON m.user_id = l.user_id
			WHERE w.user_id = ' . (int) $this->user->data['user_id'] . '
			ORDER BY w.warning_time DESC';

		$result = $this->db->sql_query($sql);
		$warnings = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$log_data = (isset($row['log_data'])) ? unserialize($row['log_data']) : [];
			$rule_title = (isset($log_data['title']) && $log_data['title']) ? $log_data['title'] : '';

			$is_anonymous = ($this->config['aws_allow_hide_moderator'] == 1 || ($this->config['aws_allow_hide_moderator'] == 2 && $row['warning_hide_mod']));
			if ($is_anonymous)
			{
				$mod_username_full = $this->user->lang('MODERATOR_HIDDEN');
			}
			else
			{
				$mod_username_full = get_username_string('full', $row['mod_user_id'], $row['mod_username'], $row['mod_user_colour']);
			}

			$warnings[] = [
				'ID'             => $row['warning_id'],
				'TIME'           => $this->user->format_date($row['warning_time']),
				'EXPIRES'        => ($row['warning_end']) ? $this->user->format_date($row['warning_end']) : $this->user->lang('PERMANENT'),
				'POINTS'         => (int) $row['warning_points'],
				'STATUS'         => $row['warning_status'],
				'TYPE'           => $rule_title ? $rule_title : (($row['warning_type'] == 1) ? $this->user->lang['BAN'] : $this->user->lang['WARNING']),
				'MODERATOR_FULL' => $mod_username_full,
				'U_POST'         => ($row['post_id']) ? append_sid("{$this->phpbb_root_path}viewtopic.{$this->php_ext}", 'p=' . $row['post_id'] . '#p' . $row['post_id']) : '',
				'S_CAN_APPEAL'   => ($this->config['aws_enable_appeals'] && $row['warning_appeal_status'] == 0 && (!isset($rules_appeal[$rule_title]) || $rules_appeal[$rule_title])),
				'APPEAL_STATUS'  => (int) $row['warning_appeal_status'],
			];
		}
		$this->db->sql_freeresult($result);

		$warnings_for_ban = (isset($this->config['warnings_for_ban'])) ? (int) $this->config['warnings_for_ban'] : 0;
		$user_warnings = (int) $this->user->data['user_warnings'];
		$has_active_warnings = ($user_warnings > 0);
		$s_clean_record = (count($warnings) == 0);
		$s_near_ban = ($warnings_for_ban > 0 && $user_warnings >= ($warnings_for_ban - 2) && $has_active_warnings);
		$s_has_warnings = ($has_active_warnings && !$s_near_ban);

		if (!function_exists('get_user_rank'))
		{
			include($this->phpbb_root_path . 'includes/functions_display.' . $this->php_ext);
		}

		$rank_title = $rank_img = $rank_img_src = '';
		get_user_rank($this->user->data['user_rank'], $this->user->data['user_posts'], $rank_title, $rank_img, $rank_img_src);
		$avatar_img = phpbb_get_user_avatar($this->user->data);

		add_form_key('aws_appeal');

		$this->template->assign_vars([
			'USER_INFRACTIONS'  => $warnings,
			'S_CLEAN_RECORD'    => $s_clean_record,
			'S_HAS_WARNINGS'    => $s_has_warnings,
			'S_NEAR_BAN'        => $s_near_ban,
			'S_APPEAL_ENABLED'  => (bool) $this->config['aws_enable_appeals'],
			'U_APPEAL_ACTION'   => append_sid("{$this->phpbb_root_path}ucp.{$this->php_ext}", 'i=-eunaumtenhoid-advancedwarningsystem-ucp-main_module&mode=appeal'),
			'USERNAME_FULL'     => get_username_string('full', $this->user->data['user_id'], $this->user->data['username'], $this->user->data['user_colour']),
			'AVATAR_IMG'        => $avatar_img,
			'RANK_TITLE'        => $rank_title,
			'RANK_IMG'          => $rank_img,
			'JOINED'            => $this->user->format_date($this->user->data['user_regdate']),
			'POSTS'             => $this->user->data['user_posts'],
			'INFRACTION_POINTS' => $user_warnings,
			'AVATAR_MAX_WIDTH'  => $this->config['avatar_max_width'],
			'AVATAR_MAX_HEIGHT' => $this->config['avatar_max_height'],
		]);
	}
}
