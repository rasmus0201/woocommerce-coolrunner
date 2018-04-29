<?php

class Coolrunner_Shipment extends Coolrunner_Api
{

	function create($args)
	{
		$required_keys = [
			'receiver_name',
			'receiver_attention',
			'receiver_street1',
			'receiver_street2',
			'receiver_zipcode',
			'receiver_city',
			'receiver_country',
			'receiver_phone',
			'receiver_email',
			'receiver_notify',
			'receiver_notify_sms',
			'receiver_notify_email',
			'sender_name',
			'sender_attention',
			'sender_street1',
			'sender_street2',
			'sender_zipcode',
			'sender_city',
			'sender_country',
			'sender_phone',
			'sender_email',
			'droppoint',
			'droppoint_id',
			'droppoint_name',
			'droppoint_street1',
			'droppoint_zipcode',
			'droppoint_city',
			'droppoint_country',
			'length',
			'width',
			'height',
			'weight',
			'carrier',
			'carrier_product',
			'carrier_service',
			'insurance',
			'insurance_value',
			'insurance_currency',
			'customs_value',
			'customs_currency',
			'reference',
			'description',
			'comment',
			'label_format',
		];

        $missing_params = array_diff_key(array_flip($required_keys), $args);

        if (!empty($missing_params)) { // We are missing some parameters
            return Coolrunner_Helper::result(true, 'Missing '.count($missing_params).' parameters', $missing_params);
        }

        $response = $this->post('shipment/create', $args);

        return Coolrunner_Helper::formatResponse($response);
	}
}
