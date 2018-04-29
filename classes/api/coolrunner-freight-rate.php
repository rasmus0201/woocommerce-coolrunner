<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Coolrunner_Freight_Rate extends Coolrunner_Api
{
    public function __call($method, $zone_from)
    {
        $zone_to = strtoupper($method);

        if (!empty($zone_from)) {
            $zone_from = strtoupper($zone_from);
        } else {
            $zone_from = 'DK';
        }

        $response = $this->all($zone_from);

        if ($response['error']) {
            return $response;
        } else if (!isset($response['result'][$zone_to])) {
            return $response;
        }

        return Coolrunner_Helper::result($response['error'], $response['message'], $response['result'][$zone_to]);
    }

    private function all($zone_from = 'DK')
    {
        $response = $this->get("freight_rates/{$zone_from}");

        return Coolrunner_Helper::formatResponse($response);
    }
}
