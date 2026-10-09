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
	'NOTIFICATION_TYPE_AWS_APPEAL'		=> 'Your warning appeal status has changed',
	'NOTIFICATION_AWS_APPEAL_APPROVED'	=> 'Good news! Your appeal for the warning <strong>%1$s</strong> (ID <strong>%2$d</strong>) has been approved by the staff. Enjoy this opportunity and avoid breaking the rules—let\'s keep our community healthy.',
	'NOTIFICATION_AWS_APPEAL_DENIED'	=> 'Unfortunately, your appeal for the warning <strong>%1$s</strong> (ID <strong>%2$d</strong>) has been denied by the staff. Please read the rules more carefully to avoid future warnings. If you have any questions, feel free to ask a moderator or administrator.',
]);
