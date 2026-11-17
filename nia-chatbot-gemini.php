<?php
/**
 * Plugin Name:       NIA Chatbot Gemini
 * Plugin URI:        https://github.com/fateb-nia/P1
 * Description:       Chatbot para o site da FATEB, com respostas geradas pela API Google Gemini por meio de um endpoint REST no WordPress. Projeto NIA-FATEB (TAP NIA-TAP-2026.09.001-1).
 * Version:           0.1.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            NIA-FATEB
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       nia-chatbot-gemini
 * Domain Path:       /languages
 *
 * Compatibilidade: o código deve rodar em PHP 7.4 (versão da hospedagem) e
 * também em PHP 8.x. Não usar recursos de PHP 8+ (match, enums, readonly,
 * atributos #[...], operador ?->, str_contains etc.).
 *
 * @package NiaChatbotGemini
 */

// Impede acesso direto ao arquivo.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'NIA_CHATBOT_GEMINI_VERSION', '0.1.0' );
define( 'NIA_CHATBOT_GEMINI_MIN_PHP', '7.4' );
define( 'NIA_CHATBOT_GEMINI_MIN_WP', '6.5' );
define( 'NIA_CHATBOT_GEMINI_FILE', __FILE__ );
define( 'NIA_CHATBOT_GEMINI_DIR', plugin_dir_path( __FILE__ ) );
define( 'NIA_CHATBOT_GEMINI_URL', plugin_dir_url( __FILE__ ) );

/**
 * Ativação: confere os requisitos mínimos e registra a versão instalada.
 *
 * Se algum requisito não for atendido, o plugin é desativado e a mensagem
 * explica o motivo, em vez de gerar erro fatal no site.
 *
 * @return void
 */
function nia_chatbot_gemini_activate() {
	global $wp_version;

	$erros = array();

	if ( version_compare( PHP_VERSION, NIA_CHATBOT_GEMINI_MIN_PHP, '<' ) ) {
		$erros[] = sprintf(
			/* translators: 1: versão mínima do PHP, 2: versão atual. */
			__( 'Requer PHP %1$s ou superior (versão atual: %2$s).', 'nia-chatbot-gemini' ),
			NIA_CHATBOT_GEMINI_MIN_PHP,
			PHP_VERSION
		);
	}

	if ( version_compare( $wp_version, NIA_CHATBOT_GEMINI_MIN_WP, '<' ) ) {
		$erros[] = sprintf(
			/* translators: 1: versão mínima do WordPress, 2: versão atual. */
			__( 'Requer WordPress %1$s ou superior (versão atual: %2$s).', 'nia-chatbot-gemini' ),
			NIA_CHATBOT_GEMINI_MIN_WP,
			$wp_version
		);
	}

	if ( ! empty( $erros ) ) {
		deactivate_plugins( plugin_basename( NIA_CHATBOT_GEMINI_FILE ) );
		wp_die(
			esc_html( 'NIA Chatbot Gemini: ' . implode( ' ', $erros ) ),
			esc_html__( 'Plugin não ativado', 'nia-chatbot-gemini' ),
			array( 'back_link' => true )
		);
	}

	// Registra a versão instalada (sem autoload, para não pesar em todas as requisições).
	add_option( 'nia_chatbot_gemini_version', NIA_CHATBOT_GEMINI_VERSION, '', 'no' );
}
register_activation_hook( __FILE__, 'nia_chatbot_gemini_activate' );

/**
 * Desativação: não apaga dados. A remoção de opções fica para o uninstall.php.
 *
 * @return void
 */
function nia_chatbot_gemini_deactivate() {
	// Intencionalmente vazio nesta etapa. Tarefas agendadas (cron),
	// se existirem no futuro, devem ser removidas aqui.
}
register_deactivation_hook( __FILE__, 'nia_chatbot_gemini_deactivate' );

/**
 * Carrega a tradução do plugin.
 *
 * @return void
 */
function nia_chatbot_gemini_load_textdomain() {
	load_plugin_textdomain(
		'nia-chatbot-gemini',
		false,
		dirname( plugin_basename( NIA_CHATBOT_GEMINI_FILE ) ) . '/languages'
	);
}
add_action( 'init', 'nia_chatbot_gemini_load_textdomain' );

// Etapa 2: carregar aqui a classe principal (includes/class-plugin.php)
// e iniciar a interface (botão flutuante e janela do chatbot).
