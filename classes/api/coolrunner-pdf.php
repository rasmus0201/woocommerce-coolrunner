<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Coolrunner_Pdf extends Coolrunner_Api
{
    public function pdf($unique_id = '')
    {
        if (empty($unique_id)) {
            return Coolrunner_Helper::result(true, 'Unique Id must be set', []);
        } else if (!is_string($unique_id)) {
			return Coolrunner_Helper::result(true, 'Unique Id has a wrong format', []);
		}

        $response = $this->get("pdf/{$unique_id}");

        //PDF response is more oddly, we cant use the generice helper class for this one..
        $body = wp_remote_retrieve_body($response);
        $array = json_decode($body, true);

        if (Coolrunner_Helper::jsonValidate()) {
            return Coolrunner_Helper::result(true, $array['message'], $array);
        } else {
            //Could not json_decode, which means we have got a successfull PDF

            return Coolrunner_Helper::result(false, 'PDF returned in result', $body);
        }
    }

    public function bulk($unique_ids = array())
    {
        if (empty($unique_ids)) {
            return Coolrunner_Helper::result(true, 'No parcels to track.', '');
        } else if (is_string($unique_ids)) {
            return $this->pdf($unique_ids);
        } else if (count($unique_ids) > 5) {
            return Coolrunner_Helper::result(true, 'Max pdf generation at a time is 5', '');
        }

        $return = array();

        foreach ($unique_ids as $id) {
            $return[$id] = $this->pdf($id);
        }

        return Coolrunner_Helper::result(false, 'PDF output is returned for each parcel as an array', $return);
    }
}
