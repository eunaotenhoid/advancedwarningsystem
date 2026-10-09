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
	'ACP_WARN_RULES'				=> 'Gerenciar Regras de Advertência',
	'ACP_WARN_RULES_EXPLAIN'		=> 'Aqui você pode gerenciar as regras predefinidas para advertências. Elas facilitarão o trabalho dos moderadores, preenchendo automaticamente a regra aplicada e a duração.',
	'ADD_RULE'					=> 'Adicionar Regra',
	'EDIT_RULE'					=> 'Editar Regra',
	'RULE_TITLE'					=> 'Título da Advertência (Regra)',
	'RULE_DESCRIPTION'			=> 'Descrição da Regra (Detalhada)',
	'RULE_DURATION'				=> 'Dias para expirar',
	'RULE_DURATION_ZERO_EXPLAIN'	=> 'Número de dias até a advertência expirar. Use 0 para nunca expirar.',
	'RULE_POINTS'					=> 'Número de Advertências (Pontos)',
	'RULE_SAVED'					=> 'Regra salva com sucesso.',
	'RULE_DELETED'				=> 'Regra excluída com sucesso.',
	'MOVE_UP'						=> 'Mover para cima',
	'MOVE_DOWN'						=> 'Mover para baixo',

	'INF_WARNING_ONLY'				=> 'Apenas Advertência',
	'INF_WARNING_ONLY_EXPLAIN'		=> 'Marque se esta infração não deve atribuir nenhum ponto (advertência verbal).',
	'INF_MOD_LOCKED'				=> 'Bloquear Edição',
	'INF_MOD_LOCKED_EXPLAIN'		=> 'Se ativado, os moderadores não poderão alterar a pontuação ou a validade ao aplicar esta infração.',
	
	'AWS_SETTINGS'				=> 'Configurações de Advertências Avançadas',
	'AWS_PM_SENDER'		=> 'Remetente da MP',
	'AWS_PM_SENDER_EXPLAIN' => 'ID do usuário que aparecerá como remetente da MP de aviso. Se 0 ou se o usuário não existir, o moderador que aplicar será o remetente.',
	'AWS_ALLOW_CUSTOM'		=> 'Permitir Regra Personalizada',
	'AWS_ALLOW_CUSTOM_EXPLAIN' => 'Se ativado, moderadores podem escrever regras/motivos não listados nas regras padrões.',
	'AWS_ALLOW_HIDE_MODERATOR' => 'Ocultar Moderador',
	'AWS_ALLOW_HIDE_MODERATOR_EXPLAIN' => 'Define se o nome do moderador deve aparecer para o usuário na lista de advertências. <ul><li><strong>Não:</strong> Sempre exibe o nome.</li><li><strong>Sim:</strong> Oculta o nome de todos os moderadores.</li><li><strong>Deixar a escolha:</strong> Permite que cada moderador decida ao aplicar a advertência.</li></ul>',
	'AWS_HIDE_MOD_NO'		=> 'Não (Sempre exibir)',
	'AWS_HIDE_MOD_YES'		=> 'Sim (Ocultar de todos)',
	'AWS_HIDE_MOD_CHOICE'	=> 'Deixar a escolha do moderador',
	'AWS_RULES_URL'		=> 'URL para as Regras do Fórum',
	'AWS_RULES_URL_EXPLAIN' => 'Link para a página de regras do fórum. Pode ser usado nas MPs com a variável {RULES_URL}.',
	'AWS_SHOW_CARDS'		=> 'Exibir Ícones de Advertência',
	'AWS_SHOW_CARDS_EXPLAIN' => 'Ativa a exibição de ícones customizáveis antes do nome de usuário no perfil e nos posts.',
	'AWS_LEVEL_THRESHOLD'	=> 'Limite',
	'AWS_LEVEL_ICON'		=> 'Ícone',
	'AWS_LEVEL_COLOR'		=> 'Cor',
	'AWS_LEVEL_POINTS'		=> 'Pontos a Debitar',
	'AWS_LEVEL_SET'		=> 'Nível %d',
	'AWS_BANNED_SET'		=> 'Status de Banido',
	'AWS_ICON_EXPLAIN'		=> 'Classe do Font Awesome (ex: fa-exclamation-triangle).',
	'AWS_COLOR_EXPLAIN'	=> 'Código hexadecimal da cor (ex: #FF0000).',
	'AWS_PM_SETTINGS'		=> 'Configuração da mp de advertencia',
	'AWS_PM_SUBJECT'		=> 'Assunto da MP',
	'AWS_PM_BODY_USER'		=> 'Corpo da MP (Advertência de Usuário)',
	'AWS_PM_BODY_POST'		=> 'Corpo da MP (Advertência de Post)',
	'AWS_PM_VARIABLES'		=> 'Variáveis disponíveis: {USERNAME}, {RULE_TYPE}, {RULE_DESCRIPTION}, {RULE}, {POST_URL}, {MODERATOR}, {POINTS}, {RULES_URL}.',

	'AWS_PM_SUBJECT_DEFAULT'		=> 'Você recebeu uma advertência',
	'AWS_PM_BODY_USER_DEFAULT'		=> "Olá {USERNAME},\n\nVocê recebeu uma advertência por: {RULE_TYPE}.\n\nDetalhes: {RULE_DESCRIPTION}",
	'AWS_PM_BODY_POST_DEFAULT'		=> "Olá {USERNAME},\n\nVocê recebeu uma advertência pela seguinte postagem: {POST_URL}.\n\nTipo: {RULE_TYPE}\nDetalhes: {RULE_DESCRIPTION}",

	'AWS_RESYNC'					=> 'Sincronizar Contadores de Advertência',
	'AWS_RESYNC_EXPLAIN'			=> 'Recalcula o número total de advertências para todos os usuários com base nos registros ativos. Útil se os contadores estiverem incorretos.',
	'AWS_RESYNC_SUCCESS'			=> 'Contadores de advertência sincronizados com sucesso.',
	'AWS_RESYNC_CONFIRM'			=> 'Tem certeza que deseja sincronizar os contadores de advertência de todos os usuários? Isso pode levar algum tempo em fóruns grandes.',

	'WARNINGS_FOR_BAN'				=> 'Advertências para banimento',
	'WARNINGS_FOR_BAN_EXPLAIN'		=> 'Número máximo de advertências para o usuário ser banido automaticamente e permanentemente.',
	'WARNINGS_GC'					=> 'Período de limpeza de advertências',
	'WARNINGS_GC_EXPLAIN'			=> 'Tempo (em segundos) para excluir advertências expiradas periodicamente.',

	'AWS_ENABLE_APPEALS'		=> 'Ativar Sistema de apelações',
	'AWS_ENABLE_APPEALS_EXPLAIN' => 'Se desativado, os usuários não poderão pedir recurso para suas advertências.',
	'RULE_ALLOW_APPEAL'		=> 'Permitir apelação',
	'RULE_ALLOW_APPEAL_EXPLAIN' => 'Se ativado, esta advertência específica poderá ser contestada pelo usuário.',
	'RULE_CONVERT_TO'			=> 'Converter para',
	'RULE_CONVERT_TO_EXPLAIN'	=> 'Se o recurso for aprovado, a regra da advertência será alterada para esta.',
	'RULE_CONVERT_NONE'			=> '-- Não converter (Apenas zerar pontos) --',

	// Logs de Apelação
	'LOG_APPEAL_REQUESTED'		=> '<strong>Pedido de recurso solicitado</strong><br />» Advertência: %1$s',
	'LOG_APPEAL_APPROVED'		=> '<strong>Pedido de recurso APROVADO</strong><br />» Usuário: %1$s<br />» Moderador: %2$s<br />» Advertência: %3$s ID #%4$d',
	'LOG_APPEAL_DENIED'			=> '<strong>Pedido de recurso NEGADO</strong><br />» Usuário: %1$s<br />» Moderador: %2$s<br />» Advertência: %3$s ID #%4$d',
	'LOG_AWS_WARNING_EDITED'	=> '<strong>Advertência editada</strong><br />» Usuário: %1$s<br />» Moderador: %2$s<br />» Advertência: %3$s ID #%4$d',
]);
