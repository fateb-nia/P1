<?php
/**
 * Controlador da REST API do NIA Chatbot.
 *
 * @package NIA_Chatbot_Gemini
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NIA_Chatbot_REST_Controller {

	/**
	 * Namespace da REST API.
	 *
	 * @var string
	 */
	const API_NAMESPACE = 'nia-chatbot/v1';

	/**
	 * Rota do endpoint de envio de mensagem.
	 *
	 * @var string
	 */
	const ROUTE_MESSAGE = '/message';

	/**
	 * Limite máximo de caracteres por mensagem.
	 *
	 * @var int
	 */
	const MAX_MESSAGE_LENGTH = 500;

	/**
	 * Registra os hooks necessários.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Registra as rotas na REST API do WordPress.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			self::API_NAMESPACE,
			self::ROUTE_MESSAGE,
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'handle_message' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => array(
					'message' => array(
						'required'          => true,
						'type'              => 'string',
						'validate_callback' => array( $this, 'validate_message' ),
						'sanitize_callback' => 'sanitize_textarea_field',
					),
				),
			)
		);
	}

	/**
	 * Valida o nonce enviado no cabeçalho da requisição.
	 *
	 * @param WP_REST_Request $request Objeto da requisição.
	 * @return bool|WP_Error
	 */
	public function check_permission( WP_REST_Request $request ) {
		$nonce = $request->get_header( 'X-WP-Nonce' );

		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Sessão expirada ou requisição inválida. Recarregue a página e tente novamente.', 'nia-chatbot-gemini' ),
				array( 'status' => 403 )
			);
		}

		return true;
	}

	/**
	 * Valida tipo, conteúdo não vazio e tamanho máximo da mensagem.
	 *
	 * @param mixed           $value   Valor recebido.
	 * @param WP_REST_Request $request Objeto da requisição.
	 * @param string          $param   Nome do parâmetro.
	 * @return bool|WP_Error
	 */
	public function validate_message( $value, WP_REST_Request $request, $param ) {
		if ( ! is_string( $value ) ) {
			return new WP_Error(
				'invalid_param',
				__( 'O formato da mensagem é inválido.', 'nia-chatbot-gemini' ),
				array( 'status' => 400 )
			);
		}

		$trimmed = trim( $value );

		if ( '' === $trimmed ) {
			return new WP_Error(
				'empty_message',
				__( 'Digite uma mensagem antes de enviar.', 'nia-chatbot-gemini' ),
				array( 'status' => 400 )
			);
		}

		$length = function_exists( 'mb_strlen' ) ? mb_strlen( $trimmed, 'UTF-8' ) : strlen( $trimmed );

		if ( $length > self::MAX_MESSAGE_LENGTH ) {
			return new WP_Error(
				'message_too_long',
				sprintf(
					/* translators: %d: número máximo de caracteres */
					__( 'A mensagem deve ter no máximo %d caracteres.', 'nia-chatbot-gemini' ),
					self::MAX_MESSAGE_LENGTH
				),
				array( 'status' => 400 )
			);
		}

		return true;
	}

	/**
	 * Processa a mensagem validada e retorna a resposta.
	 *
	 * @param WP_REST_Request $request Objeto da requisição.
	 * @return WP_REST_Response
	 */
	public function handle_message( WP_REST_Request $request ) {
		$message = $request->get_param( 'message' );

		// Na Etapa 4, este método chamará NIA_Chatbot_Gemini_Client::ask( $message ).
		$reply = $this->get_server_stub_reply( $message );

		return new WP_REST_Response(
			array(
				'success' => true,
				'reply'   => $reply,
			),
			200
		);
	}

	/**
	 * Resposta simulada gerada pelo servidor WordPress (validação da Etapa 3).
	 *
	 * @param string $message Mensagem sanitizada.
	 * @return string
	 */
	private function get_server_stub_reply( $message ) {
		$length = function_exists( 'mb_strlen' ) ? mb_strlen( $message, 'UTF-8' ) : strlen( $message );

		return sprintf(
			'Conexão com a REST API do WordPress validada com sucesso! Recebi sua mensagem ("%s" — %d caracteres). Na Etapa 4, este endpoint será conectado à API Gemini da FATEB.',
			$message,
			$length
		);
	}
}