<?php
/**
 *
 * Advanced Warnings extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2013 rxu, 2024-2026 eunaumtenhoid
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
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
	'CANNOT_WARN_FOUNDER'	=> 'You cannot warn founder.',
	'EDIT_WARNING'			=> 'Edit warning',
	'USER_WARNING_EDITED'	=> 'Warning edited successfully.',
	'LENGTH_WARNING_INVALID' => 'The date has to be formatted <kbd>YYYY-MM-DD</kbd>.',
	'LIST_WARNINGS'			=> [
		1 => '%d warning',
		2 => '%d warnings',
	],

	'SELECT_RULE'			=> 'Select Predefined Rule',
	'NO_RULE_SELECTED'		=> 'You must select a warning rule before submitting.',
	'CUSTOM_RULE'			=> 'Custom Rule',
	'NEVER_EXPIRE'			=> 'Never expires',
	'SEND_AS_ME'			=> 'Send as me',
	'SEND_AS_ME_EXPLAIN'	=> 'If checked, you will appear as the sender of the PM, ignoring the global setting.',
	'HIDE_MODERATOR'		=> 'Hide my name',
	'HIDE_MODERATOR_EXPLAIN' => 'If checked, your name will not appear as the person who issued the warning.',
	'MODERATOR'				=> 'Moderator',
	'RULE_POINTS'			=> 'Points',

	'APPROVE'				=> 'Approve',
	'DENY'					=> 'Deny',
	'APPEALS_LIST'			=> 'Appeals List',
	'CONFIRM_APPROVE_APPEAL' => 'Are you sure you want to approve this appeal?',
	'CONFIRM_DENY_APPEAL'	=> 'Are you sure you want to deny this appeal?',

	'LOG_USER_WARNING_BODY_AWS' => 'This user received the following Warning [%1$s]<br />» Rule: %2$s',
	'LOG_APPEAL_REQUESTED'	=> '<strong>Appeal request submitted</strong><br />» Warning: %1$s',
	'LOG_APPEAL_APPROVED'	=> '<strong>Appeal request APPROVED</strong><br />» User: %1$s<br />» Moderator: %2$s<br />» Warning: %3$s ID #%4$d',
	'LOG_APPEAL_DENIED'		=> '<strong>Appeal request DENIED</strong><br />» User: %1$s<br />» Moderator: %2$s<br />» Warning: %3$s ID #%4$d',
	'LOG_AWS_WARNING_EDITED' => '<strong>Warning edited</strong><br />» User: %1$s<br />» Moderator: %2$s<br />» Warning: %3$s ID #%4$d',
]);
