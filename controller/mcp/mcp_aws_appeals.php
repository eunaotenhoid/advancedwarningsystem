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
 * Controller for MCP AWS Appeals
 */
class mcp_aws_appeals extends mcp_aws_base
{
	public function handle($action = '', $u_action = '')
	{
		$warning_id = 0;
		if ($this->request->is_set_post('approve'))
		{
			$action = 'approve';
			$warning_id = $this->request->variable('approve', 0);
		}
		else if ($this->request->is_set_post('deny'))
		{
			$action = 'deny';
			$warning_id = $this->request->variable('deny', 0);
		}

		if ($action == 'approve' || $action == 'deny')
		{
			if (check_form_key('mcp_aws_appeals'))
			{
				$this->handle_appeal($warning_id, $action);

				$meta_info = append_sid("{$this->phpbb_root_path}mcp.{$this->php_ext}", 'i=-eunaumtenhoid-advancedwarningsystem-mcp-main_module&amp;mode=appeals');
				meta_refresh(2, $meta_info);
				$message = $this->user->lang['APPEAL_ACTION_SUCCESS'] . '<br /><br />' . sprintf($this->user->lang['RETURN_PAGE'], '<a href="' . $meta_info . '">', '</a>');
				trigger_error($message);
			}
			else
			{
				trigger_error($this->user->lang['FORM_INVALID']);
			}
		}

		$pagination = $this->container->get('pagination');
		$start = $this->request->variable('start', 0);
		$limit = (int) $this->config['topics_per_page'];

		// Count total pending appeals
		$sql = 'SELECT COUNT(warning_id) AS total_appeals 
			FROM ' . $this->table_prefix . 'warnings 
			WHERE warning_appeal_status = 1';
		$result = $this->db->sql_query($sql);
		$total_appeals = (int) $this->db->sql_fetchfield('total_appeals');
		$this->db->sql_freeresult($result);

		$sql = 'SELECT w.warning_id, w.warning_points, w.warning_time, w.warning_appeal_reason, w.warning_appeal_time,
				u.user_id, u.username, u.user_colour,
				l.log_data
			FROM ' . $this->table_prefix . 'warnings w
			INNER JOIN ' . USERS_TABLE . ' u ON u.user_id = w.user_id
			LEFT JOIN ' . LOG_TABLE . ' l ON l.log_id = w.log_id
			WHERE w.warning_appeal_status = 1
			ORDER BY w.warning_appeal_time ASC';

		$result = $this->db->sql_query_limit($sql, $limit, $start);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$log_data = (isset($row['log_data'])) ? unserialize($row['log_data']) : [];
			$rule_title = (isset($log_data['title']) && $log_data['title']) ? $log_data['title'] : '';

			$this->template->assign_block_vars('appeals', [
				'WARNING_ID'    => $row['warning_id'],
				'USERNAME_FULL' => get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
				'WARNING_TIME'  => $this->user->format_date($row['warning_time']),
				'APPEAL_TIME'   => ($row['warning_appeal_time']) ? $this->user->format_date(!is_numeric($row['warning_appeal_time']) ? strtotime($row['warning_appeal_time']) : (int) $row['warning_appeal_time']) : '',
				'APPEAL_REASON' => $row['warning_appeal_reason'],
				'WARNING_TYPE'  => $rule_title ?: $this->user->lang['WARNING'],
				'U_APPROVE'     => $u_action . '&amp;action=approve&amp;warn_id=' . $row['warning_id'],
				'U_DENY'        => $u_action . '&amp;action=deny&amp;warn_id=' . $row['warning_id'],
			]);
		}
		$this->db->sql_freeresult($result);

		$this->template->assign_vars([
			'PAGINATION'    => $pagination->generate_template_pagination($u_action, 'pagination', 'start', $total_appeals, $limit, $start),
			'PAGE_NUMBER'   => $pagination->on_page($total_appeals, $limit, $start),
			'TOTAL_APPEALS' => $this->user->lang('LIST_WARNINGS', $total_appeals),
			'S_ON_PAGE'     => ($total_appeals > $limit),
		]);

		add_form_key('mcp_aws_appeals');
	}

	public function handle_appeal($warning_id, $action)
	{
		$warning_id = (int) $warning_id;
		$sql = 'SELECT w.*, u.username AS warn_username, l.log_data 
			FROM ' . $this->table_prefix . 'warnings w
			LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = w.user_id
			LEFT JOIN ' . LOG_TABLE . ' l ON l.log_id = w.log_id
			WHERE w.warning_id = ' . $warning_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$row || $row['warning_appeal_status'] != 1)
		{
			return;
		}

		$target_username = (isset($row['warn_username']) && $row['warn_username']) ? $row['warn_username'] : 'N/A';
		$target_user_id  = (int) $row['user_id'];
		$moderator_name  = $this->user->data['username'];
		$warn_id         = (int) $row['warning_id'];

		$log_data = (isset($row['log_data'])) ? unserialize($row['log_data']) : [];
		$warn_title = (isset($log_data['title']) && $log_data['title']) ? $log_data['title'] : ((isset($log_data[0])) ? $log_data[0] : 'Warning');

		if ($action == 'approve')
		{
			$current_warning_points = (int) $row['warning_points'];

			$sql = 'UPDATE ' . $this->table_prefix . 'warnings 
				SET warning_appeal_status = 2, 
					warning_points = 0 
				WHERE warning_id = ' . $warn_id;
			$this->db->sql_query($sql);

			if ($current_warning_points > 0)
			{
				$sql = 'UPDATE ' . USERS_TABLE . ' 
					SET user_warnings = CASE 
						WHEN user_warnings >= ' . $current_warning_points . ' THEN user_warnings - ' . $current_warning_points . ' 
						ELSE 0 
					END 
					WHERE user_id = ' . $target_user_id;
				$this->db->sql_query($sql);
			}

			// Check rule conversion
			if ($row['log_id'])
			{
				$sql = 'SELECT log_id, log_data FROM ' . LOG_TABLE . ' WHERE log_id = ' . (int) $row['log_id'];
				$res = $this->db->sql_query($sql);
				$log_row = $this->db->sql_fetchrow($res);
				$this->db->sql_freeresult($res);

				if ($log_row)
				{
					$log_data = unserialize($log_row['log_data']);
					$old_rule_title = isset($log_data['title']) ? $log_data['title'] : '';

					if ($this->db_tools->sql_table_exists($this->rules_table))
					{
						$sql = 'SELECT rule_appeal_convert_to FROM ' . $this->rules_table . " WHERE rule_title = '" . $this->db->sql_escape($old_rule_title) . "'";
						$result = $this->db->sql_query($sql);
						$convert_to = $this->db->sql_fetchfield('rule_appeal_convert_to');
						$this->db->sql_freeresult($result);

						if ($convert_to)
						{
							$log_data['title'] = $convert_to;
							foreach ($log_data as $key => $val)
							{
								if (is_numeric($key) && $val == $old_rule_title)
								{
									$log_data[$key] = $convert_to;
									break;
								}
							}

							$sql = "UPDATE " . LOG_TABLE . " SET log_data = '" . $this->db->sql_escape(serialize($log_data)) . "' WHERE log_id = " . (int) $log_row['log_id'];
							$this->db->sql_query($sql);
						}
					}
				}
			}

			add_log('mod', 0, 0, 'LOG_APPEAL_APPROVED', $target_username, $moderator_name, $warn_title, $warn_id);
			add_log('user', $target_user_id, 'LOG_APPEAL_APPROVED', $target_username, $moderator_name, $warn_title, $warn_id);

			$notification_manager = $this->container->get('notification_manager');
			$notification_manager->add_notifications([
				'eunaumtenhoid.advancedwarningsystem.notification.type.appeal_result',
			], [
				'warning_id'    => $warn_id,
				'warning_title' => $warn_title,
				'action'        => $action,
				'user_id'       => $target_user_id,
				'moderator_id'  => $this->user->data['user_id'],
			]);

			if ($this->container && $this->container->has('dispatcher'))
			{
				$this->container->get('dispatcher')->trigger_event('eunaumtenhoid.advancedwarningsystem.appeal_approved', [
					'warning_id'   => (int) $warn_id,
					'user_id'      => (int) $target_user_id,
					'moderator_id' => (int) $this->user->data['user_id'],
					'rule_title'   => (string) $warn_title,
				]);
			}
		}
		else
		{
			// Deny
			$sql = 'UPDATE ' . $this->table_prefix . 'warnings 
				SET warning_appeal_status = 3 
				WHERE warning_id = ' . $warn_id;
			$this->db->sql_query($sql);

			add_log('mod', 0, 0, 'LOG_APPEAL_DENIED', $target_username, $moderator_name, $warn_title, $warn_id);
			add_log('user', $target_user_id, 'LOG_APPEAL_DENIED', $target_username, $moderator_name, $warn_title, $warn_id);

			$notification_manager = $this->container->get('notification_manager');
			$notification_manager->add_notifications([
				'eunaumtenhoid.advancedwarningsystem.notification.type.appeal_result',
			], [
				'warning_id'    => $warn_id,
				'warning_title' => $warn_title,
				'action'        => $action,
				'user_id'       => $target_user_id,
				'moderator_id'  => $this->user->data['user_id'],
			]);

			if ($this->container && $this->container->has('dispatcher'))
			{
				$this->container->get('dispatcher')->trigger_event('eunaumtenhoid.advancedwarningsystem.appeal_denied', [
					'warning_id'   => (int) $warn_id,
					'user_id'      => (int) $target_user_id,
					'moderator_id' => (int) $this->user->data['user_id'],
					'rule_title'   => (string) $warn_title,
				]);
			}
		}
	}
}
