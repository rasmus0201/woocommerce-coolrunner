<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Coolrunner_Tracking extends Coolrunner_Api
{
    public function track($package_number = '')
    {
        if (empty($package_number)) {
            return Coolrunner_Helper::result(true, 'Empty parcel ID', '');
        }

        $response = $this->get("tracking/{$package_number}");

        $formattedResponse = Coolrunner_Helper::formatResponse($response);

        if (!isset($formattedResponse['result']['tracking'])) {
            return $formattedResponse;
        }

        return Coolrunner_Helper::result($formattedResponse['error'], $formattedResponse['message'], array(
            'carrier'           => $formattedResponse['result']['carrier'],
            'package_number'    => $formattedResponse['result']['package_number'],
            'tracking'          => $formattedResponse['result']['tracking'],
        ));
    }

    public function bulk($package_numbers = array())
    {
        if (empty($package_numbers)) {
            return Coolrunner_Helper::result(true, 'No package numbers', []);
        } else if (is_string($package_numbers)) {
            return $this->track($package_numbers);
        }

        $return = array();

        foreach ($package_numbers as $package_number) {
            $return[$package_number] = $this->track($package_number);
        }

        return Coolrunner_Helper::result(false, 'Tracking information is returned for each parcel as an array', $return);
    }
}
