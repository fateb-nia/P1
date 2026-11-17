<?php
/**
 * Plugin Name:       NIA Chatbot Gemini
 * Plugin URI:        https://github.com/fateb-nia/P1
 * Description:       Assistente virtual institucional da FATEB integrado à API Gemini.
 * Version:           0.3.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            NIA - Núcleo de Inteligência Artificial FATEB
 * Text Domain:       nia-chatbot-gemini
 *
 * @package NIA_Chatbot_Gemini
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'NIA_CHATBOT_VERSION', '0.3.0' );
define( 'NIA_CHATBOT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'NIA_CHATBOT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once NIA_CHATBOT_PLUGIN_DIR . 'includes/class-plugin.php';

/**
 * Executa rotinas na ativação do plugin.
 *
 * @return void
 */
function nia_chatbot_activate() {
	if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die(
			esc_html__( 'O plugin NIA Chatbot Gemini requer PHP 7.4 ou superior.', 'nia-chatbot-gemini' ),
			esc_html__( 'Erro de compatibilidade', 'nia-chatbot-gemini' ),
			array( 'back_link' => true )
		);
	}
}
register_activation_hook( __FILE__, 'nia_chatbot_activate' );

/**
 * Executa rotinas na desativação do plugin.
 *
 * @return void
 */
function nia_chatbot_deactivate() {
	// Limpeza temporária na desativação (transients serão tratados na Etapa 5).
}
register_deactivation_hook( __FILE__, 'nia_chatbot_deactivate' );

/**
 * Inicializa o plugin após o carregamento dos plugins do WordPress.
 *
 * @return void
 */
function nia_chatbot_run() {
	$plugin = new NIA_Chatbot_Plugin();$plugin->init();
}
add_action( 'plugins_loaded', 'nia_chatbot_run' );