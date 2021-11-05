<?php

class ValidBot_Base {
	static $instance = null;

	public function __construct() {
	
	}

	public static function getInstance() {
		if(!self::$instance) self::$instance = new self;
		return self::$instance;
	}

	/* 	Activate the plugin
		Create any caches, DB tables or necessary options
	*/
	public function activate() { 
		$this->log("activate");
	}

	/* 	Deactivate the plugin
		Flush cache and temporary files. Flush permalinks
	*/
	public function deactivate() { 
		$this->log("deactivate");
	}

	/* 	Uninstall the plugin
		Remove any DB tables or options
	*/
	public function uninstall() { 
		$this->log("uninstall");
	}

	public function get_api_key() {
		$key = get_option('validbot_api_key');
		return $key;
	}
	public function get_validbot_account() {
		return get_option('validbot_email');
	}
	public function get_validbot_subscriber() {
		return get_option('validbot_subscriber');
	}

	public function save_api_key($api_key) {
		$api_key = preg_replace('/[^a-z0-9_]/i', '', $api_key);

		if(empty($api_key)) return "";
		if(strlen($api_key)<40) return "Invalid API Key";

		//make api call to validate the key
		$path = "https://www.validbot.com/api/validate_key.php";
		$args = array('body' => array("api_key"=>$api_key));
		$response = wp_remote_post($path,$args);
		$answer = $response['body'];
		$code = intval($response['response']['code']);

		if($code==400 || $code==401) {
			return $answer;
		}
		$data = json_decode($answer,true);

		update_option('validbot_email', $data['email']);
		update_option('validbot_subscriber', $data['subscriber']);
		update_option('validbot_api_key', $api_key);

		return false;
	}

	public function run_tests() {
		$api_key = $this->get_api_key();
		$url = get_site_url();

		//make api call to validate the key
		$path = "https://www.validbot.com/api/start.php";
		$args = array('body' => array("api_key"=>$api_key,"url"=>$url));
		$response = wp_remote_post($path,$args);
		$answer = $response['body'];
		$code = intval($response['response']['code']);

		$this->log($answer);
		if($code==401) { //unauthorized
			delete_option('validbot_api_key');
			delete_option('validbot_email');
			delete_option('validbot_subscriber');
		} else if($code==400) { //bad request
			return false;
		}
		$data = json_decode($answer,true);

		return $data;
	}

	public function get_last_report() {
		$api_key = $this->get_api_key();
		$url = get_site_url();

		//make api call to validate the key
		$path = "https://www.validbot.com/api/last_report.php";
		$args = array('body' => array("api_key"=>$api_key,"url"=>$url));
		$response = wp_remote_post($path,$args);
		$answer = $response['body'];
		$code = intval($response['response']['code']);

		if($code==401) { //unauthorized
			delete_option('validbot_api_key');
			delete_option('validbot_email');
			delete_option('validbot_subscriber');
		} else if($code==400) { //bad request
			return false;
		} else if($code==404) { //Not Found
			return false;
		}
		$data = json_decode($answer,true);

		return $data;
	}

	public function log($txt) {
		$date = date('m/d/Y H:i:s');
		//file_put_contents(plugin_dir_path(__FILE__).'log.txt', $date." : ".$txt."\n", FILE_APPEND);
	}
}
