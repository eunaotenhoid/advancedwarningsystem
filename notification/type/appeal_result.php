<?php
/**
 *
 * Advanced Warnings extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2024 eunaumtenhoid
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace eunaumtenhoid\advancedwarningsystem\notification\type;

/**
 * Appeal result notification type.
 */
class appeal_result extends \phpbb\notification\type\base
{
	/** @var \phpbb\config\config */
	protected $config;

	/**
	 * Set config
	 *
	 * @param \phpbb\config\config $config
	 */
	public function set_config(\phpbb\config\config $config)
	{
		$this->config = $config;
	}

	/**
	 * Get the notification type name
	 *
	 * @return string
	 */
	public function get_type()
	{
		return 'eunaumtenhoid.advancedwarningsystem.notification.type.appeal_result';
	}

	/**
	 * Notification option data
	 *
	 * @var array
	 */
	public static $notification_option = [
		'id'    => 'eunaumtenhoid.advancedwarningsystem.notification.type.appeal_result',
		'lang'  => 'NOTIFICATION_TYPE_AWS_APPEAL',
		'group' => 'NOTIFICATION_GROUP_MISCELLANEOUS',
	];

	/**
	 * Is this notification available?
	 *
	 * @return bool
	 */
	public function is_available()
	{
		return (bool) $this->config['aws_enable_appeals'];
	}

	/**
	 * Get the item id
	 *
	 * @param array $data Notification data
	 * @return int
	 */
	public static function get_item_id($data)
	{
		return (int) $data['warning_id'];
	}

	/**
	 * Get the item parent id
	 *
	 * @param array $data Notification data
	 * @return int
	 */
	public static function get_item_parent_id($data)
	{
		return 0;
	}

	/**
	 * Find users for notification
	 *
	 * @param array $data Notification data
	 * @param array $pre_create_data Pre-create data
	 * @return array
	 */
	public function find_users_for_notification($data, $pre_create_data = [])
	{
		return $this->check_user_notification_options([$data['user_id']], $pre_create_data);
	}

	/**
	 * Get total number of users for this notification
	 *
	 * @return int
	 */
	public function get_users_count()
	{
		return 1;
	}

	/**
	 * Get the notification title
	 *
	 * @return string
	 */
	public function get_title()
	{
		$action = ($this->get_data('action') == 'approve') ? 'APPROVED' : 'DENIED';
		return $this->language->lang('NOTIFICATION_AWS_APPEAL_' . $action, $this->get_data('warning_title'), (int) $this->get_data('warning_id'));
	}

	/**
	 * Get the notification URL
	 *
	 * @return string
	 */
	public function get_url()
	{
		return append_sid($this->phpbb_root_path . 'ucp.' . $this->php_ext, 'i=-eunaumtenhoid-advancedwarningsystem-ucp-main_module&mode=warning_list');
	}

	/**
	 * Create insertion array
	 *
	 * @param array $data Notification data
	 * @return void
	 */
	public function create_insert_array($data, $pre_create_data = [])
	{
		$this->set_data('warning_id', (int) $data['warning_id']);
		$this->set_data('warning_title', (string) $data['warning_title']);
		$this->set_data('action', (string) $data['action']);
		$this->set_data('moderator_id', (int) $data['moderator_id']);
		$this->set_data('user_id', (int) $data['user_id']);

		parent::create_insert_array($data, $pre_create_data);
	}

	/**
	 * Get the email template
	 *
	 * @return string|bool
	 */
	public function get_email_template()
	{
		return false;
	}

	/**
	 * Get the email template variables
	 *
	 * @return array
	 */
	public function get_email_template_variables()
	{
		return [];
	}

	/**
	 * Get the redirect URL
	 *
	 * @return string
	 */
	public function get_redirect_url()
	{
		return $this->get_url();
	}

	/**
	 * Users to query
	 *
	 * @return array
	 */
	public function users_to_query()
	{
		return [];
	}
}
