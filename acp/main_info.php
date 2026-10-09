<?php
/**
*
* Advanced Warnings extension for the phpBB Forum Software package.
*
* @copyright (c) 2013 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* */

namespace eunaumtenhoid\advancedwarningsystem\acp;

class main_info
{
	function module()
	{
		return [
			'filename'	=> '\eunaumtenhoid\advancedwarningsystem\acp\main_module',
			'title'		=> 'ACP_WARN_RULES',
			'version'	=> '1.0.0',
			'modes'		=> [
				'main'		=> ['title' => 'ACP_WARN_RULES', 'auth' => 'acl_a_board', 'cat' => ['ACP_BOARD_CONFIGURATION'], 'display' => true],
			],
		];
	}
}
