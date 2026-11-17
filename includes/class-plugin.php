<?php
/**
 * Classe principal de orquestração do plugin.
 *
 * @package NIA_Chatbot_Gemini
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NIA_Chatbot_Plugin {

	/**
	 * Controlador da REST API.
	 *
	 * @var NIA_Chatbot_REST_Controller
	 */
	private $rest_controller;

	/**
	 * Inicializa dependências e hooks do WordPress.
	 *
	 * @return void
	 */
	public function init() {
		$this->load_dependencies();

		$this->rest_controller = new NIA_Chatbot_REST_Controller();
		$this->rest_controller->register_hooks();

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_footer', array( $this, 'render_widget_container' ) );
	}

	/**
	 * Carrega os arquivos auxiliares do plugin.
	 *
	 * @return void
	 */
	private function load_dependencies() {
		require_once NIA_CHATBOT_PLUGIN_DIR . 'includes/class-rest-controller.php';
	}

	/**
	 * Enfileira os assets do frontend e injeta a configuração da REST API.
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		if ( is_admin() ) {
			return;
		}

		wp_enqueue_style(
			'nia-chatbot-style',
			NIA_CHATBOT_PLUGIN_URL . 'assets/css/chatbot.css',
			array(),
			NIA_CHATBOT_VERSION
		);

		wp_enqueue_script(
			'nia-chatbot-script',
			NIA_CHATBOT_PLUGIN_URL . 'assets/js/chatbot.js',
			array(),
			NIA_CHATBOT_VERSION,
			true
		);

		wp_localize_script(
			'nia-chatbot-script',
			'niaChatbotConfig',
			array(
				'endpoint'  => esc_url_raw( rest_url( 'nia-chatbot/v1/message' ) ),
				'nonce'     => wp_create_nonce( 'wp_rest' ),
				'maxLength' => NIA_Chatbot_REST_Controller::MAX_MESSAGE_LENGTH,
			)
		);
	}

	/**
	 * Renderiza o container raiz do widget no rodapé das páginas públicas.
	 *
	 * @return void
	 */
	public function render_widget_container() {
		if ( is_admin() ) {
			return;
		}

		echo '<div id="nia-chatbot-root" aria-live="polite"></div>';
	}
}