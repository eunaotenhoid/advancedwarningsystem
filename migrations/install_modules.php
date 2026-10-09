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

class install_modules extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return ['\eunaumtenhoid\advancedwarningsystem\migrations\install'];
	}

	public function update_data()
	{
		return [
			// ACP module
			['module.add', ['acp', 'ACP_BOARD_CONFIGURATION', [
				'module_basename'	=> '\eunaumtenhoid\advancedwarningsystem\acp\main_module',
				'module_langname'	=> 'ACP_WARN_RULES',
				'module_mode'		=> 'main',
				'module_auth'		=> 'ext_eunaumtenhoid/advancedwarningsystem && acl_a_board',
				'module_display'	=> 0,
			]]],

			// MCP modules
			['module.add', ['mcp', 'MCP_WARN', [
				'module_basename'	=> '\eunaumtenhoid\advancedwarningsystem\mcp\main_module',
				'module_auth'		=> 'ext_eunaumtenhoid/advancedwarningsystem',
				'modes'				=> ['front', 'list', 'warn_user', 'warn_post', 'appeals'],
			]]],

			// UCP module
			['module.add', ['ucp', 'UCP_PROFILE', [
				'module_basename'	=> '\eunaumtenhoid\advancedwarningsystem\ucp\main_module',
				'module_auth'		=> 'ext_eunaumtenhoid/advancedwarningsystem',
				'modes'				=> ['warning_list', 'appeal'],
			]]],
		];
	}
}
