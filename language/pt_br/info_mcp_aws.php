<?php
/**
 *
 * info_mcp_warnings [Portuguese (Brazilian)]
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

	'AWS_WARN_FRONT'	=> 'Página Inicial',
	'AWS_WARN_LIST'		=> 'Lista de Advertências',
	'AWS_WARN_USER'		=> 'Advertir Usuário',
	'AWS_WARN_POST'		=> 'Advertir por Post',
	'MCP_WARN_APPEALS'	=> 'Apelações',

	'SELECT_RULE'		=> 'Selecionar Regra Predefinida',
	'CUSTOM_RULE'		=> 'Regra Personalizada',
	'NEVER_EXPIRE'		=> 'Nunca expira',
	'SEND_AS_ME'		=> 'Enviar como eu',
	'SEND_AS_ME_EXPLAIN' => 'Se marcado, você aparecerá como remetente da MP, ignorando a configuração global.',
	'HIDE_MODERATOR'	=> 'Ocultar meu nome',
	'HIDE_MODERATOR_EXPLAIN' => 'Se marcado, seu nome não aparecerá como quem aplicou a advertência.',
	'MODERATOR'			=> 'Moderador',

	'LOG_USER_WARNING_BODY_AWS'		=> 'Este usuário recebeu a seguinte Advertência [%1$s]<br />» Regra: %2$s',
	'RULE_POINTS'					=> 'Pontos',

	// Logs de Apelação
	'LOG_APPEAL_REQUESTED'		=> '<strong>Pedido de recurso solicitado</strong><br />» Advertência: %1$s',
	'LOG_APPEAL_APPROVED'		=> '<strong>Pedido de recurso APROVADO</strong><br />» Usuário: %1$s<br />» Moderador: %2$s<br />» Advertência: %3$s ID #%4$d',
	'LOG_APPEAL_DENIED'			=> '<strong>Pedido de recurso NEGADO</strong><br />» Usuário: %1$s<br />» Moderador: %2$s<br />» Advertência: %3$s ID #%4$d',
	'LOG_AWS_WARNING_EDITED'	=> '<strong>Advertência editada</strong><br />» Usuário: %1$s<br />» Moderador: %2$s<br />» Advertência: %3$s ID #%4$d',
]);
