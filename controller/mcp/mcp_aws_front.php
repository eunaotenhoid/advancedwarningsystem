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
 * Controller for MCP AWS Front overview
 */
class mcp_aws_front extends mcp_aws_base
{
	public function handle($action = '', $u_action = '')
	{
		$this->template->assign_vars([
			'U_FIND_USERNAME' => append_sid("{$this->phpbb_root_path}memberlist.{$this->php_ext}", 'mode=searchuser&amp;form=mcp&amp;field=username&amp;select_single=true'),
			'U_POST_ACTION'   => append_sid("{$this->phpbb_root_path}mcp.{$this->php_ext}", 'i=-eunaumtenhoid-advancedwarningsystem-mcp-main_module&amp;mode=warn_user'),
		]);

		$highest = [];
		$count = 0;

		view_warned_users($highest, $count, 5);

		foreach ($highest as $row)
		{
			$this->template->assign_block_vars('highest', [
				'U_NOTES'         => append_sid("{$this->phpbb_root_path}mcp.{$this->php_ext}", 'i=notes&amp;mode=user_notes&amp;u=' . $row['user_id']),
				'USERNAME_FULL'   => get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
				'USERNAME'        => $row['username'],
				'USERNAME_COLOUR' => ($row['user_colour']) ? '#' . $row['user_colour'] : '',
				'U_USER'          => append_sid("{$this->phpbb_root_path}memberlist.{$this->php_ext}", 'mode=viewprofile&amp;u=' . $row['user_id']),
				'WARNING_TIME'    => $this->user->format_date($row['user_last_warning']),
				'WARNINGS'        => $row['user_warnings'],
			]);
		}

		$sql = 'SELECT u.user_id, u.username, u.username_clean, u.user_colour, u.user_warnings, w.warning_time
			FROM ' . USERS_TABLE . ' u, ' . WARNINGS_TABLE . ' w
			WHERE u.user_id = w.user_id
			ORDER BY w.warning_time DESC';
		$result = $this->db->sql_query_limit($sql, 5);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('latest', [
				'U_NOTES'         => append_sid("{$this->phpbb_root_path}mcp.{$this->php_ext}", 'i=notes&amp;mode=user_notes&amp;u=' . $row['user_id']),
				'USERNAME_FULL'   => get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
				'USERNAME'        => $row['username'],
				'USERNAME_COLOUR' => ($row['user_colour']) ? '#' . $row['user_colour'] : '',
				'U_USER'          => append_sid("{$this->phpbb_root_path}memberlist.{$this->php_ext}", 'mode=viewprofile&amp;u=' . $row['user_id']),
				'WARNING_TIME'    => $this->user->format_date($row['warning_time']),
				'WARNINGS'        => $row['user_warnings'],
			]);
		}
		$this->db->sql_freeresult($result);
	}
}
