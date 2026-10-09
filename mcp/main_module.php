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

/**
 * MCP Module Dispatcher
 */
class main_module
{
	/** @var \Symfony\Component\DependencyInjection\ContainerInterface */
	protected $container;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\request\request */
	protected $request;

	public $p_master;
	public $u_action;
	public $page_title;
	public $tpl_name;

	/**
	 * Constructor
	 */
	public function __construct(
		$config = null,
		$db = null,
		$auth = null,
		$template = null,
		$user = null,
		$request = null,
		$phpbb_log = null,
		$cache = null,
		$container = null,
		$phpbb_root_path = null,
		$php_ext = null,
		$table_prefix = null
	) {
		global $phpbb_container;

		$this->container = $container ?: $phpbb_container;
		$this->user = $user ?: $this->container->get('user');
		$this->request = $request ?: $this->container->get('request');

		// Handle phpBB passing p_master as first arg if loaded legacy style
		if (is_object($config) && !($config instanceof \phpbb\config\config))
		{
			$this->p_master = $config;
			$this->u_action = isset($this->p_master->u_action) ? $this->p_master->u_action : '';
		}
	}

	/**
	 * Master function
	 */
	public function main($id, $mode)
	{
		$action = $this->request->variable('action', '');
		if (!$action)
		{
			$action = $this->request->variable('action', ['' => ''], true);
			if (is_array($action))
			{
				$action = key($action);
			}
		}

		$this->page_title = 'MCP_WARN';

		add_form_key('mcp_warn');

		$this->user->add_lang('acp/ban');
		$this->user->add_lang_ext('eunaumtenhoid/advancedwarningsystem', ['common', 'mcp', 'info_mcp_aws', 'info_acp_aws']);

		switch ($mode)
		{
			case 'front':
				$this->tpl_name = 'mcp_warn_front';
				$controller = $this->container->get('eunaumtenhoid.advancedwarningsystem.controller.mcp_aws_front');
				$controller->handle($action, $this->u_action);
			break;

			case 'list':
				$this->tpl_name = 'mcp_aws_warnings_warn_list';
				$controller = $this->container->get('eunaumtenhoid.advancedwarningsystem.controller.mcp_aws_list');
				$controller->handle($action, $this->u_action);
			break;

			case 'warn_post':
				$this->tpl_name = 'mcp_aws_warnings_warn_post';
				$controller = $this->container->get('eunaumtenhoid.advancedwarningsystem.controller.mcp_aws_warn_post');
				$controller->handle($action, $this->u_action, $this->p_master);
			break;

			case 'warn_user':
				$this->tpl_name = 'mcp_aws_warnings_warn_user';
				$controller = $this->container->get('eunaumtenhoid.advancedwarningsystem.controller.mcp_aws_warn_user');
				$controller->handle($action, $this->u_action, $this->p_master);
			break;

			case 'appeals':
				$this->tpl_name = 'mcp_aws_warnings_appeals';
				$controller = $this->container->get('eunaumtenhoid.advancedwarningsystem.controller.mcp_aws_appeals');
				$controller->handle($action, $this->u_action);
			break;
		}
	}
}
