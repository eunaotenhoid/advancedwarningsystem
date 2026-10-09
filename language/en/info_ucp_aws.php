<?php
/**
*
* info_ucp_warnings [English]
*
* @package language
* @version $Id$
* @copyright (c) 2024 eunaumtenhoid
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

/**
* DO NOT CHANGE
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
	'UCP_AWS_MAIN'		=> 'My Warnings',
	'UCP_AWS_LIST'		=> 'Warnings List',
	'UCP_AWS_APPEAL'	=> 'Request Appeal',
	'UCP_AWS_WARNINGS'	=> 'My Warnings',
	'UCP_AWS'			=> 'My Warnings',
]);
