<?php
/**
 *
 * Advanced Warnings extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2013 rxu, 2024-2026 eunaumtenhoid
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace eunaumtenhoid\advancedwarningsystem\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Frontend Event listener
 */
class listener implements EventSubscriberInterface
{
	const BAN = 1;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var string phpbb_root_path */
	protected $phpbb_root_path;

	/** @var string phpEx */
	protected $php_ext;

	/** @var array */
	public $warnings = [];

	/** @var array */
	public $users_banned = [];

	/** @var bool Flag indicating if warnings data has been lazy loaded */
	protected $warnings_loaded = false;

	/** @var string table_prefix */
	protected $table_prefix;

	/** @var \phpbb\extension\manager */
	protected $ext_manager;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config              $config           Config object
	 * @param \phpbb\db\driver\driver_interface $db               DBAL object
	 * @param \phpbb\auth\auth                  $auth             Auth object
	 * @param \phpbb\template\template          $template         Template object
	 * @param \phpbb\user                       $user             User object
	 * @param \phpbb\request\request            $request          Request object
	 * @param string                            $phpbb_root_path  phpbb_root_path
	 * @param string                            $php_ext          phpEx
	 * @param string                            $table_prefix     table_prefix
	 * @param \phpbb\extension\manager          $ext_manager      Extension manager
	 * @access public
	 */
	public function __construct(
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\auth\auth $auth,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\request\request $request,
		$phpbb_root_path,
		$php_ext,
		$table_prefix,
		\phpbb\extension\manager $ext_manager
	) {
		$this->template = $template;
		$this->user = $user;
		$this->auth = $auth;
		$this->db = $db;
		$this->config = $config;
		$this->request = $request;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->table_prefix = $table_prefix;
		$this->ext_manager = $ext_manager;
	}

	public static function getSubscribedEvents()
	{
		return [
			'core.user_setup'                           => 'load_language',
			'core.modify_mcp_modules_display_option'    => 'set_display_option',
			'core.memberlist_view_profile'              => 'add_memberlist_info',
			'core.memberlist_prepare_profile_data'      => 'add_warn_link',
			'core.viewtopic_cache_user_data'            => 'modify_viewtopic_usercache_data',
			'core.viewtopic_modify_post_row'            => 'modify_postrow',
			'core.delete_posts_in_transaction'          => 'handle_delete_posts',
			'core.delete_warnings_after'                => 'resync_user_warnings_after_delete',
			'core.mcp_front_view_before'                => 'add_mcp_front_warnings',
		];
	}

	public function load_language($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = [
			'ext_name' => 'eunaumtenhoid/advancedwarningsystem',
			'lang_set' => ['common', 'info_mcp_aws'],
		];
		$event['lang_set_ext'] = $lang_set_ext;
	}

	public function resync_user_warnings_after_delete($event)
	{
		$user_id = (int) $event['user_id'];

		// Recalculate total points
		$sql = 'SELECT SUM(warning_points) AS total_points
			FROM ' . WARNINGS_TABLE . '
			WHERE user_id = ' . $user_id . '
				AND warning_status = 1';
		$result = $this->db->sql_query($sql);
		$total_points = (int) $this->db->sql_fetchfield('total_points');
		$this->db->sql_freeresult($result);

		// Update user table
		$sql = 'UPDATE ' . USERS_TABLE . '
			SET user_warnings = ' . $total_points . '
			WHERE user_id = ' . $user_id;
		$this->db->sql_query($sql);
	}

	/**
	 * Adds latest warnings to the MCP front page
	 */
	public function add_mcp_front_warnings($event)
	{
		$sql = 'SELECT u.user_id, u.username, u.user_colour, w.warning_id, w.warning_time, w.warning_type, l.log_data
			FROM ' . USERS_TABLE . ' u, ' . WARNINGS_TABLE . ' w, ' . LOG_TABLE . ' l
			WHERE u.user_id = w.user_id
				AND l.log_id = w.log_id
			ORDER BY w.warning_time DESC';
		$result = $this->db->sql_query_limit($sql, 5);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$log_data = (isset($row['log_data'])) ? unserialize($row['log_data']) : [];
			$rule_title = (isset($log_data['title']) && $log_data['title']) ? $log_data['title'] : $this->user->lang['WARNING'];

			$this->template->assign_block_vars('mcp_latest_warnings', [
				'U_USER'       => get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
				'WARNING_TYPE' => $rule_title . ' (' . (($row['warning_type'] == self::BAN) ? $this->user->lang['BAN'] : $this->user->lang['WARNING']) . ')',
				'DATE'         => $this->user->format_date($row['warning_time']),
				'U_NOTES'      => append_sid("{$this->phpbb_root_path}mcp.{$this->php_ext}", 'i=notes&amp;mode=user_notes&amp;u=' . $row['user_id']),
			]);
		}
		$this->db->sql_freeresult($result);
	}

	public function set_display_option($event)
	{
		$module = $event['module'];
		$user_id = $event['user_id'];
		$username = $event['username'];

		if (!$user_id && $username == '')
		{
			$module->set_display('\eunaumtenhoid\advancedwarningsystem\mcp\main_module', 'warn_user', false);
		}

		if (!$this->config['aws_enable_appeals'])
		{
			$module->set_display('\eunaumtenhoid\advancedwarningsystem\mcp\main_module', 'appeals', false);
		}

		$event['module'] = $module;
	}

	public function add_memberlist_info($event)
	{
		$this->ensure_warnings_data_loaded();

		$user_id = (int) $event['member']['user_id'];
		$user = [];

		$this->user->add_lang_ext('eunaumtenhoid/advancedwarningsystem', ['common', 'info_mcp_aws']);
		$sql = 'SELECT w.warning_id, w.post_id, w.warning_time, w.warning_end, w.warning_type, w.warning_status, w.warning_hide_mod, l.user_id, l.log_data, l.reportee_id, u.username, u.user_colour
			FROM ' . WARNINGS_TABLE . ' w, ' . LOG_TABLE . ' l, ' . USERS_TABLE . " u
				WHERE w.user_id = $user_id
					AND l.log_id = w.log_id
					AND u.user_id = l.user_id
						ORDER BY w.warning_status DESC, w.warning_id DESC";

		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			if (!$this->auth->acl_get('m_warn') && !$row['warning_status'])
			{
				continue;
			}

			$warning_time = $this->user->lang['WARNING_EXPIRED'];
			if ($row['warning_end'])
			{
				$warning_time = $this->user->format_date($row['warning_end']);
			}
			else if (($row['warning_end'] == 0) && $row['warning_status'])
			{
				$warning_time = $this->user->lang['PERMANENT'];
			}

			$warning_data = unserialize($row['log_data']);

			$is_anonymous = ($this->config['aws_allow_hide_moderator'] == 1 || ($this->config['aws_allow_hide_moderator'] == 2 && $row['warning_hide_mod']));
			$user[] = [
				'U_EDIT'             => ($this->auth->acl_get('m_warn')) ? append_sid("{$this->phpbb_root_path}mcp.$this->php_ext", 'i=-eunaumtenhoid-advancedwarningsystem-mcp-main_module&amp;mode=' . (($row['post_id']) ? 'warn_post&amp;p=' . $row['post_id'] : 'warn_user') . '&amp;u=' . $user_id . '&amp;warn_id=' . $row['warning_id']) : '',
				'USERNAME_FULL'      => ($is_anonymous && !$this->auth->acl_get('m_warn')) ? $this->user->lang['MODERATOR'] : get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
				'USERNAME_COLOUR'    => ($is_anonymous && !$this->auth->acl_get('m_warn')) ? '' : (($row['user_colour']) ? '#' . $row['user_colour'] : ''),
				'WARNING_TIME'       => $warning_time,
				'WARNING'            => (isset($warning_data[0])) ? $warning_data[0] : '',
				'WARNINGS'           => $this->user->format_date($row['warning_time']),
				'WARNING_STATUS'     => (bool) $row['warning_status'],
				'WARNING_TYPE'       => ($row['warning_type'] == self::BAN) ? $this->user->lang['BAN'] : $this->user->lang['WARNING'],
				'U_WARNING_POST_URL' => ($row['post_id']) ? append_sid("{$this->phpbb_root_path}viewtopic.$this->php_ext", 'p=' . $row['post_id'] . '#p' . $row['post_id']) : '',
			];
		}
		$this->db->sql_freeresult($result);

		$this->template->assign_block_vars_array('user', $user);

		if ($this->config['aws_show_cards'])
		{
			$warnings_count = (int) $event['member']['user_warnings'];
			$is_banned = (isset($this->users_banned[$user_id])) ? true : false;

			$this->template->assign_vars([
				'AWS_ICONS' => $this->get_active_icons($warnings_count, $is_banned),
			]);
		}

		$event['warn_user_enabled'] = $this->auth->acl_get('m_warn');
	}

	public function add_warn_link($event)
	{
		$user_id = (int) $event['data']['user_id'];
		$template_data = $event['template_data'];
		$template_data['U_WARN'] = ($this->auth->acl_get('m_warn')) ? append_sid("{$this->phpbb_root_path}mcp.$this->php_ext", 'i=-eunaumtenhoid-advancedwarningsystem-mcp-main_module&amp;mode=warn_user&amp;u=' . $user_id) : '';
		$event['template_data'] = $template_data;
	}

	public function modify_viewtopic_usercache_data($event)
	{
		$user_cache_data = $event['user_cache_data'];
		$row = $event['row'];

		$user_cache_data = array_merge($user_cache_data, [
			'user_ban_id'   => (isset($row['user_ban_id'])) ? $row['user_ban_id'] : 0,
			'user_warnings' => (isset($row['user_warnings'])) ? $row['user_warnings'] : 0,
		]);
		$event['user_cache_data'] = $user_cache_data;
	}

	public function modify_postrow($event)
	{
		$this->ensure_warnings_data_loaded();

		$this->user->add_lang('acp/ban');
		$this->user->add_lang_ext('eunaumtenhoid/advancedwarningsystem', ['common', 'info_mcp_aws']);

		$row = $event['row'];
		$postrow = $event['post_row'];
		$user_cache = $event['user_poster_data'];
		$poster_id = $row['user_id'];
		$post_id = $row['post_id'];
		$forum_id = $row['forum_id'];

		$is_anonymous = false;
		if (isset($this->warnings[$row['post_id']]))
		{
			$is_anonymous = ($this->config['aws_allow_hide_moderator'] == 1 || ($this->config['aws_allow_hide_moderator'] == 2 && $this->warnings[$row['post_id']]['hide_mod']));
		}

		$postrow = array_merge($postrow, [
			'WARNING'        => isset($this->warnings[$row['post_id']]) ? bbcode_nl2br($this->warnings[$row['post_id']]['warning'][0]) : '',
			'WARNING_POSTER' => isset($this->warnings[$row['post_id']]) ? (($is_anonymous && !$this->auth->acl_get('m_warn')) ? $this->user->lang['MODERATOR'] : get_username_string('full', $this->warnings[$row['post_id']]['user_id'], $this->warnings[$row['post_id']]['username'], $this->warnings[$row['post_id']]['user_colour'])) : '',
			'WARNING_TIME'   => isset($this->warnings[$row['post_id']]) ? $this->user->format_date($this->warnings[$row['post_id']]['warning_time']) : '',
			'WARNING_TYPE'   => isset($this->warnings[$row['post_id']]) ? $this->warnings[$row['post_id']]['warning_type'] : '',
			'POSTER_BANNED'  => (isset($user_cache['user_ban_id']) && $user_cache['user_ban_id']) ? true : ((isset($this->users_banned[$poster_id])) ? true : false),
			'POSTER_BAN_END' => (isset($user_cache['user_ban_id']) && $user_cache['user_ban_id']) ? $this->user->lang('BANNED_BY_X_WARNINGS', (int) $this->config['warnings_for_ban']) : ((isset($this->users_banned[$poster_id])) ? (($this->users_banned[$poster_id]['ban_end'] > 0) ? sprintf($this->user->lang['BANNED_UNTIL'], $this->user->format_date($this->users_banned[$poster_id]['ban_end'])) : $this->user->lang['BANNED_PERMANENTLY']) : ''),
		]);

		if ($this->config['aws_show_cards'])
		{
			$warnings_count = (int) $user_cache['user_warnings'];
			$is_banned = $postrow['POSTER_BANNED'];

			$postrow = array_merge($postrow, [
				'AWS_ICONS' => $this->get_active_icons($warnings_count, $is_banned),
			]);
		}

		$postrow['U_WARN'] = ($this->auth->acl_get('m_warn') && $poster_id != $this->user->data['user_id'] && $poster_id != ANONYMOUS) ? append_sid("{$this->phpbb_root_path}mcp.$this->php_ext", 'i=-eunaumtenhoid-advancedwarningsystem-mcp-main_module&amp;mode=warn_post&amp;f=' . $forum_id . '&amp;p=' . $post_id, true, $this->user->session_id) : '';

		$event['post_row'] = $postrow;
	}

	public function handle_delete_posts($event)
	{
		$post_ids = $event['post_ids'];

		$sql = 'UPDATE ' . WARNINGS_TABLE . '
			SET post_id = 0
			WHERE ' . $this->db->sql_in_set('post_id', $post_ids);
		$this->db->sql_query($sql);
	}

	/**
	 * Lazy-loads active warnings and banlist data only when needed
	 */
	protected function ensure_warnings_data_loaded()
	{
		if ($this->warnings_loaded)
		{
			return;
		}

		$this->warnings_loaded = true;
		$this->warnings = $this->users_banned = [];

		$sql = 'SELECT w.post_id, w.warning_time, w.warning_end, w.warning_type, w.warning_status, w.warning_hide_mod, l.user_id, l.log_data, l.reportee_id, u.username, u.user_colour
			FROM ' . WARNINGS_TABLE . ' w, ' . LOG_TABLE . ' l, ' . USERS_TABLE . ' u
				WHERE w.warning_status = 1
					AND l.log_id = w.log_id
					AND u.user_id = l.user_id';

		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			if ($row['post_id'])
			{
				$this->warnings[$row['post_id']] = [
					'warning_time' => $row['warning_time'],
					'warning_end'  => $row['warning_end'],
					'warning_type' => $row['warning_type'],
					'user_id'      => $row['user_id'],
					'username'     => $row['username'],
					'user_colour'  => $row['user_colour'],
					'hide_mod'     => $row['warning_hide_mod'],
					'warning'      => unserialize($row['log_data']),
				];
			}
		}
		$this->db->sql_freeresult($result);

		$sql = 'SELECT b.ban_end, u.user_id
			FROM ' . BANLIST_TABLE . ' b, ' . USERS_TABLE . ' u
				WHERE (b.ban_end >= ' . time() . ' OR b.ban_end = 0)
					AND u.user_id = b.ban_userid';

		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->users_banned[$row['user_id']] = [
				'ban_end' => $row['ban_end'],
			];
		}
		$this->db->sql_freeresult($result);
	}

	private function get_active_icons($warnings_count, $is_banned)
	{
		$icons = [];
		for ($i = 1; $i <= 5; $i++)
		{
			$threshold = (isset($this->config['aws_level' . $i . '_threshold'])) ? (int) $this->config['aws_level' . $i . '_threshold'] : 0;
			if ($threshold > 0 && $warnings_count >= $threshold)
			{
				$color = (string) $this->config['aws_level' . $i . '_color'];
				if ($color !== '' && $color[0] !== '#')
				{
					$color = '#' . $color;
				}

				$icons[] = [
					'ICON'  => $this->config['aws_level' . $i . '_icon'],
					'COLOR' => $color,
				];
			}
		}

		if ($is_banned && $this->config['aws_banned_icon'])
		{
			$color = (string) $this->config['aws_banned_color'];
			if ($color !== '' && $color[0] !== '#')
			{
				$color = '#' . $color;
			}

			$icons[] = [
				'ICON'  => $this->config['aws_banned_icon'],
				'COLOR' => $color,
			];
		}

		return $icons;
	}
}
