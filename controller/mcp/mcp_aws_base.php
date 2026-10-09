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
 * Base MCP AWS Controller with shared warning operations
 */
abstract class mcp_aws_base
{
	const WARNING = 0;
	const BAN = 1;
	const WARNING_BAN = 2;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\db\tools\tools_interface */
	protected $db_tools;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\log\log */
	protected $phpbb_log;

	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var \Symfony\Component\DependencyInjection\ContainerInterface */
	protected $container;

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
		\phpbb\auth\auth $auth,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\request\request $request,
		\phpbb\log\log $phpbb_log,
		\phpbb\cache\driver\driver_interface $cache,
		$container,
		$phpbb_root_path,
		$php_ext,
		$table_prefix,
		$rules_table = ''
	) {
		$this->config = $config;
		$this->db = $db;
		$this->db_tools = $db_tools;
		$this->auth = $auth;
		$this->template = $template;
		$this->user = $user;
		$this->request = $request;
		$this->phpbb_log = $phpbb_log;
		$this->cache = $cache;
		$this->container = $container;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->table_prefix = $table_prefix;
		$this->rules_table = $rules_table ?: $this->table_prefix . 'aws_warning_rules';

		if (!defined('WARNING'))
		{
			define('WARNING', 0);
			define('BAN', 1);
			define('WARNING_BAN', 2);
		}
	}

	public function get_warning_end($warn_len, $warn_len_other)
	{
		$current_time = time();
		if ($warn_len)
		{
			if ($warn_len != -1 || !$warn_len_other)
			{
				$warn_end = max($current_time, $current_time + ($warn_len) * 60);
			}
			else
			{
				$warn_other = explode('-', $warn_len_other);
				if (sizeof($warn_other) == 3 && ((int) $warn_other[0] < 9999) && (strlen($warn_other[0]) == 4) && (strlen($warn_other[1]) == 2) && (strlen($warn_other[2]) == 2))
				{
					$warn_end = max($current_time, gmmktime(0, 0, 0, (int) $warn_other[1], (int) $warn_other[2], (int) $warn_other[0]));
				}
				else
				{
					trigger_error('LENGTH_WARNING_INVALID');
				}
			}
		}
		else
		{
			$warn_end = 0;
		}
		return $warn_end;
	}

	public function display_warn_options($default = 0)
	{
		$warn_end_text = [
			0     => $this->user->lang('PERMANENT'),
			30    => $this->user->lang('30_MINS'),
			60    => $this->user->lang('1_HOUR'),
			360   => $this->user->lang('6_HOURS'),
			1440  => $this->user->lang('1_DAY'),
			10080 => $this->user->lang('7_DAYS'),
			20160 => $this->user->lang('2_WEEKS'),
			40320 => $this->user->lang('1_MONTH'),
			-1    => $this->user->lang('UNTIL') . ' -&gt; ',
		];
		$warn_end_options = '';
		foreach ($warn_end_text as $length => $text)
		{
			$selected = ($length == $default) ? ' selected="selected"' : '';
			$warn_end_options .= '<option value="' . $length . '"' . $selected . '>' . $text . '</option>';
		}
		$this->template->assign_vars(['S_WARN_END_OPTIONS' => $warn_end_options]);
	}

	public function select_warn_type($default = self::WARNING)
	{
		$warn_type_text = [self::WARNING => $this->user->lang('WARNING'), self::BAN => $this->user->lang('BAN')];
		if (!$this->auth->acl_get('m_ban'))
		{
			unset($warn_type_text[self::BAN]);
		}
		$warn_type_options = '';
		foreach ($warn_type_text as $type => $text)
		{
			$selected = ($type == $default) ? ' selected="selected"' : '';
			$warn_type_options .= '<option value="' . $type . '"' . $selected . '>' . $text . '</option>';
		}
		$this->template->assign_vars(['S_WARN_TYPE_OPTIONS' => $warn_type_options]);
	}

	public function user_notify($email_template, $user_row, $assign_vars_array, $template_path = '', $use_queue = false)
	{
		include_once($this->phpbb_root_path . 'includes/functions_messenger.' . $this->php_ext);
		$messenger = new \messenger($use_queue);

		$lang_dir = $user_row['user_lang'];
		$expected_file = $this->phpbb_root_path . 'ext/eunaumtenhoid/advancedwarningsystem/language/' . $lang_dir . '/email/' . $email_template . '.txt';
		if (!file_exists($expected_file))
		{
			$lang_dir = 'en'; // Fallback to English
		}
		$final_template_path = $this->phpbb_root_path . 'ext/eunaumtenhoid/advancedwarningsystem/language/' . $lang_dir . '/email';

		$messenger->template($email_template, $lang_dir, $final_template_path);
		$messenger->set_addresses($user_row);
		$messenger->anti_abuse_headers($this->config, $this->user);
		$messenger->assign_vars($assign_vars_array);
		$messenger->send($user_row['user_notify_type']);
		return true;
	}

	protected function assign_rules_to_template()
	{
		if ($this->db_tools->sql_table_exists($this->rules_table))
		{
			$sql = 'SELECT * FROM ' . $this->rules_table . ' ORDER BY rule_order ASC, rule_id ASC';
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$this->template->assign_block_vars('rules', [
					'ID'          => $row['rule_id'],
					'TITLE'       => $row['rule_title'],
					'DESCRIPTION' => $row['rule_description'],
					'DURATION'    => $row['rule_duration'],
					'POINTS'      => $row['rule_points'],
					'IS_WARNING'  => $row['rule_is_warning'],
					'IS_LOCKED'   => $row['rule_is_locked'],
				]);
			}
			$this->db->sql_freeresult($result);
		}
	}

	public function add_warning($user_row, $warning, $warn_len, $warn_len_other, $warn_type = self::WARNING, $send_pm = true, $post_id = 0, $send_as_me = false, $hide_moderator = false, $rule_points = 1, $rule_title = '', $rule_description = '')
	{
		if (!in_array($warn_type, [self::WARNING, self::BAN]))
		{
			$warn_type = self::WARNING;
		}

		$warn_end = $this->get_warning_end($warn_len, $warn_len_other);

		if ($send_pm)
		{
			require_once($this->phpbb_root_path . 'includes/functions_privmsgs.' . $this->php_ext);
			require_once($this->phpbb_root_path . 'includes/message_parser.' . $this->php_ext);

			$user_row['user_lang'] = (file_exists($this->phpbb_root_path . 'language/' . $user_row['user_lang'] . "/mcp.{$this->php_ext}")) ? $user_row['user_lang'] : $this->config['default_lang'];

			$pm_sender_id = (int) $this->config['aws_pm_sender'];
			$pm_sender_name = $this->user->data['username'];

			if ($send_as_me || !$pm_sender_id)
			{
				$pm_sender_id = (int) $this->user->data['user_id'];
			}
			else
			{
				$sql = 'SELECT username FROM ' . USERS_TABLE . ' WHERE user_id = ' . (int) $pm_sender_id;
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);
				if ($row)
				{
					$pm_sender_name = $row['username'];
				}
				else
				{
					$pm_sender_id = (int) $this->user->data['user_id'];
				}
			}

			$post_url = '';
			if ($post_id)
			{
				$post_url = generate_board_url() . "/viewtopic.{$this->php_ext}?p=$post_id#p$post_id";
			}

			$pm_subject = $this->config['aws_pm_subject'] ?: $this->user->lang['AWS_PM_SUBJECT_DEFAULT'];
			$pm_body = ($post_id ? $this->config['aws_pm_body_post'] : $this->config['aws_pm_body_user']) ?: ($post_id ? $this->user->lang['AWS_PM_BODY_POST_DEFAULT'] : $this->user->lang['AWS_PM_BODY_USER_DEFAULT']);

			$variables = [
				'{USERNAME}'         => $user_row['username'],
				'{RULE}'             => $warning,
				'{RULE_TYPE}'        => $rule_title,
				'{RULE_DESCRIPTION}' => $rule_description,
				'{POST_URL}'         => $post_url,
				'{MODERATOR}'        => $this->user->data['username'],
				'{POINTS}'           => $rule_points,
				'{RULES_URL}'        => $this->config['aws_rules_url'],
			];

			$pm_subject = str_replace(array_keys($variables), array_values($variables), $pm_subject);
			$pm_body = str_replace(array_keys($variables), array_values($variables), $pm_body);

			$message_parser = new \parse_message;
			$message_parser->message = $pm_body;
			$message_parser->parse(true, true, true, false, false, true, true);

			$pm_data = [
				'from_user_id'    => $pm_sender_id,
				'from_user_ip'    => $this->user->ip,
				'from_username'   => $pm_sender_name,
				'enable_sig'      => false,
				'enable_bbcode'   => true,
				'enable_smilies'  => true,
				'enable_urls'     => true,
				'icon_id'         => 0,
				'bbcode_bitfield' => $message_parser->bbcode_bitfield,
				'bbcode_uid'      => $message_parser->bbcode_uid,
				'message'         => $message_parser->message,
				'address_list'    => ['u' => [$user_row['user_id'] => 'to']],
			];

			submit_pm('post', $pm_subject, $pm_data, false);
		}

		$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_WARNING', time(), ['username' => $user_row['username']]);

		$log_key = $rule_title ? 'LOG_USER_WARNING_BODY_AWS' : 'LOG_USER_WARNING_BODY';
		$log_args = $rule_title ? [$rule_title, $warning] : [$warning];
		$log_args += ['title' => $rule_title, 'reportee_id' => $user_row['user_id']];

		$log_id = $this->phpbb_log->add('user', $this->user->data['user_id'], $user_row['user_ip'], $log_key, time(), $log_args);

		$sql_ary = [
			'user_id'               => $user_row['user_id'],
			'post_id'               => $post_id,
			'log_id'                => $log_id,
			'warning_time'          => time(),
			'warning_end'           => (int) $warn_end,
			'warning_type'          => $warn_type,
			'warning_status'        => 1,
			'warning_hide_mod'      => (int) $hide_moderator,
			'warning_points'        => (int) $rule_points,
			'warning_appeal_status' => 0,
			'warning_appeal_reason' => '',
			'warning_appeal_time'   => 0,
		];

		$this->db->sql_query('INSERT INTO ' . WARNINGS_TABLE . ' ' . $this->db->sql_build_array('INSERT', $sql_ary));

		$sql = 'UPDATE ' . USERS_TABLE . '
			SET user_warnings = user_warnings + ' . (int) $rule_points . ',
				user_last_warning = ' . time() . '
			WHERE user_id = ' . (int) $user_row['user_id'];
		$this->db->sql_query($sql);

		$this->cache->destroy('sql', WARNINGS_TABLE);

		$forum_id = $topic_id = 0;
		if ($post_id)
		{
			$sql = 'SELECT forum_id, topic_id FROM ' . POSTS_TABLE . ' WHERE post_id = ' . (int) $post_id;
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);
			$forum_id = (int) $row['forum_id'];
			$topic_id = (int) $row['topic_id'];
		}

		add_log('mod', $forum_id, $topic_id, 'LOG_USER_WARNING', $user_row['username']);
		add_log('user', (int) $user_row['user_id'], 'LOG_USER_WARNING', $user_row['username']);

		// Dispatch core and custom events for extensions (e.g. Economy, Download Center)
		if ($this->container && $this->container->has('dispatcher'))
		{
			$dispatcher = $this->container->get('dispatcher');

			// phpBB Native core.user_warn event
			$user_id = (int) $user_row['user_id'];
			$warning_reporter_id = (int) $this->user->data['user_id'];
			$dispatcher->trigger_event('core.user_warn', compact('user_id', 'post_id', 'warning', 'warning_reporter_id'));

			// Advanced Warning System custom event
			$warning_id = (int) $this->db->sql_nextid();
			$warning_type = (int) $warn_type;
			$warning_points = (int) $rule_points;
			$warning_end = (int) $warn_end;
			$moderator_id = (int) $this->user->data['user_id'];
			$dispatcher->trigger_event('eunaumtenhoid.advancedwarningsystem.add_warning_after', compact(
				'warning_id',
				'user_id',
				'post_id',
				'warning_type',
				'warning_points',
				'warning_end',
				'rule_title',
				'warning',
				'moderator_id'
			));
		}

		// Points Deduction Integration
		$extension_manager = $this->container->get('ext.manager');
		$ups_enabled = $extension_manager->is_enabled('dmzx/ultimatepoints');
		$aps_enabled = $extension_manager->is_enabled('phpbbstudio/aps');

		if ($ups_enabled || $aps_enabled)
		{
			$old_warnings = (int) $user_row['user_warnings'];
			$new_warnings = $old_warnings + (int) $rule_points;
			$points_to_debit = 0;
			$levels_reached = [];

			for ($i = 1; $i <= 5; $i++)
			{
				$threshold = (int) (isset($this->config['aws_level' . $i . '_threshold']) ? $this->config['aws_level' . $i . '_threshold'] : 0);
				$level_points = (int) (isset($this->config['aws_level' . $i . '_points']) ? $this->config['aws_level' . $i . '_points'] : 0);

				if ($threshold > 0 && $level_points > 0)
				{
					if ($old_warnings < $threshold && $new_warnings >= $threshold)
					{
						$points_to_debit += $level_points;
						$levels_reached[] = $i;
					}
				}
			}

			if ($points_to_debit > 0)
			{
				$sql = 'SELECT user_points FROM ' . USERS_TABLE . ' WHERE user_id = ' . (int) $user_row['user_id'];
				$result = $this->db->sql_query($sql);
				$current_points = (float) $this->db->sql_fetchfield('user_points');
				$this->db->sql_freeresult($result);

				$sql = 'UPDATE ' . USERS_TABLE . '
					SET user_points = user_points - ' . (float) $points_to_debit . '
					WHERE user_id = ' . (int) $user_row['user_id'];
				$this->db->sql_query($sql);

				foreach ($levels_reached as $lvl)
				{
					$lvl_points = (int) $this->config['aws_level' . $lvl . '_points'];
					$comment = sprintf($this->user->lang['AWS_LEVEL_POINTS_LOG'], $lvl_points, $lvl);

					if ($aps_enabled)
					{
						$log_args = serialize([$lvl, (string) $lvl_points]);
						$this->db->sql_query('INSERT INTO ' . $this->table_prefix . 'aps_logs ' . $this->db->sql_build_array('INSERT', [
							'log_action'   => 'LOG_APS_WARNING_POINTS',
							'log_actions'  => $log_args,
							'log_time'     => time(),
							'user_id'      => $user_row['user_id'],
							'reportee_id'  => $this->user->data['user_id'],
							'points_old'   => $current_points,
							'points_new'   => $current_points - $lvl_points,
							'points_sum'   => $lvl_points,
							'log_approved' => 1,
						]));
					}

					if ($ups_enabled)
					{
						$this->db->sql_query('INSERT INTO ' . $this->table_prefix . 'points_log ' . $this->db->sql_build_array('INSERT', [
							'point_send'    => (int) $user_row['user_id'],
							'point_recv'    => 0,
							'point_amount'  => (float) $lvl_points,
							'point_date'    => time(),
							'point_type'    => 2,
							'point_comment' => (string) $comment,
							'point_sendold' => (float) $current_points,
							'point_recvold' => 0,
						]));
					}

					$current_points -= $lvl_points;
				}
			}
		}
	}

	public function edit_warning($warning_row, $user_row, $warning, $warn_len, $warn_len_other, $warn_type, $rule_points = 1, $rule_title = '')
	{
		if (!isset($warning_row) || empty($warning_row))
		{
			return false;
		}

		$warn_end = $this->get_warning_end($warn_len, $warn_len_other);

		$warning_type_change = false;
		if ($warning_row['warning_type'] != $warn_type && in_array($warn_type, [self::WARNING, self::BAN]))
		{
			$warning_type_change = true;
		}

		$sql_warn_ary = [
			'warning_end'    => (int) $warn_end,
			'warning_type'   => $warn_type,
			'warning_points' => (int) $rule_points,
		];

		$sql_log_ary = [
			'log_data' => serialize([
				0       => $warning,
				'title' => $rule_title,
			]),
		];

		$ban_id = 0;
		if ($warning_row['warning_type'] == self::BAN)
		{
			$sql = 'SELECT ban_id FROM ' . BANLIST_TABLE . '
						WHERE ban_userid = ' . $warning_row['user_id'] . '
							AND ban_end = ' . $warning_row['warning_end'];
			$result = $this->db->sql_query($sql);
			$ban_id = $this->db->sql_fetchfield('ban_id');
			$this->db->sql_freeresult($result);

			if (!$warning_type_change && $ban_id)
			{
				$warning = str_replace(["\r", "\n"], '<br />', $warning);
				$sql_ban_ary = [
					'ban_end'         => (int) $warn_end,
					'ban_reason'      => (string) $warning,
					'ban_give_reason' => (string) $warning,
				];
				$sql = 'UPDATE ' . BANLIST_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ban_ary) . ' WHERE ban_id = ' . $ban_id;
				$this->db->sql_query($sql);
			}
		}

		if ($warning_type_change)
		{
			if ($warn_type == self::WARNING && $ban_id)
			{
				user_unban('user', $ban_id);
			}
			else if ($warn_type == self::BAN)
			{
				$ban = utf8_normalize_nfc($user_row['username']);
				$warning = str_replace(["\r", "\n"], '<br />', $warning);
				user_ban('user', $ban, $warn_len, $warn_len_other, 0, $warning, $warning);
			}
		}

		$sql = 'UPDATE ' . WARNINGS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_warn_ary) . ' WHERE warning_id = ' . $warning_row['warning_id'];
		$this->db->sql_query($sql);

		$sql = 'UPDATE ' . LOG_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_log_ary) . ' WHERE log_id = ' . $warning_row['log_id'];
		$this->db->sql_query($sql);

		$old_points = (isset($warning_row['warning_points'])) ? (int) $warning_row['warning_points'] : 1;
		if ($old_points != (int) $rule_points)
		{
			$delta = (int) $rule_points - $old_points;
			$sql = 'UPDATE ' . USERS_TABLE . '
				SET user_warnings = user_warnings + ' . $delta . '
				WHERE user_id = ' . (int) $user_row['user_id'];
			$this->db->sql_query($sql);
		}

		add_log('mod', 0, 0, 'LOG_AWS_WARNING_EDITED', $user_row['username'], $this->user->data['username'], $rule_title, (int) $warning_row['warning_id']);

		// Dispatch custom event for extensions
		if ($this->container && $this->container->has('dispatcher'))
		{
			$dispatcher = $this->container->get('dispatcher');
			$warning_id = (int) $warning_row['warning_id'];
			$user_id = (int) $user_row['user_id'];
			$warning_points = (int) $rule_points;
			$warning_end = (int) $warn_end;
			$moderator_id = (int) $this->user->data['user_id'];
			$dispatcher->trigger_event('eunaumtenhoid.advancedwarningsystem.edit_warning_after', compact(
				'warning_id',
				'user_id',
				'warning_points',
				'warning_end',
				'rule_title',
				'warning',
				'moderator_id'
			));
		}

		$this->cache->destroy('sql', WARNINGS_TABLE);
	}
}
