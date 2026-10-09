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
 * ACP Event listener
 */
class acp_listener implements EventSubscriberInterface
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

	/** @var string phpbb_root_path */
	protected $phpbb_root_path;

	/** @var string phpEx */
	protected $php_ext;

	/** @var string table_prefix */
	protected $table_prefix;

	/** @var \phpbb\extension\manager */
	protected $ext_manager;

	/** @var string rules_table */
	protected $rules_table;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config              $config           Config object
	 * @param \phpbb\db\driver\driver_interface $db               DBAL object
	 * @param \phpbb\db\tools\tools_interface   $db_tools         DB tools object
	 * @param \phpbb\template\template          $template         Template object
	 * @param \phpbb\user                       $user             User object
	 * @param \phpbb\request\request            $request          Request object
	 * @param string                            $phpbb_root_path  phpbb_root_path
	 * @param string                            $php_ext          phpEx
	 * @param string                            $table_prefix     table_prefix
	 * @param \phpbb\extension\manager          $ext_manager      Extension manager
	 * @param string                            $rules_table      Rules table name
	 */
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
		\phpbb\extension\manager $ext_manager,
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
		$this->ext_manager = $ext_manager;
		$this->rules_table = $rules_table ?: $this->table_prefix . 'aws_warning_rules';
	}

	public static function getSubscribedEvents()
	{
		return [
			'core.acp_board_config_edit_add' => ['add_acp_config', -2],
			'core.adm_page_header'           => 'add_acp_lang',
			'core.modify_module_row'         => 'modify_extra_url',
		];
	}

	public function add_acp_lang($event)
	{
		$this->user->add_lang_ext('eunaumtenhoid/advancedwarningsystem', ['info_mcp_aws', 'info_acp_aws']);
	}

	public function modify_extra_url($event)
	{
		$row = $event['row'];
		$module_row = $event['module_row'];

		if ($row['module_basename'] == '\eunaumtenhoid\advancedwarningsystem\mcp\main_module')
		{
			$url_func = 'phpbb_module_warn_url';
			$module_row['url_extra'] = (function_exists($url_func)) ? $url_func($row['module_mode'], $row) : '';
			$event['module_row'] = $module_row;
		}
	}

	public function add_acp_config($event)
	{
		$this->user->add_lang_ext('eunaumtenhoid/advancedwarningsystem', ['common', 'acp', 'info_acp_aws']);

		$mode = $event['mode'];
		$display_vars = $event['display_vars'];

		if (in_array($mode, ['settings', 'warnings']))
		{
			$sender_id = (int) (isset($this->config['aws_pm_sender']) ? $this->config['aws_pm_sender'] : 0);
			$sender_name = '';
			if ($sender_id > 0)
			{
				$sql = 'SELECT username, user_colour FROM ' . USERS_TABLE . ' WHERE user_id = ' . (int) $sender_id;
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);
				if ($row)
				{
					$sender_name = ' <strong>[' . get_username_string('full', $sender_id, $row['username'], $row['user_colour']) . ']</strong>';
				}
			}

			// Fetch rules to show them in the same block
			$rules_html = '';
			$rules_data = [];

			// Safe cross-database table existence check
			if ($this->db_tools->sql_table_exists($this->rules_table))
			{
				$sql = 'SELECT * FROM ' . $this->rules_table . ' ORDER BY rule_order ASC, rule_id ASC';
				$result = $this->db->sql_query($sql);
				$rules_data = $this->db->sql_fetchrowset($result);
				$this->db->sql_freeresult($result);
			}

			$total_rules = count($rules_data);
			$has_rules = ($total_rules > 0);

			$rules_html .= '<table class="table1" style="margin-top: 10px;"><thead><tr><th>' . $this->user->lang['RULE_TITLE'] . '</th><th>' . $this->user->lang['RULE_DURATION'] . '</th><th style="text-align:center; width: 120px;">' . $this->user->lang['ACTION'] . '</th></tr></thead><tbody>';

			foreach ($rules_data as $i => $row)
			{
				$u_edit = append_sid($this->phpbb_root_path . 'adm/index.' . $this->php_ext, 'i=-eunaumtenhoid-advancedwarningsystem-acp-main_module&amp;mode=main&amp;action=edit&amp;id=' . $row['rule_id']);
				$u_delete = append_sid($this->phpbb_root_path . 'adm/index.' . $this->php_ext, 'i=-eunaumtenhoid-advancedwarningsystem-acp-main_module&amp;mode=main&amp;action=delete&amp;id=' . $row['rule_id']);
				$u_move_up = append_sid($this->phpbb_root_path . 'adm/index.' . $this->php_ext, 'i=-eunaumtenhoid-advancedwarningsystem-acp-main_module&amp;mode=main&amp;action=move_up&amp;id=' . $row['rule_id']);
				$u_move_down = append_sid($this->phpbb_root_path . 'adm/index.' . $this->php_ext, 'i=-eunaumtenhoid-advancedwarningsystem-acp-main_module&amp;mode=main&amp;action=move_down&amp;id=' . $row['rule_id']);

				$icon_up_disabled = '<span class="up-disabled" style="display:none;"><i class="icon fa-chevron-up fa-fw" style="color: #ccc; cursor: not-allowed; opacity: 0.5;"></i></span>';
				$icon_up_enabled  = '<span class="up"><a href="' . $u_move_up . '" data-ajax="row_up"><i class="icon fa-chevron-up fa-fw" style="color: #225985;"></i></a></span>';

				$icon_down_disabled = '<span class="down-disabled" style="display:none;"><i class="icon fa-chevron-down fa-fw" style="color: #ccc; cursor: not-allowed; opacity: 0.5;"></i></span>';
				$icon_down_enabled  = '<span class="down"><a href="' . $u_move_down . '" data-ajax="row_down"><i class="icon fa-chevron-down fa-fw" style="color: #225985;"></i></a></span>';

				$rules_html .= '<tr id="rule_' . $row['rule_id'] . '">
					<td style="vertical-align: middle;">' . htmlspecialchars($row['rule_title'], ENT_QUOTES, 'UTF-8') . '</td>
					<td style="vertical-align: middle;">' . ($row['rule_duration'] ? $row['rule_duration'] . ' ' . $this->user->lang['DAYS'] : $this->user->lang['PERMANENT']) . '</td>
					<td class="actions" style="text-align:center; white-space: nowrap; width: 100px;">
						' . $icon_up_disabled . $icon_up_enabled . '&nbsp;' . $icon_down_disabled . $icon_down_enabled . '&nbsp;
						<a href="' . $u_edit . '" title="' . $this->user->lang['EDIT'] . '"><i class="icon fa-gear fa-fw" style="color: #228822; font-size: 1.8em;" aria-hidden="true"></i></a>&nbsp;
						<a href="' . $u_delete . '" title="' . $this->user->lang['DELETE'] . '" data-ajax="row_delete"><i class="icon fa-times fa-fw" style="color: #bc2a4d; font-size: 1.8em;" aria-hidden="true"></i></a>
					</td>
				</tr>';
			}

			if (!$has_rules)
			{
				$rules_html .= '<tr><td colspan="3">' . $this->user->lang['NO_ENTRIES'] . '</td></tr>';
			}
			$rules_html .= '</tbody></table>';
			$rules_html .= '<div class="aws-rules-wrapper" style="text-align: left;"><br /><a href="' . append_sid($this->phpbb_root_path . 'adm/index.' . $this->php_ext, 'i=-eunaumtenhoid-advancedwarningsystem-acp-main_module&amp;mode=main&amp;action=add') . '" class="button2">' . $this->user->lang['ADD_RULE'] . '</a>';
			$rules_html .= '&nbsp; <a href="' . append_sid($this->phpbb_root_path . 'adm/index.' . $this->php_ext, 'i=-eunaumtenhoid-advancedwarningsystem-acp-main_module&amp;mode=main&amp;action=resync') . '" class="button2">' . $this->user->lang['AWS_RESYNC'] . '</a>';
			$rules_html .= '&nbsp; <a href="' . append_sid($this->phpbb_root_path . 'adm/index.' . $this->php_ext, 'i=-eunaumtenhoid-advancedwarningsystem-acp-main_module&amp;mode=main') . '" class="button2">' . $this->user->lang['ACP_WARN_RULES'] . '</a></div>';

			// Build configuration array
			$hide_mod_val = (int) (isset($this->config['aws_allow_hide_moderator']) ? $this->config['aws_allow_hide_moderator'] : 2);
			$hide_mod_html = '<label><input type="radio" class="radio" name="config[aws_allow_hide_moderator]" value="1"' . (($hide_mod_val === 1) ? ' checked="checked"' : '') . ' /> ' . $this->user->lang['AWS_HIDE_MOD_YES'] . '</label> ';
			$hide_mod_html .= '<label><input type="radio" class="radio" name="config[aws_allow_hide_moderator]" value="0"' . (($hide_mod_val === 0) ? ' checked="checked"' : '') . ' /> ' . $this->user->lang['AWS_HIDE_MOD_NO'] . '</label> ';
			$hide_mod_html .= '<label><input type="radio" class="radio" name="config[aws_allow_hide_moderator]" value="2"' . (($hide_mod_val === 2) ? ' checked="checked"' : '') . ' /> ' . $this->user->lang['AWS_HIDE_MOD_CHOICE'] . '</label>';
			$hide_mod_html .= '<style>#aws_allow_hide_moderator { display: none; }</style>';

			$warnings_for_ban = [
				'aws_allow_custom' => ['lang' => 'AWS_ALLOW_CUSTOM', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true],
				'aws_allow_hide_moderator' => ['lang' => 'AWS_ALLOW_HIDE_MODERATOR', 'validate' => 'int', 'type' => 'text:1:1', 'explain' => true, 'append' => $hide_mod_html],
				'aws_rules_url' => ['lang' => 'AWS_RULES_URL', 'validate' => 'string', 'type' => 'text:40:255', 'explain' => true],
				'aws_show_cards' => ['lang' => 'AWS_SHOW_CARDS', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true],
				'aws_enable_appeals' => ['lang' => 'AWS_ENABLE_APPEALS', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true],
				'warnings_for_ban' => [
					'lang' => 'WARNINGS_FOR_BAN',
					'validate' => 'int',
					'type' => 'text:3:3',
					'explain' => '<strong><hr />' . $this->user->lang['AWS_BANNED_SET'] . '</strong><br />' . $this->user->lang['WARNINGS_FOR_BAN_EXPLAIN'],
					'append' => ' ' . $this->user->lang['AWS_LEVEL_ICON'] . ' <input type="text" name="aws_banned_icon" id="aws_banned_icon" value="' . (isset($this->config['aws_banned_icon']) ? htmlspecialchars($this->config['aws_banned_icon'], ENT_QUOTES, 'UTF-8') : '') . '" size="20" class="inputbox autowidth" /> ' . $this->user->lang['AWS_LEVEL_COLOR'] . ' <input type="text" name="aws_banned_color" id="aws_banned_color" value="' . (isset($this->config['aws_banned_color']) ? htmlspecialchars($this->config['aws_banned_color'], ENT_QUOTES, 'UTF-8') : '') . '" size="10" class="inputbox autowidth" />'
				],
			];

			$points_enabled = $this->ext_manager->is_enabled('dmzx/ultimatepoints') || $this->ext_manager->is_enabled('phpbbstudio/aps');

			for ($i = 1; $i <= 5; $i++)
			{
				$icon_key = 'aws_level' . $i . '_icon';
				$color_key = 'aws_level' . $i . '_color';
				$points_key = 'aws_level' . $i . '_points';

				$append_html = ' ' . $this->user->lang['AWS_LEVEL_ICON'] . ' <input type="text" name="' . $icon_key . '" id="' . $icon_key . '" value="' . (isset($this->config[$icon_key]) ? htmlspecialchars($this->config[$icon_key], ENT_QUOTES, 'UTF-8') : '') . '" size="20" class="inputbox autowidth" /> ' . $this->user->lang['AWS_LEVEL_COLOR'] . ' <input type="text" name="' . $color_key . '" id="' . $color_key . '" value="' . (isset($this->config[$color_key]) ? htmlspecialchars($this->config[$color_key], ENT_QUOTES, 'UTF-8') : '') . '" size="10" class="inputbox autowidth" />';

				if ($points_enabled)
				{
					$append_html .= ' ' . $this->user->lang['AWS_LEVEL_POINTS'] . ' <input type="text" name="' . $points_key . '" id="' . $points_key . '" value="' . (isset($this->config[$points_key]) ? (int) $this->config[$points_key] : 0) . '" size="5" class="inputbox autowidth" />';
				}

				$warnings_for_ban['aws_level' . $i . '_threshold'] = [
					'lang' => 'AWS_LEVEL_SET',
					'validate' => 'int',
					'type' => 'text:3:3',
					'explain' => ($i == 1),
					'lang_params' => [$i],
					'append' => $append_html
				];
			}

			// Add the Rules Table at the bottom
			$warnings_for_ban['aws_manage_rules'] = ['lang' => 'ACP_WARN_RULES', 'type' => 'text:1:1', 'explain' => true, 'append' => $rules_html . '<style>#aws_manage_rules { display: none; }</style>'];

			// PM Settings Legend and Fields
			$warnings_for_ban['legend_pm'] = 'AWS_PM_SETTINGS';
			$warnings_for_ban['aws_pm_sender'] = ['lang' => 'AWS_PM_SENDER', 'validate' => 'int', 'type' => 'text:5:10', 'explain' => true, 'append' => $sender_name];
			$warnings_for_ban['aws_pm_subject'] = ['lang' => 'AWS_PM_SUBJECT', 'validate' => 'string', 'type' => 'text:40:255', 'explain' => false];
			$warnings_for_ban['aws_pm_body_user'] = ['lang' => 'AWS_PM_BODY_USER', 'validate' => 'string', 'type' => 'textarea:8:40', 'explain' => true, 'append' => '<br />' . $this->user->lang['AWS_PM_VARIABLES']];
			$warnings_for_ban['aws_pm_body_post'] = ['lang' => 'AWS_PM_BODY_POST', 'validate' => 'string', 'type' => 'textarea:8:40', 'explain' => true, 'append' => '<br />' . $this->user->lang['AWS_PM_VARIABLES']];

			// Saving logic for custom appended fields
			if ($this->request->is_set_post('submit'))
			{
				for ($i = 1; $i <= 5; $i++)
				{
					$icon_key = 'aws_level' . $i . '_icon';
					$color_key = 'aws_level' . $i . '_color';

					if ($this->request->is_set_post($icon_key))
					{
						$this->config->set($icon_key, $this->request->variable($icon_key, '', true));
					}

					if ($this->request->is_set_post($color_key))
					{
						$this->config->set($color_key, $this->request->variable($color_key, '', true));
					}

					$points_key = 'aws_level' . $i . '_points';
					if ($this->request->is_set_post($points_key))
					{
						$this->config->set($points_key, $this->request->variable($points_key, 0));
					}
				}

				if ($this->request->is_set_post('aws_banned_icon'))
				{
					$this->config->set('aws_banned_icon', $this->request->variable('aws_banned_icon', '', true));
				}

				if ($this->request->is_set_post('aws_banned_color'))
				{
					$this->config->set('aws_banned_color', $this->request->variable('aws_banned_color', '', true));
				}
			}

			if (isset($display_vars['vars']['warnings_expire_days']))
			{
				$display_vars['vars'] = phpbb_insert_config_array($display_vars['vars'], $warnings_for_ban, ['before' => 'warnings_expire_days']);
				unset($display_vars['vars']['warnings_expire_days']);
			}
			else
			{
				$display_vars['vars'] = array_merge($display_vars['vars'], $warnings_for_ban);
			}
			$event['display_vars'] = $display_vars;
		}
	}
}
