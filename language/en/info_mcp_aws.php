<?php
/**
 *
 * info_mcp_warnings [English]
 *
 * @package language
 * @version $Id$
 * @copyright (c) 2013 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

/**
 * @ignore
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

$lang = array_merge($lang, [

	'AWS_WARN_FRONT'	=> 'Front page',
	'AWS_WARN_LIST'		=> 'Warnings list',
	'AWS_WARN_USER'		=> 'Warn user',
	'AWS_WARN_POST'		=> 'Warn for post',
	'MCP_WARN_APPEALS'	=> 'Appeals',

	'SELECT_RULE'		=> 'Select Predefined Rule',
	'CUSTOM_RULE'		=> 'Custom Rule',
	'NEVER_EXPIRE'		=> 'Never expires',
	'SEND_AS_ME'		=> 'Send as me',
	'SEND_AS_ME_EXPLAIN' => 'If checked, you will appear as the sender of the PM, ignoring the global setting.',
	'HIDE_MODERATOR'	=> 'Hide my name',
	'HIDE_MODERATOR_EXPLAIN' => 'If checked, your name will not appear as the person who issued the warning.',
	'MODERATOR'			=> 'Moderator',

	'LOG_USER_WARNING_BODY_AWS'		=> 'This user received the following Warning [%1$s]<br />» Rule: %2$s',
	'RULE_POINTS'					=> 'Points',

	// Appeal Logs
	'LOG_APPEAL_REQUESTED'		=> '<strong>Appeal request submitted</strong><br />» Warning: %1$s',
	'LOG_APPEAL_APPROVED'		=> '<strong>Appeal request APPROVED</strong><br />» User: %1$s<br />» Moderator: %2$s<br />» Warning: %3$s ID #%4$d',
	'LOG_APPEAL_DENIED'			=> '<strong>Appeal request DENIED</strong><br />» User: %1$s<br />» Moderator: %2$s<br />» Warning: %3$s ID #%4$d',
	'LOG_AWS_WARNING_EDITED'	=> '<strong>Warning edited</strong><br />» User: %1$s<br />» Moderator: %2$s<br />» Warning: %3$s ID #%4$d',
]);
