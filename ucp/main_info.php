<?php
/**
 *
 * Advanced Warnings extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2005 phpBB Group, 2024-2026 eunaumtenhoid
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace eunaumtenhoid\advancedwarningsystem\ucp;

class main_info
{
	public function module()
	{
		return [
			'filename'	=> '\eunaumtenhoid\advancedwarningsystem\ucp\main_module',
			'title'		=> 'UCP_AWS_MAIN',
			'version'	=> '1.0.0',
			'modes'		=> [
				'warning_list'	=> [
					'title' => 'UCP_AWS',
					'auth'  => 'ext_eunaumtenhoid/advancedwarningsystem',
					'cat'   => ['UCP_PROFILE'],
				],
				'appeal'		=> [
					'title'   => 'UCP_AWS_APPEAL',
					'auth'    => 'ext_eunaumtenhoid/advancedwarningsystem',
					'cat'     => ['UCP_PROFILE'],
					'display' => false,
				],
			],
		];
	}
}
