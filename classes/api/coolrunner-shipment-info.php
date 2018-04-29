<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Coolrunner_Shipment_Info extends Coolrunner_Api
{
    public function info($shipment_id = '')
    {
		if (empty($shipment_id)) {
            return Coolrunner_Helper::result(true, 'Empty shipment ID', '');
        }

        $response = $this->get("shipment/info/{$shipment_id}");

        return Coolrunner_Helper::formatResponse($response);
    }

    public function bulk($ids = array())
    {
        if (empty($ids)) {
            return Coolrunner_Helper::result(true, 'No package numbers', []);
        } else if (is_string($ids)) {
            return $this->info($ids);
        }

        $return = array();

        foreach ($ids as $id) {
            $return[$id] = $this->info($id);
        }

        return Coolrunner_Helper::result(false, 'Shipment information is returned for each parcel as an array', $return);
    }
}
