<?php
/**
 *
 * Advanced Warnings extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2013 rxu, 2024-2026 eunaumtenhoid
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace eunaumtenhoid\advancedwarningsystem\controller\mcp;

/**
 * Controller for MCP AWS Warnings List
 */
class mcp_aws_list extends mcp_aws_base
{
	public function handle($action = '', $u_action = '')
	{
		$this->user->add_lang('memberlist');
		$pagination = $this->container->get('pagination');

		$start = $this->request->variable('start', 0);
		$st    = $this->request->variable('st', 0);
		$sk    = $this->request->variable('sk', 'b');
		$sd    = $this->request->variable('sd', 'd');

		$limit_days = [
			0   => $this->user->lang('ALL_ENTRIES'),
			1   => $this->user->lang('1_DAY'),
			7   => $this->user->lang('7_DAYS'),
			14  => $this->user->lang('2_WEEKS'),
			30  => $this->user->lang('1_MONTH'),
			90  => $this->user->lang('3_MONTHS'),
			180 => $this->user->lang('6_MONTHS'),
			365 => $this->user->lang('1_YEAR'),
		];
		$sort_by_text = ['a' => $this->user->lang('SORT_USERNAME'), 'b' => $this->user->lang('SORT_DATE'), 'c' => $this->user->lang('SORT_WARNINGS')];
		$sort_by_sql = ['a' => 'username_clean', 'b' => 'warning_time', 'c' => 'user_warnings'];

		$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
		gen_sort_selects($limit_days, $sort_by_text, $st, $sk, $sd, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);

		$sql_where = ($st) ? (time() - ($st * 86400)) : 0;
		$sql_sort = $sort_by_sql[$sk] . ' ' . (($sd == 'd') ? 'DESC' : 'ASC');

		$users = [];
		$user_count = 0;

		$this->view_warnings_list($users, $user_count, $this->config['topics_per_page'], $start, $sql_where, $sql_sort);

		$this->assign_rules_to_template();

		foreach ($users as $row)
		{
			$log_data = (isset($row['log_data'])) ? unserialize($row['log_data']) : [];
			$rule_title = (isset($log_data['title']) && $log_data['title']) ? $log_data['title'] : '';

			$this->template->assign_block_vars('user', [
				'U_NOTES'            => append_sid("{$this->phpbb_root_path}mcp.{$this->php_ext}", 'i=notes&amp;mode=user_notes&amp;u=' . $row['user_id']),
				'USERNAME_FULL'      => get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
				'USERNAME'           => $row['username'],
				'USERNAME_COLOUR'    => ($row['user_colour']) ? '#' . $row['user_colour'] : '',
				'U_USER'             => append_sid("{$this->phpbb_root_path}memberlist.{$this->php_ext}", 'mode=viewprofile&amp;u=' . $row['user_id']),
				'WARNING_TIME'       => $this->user->format_date($row['warning_time']),
				'WARNING_EXPIRES'    => ($row['warning_end']) ? $this->user->format_date($row['warning_end']) : $this->user->lang('PERMANENT'),
				'WARNING_POINTS'     => (int) $row['warning_points'],
				'WARNING_STATUS'     => ($row['warning_status']) ? true : false,
				'WARNING_TYPE'       => $rule_title ? $rule_title . ' (' . (($row['warning_type'] == self::BAN) ? $this->user->lang['BAN'] : $this->user->lang['WARNING']) . ')' : (($row['warning_type'] == self::BAN) ? $this->user->lang['BAN'] : $this->user->lang['WARNING']),
				'MODERATOR_FULL'     => get_username_string('full', $row['mod_user_id'], $row['mod_username'], $row['mod_user_colour']),
				'U_WARNING_POST_URL' => ($row['post_id']) ? append_sid("{$this->phpbb_root_path}viewtopic.{$this->php_ext}", 'p=' . $row['post_id'] . '#p' . $row['post_id']) : '',
				'U_EDIT'             => ($this->auth->acl_get('m_warn')) ? append_sid("{$this->phpbb_root_path}mcp.{$this->php_ext}", 'i=-eunaumtenhoid-advancedwarningsystem-mcp-main_module&amp;mode=' . (($row['post_id']) ? 'warn_post&amp;p=' . $row['post_id'] : 'warn_user') . '&amp;u=' . $row['user_id'] . '&amp;warn_id=' . $row['warning_id']) : '',
			]);
		}

		$base_url = append_sid("{$this->phpbb_root_path}mcp.{$this->php_ext}", "i=-eunaumtenhoid-advancedwarningsystem-mcp-main_module&amp;mode=list&amp;st=$st&amp;sk=$sk&amp;sd=$sd");
		if ($user_count)
		{
			$pagination->generate_template_pagination($base_url, 'pagination', 'start', $user_count, $this->config['topics_per_page'], $start);
		}

		$this->template->assign_vars([
			'U_POST_ACTION'      => $u_action,
			'S_CLEAR_ALLOWED'    => ($this->auth->acl_get('a_clearlogs')) ? true : false,
			'S_SELECT_SORT_DIR'  => $s_sort_dir,
			'S_SELECT_SORT_KEY'  => $s_sort_key,
			'S_SELECT_SORT_DAYS' => $s_limit_days,
			'PAGE_NUMBER'        => ($user_count) ? $pagination->on_page($user_count, $this->config['topics_per_page'], $start) : '',
			'TOTAL_WARNINGS'     => $this->user->lang('LIST_WARNINGS', (int) $user_count),
		]);
	}

	public function view_warnings_list(&$users, &$user_count, $limit = 0, $offset = 0, $limit_days = 0, $sort_by = 'warning_time DESC')
	{
		$sql = 'SELECT u.user_id, u.username, u.user_colour, u.user_warnings, u.user_last_warning, w.warning_id, w.warning_points, w.warning_time, w.warning_end, w.warning_status, w.post_id, w.warning_type,
				m.user_id AS mod_user_id, m.username AS mod_username, m.user_colour AS mod_user_colour,
				l.log_data
			FROM ' . WARNINGS_TABLE . ' w
			LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = w.user_id
			LEFT JOIN ' . LOG_TABLE . ' l ON l.log_id = w.log_id
			LEFT JOIN ' . USERS_TABLE . ' m ON m.user_id = l.user_id
			' . (($limit_days) ? " WHERE w.warning_time >= $limit_days" : '') . '
			ORDER BY ' . ((strstr($sort_by, 'user')) ? 'u.' : 'w.') . $sort_by;
		$result = $this->db->sql_query_limit($sql, $limit, $offset);
		$users = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		$sql = 'SELECT count(warning_id) AS warnings_count FROM ' . WARNINGS_TABLE . (($limit_days) ? " WHERE warning_time >= $limit_days" : '');
		$result = $this->db->sql_query($sql);
		$user_count = (int) $this->db->sql_fetchfield('warnings_count');
		$this->db->sql_freeresult($result);
	}
}
