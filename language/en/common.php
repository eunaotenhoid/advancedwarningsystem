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
	'BAN'					=> 'Ban',
	'BANNED_UNTIL'			=> 'until %s',
	'BANNED'				=> 'Banned',
	'BANNED_PERMANENTLY'	=> 'Permanently',
	'BANNED_BY_X_WARNINGS'	=> [
		1 => 'by %d warning',
		2 => 'by %d warnings',
	],
	'PERMANENT'				=> 'Permanent',
	'WARNING'				=> 'Warning',
	'WARNING_TYPE'			=> 'Warning type',
	'WARNINGS'				=> 'Warnings',
	'WARNING_BAN'			=> [
		1 => 'Banned by %d warning. Last warning rule: %s',
		2 => 'Banned by %d warnings. Last warning rule: %s',
	],
	'WARNINGS_EXPLAIN'		=> 'Warnings list',
	'WARNING_EXPIRES'		=> 'Warning expires',
	'WARNING_EXPIRED'		=> 'Expired',
	'WARNING_POST'			=> 'Go to post',
	'WARNING_TIME'			=> 'Warning issued',

	'YELLOW_CARD'			=> 'Yellow Card',
	'RED_CARD'				=> 'Red Card',

	'UCP_AWS_WARNINGS'		=> 'My Warnings',
	'UCP_AWS'				=> 'My Warnings',
	'WARNING_LIST'			=> 'My Warnings',
	'MODERATOR_HIDDEN'		=> 'Hidden Moderator',
	'NO_WARNINGS'			=> 'You have no warnings.',
	'ACTIVE'				=> 'Active',

	'CLEAN_RECORD_MESSAGE'		=> 'Congratulations! You have a clean record, keep it up.',
	'ACTIVE_WARNINGS_MESSAGE'	=> 'Notice: You have active warnings on your account. Please respect the community rules to avoid further penalties.',
	'NEAR_BAN_MESSAGE'			=> 'Critical Alert! You are very close to being permanently banned from the site. Avoid any further infractions to prevent account termination.',

	'REQUEST_APPEAL'			=> 'Request Appeal',
	'APPEAL_PENDING'			=> 'Appeal Pending',
	'APPEAL_TIME'				=> 'Appeal Date',
	'APPEAL_APPROVED'			=> 'Appeal Approved',
	'APPEAL_DENIED'				=> 'Appeal Denied',
	'APPEAL_REASON'				=> 'Appeal Reason',
	'APPEAL_REASON_EXPLAIN'		=> 'Describe why you believe this warning should be reviewed.',
	'APPEAL_SUBMITTED_SUCCESS'	=> 'Your appeal request has been submitted successfully.',
	'APPEAL_ACTION_SUCCESS'		=> 'Appeal action processed successfully.',
	'MOTIVO_DA_APELACAO'		=> 'Appeal message',
	'APPEAL'					=> 'Appeal',
	'APPEAL_REASON_EMPTY'		=> 'You must provide a reason for the appeal.',
	'RULE_CANNOT_APPEAL'		=> 'This rule does not allow appeal.',

	'AJAX_ERROR'			=> 'A system error occurred while processing the request.',
	'LOADING'				=> 'Loading...',
	'SUCCESS'				=> 'Success',
	'ERROR'					=> 'Error',
	'INFORMATION'			=> 'Information',

	'AWS_LEVEL_POINTS_LOG'	=> '%1$d points debited for reaching warning level %2$d',
	'LOG_APS_WARNING_POINTS' => '<strong>Automatic deduction for Warning</strong><br />» Level reached: %1$s, Points removed: %2$s',
]);
