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
	'CANNOT_WARN_FOUNDER'	=> 'Você não pode advertir o fundador.',
	'EDIT_WARNING'			=> 'Editar advertência',
	'USER_WARNING_EDITED'	=> 'Advertência editada com sucesso.',
	'LENGTH_WARNING_INVALID' => 'A data tem que ser formatada como <kbd>AAAA-MM-DD</kbd>.',
	'LIST_WARNINGS'			=> [
		1 => '%d advertência',
		2 => '%d advertências',
	],

	'SELECT_RULE'			=> 'Selecione uma Regra Padrão',
	'NO_RULE_SELECTED'		=> 'Você deve selecionar uma regra de advertência antes de enviar.',
	'CUSTOM_RULE'			=> 'Regra Personalizada',
	'NEVER_EXPIRE'			=> 'Nunca expira',
	'SEND_AS_ME'			=> 'Enviar como eu',
	'SEND_AS_ME_EXPLAIN'	=> 'Se marcado, você aparecerá como remetente da MP ignorando a opção global.',
	'HIDE_MODERATOR'		=> 'Ocultar meu nome',
	'HIDE_MODERATOR_EXPLAIN' => 'Se marcado, seu nome não aparecerá como o autor da advertência.',
	'MODERATOR'				=> 'Moderador',
	'RULE_POINTS'			=> 'Pontos',

	'APPROVE'				=> 'Aprovar',
	'DENY'					=> 'Negar',
	'APPEALS_LIST'			=> 'Lista de Apelações',
	'CONFIRM_APPROVE_APPEAL' => 'Tem certeza de que deseja aprovar este recurso?',
	'CONFIRM_DENY_APPEAL'	=> 'Tem certeza de que deseja negar este recurso?',

	'LOG_USER_WARNING_BODY_AWS' => 'Este usuário recebeu a seguinte advertência [%1$s]<br />» Regra: %2$s',
	'LOG_APPEAL_REQUESTED'	=> '<strong>Pedido de recurso solicitado</strong><br />» Advertência: %1$s',
	'LOG_APPEAL_APPROVED'	=> '<strong>Pedido de recurso APROVADO</strong><br />» Usuário: %1$s<br />» Moderador: %2$s<br />» Advertência: %3$s ID #%4$d',
	'LOG_APPEAL_DENIED'		=> '<strong>Pedido de recurso NEGADO</strong><br />» Usuário: %1$s<br />» Moderador: %2$s<br />» Advertência: %3$s ID #%4$d',
	'LOG_AWS_WARNING_EDITED' => '<strong>Advertência editada</strong><br />» Usuário: %1$s<br />» Moderador: %2$s<br />» Advertência: %3$s ID #%4$d',
]);
