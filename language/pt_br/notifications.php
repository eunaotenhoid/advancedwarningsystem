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
	'NOTIFICATION_TYPE_AWS_APPEAL'		=> 'Resultados de recursos',
	'NOTIFICATION_AWS_APPEAL_APPROVED'	=> 'Temos uma ótima notícia para você: sua apelação referente à advertência <strong>%1$s</strong> (ID <strong>%2$d</strong>) foi aprovada pela equipe do site! Aproveite essa oportunidade e evite quebrar as regras para construirmos uma comunidade saudável.',
	'NOTIFICATION_AWS_APPEAL_DENIED'	=> 'Infelizmente sua apelação referente à advertência <strong>%1$s</strong> (ID <strong>%2$d</strong>) foi negada pela equipe do site. Leia as regras mais atentamente para evitar futuras advertências. Em caso de dúvidas, procure um moderador ou administrador.',
]);
