<?php
/**
*
* Advanced Warnings extension for the phpBB Forum Software package.
*
* @copyright (c) 2013 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace eunaumtenhoid\advancedwarningsystem\cron\task;

/**
* Tidy warnings cron task.
*
* @package advancedwarnings
*/
class aws_tidy_warnings extends \phpbb\cron\task\base
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var \phpbb\log\log */
	protected $phpbb_log;

	/**
	* Constructor
	*
	* @param \phpbb\config\config                 $config       Config object
	* @param \phpbb\db\driver\driver_interface    $db           DBAL object
	* @param \phpbb\user                          $user         User object
	* @param \phpbb\cache\driver\driver_interface $cache        Cache driver object
	* @param \phpbb\log\log                       $phpbb_log    Log object
	* @return \eunaumtenhoid\advancedwarningsystem\cron\task\aws_tidy_warnings
	* @access public
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\user $user, \phpbb\cache\driver\driver_interface $cache, \phpbb\log\log $phpbb_log)
	{
		$this->config = $config;
		$this->db = $db;
		$this->user = $user;
		$this->cache = $cache;
		$this->phpbb_log = $phpbb_log;
	}

	/**
	* Runs this cron task.
	*
	* @return null
	*/
	public function run()
	{
		$this->cron_tidy_warnings();
	}

	/**
	* Returns whether this cron task should run now, because enough time
	* has passed since it was last run.
	*
	* The interval between topics tidying is specified in extension
	* configuration.
	*
	* @return bool
	*/
	public function should_run()
	{
		return $this->config['warnings_last_gc'] < time() - $this->config['warnings_gc'];
	}

	/**
	* The main cron task code.
	*/
	public function cron_tidy_warnings($topic_ids = [])
	{
		$warning_list = $user_list = $unban_list = [];

		$current_time = time();

		$sql = 'SELECT * FROM ' . WARNINGS_TABLE . "
			WHERE warning_end < $current_time 
			AND warning_end > 0 
			AND warning_status = 1";
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$warning_list[] = $row['warning_id'];
			$user_list[$row['user_id']] = isset($user_list[$row['user_id']]) ? ($user_list[$row['user_id']] + (int) $row['warning_points']) : (int) $row['warning_points'];
		}
		$this->db->sql_freeresult($result);

		if (sizeof($warning_list))
		{
			$this->db->sql_transaction('begin');

			$sql = 'UPDATE ' . WARNINGS_TABLE . ' SET warning_status = 0
				WHERE ' . $this->db->sql_in_set('warning_id', $warning_list);
			$this->db->sql_query($sql);

			foreach ($user_list as $user_id => $value)
			{
				$sql = 'UPDATE ' . USERS_TABLE . ' SET user_warnings = (CASE WHEN user_warnings >= ' . (int) $value . ' THEN user_warnings - ' . (int) $value . ' ELSE 0 END)
					WHERE user_id = ' . (int) $user_id;
				$this->db->sql_query($sql);
			}

			$this->db->sql_transaction('commit');

			global $phpbb_dispatcher;
			if ($phpbb_dispatcher)
			{
				$phpbb_dispatcher->trigger_event('eunaumtenhoid.advancedwarningsystem.tidy_warnings_after', compact('warning_list', 'user_list'));
			}
		}

		$this->cache->destroy('sql', [WARNINGS_TABLE, BANLIST_TABLE]);
		$this->config->set('warnings_last_gc', time(), true);
	}
}



