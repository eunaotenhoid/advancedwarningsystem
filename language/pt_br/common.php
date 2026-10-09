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
	'BAN'					=> 'Banimento',
	'BANNED_UNTIL'			=> 'até %s',
	'BANNED'				=> 'Banido',
	'BANNED_PERMANENTLY'	=> 'Permanentemente',
	'BANNED_BY_X_WARNINGS'	=> [
		1 => 'por %d advertência',
		2 => 'por %d advertências',
	],
	'PERMANENT'				=> 'Permanente',
	'WARNING'				=> 'Advertência',
	'WARNING_TYPE'			=> 'Tipo de advertência',
	'WARNINGS'				=> 'Advertências',
	'WARNING_BAN'			=> [
		1 => 'Banido por %d advertência. Regra da última advertência: %s',
		2 => 'Banido por %d advertências. Regra da última advertência: %s',
	],
	'WARNINGS_EXPLAIN'		=> 'Lista de advertências',
	'WARNING_EXPIRES'		=> 'A advertência expira',
	'WARNING_EXPIRED'		=> 'Expirada',
	'WARNING_POST'			=> 'Ir para a postagem',
	'WARNING_TIME'			=> 'Advertência emitida',

	'YELLOW_CARD'			=> 'Cartão Amarelo',
	'RED_CARD'				=> 'Cartão Vermelho',

	'UCP_AWS_WARNINGS'		=> 'Minhas Advertências',
	'UCP_AWS'				=> 'Minhas Advertências',
	'WARNING_LIST'			=> 'Minhas Advertências',
	'MODERATOR_HIDDEN'		=> 'Moderador Oculto',
	'NO_WARNINGS'			=> 'Você não possui advertências.',
	'ACTIVE'				=> 'Ativa',

	'CLEAN_RECORD_MESSAGE'		=> 'Parabéns! Você tem uma ficha impecável, continue assim.',
	'ACTIVE_WARNINGS_MESSAGE'	=> 'Atenção! Você possui advertências ativas em sua conta. Respeite as regras da comunidade para evitar novas penalidades.',
	'NEAR_BAN_MESSAGE'			=> 'Alerta Crítico! Você está muito próximo de ser banido permanentemente do site. Evite qualquer nova infração para não perder o acesso à sua conta.',

	'REQUEST_APPEAL'			=> 'Pedir Recurso',
	'APPEAL_PENDING'			=> 'Recurso Pendente',
	'APPEAL_APPROVED'			=> 'Recurso Aprovado',
	'APPEAL_DENIED'				=> 'Recurso Negado',
	'APPEAL_TIME'				=> 'Data do Recurso',
	'APPEAL_REASON'				=> 'Motivo do Recurso',
	'APPEAL_REASON_EXPLAIN'		=> 'Descreva por que você acredita que esta advertência deve ser revisada.',
	'APPEAL_SUBMITTED_SUCCESS'	=> 'Seu pedido de recurso foi enviado com sucesso.',
	'APPEAL_ACTION_SUCCESS'		=> 'Ação de recurso processada com sucesso.',
	'MOTIVO_DA_APELACAO'		=> 'Mensagem da apelação',
	'APPEAL'					=> 'Recurso',
	'APPEAL_REASON_EMPTY'		=> 'Você deve fornecer um motivo para a apelação.',
	'RULE_CANNOT_APPEAL'		=> 'Esta regra não permite apelação.',

	'AJAX_ERROR'			=> 'Ocorreu um erro de sistema ao processar a solicitação.',
	'LOADING'				=> 'Carregando...',
	'SUCCESS'				=> 'Sucesso',
	'ERROR'					=> 'Erro',
	'INFORMATION'			=> 'Informação',

	'AWS_LEVEL_POINTS_LOG'	=> '%1$d pontos debitados por atingir o nível de advertência %2$d',
	'LOG_APS_WARNING_POINTS' => '<strong>Débito automático por Advertência</strong><br />» Nível atingido: %1$s, Pontos removidos: %2$s',
]);
