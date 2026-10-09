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
	'ACP_WARN_RULES'				=> 'Manage Warning Rules',
	'ACP_WARN_RULES_EXPLAIN'		=> 'Here you can manage the predefined rules for warnings. They will make the moderators\' work easier by automatically filling in the rule and duration.',
	'ADD_RULE'					=> 'Add Rule',
	'EDIT_RULE'					=> 'Edit Rule',
	'RULE_TITLE'					=> 'Warning Title (Rule Type)',
	'RULE_DESCRIPTION'			=> 'Rule Description (Detailed)',
	'RULE_DURATION'				=> 'Days until expiration',
	'RULE_DURATION_ZERO_EXPLAIN'	=> 'Number of days until the warning expires. Use 0 for never expires.',
	'RULE_POINTS'					=> 'Number of Warnings',
	'RULE_SAVED'					=> 'Rule successfully saved.',
	'RULE_DELETED'				=> 'Rule successfully deleted.',
	'MOVE_UP'						=> 'Move up',
	'MOVE_DOWN'						=> 'Move down',

	'INF_WARNING_ONLY'				=> 'Only Warning',
	'INF_WARNING_ONLY_EXPLAIN'		=> 'Check if this infraction should not assign any points (verbal warning).',
	'INF_MOD_LOCKED'				=> 'Block Edition',
	'INF_MOD_LOCKED_EXPLAIN'		=> 'If enabled, moderators will not be able to change the score or validity when applying this infraction.',
	
	'AWS_SETTINGS'				=> 'Advanced Warnings Settings',
	'AWS_PM_SENDER'		=> 'PM Sender',
	'AWS_PM_SENDER_EXPLAIN' => 'User ID that will appear as the sender of the warning PM. If 0 or if the user doesn\'t exist, the moderator who applies the warning will be the sender.',
	'AWS_ALLOW_CUSTOM'		=> 'Allow Custom Rule',
	'AWS_ALLOW_CUSTOM_EXPLAIN' => 'If enabled, moderators can write rules not listed in the standard rules.',
	'AWS_ALLOW_HIDE_MODERATOR' => 'Hide Moderator',
	'AWS_ALLOW_HIDE_MODERATOR_EXPLAIN' => 'Defines if the moderator name should be visible to the user in the warnings list. <ul><li><strong>No:</strong> Always show name.</li><li><strong>Yes:</strong> Hide name for all moderators.</li><li><strong>Moderator Choice:</strong> Allow each moderator to decide when issuing the warning.</li></ul>',
	'AWS_HIDE_MOD_NO'		=> 'No (Always show)',
	'AWS_HIDE_MOD_YES'		=> 'Yes (Hide for all)',
	'AWS_HIDE_MOD_CHOICE'	=> 'Moderator Choice',
	'AWS_RULES_URL'		=> 'Forum Rules URL',
	'AWS_RULES_URL_EXPLAIN' => 'Link to the forum rules page. Can be used in PMs with the {RULES_URL} variable.',
	'AWS_SHOW_CARDS'		=> 'Display Warning Icons',
	'AWS_SHOW_CARDS_EXPLAIN' => 'Enables the display of customizable icons before usernames in profiles and posts.',
	'AWS_LEVEL_THRESHOLD'	=> 'Threshold',
	'AWS_LEVEL_ICON'		=> 'Icon',
	'AWS_LEVEL_COLOR'		=> 'Color',
	'AWS_LEVEL_POINTS'		=> 'Points to Debit',
	'AWS_LEVEL_SET'		=> 'Level %d',
	'AWS_BANNED_SET'		=> 'Banned Status',
	'AWS_ICON_EXPLAIN'		=> 'Font Awesome class (e.g., fa-exclamation-triangle).',
	'AWS_COLOR_EXPLAIN'	=> 'Hex color code (e.g., #FF0000).',
	'AWS_PM_SETTINGS'		=> 'PM Settings',
	'AWS_PM_SUBJECT'		=> 'PM Subject',
	'AWS_PM_BODY_USER'		=> 'PM Body (User Warning)',
	'AWS_PM_BODY_POST'		=> 'PM Body (Post Warning)',
	'AWS_PM_VARIABLES'		=> 'Available variables: {USERNAME}, {RULE_TYPE}, {RULE_DESCRIPTION}, {RULE}, {POST_URL}, {MODERATOR}, {POINTS}, {RULES_URL}.',

	'AWS_PM_SUBJECT_DEFAULT'		=> 'You have received a warning',
	'AWS_PM_BODY_USER_DEFAULT'		=> "Hello {USERNAME},\n\nYou have received a warning for: {RULE_TYPE}.\n\nDetails: {RULE_DESCRIPTION}",
	'AWS_PM_BODY_POST_DEFAULT'		=> "Hello {USERNAME},\n\nYou have received a warning for the following post: {POST_URL}.\n\nType: {RULE_TYPE}\nDetails: {RULE_DESCRIPTION}",

	'AWS_RESYNC'					=> 'Resync Warning Counts',
	'AWS_RESYNC_EXPLAIN'			=> 'Recalculates the total number of warnings for all users based on active records. Useful if counts are incorrect.',
	'AWS_RESYNC_SUCCESS'			=> 'Warning counts successfully resynced.',
	'AWS_RESYNC_CONFIRM'			=> 'Are you sure you want to resync the warning counts for all users? This may take some time on large forums.',

	'WARNINGS_FOR_BAN'				=> 'Warnings for ban',
	'WARNINGS_FOR_BAN_EXPLAIN'		=> 'Maximum warnings for user to be banned automatically and permanently.',
	'WARNINGS_GC'					=> 'Warnings pruning period',
	'WARNINGS_GC_EXPLAIN'			=> 'Time (in seconds) to prune expired warnings periodically.',

	'AWS_ENABLE_APPEALS'		=> 'Enable appeal system',
	'AWS_ENABLE_APPEALS_EXPLAIN' => 'If disabled, users will not be able to request an appeal for their warnings.',
	'RULE_ALLOW_APPEAL'		=> 'Allow appeal',
	'RULE_ALLOW_APPEAL_EXPLAIN' => 'If enabled, this specific warning rule can be challenged by the user.',
	'RULE_CONVERT_TO'			=> 'Convert to',
	'RULE_CONVERT_TO_EXPLAIN'	=> 'If the appeal is approved, the warning rule will be changed to this one.',
	'RULE_CONVERT_NONE'			=> '-- Do not convert (Zero points only) --',

	// Logs
	'LOG_APPEAL_REQUESTED'		=> '<strong>Appeal request submitted</strong><br />» Warning: %1$s',
	'LOG_APPEAL_APPROVED'		=> '<strong>Appeal request APPROVED</strong><br />» User: %1$s<br />» Moderator: %2$s<br />» Warning: %3$s ID #%4$d',
	'LOG_APPEAL_DENIED'			=> '<strong>Appeal request DENIED</strong><br />» User: %1$s<br />» Moderator: %2$s<br />» Warning: %3$s ID #%4$d',
	'LOG_AWS_WARNING_EDITED'	=> '<strong>Warning edited</strong><br />» User: %1$s<br />» Moderator: %2$s<br />» Warning: %3$s ID #%4$d',
]);
