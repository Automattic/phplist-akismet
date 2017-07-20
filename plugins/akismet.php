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
}
