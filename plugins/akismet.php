<?php
// ini_set('error_log', __DIR__ . '/errors.log');
// error_reporting( E_ALL );
/**
 * Akismet Plugin for PHPList
 **/
class akismet extends phplistPlugin {
	public $name = 'Akismet Plugin';
	public $enabled = true;
	public $description = 'Check subscriber email addresses for spam using the Akismet service.';
	public $authors = 'Automattic';
	public $version = '0.01';
	public $settings = array(
		'akismet_api_key' => array(
			'value' => 'Enter your API Key here.',
			'description' => 'API Key for Akismet API - https://docs.akismet.com/getting-started/api-key/',
			'type' => 'text',
			'allowempty' => true,
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
		$request = 'blog='. urlencode($data['blog']) .
			'&user_ip='. urlencode($data['user_ip']) .
			'&user_agent='. urlencode($data['user_agent']) .
			'&referrer='. urlencode($data['referrer']) .
			'&permalink='. urlencode($data['permalink']) .
			'&comment_type='. urlencode($data['comment_type']) .
			'&comment_author='. urlencode($data['comment_author']) .
			'&comment_author_email='. urlencode($data['comment_author_email']) .
			'&comment_author_url='. urlencode($data['comment_author_url']) .
			'&comment_content='. urlencode($data['comment_content']);
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
