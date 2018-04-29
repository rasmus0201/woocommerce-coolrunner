<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Coolrunner_Droppoint extends Coolrunner_Api
{
    private function droppoints($carrier = 'dao', $args = array())
    {
		if (empty($args['number_of_droppoints']) || empty($args['postcode']) ){
			return Coolrunner_Helper::result(true, 'Args must be set', []);

		} else if ($args['number_of_droppoints'] < 1) {
			return Coolrunner_Helper::result(true, 'Number of droppoints is out of range', []);

		} else if ($args['number_of_droppoints'] > 25) {
			return Coolrunner_Helper::result(true, 'Number of droppoints is out of range', []);

		} else if (Coolrunner_Helper::isInvalidCountryCode($args['country_code'])) {
			return Coolrunner_Helper::result(true, 'country_code is wrong "'.$args['country_code'].'"', []);

		} else if (Coolrunner_Helper::isInvalidCarrier($carrier)) {
			return Coolrunner_Helper::result(true, 'Invalid carrier "'.$carrier.'"', []);
		} else if (Coolrunner_Helper::isInvalidPostcode($args['postcode'])) {
			return Coolrunner_Helper::result(true, 'Invalid postcode "'.$args['postcode'].'"', []);
		}

        //Go on after various checks
        $carrier = strtolower($carrier);
        $response = $this->post("droppoints/{$carrier}", $args);

        return Coolrunner_Helper::formatResponse($response);
    }

    public function dao($country_code = 'DK', $postcode = '', $street = '', $number_of_droppoints = 5)
    {
        return $this->droppoints('dao', array(
            'country_code'          => (!is_string($country_code)) ? '' : $country_code,
            'postcode'              => (!is_string($postcode)) ? '' : $postcode,
            'street'                => (!is_string($street)) ? '' : $street,
            'number_of_droppoints'  => (!is_int($number_of_droppoints)) ? 5 : $number_of_droppoints,
        ));
    }

    public function pdk($country_code = 'DK', $postcode = '', $street = '', $number_of_droppoints = 5)
    {
        return $this->droppoints('pdk', array(
            'country_code'          => (!is_string($country_code)) ? '' : $country_code,
            'postcode'              => (!is_string($postcode)) ? '' : $postcode,
            'street'                => (!is_string($street)) ? '' : $street,
            'number_of_droppoints'  => (!is_int($number_of_droppoints)) ? 5 : $number_of_droppoints,
        ));
    }

    public function gls($country_code = 'DK', $postcode = '', $street = '', $number_of_droppoints = 5)
    {
        return $this->droppoints('gls', array(
            'country_code'          => (!is_string($country_code)) ? '' : $country_code,
            'postcode'              => (!is_string($postcode)) ? '' : $postcode,
            'street'                => (!is_string($street)) ? '' : $street,
            'number_of_droppoints'  => (!is_int($number_of_droppoints)) ? 5 : $number_of_droppoints,
        ));
    }

    public function multipleCarriers($carriers = array(), $args = array())
    {
        if (Coolrunner_Helper::isInvalidCarrier($carriers)) {
            return Coolrunner_Helper::result(true, 'One 1 more carriers are not defined', []);
        }

        $return = array();

        foreach ($carriers as $carrier) {
            $return[$carrier] = $this->droppoints($carrier, $args);
        }

        return Coolrunner_Helper::result(false, 'Returned an array of droppoints by different carriers for the same address', $return);
    }

    public function multipleAddresses($carrier, $addresses = array())
    {
        if (Coolrunner_Helper::isInvalidCarrier($carrier)) {
            return Coolrunner_Helper::result(true, 'The carrier is not defined', []);
        }

        $return = array();

        foreach ($addresses as $address) {
            $return[] = $this->droppoints($carrier, $address);
        }

        return Coolrunner_Helper::result(false, 'Returned an array of droppoints for multiple addresses with the same carrier', $return);;
    }
}
