<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Coolrunner_Auth extends Coolrunner_Api
{
    public function isAuth(){
        $response = $this->auth();

        return Coolrunner_Helper::result(!$response, 'Auth returned '.$response, $response);
    }
}
