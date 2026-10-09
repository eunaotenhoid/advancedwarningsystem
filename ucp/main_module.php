<?php
/**
 *
 * Advanced Warnings extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2013 rxu, 2024-2026 eunaumtenhoid
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace eunaumtenhoid\advancedwarningsystem\ucp;

if (!defined('IN_PHPBB'))
{
	exit;
}

/**
 * UCP Module Dispatcher
 */
class main_module
{
	public $u_action;
	public $tpl_name;
	public $page_title;

	public function main($id, $mode)
	{
		global $phpbb_container, $user;

		$user->add_lang_ext('eunaumtenhoid/advancedwarningsystem', ['common', 'info_ucp_aws']);

		switch ($mode)
		{
			case 'appeal':
				$this->page_title = 'UCP_AWS_APPEAL';
				$controller = $phpbb_container->get('eunaumtenhoid.advancedwarningsystem.controller.ucp_aws_appeal');
				$controller->handle($this->u_action);
			break;

			case 'warning_list':
			default:
				$this->tpl_name = 'ucp_aws_warnings_list';
				$this->page_title = 'UCP_AWS_WARNINGS';
				$controller = $phpbb_container->get('eunaumtenhoid.advancedwarningsystem.controller.ucp_aws_warning_list');
				$controller->handle($this->u_action);
			break;
		}
	}
}
