<?php
/**
 * Akismet Plugin for PHPList
 **/
class akismet extends phplistPlugin {
	public $name = 'Akismet Plugin';
	public $enabled = true;
	public $description = 'Check subscription requests for spam using the Akismet service.';
	public $authors = 'Automattic';
	public $version = '1.00';
	public $settings = array(
		'akismet_api_key' => array(
			'value' => 'Enter your API Key here.',
			'description' => 'API Key for Akismet API - https://docs.akismet.com/getting-started/api-key/',
			'type' => 'text',
			'allowempty' => true,
			'category' => 'Akismet',
		),
		'akismet_spam_message' => array(
			'value' => 'Your subscription request has been denied.',
			'description' => 'Message to display when a subscription is blocked by Akismet',
			'type' => 'text',
			'allowempty' => false,
			'category' => 'Akismet',
		),
		'akismet_enable_logging' => array(
			'value' => false,
			'description' => 'Track blocked subscriptions in the Event Log',
			'type' => 'boolean',
			'allowempty' => false,
			'category' => 'Akismet',
		),
	);

	public function __construct() {
		$this->coderoot = __DIR__;
		parent::__construct();
	}

	public function dependencyCheck() {
		return array(
			'OpenSSL support available' => extension_loaded( 'openssl' ),
		);
	}

	public function adminmenu() {
		return array();
	}

	public function validateSubscriptionPage( $pageData ) {
		if ( $this->enabled ) {
			if ( ! empty( $_POST ) ) {
				if ( $this->akismet_verify_key( getConfig( 'akismet_api_key' ), getConfig( 'website' ) ) ) {

					$_data = array(
						'blog' => getConfig( 'website' ),
						'user_ip' => isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '',
						'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '',
						'referrer' => isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '',
						'permalink' => '',
						'comment_type' => 'signup',
						'comment_author' => '',
						'comment_author_email' => isset( $_POST['email'] ) ? $_POST['email'] : '',
						'comment_author_url' => '',
						'comment_content' => '',
					);

					foreach ( $_POST as $_key => $_value ) {
						if ( is_string( $_value ) ) {
							$_data["POST_{$_key}"] = $_value;
						}
					}

					foreach ( $_SERVER as $_key => $_value ) {
						if ( ! is_string( $_value ) ) {
							continue;
						}

						if ( preg_match( '/^HTTP_COOKIE/', $_key ) ) {
							continue;
						}

						// Send any potentially useful $_SERVER vars, but avoid sending junk we don't need.
						if ( preg_match( '/^(HTTP_|REMOTE_ADDR|REQUEST_URI|DOCUMENT_URI)/', $_key ) ) {
								$_data[$_key] = $_value;
						}
					}

					if ( $this->akismet_comment_check( getConfig( 'akismet_api_key' ), $_data ) ) {
						if ( true == getConfig( 'akismet_enable_logging' ) ) {
							$_message = "Akismet blocked spam subscription from {$_data['comment_author_email']} via {$_data['user_ip']}.";
							logEvent( $_message );
						}
						return getConfig( 'akismet_spam_message' );
					}
				}
			}
		}

		return '';
	}

	// From Akismet Documentation
	function akismet_verify_key( $key, $blog ) {
		$blog = urlencode($blog);
		$request = 'key='. $key .'&blog='. $blog;
		$host = $http_host = 'rest.akismet.com';
		$path = '/1.1/verify-key';
		$port = 443;
		$akismet_ua = 'PHPList/' . VERSION . " PHPList-Akismet/{$this->version}";
		$content_length = strlen( $request );
		$http_request  = "POST $path HTTP/1.0\r\n";
		$http_request .= "Host: $host\r\n";
		$http_request .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$http_request .= "Content-Length: {$content_length}\r\n";
		$http_request .= "User-Agent: {$akismet_ua}\r\n";
		$http_request .= "\r\n";
		$http_request .= $request;
		$response = '';
		if( false != ( $fs = @fsockopen( 'ssl://' . $http_host, $port, $errno, $errstr, 10 ) ) ) {

			fwrite( $fs, $http_request );

			while ( !feof( $fs ) )
				$response .= fgets( $fs, 1160 ); // One TCP-IP packet
			fclose( $fs );

			$response = explode( "\r\n\r\n", $response, 2 );
		}

		if ( 'valid' == $response[1] )
			return true;
		else
			return false;
	}

	function akismet_comment_check( $key, $data ) {
		$request = http_build_query( $data );

		$host = $http_host = $key.'.rest.akismet.com';
		$path = '/1.1/comment-check';
		$port = 443;
		$akismet_ua = 'PHPList/' . VERSION . " PHPList-Akismet/{$this->version}";
		$content_length = strlen( $request );
		$http_request  = "POST $path HTTP/1.0\r\n";
		$http_request .= "Host: $host\r\n";
		$http_request .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$http_request .= "Content-Length: {$content_length}\r\n";
		$http_request .= "User-Agent: {$akismet_ua}\r\n";
		$http_request .= "\r\n";
		$http_request .= $request;

		$response = '';
		if( false != ( $fs = @fsockopen( 'ssl://' . $http_host, $port, $errno, $errstr, 10 ) ) ) {

			fwrite( $fs, $http_request );

			while ( !feof( $fs ) )
				$response .= fgets( $fs, 1160 ); // One TCP-IP packet
			fclose( $fs );

			$response = explode( "\r\n\r\n", $response, 2 );
		}

		if ( 'true' == $response[1] )
			return true;
		else
			return false;
	}
}
