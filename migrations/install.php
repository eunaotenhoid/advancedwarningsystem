<?php
/**
 *
 * Advanced Warnings extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2024-2026 eunaumtenhoid
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace eunaumtenhoid\advancedwarningsystem\migrations;

class install extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return ['\phpbb\db\migration\data\v310\gold'];
	}

	public function update_schema()
	{
		return [
			'add_tables' => [
				$this->table_prefix . 'aws_warning_rules' => [
					'COLUMNS' => [
						'rule_id'					=> ['UINT', null, 'auto_increment'],
						'rule_title'				=> ['VCHAR:255', ''],
						'rule_description'			=> ['TEXT_UNI', ''],
						'rule_duration'				=> ['USINT', 0],
						'rule_points'				=> ['USINT', 0],
						'rule_is_warning'			=> ['BOOL', 0],
						'rule_is_locked'			=> ['BOOL', 0],
						'rule_allow_appeal'			=> ['BOOL', 1],
						'rule_appeal_convert_to'	=> ['VCHAR:255', ''],
						'rule_order'				=> ['UINT', 0],
					],
					'PRIMARY_KEY' => 'rule_id',
				],
			],
			'add_columns' => [
				$this->table_prefix . 'users' => [
					'user_ban_id'				=> ['BOOL', 0],
				],
				$this->table_prefix . 'warnings' => [
					'warning_points'			=> ['USINT', 0],
					'warning_hide_mod'			=> ['BOOL', 0],
					'warning_end'				=> ['INT:11', 0],
					'warning_type'				=> ['BOOL', 0],
					'warning_status'			=> ['BOOL', 0],
					'warning_appeal_status'		=> ['UINT', 0],
					'warning_appeal_reason'		=> ['TEXT_UNI', null],
					'warning_appeal_time'		=> ['INT:11', 0],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_tables' => [
				$this->table_prefix . 'aws_warning_rules',
			],
			'drop_columns' => [
				$this->table_prefix . 'users' => [
					'user_ban_id',
				],
				$this->table_prefix . 'warnings' => [
					'warning_points',
					'warning_hide_mod',
					'warning_end',
					'warning_type',
					'warning_status',
					'warning_appeal_status',
					'warning_appeal_reason',
					'warning_appeal_time',
				],
			],
		];
	}

	public function update_data()
	{
		return [
			// Configs
			['config.add', ['aws_pm_subject', 'You have received a warning']],
			['config.add', ['aws_pm_body_user', "Hello {USERNAME},\n\nYou have received a warning for: {RULE_TYPE}.\n\nDetails: {RULE_DESCRIPTION}"]],
			['config.add', ['aws_pm_body_post', "Hello {USERNAME},\n\nYou have received a warning for the following post: {POST_URL}.\n\nType: {RULE_TYPE}\nDetails: {RULE_DESCRIPTION}"]],
			['config.add', ['aws_pm_sender', 0]],
			['config.add', ['aws_allow_custom', 0]],
			['config.add', ['aws_allow_hide_moderator', 2]],
			['config.add', ['aws_rules_url', '']],
			['config.add', ['aws_show_cards', 1]],
			['config.add', ['aws_enable_appeals', 0]],
			['config.update', ['warnings_for_ban', 0]],

			// Levels thresholds, icons, colors and points
			['config.add', ['aws_level1_threshold', 1]],
			['config.add', ['aws_level1_icon', 'fa-exclamation-triangle']],
			['config.add', ['aws_level1_color', '#FFD700']],
			['config.add', ['aws_level1_points', 0]],

			['config.add', ['aws_level2_threshold', 2]],
			['config.add', ['aws_level2_icon', 'fa-exclamation-circle']],
			['config.add', ['aws_level2_color', '#FFA500']],
			['config.add', ['aws_level2_points', 0]],

			['config.add', ['aws_level3_threshold', 3]],
			['config.add', ['aws_level3_icon', 'fa-exclamation-circle']],
			['config.add', ['aws_level3_color', '#FF8C00']],
			['config.add', ['aws_level3_points', 0]],

			['config.add', ['aws_level4_threshold', 4]],
			['config.add', ['aws_level4_icon', 'fa-times-circle']],
			['config.add', ['aws_level4_color', '#FF4500']],
			['config.add', ['aws_level4_points', 0]],

			['config.add', ['aws_level5_threshold', 5]],
			['config.add', ['aws_level5_icon', 'fa-times-circle']],
			['config.add', ['aws_level5_color', '#FF0000']],
			['config.add', ['aws_level5_points', 0]],

			['config.add', ['aws_banned_icon', 'fa-ban']],
			['config.add', ['aws_banned_color', '#000000']],

			// Default Rules
			['custom', [[$this, 'insert_default_rules']]],

			// Convert existing native warnings if any
			['custom', [[$this, 'convert_existing_warnings']]],

			// Versioning
			['config.add', ['aws_version', '1.0.0']],
		];
	}

	public function revert_data()
	{
		return [
			['custom', [[$this, 'resync_user_warnings_to_counts']]],
		];
	}

	/**
	 * Insert default rules during installation
	 */
	public function insert_default_rules()
	{
		// First rule: Alert
		$sql = 'INSERT INTO ' . $this->table_prefix . "aws_warning_rules 
			(rule_title, rule_description, rule_duration, rule_points, rule_is_warning, rule_is_locked, rule_allow_appeal, rule_order)
			VALUES ('Alert', 'Just an alert', 0, 0, 1, 1, 0, 10)";
		$this->db->sql_query($sql);

		// Second rule: Warning
		$sql = 'INSERT INTO ' . $this->table_prefix . "aws_warning_rules 
			(rule_title, rule_description, rule_duration, rule_points, rule_is_warning, rule_is_locked, rule_allow_appeal, rule_order)
			VALUES ('Warning', '', 0, 1, 1, 1, 0, 20)";
		$this->db->sql_query($sql);
	}

	/**
	 * Convert existing native phpBB warnings to AWS format
	 */
	public function convert_existing_warnings()
	{
		$now = time();
		$expire_days = isset($this->config['warnings_expire_days']) ? (int) $this->config['warnings_expire_days'] : 0;
		$expire_seconds = $expire_days * 86400;

		// 1. Set warning_points = 1 and compute warning_end for any existing warnings with 0 points
		if ($expire_days > 0)
		{
			$sql = 'UPDATE ' . $this->table_prefix . 'warnings
				SET warning_points = 1,
					warning_end = warning_time + ' . (int) $expire_seconds . '
				WHERE warning_points = 0';
			$this->db->sql_query($sql);

			$sql = 'UPDATE ' . $this->table_prefix . 'warnings
				SET warning_status = CASE WHEN warning_end > ' . (int) $now . ' THEN 1 ELSE 0 END
				WHERE warning_points = 1';
			$this->db->sql_query($sql);
		}
		else
		{
			// Permanent warnings
			$sql = 'UPDATE ' . $this->table_prefix . 'warnings
				SET warning_points = 1,
					warning_end = 0,
					warning_status = 1
				WHERE warning_points = 0';
			$this->db->sql_query($sql);
		}

		// 2. Resync active user_warnings total points
		$this->resync_user_warnings_to_points();
	}

	/**
	 * Calculate user_warnings based on active warning points
	 */
	public function resync_user_warnings_to_points()
	{
		$sql = 'UPDATE ' . USERS_TABLE . ' SET user_warnings = 0';
		$this->db->sql_query($sql);

		$sql = 'SELECT user_id, SUM(warning_points) AS total_points
			FROM ' . $this->table_prefix . 'warnings
			WHERE warning_status = 1
			GROUP BY user_id';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$sql = 'UPDATE ' . USERS_TABLE . '
				SET user_warnings = ' . (int) $row['total_points'] . '
				WHERE user_id = ' . (int) $row['user_id'];
			$this->db->sql_query($sql);
		}
		$this->db->sql_freeresult($result);
	}

	/**
	 * Restore user_warnings column to plain counts upon uninstallation
	 */
	public function resync_user_warnings_to_counts()
	{
		$sql = 'UPDATE ' . USERS_TABLE . ' SET user_warnings = 0';
		$this->db->sql_query($sql);

		$sql = 'SELECT user_id, COUNT(warning_id) AS total_warnings
			FROM ' . $this->table_prefix . 'warnings
			GROUP BY user_id';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$sql = 'UPDATE ' . USERS_TABLE . '
				SET user_warnings = ' . (int) $row['total_warnings'] . '
				WHERE user_id = ' . (int) $row['user_id'];
			$this->db->sql_query($sql);
		}
		$this->db->sql_freeresult($result);
	}
}
