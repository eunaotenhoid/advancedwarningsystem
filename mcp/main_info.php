<?php
/**
 *
 * Advanced Warnings extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2013 rxu, 2024-2026 eunaumtenhoid
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace eunaumtenhoid\advancedwarningsystem\mcp;

class main_info
{
	public function module()
	{
		return [
			'filename'	=> '\eunaumtenhoid\advancedwarningsystem\mcp\main_module',
			'title'		=> 'MCP_AWS',
			'version'	=> '1.0.0',
			'modes'		=> [
				'front'		=> ['title' => 'AWS_WARN_FRONT', 'auth' => 'ext_eunaumtenhoid/advancedwarningsystem && aclf_m_warn', 'cat' => ['MCP_WARN']],
				'list'		=> ['title' => 'AWS_WARN_LIST', 'auth' => 'ext_eunaumtenhoid/advancedwarningsystem && aclf_m_warn', 'cat' => ['MCP_WARN']],
				'warn_user'	=> ['title' => 'AWS_WARN_USER', 'auth' => 'ext_eunaumtenhoid/advancedwarningsystem && aclf_m_warn', 'cat' => ['MCP_WARN']],
				'warn_post'	=> ['title' => 'AWS_WARN_POST', 'auth' => 'ext_eunaumtenhoid/advancedwarningsystem && acl_m_warn && acl_f_read,$id', 'cat' => ['MCP_WARN']],
				'appeals'	=> ['title' => 'MCP_WARN_APPEALS', 'auth' => 'ext_eunaumtenhoid/advancedwarningsystem && acl_m_warn', 'cat' => ['MCP_WARN']],
			],
		];
	}
}
