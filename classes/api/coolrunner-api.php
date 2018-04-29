<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/*
*Include WP Http Class
*/
if( !class_exists( 'WP_Http' ) ) {
	include_once( ABSPATH . WPINC. '/class-http.php' );
}

class Coolrunner_Api
{
    private $email = '';
    private $token = '';
    private $auth_credentials = '';
    private $request = '';

    protected $api_url = 'https://api.coolrunner.dk/v1/';


    public function __construct($auth_email, $auth_token)
    {
        if ($auth_email == '') {
            throw new Exception("No user was given", 1);
        } else if ($auth_token == ''){
            throw new Exception("No token was given", 1);
        }

        $this->token = $auth_token;
        $this->email = $auth_email;

        $this->auth_credentials = $this->authCredentials();
    }

    private function authCredentials()
    {
        return array(
			'Content-Type: application/json',
			'Authorization'  => 'Basic '. base64_encode($this->email.':'.$this->token),
            'X-Developer-Id' => 'Rasmus Bundsgaard Coolrunner WooCommerce',
		);
    }

    private function request($request_type, $url = '', $headers = array(), $body = array())
    {
        $this->request = wp_remote_request(
            $this->api_url.$url,
            array(
                'method'                => strtoupper($request_type),
                'timeout'               => 10,
                'redirection'           => 5,
                'httpversion'           => '1.0',
                'user-agent'            => 'WordPress/Bundsgaard-Coolrunner;',
                'reject_unsafe_urls'    => false,
                'blocking'              => true,
                'cookies'               => array(),
                'compress'              => false,
                'decompress'            => true,
                'sslverify'             => true,
                'stream'                => false,
                'filename'              => null,
                'limit_response_size'   => null,

                'headers'               => array_merge($this->auth_credentials, $headers),
                'body'                  => $body,
            )
        );

        if (is_wp_error($this->request)) {
            return array(
                'header'    => [],
                'body'      => '',
                'response'  => array(
                    'code'      => -1,
                    'message'   => _x('Probably no or little internet connection', 'api', 'coolrunner')
                )
            );
        }

        return $this->request;
    }


    public function post($url = '', $body = array(), $headers = array())
    {
        return $this->request('POST', $url, $headers, $body);
    }

    public function get($url = '', $headers = array())
    {
        return $this->request('GET', $url, $headers);
    }

    protected function auth()
    {
        $response = $this->get('me');

        if (is_array($response) && wp_remote_retrieve_response_code($response) == 200) {
            return true;
        } else {
            return false;
        }
    }
}
