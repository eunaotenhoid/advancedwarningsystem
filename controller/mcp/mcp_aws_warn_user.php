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
 * Controller for MCP AWS Warn User
 */
class mcp_aws_warn_user extends mcp_aws_base
{
	public function handle($action = '', $u_action = '', $p_master = null)
	{
		$user_id = $this->request->variable('u', 0);
		$username = $this->request->variable('username', '', true);
		$notify = $this->request->is_set('notify_user');
		$warning = $this->request->variable('warning', '', true);
		$warn_len = $this->request->variable('warnlength', 0);
		$warn_len_other	= $this->request->variable('warnlengthother', '', true);
		$warn_type = $this->request->variable('warntype', self::WARNING);
		$warning_id = $this->request->variable('warn_id', 0);
		$send_as_me = $this->request->is_set('send_as_me');

		$hide_moderator = false;
		if ($this->config['aws_allow_hide_moderator'] == 1)
		{
			$hide_moderator = true;
		}
		else if ($this->config['aws_allow_hide_moderator'] == 2)
		{
			$hide_moderator = $this->request->is_set('hide_moderator');
		}

		$rule_points = $this->request->variable('rule_points', 1);
		$predefined_id = $this->request->variable('predefined_rule', 0);

		$rule_title = $rule_description = '';
		if ($predefined_id > 0)
		{
			$sql = 'SELECT rule_title, rule_description, rule_points FROM ' . $this->rules_table . ' WHERE rule_id = ' . (int) $predefined_id;
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if ($row)
			{
				$rule_title = (string) $row['rule_title'];
				$rule_description = (string) $row['rule_description'];
				$rule_points = (int) $row['rule_points'];
			}
		}
		else if ($predefined_id == -1)
		{
			$rule_title = $this->user->lang('CUSTOM_RULE');
		}

		if (!$user_id && !$username)
		{
			// Delegate to front controller if user not selected
			/** @var mcp_aws_front $front_ctrl */
			$front_ctrl = $this->container->get('eunaumtenhoid.advancedwarningsystem.controller.mcp_aws_front');
			$front_ctrl->handle($action, $u_action);
			return;
		}

		$sql_where = ($user_id) ? "user_id = $user_id" : "username_clean = '" . $this->db->sql_escape(utf8_clean_string($username)) . "'";

		$sql = 'SELECT * FROM ' . USERS_TABLE . ' WHERE ' . $sql_where;
		$result = $this->db->sql_query($sql);
		$user_row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$user_row)
		{
			trigger_error('NO_USER');
		}

		if ($user_row['user_id'] == $this->user->data['user_id'])
		{
			trigger_error('CANNOT_WARN_SELF');
		}

		if ($user_row['user_type'] == USER_FOUNDER)
		{
			trigger_error('CANNOT_WARN_FOUNDER');
		}

		$user_id = $user_row['user_id'];
		$warning_edit = [];
		$warning_row = null;
		if ($warning_id)
		{
			$sql = 'SELECT w.*, l.log_data
				FROM ' . WARNINGS_TABLE . ' w, ' . LOG_TABLE . " l
				WHERE w.warning_id = '$warning_id'
					AND l.log_id = w.log_id";
			$result = $this->db->sql_query($sql);
			$warning_row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if (!$warning_row)
			{
				trigger_error('NO_WARNING');
			}

			$warning_edit = unserialize($warning_row['log_data']);
		}

		if (strpos($u_action, "&amp;u=$user_id") === false)
		{
			if ($p_master && method_exists($p_master, 'adjust_url'))
			{
				$p_master->adjust_url('&amp;u=' . $user_id);
			}
			$u_action .= "&amp;u=$user_id";
		}

		if ($this->config['allow_privmsg'])
		{
			$auth2 = new \phpbb\auth\auth();
			$auth2->acl($user_row);
			$s_can_notify = ($auth2->acl_get('u_readpm')) ? true : false;
			unset($auth2);
		}
		else
		{
			$s_can_notify = false;
		}

		if ($notify && !$s_can_notify)
		{
			$notify = false;
		}

		$warnings_for_ban = (int) $this->config['warnings_for_ban'];
		if (!$warning_id && $warnings_for_ban > 0 && ($user_row['user_warnings'] + $rule_points >= $warnings_for_ban))
		{
			$warn_type = self::WARNING_BAN;
			$warning = $this->user->lang('WARNING_BAN', ((int) $user_row['user_warnings'] + $rule_points), $warning);
		}

		if ($action == 'add_warning')
		{
			if (check_form_key('mcp_warn'))
			{
				if (!$warning_id)
				{
					if ($predefined_id == 0 || ($predefined_id == -1 && !$this->config['aws_allow_custom']))
					{
						trigger_error('NO_RULE_SELECTED');
					}
					else if ($predefined_id == -1 && empty($warning))
					{
						trigger_error('NO_WARNING');
					}
					else if ($predefined_id > 0 && empty($rule_title))
					{
						trigger_error('NO_RULE_SELECTED');
					}
				}

				if (!function_exists('user_ban'))
				{
					include_once($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);
				}
				if ($warning_id)
				{
					$this->edit_warning($warning_row, $user_row, $warning, $warn_len, $warn_len_other, $warn_type, $rule_points, $rule_title);
					$msg = $this->user->lang['USER_WARNING_EDITED'] . (($warn_type == self::BAN) ? '<br /><br />' . $this->user->lang['BAN_UPDATE_SUCCESSFUL'] : '');
					$email_template = 'warning_edited';
				}
				else
				{
					$this->add_warning($user_row, $warning, $warn_len, $warn_len_other, $warn_type, $notify, 0, $send_as_me, $hide_moderator, $rule_points, $rule_title, $rule_description);
					$msg = $this->user->lang['USER_WARNING_ADDED'];
					$email_template = 'warning_user';

					if ($warn_type == self::BAN)
					{
						$ban = utf8_normalize_nfc($user_row['username']);
						$warning = str_replace(["\r", "\n"], '<br />', $warning);
						user_ban('user', $ban, $warn_len, $warn_len_other, 0, $warning, $warning);
						$msg .= '<br /><br />' . $this->user->lang['BAN_UPDATE_SUCCESSFUL'];
						$email_template = 'warning_user_ban';
					}
					else if ($warn_type == self::WARNING_BAN)
					{
						$ban = utf8_normalize_nfc($user_row['username']);
						$warning = str_replace(["\r", "\n"], '<br />', $warning);
						$user_ban_id = (int) user_ban('user', $ban, 0, 0, 0, $warning, $warning);

						if ($user_ban_id)
						{
							$sql = 'UPDATE ' . USERS_TABLE . "	SET user_ban_id = $user_ban_id
								WHERE user_id = " . $user_row['user_id'];
							$this->db->sql_query($sql);
							$msg .= '<br /><br />' . $this->user->lang['BAN_UPDATE_SUCCESSFUL'];
						}
						$email_template = 'warning_ban_by_warning';
					}
				}
				if ($notify)
				{
					$length = $this->get_warning_end($warn_len, $warn_len_other);
					$assign_vars_array = [
						'USERNAME'           => htmlspecialchars_decode($user_row['username']),
						'TO_USERNAME'        => htmlspecialchars_decode($user_row['username']),
						'FROM_USERNAME'      => htmlspecialchars_decode($this->user->data['username']),
						'WARNINGS_COUNT'     => htmlspecialchars_decode($this->config['warnings_for_ban']),
						'RULE'               => htmlspecialchars_decode($warning),
						'RULE_TYPE'          => $rule_title,
						'RULE_DESCRIPTION'   => $rule_description,
						'WARNING_POSTER'     => htmlspecialchars_decode($this->user->data['username']),
						'WARNING_LENGTH'     => ($length) ? htmlspecialchars_decode($this->user->format_date($length, $user_row['user_dateformat'])) : htmlspecialchars_decode($this->user->lang['PERMANENT']),
						'WARNING_TYPE'       => ($warn_type == self::BAN) ? htmlspecialchars_decode($this->user->lang['BAN']) : htmlspecialchars_decode($this->user->lang['WARNING']),
						'WARNING_TYPE_OLD'   => ($warning_id) ? (($warning_row['warning_type'] == self::BAN) ? htmlspecialchars_decode($this->user->lang['BAN']) : htmlspecialchars_decode($this->user->lang['WARNING'])) : '',
						'WARNING_LENGTH_OLD' => ($warning_id) ? (($warning_row['warning_end']) ? htmlspecialchars_decode($this->user->format_date($warning_row['warning_end'], $user_row['user_dateformat'])) : htmlspecialchars_decode($this->user->lang['PERMANENT'])) : '',
						'WARNING_OLD'        => (isset($warning_edit[0])) ? $warning_edit[0] : '',
					];
					$this->user_notify($email_template, $user_row, $assign_vars_array);
				}
			}
			else
			{
				$msg = $this->user->lang['FORM_INVALID'];
			}
			$redirect = append_sid("{$this->phpbb_root_path}mcp.{$this->php_ext}", "i=notes&amp;mode=user_notes&amp;u=$user_id");
			meta_refresh(2, $redirect);
			trigger_error($msg . '<br /><br />' . $this->user->lang('RETURN_PAGE', '<a href="' . $redirect . '">', '</a>'));
		}

		if (!function_exists('phpbb_get_user_avatar'))
		{
			include($this->phpbb_root_path . 'includes/functions.' . $this->php_ext);
		}

		if (!function_exists('get_user_rank'))
		{
			include($this->phpbb_root_path . 'includes/functions_display.' . $this->php_ext);
		}

		get_user_rank($user_row['user_rank'], $user_row['user_posts'], $rank_title, $rank_img, $rank_img_src);
		$avatar_img = phpbb_get_user_avatar($user_row);

		if (isset($warning_row['warning_type']))
		{
			$this->select_warn_type($warning_row['warning_type']);
		}
		else
		{
			$this->select_warn_type();
		}

		if (isset($warning_row['warning_end']) && $warning_row['warning_end'])
		{
			$this->display_warn_options('-1');
		}
		else
		{
			$this->display_warn_options();
		}

		$this->template->assign_vars([
			'U_POST_ACTION'          => $u_action,
			'RANK_TITLE'             => $rank_title,
			'JOINED'                 => $this->user->format_date($user_row['user_regdate']),
			'POSTS'                  => ($user_row['user_posts']) ? $user_row['user_posts'] : 0,
			'WARNINGS'               => ($user_row['user_warnings']) ? $user_row['user_warnings'] : 0,
			'WARNING'                => (isset($warning_edit[0])) ? $warning_edit[0] : '',
			'WARNING_POINTS'         => (isset($warning_row['warning_points'])) ? (int) $warning_row['warning_points'] : 1,
			'WARNING_ID'             => (isset($warning_row['warning_id'])) ? $warning_row['warning_id'] : '',
			'WARNING_END'            => (isset($warning_row['warning_end']) && $warning_row['warning_end']) ? $this->user->format_date($warning_row['warning_end'], 'Y-m-d') : '',
			'USERNAME_FULL'          => get_username_string('full', $user_row['user_id'], $user_row['username'], $user_row['user_colour']),
			'USERNAME_COLOUR'        => get_username_string('colour', $user_row['user_id'], $user_row['username'], $user_row['user_colour']),
			'USERNAME'               => get_username_string('username', $user_row['user_id'], $user_row['username'], $user_row['user_colour']),
			'U_PROFILE'              => get_username_string('profile', $user_row['user_id'], $user_row['username'], $user_row['user_colour']),
			'AVATAR_IMG'             => $avatar_img,
			'RANK_IMG'               => $rank_img,
			'S_CAN_NOTIFY'           => $s_can_notify,
			'S_ALLOW_CUSTOM'         => (bool) $this->config['aws_allow_custom'],
			'S_ALLOW_HIDE_MODERATOR' => (bool) $this->config['aws_allow_hide_moderator'],
			'S_HIDE_MOD_CHOICE'      => ($this->config['aws_allow_hide_moderator'] == 2),
			'S_SHOW_SEND_AS_ME'      => ((int) $this->config['aws_pm_sender'] > 0),
		]);

		$this->assign_rules_to_template();
	}
}
