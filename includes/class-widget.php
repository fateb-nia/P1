<?php
/**
 * Carrega o widget do chatbot no frontend do site.
 *
 * @package NiaChatbotGemini
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NIA_Chatbot_Gemini_Widget {

	const HANDLE = 'nia-chatbot-gemini';

	/**
	 * Registra os hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
	}

	/**
	 * Carrega CSS e JS apenas no frontend (nunca no painel administrativo).
	 *
	 * @return void
	 */
	public static function enqueue() {
		if ( is_admin() ) {
			return;
		}

		wp_enqueue_style(
			self::HANDLE,
			NIA_CHATBOT_GEMINI_URL . 'assets/css/chatbot.css',
			array(),
			NIA_CHATBOT_GEMINI_VERSION
		);

		wp_enqueue_script(
			self::HANDLE,
			NIA_CHATBOT_GEMINI_URL . 'assets/js/chatbot.js',
			array(),
			NIA_CHATBOT_GEMINI_VERSION,
			true
		);

		// Configuracao enviada ao JS. Nunca colocar chaves ou segredos aqui.
		$config = array(
			'title'       => __( 'Assistente FATEB', 'nia-chatbot-gemini' ),
			'welcome'     => __( 'Olá! Sou o assistente virtual da FATEB. Como posso ajudar?', 'nia-chatbot-gemini' ),
			'placeholder' => __( 'Digite sua pergunta…', 'nia-chatbot-gemini' ),
			'maxLength'   => 500,
		);

		wp_add_inline_script(
			self::HANDLE,
			'window.NiaChatbotConfig = ' . wp_json_encode( $config ) . ';',
			'before'
		);
	}
}
