<?php

class Coolrunner_Setting
{
	public static function all(){
		return [
			'api_username'			=> self::api_username(),
			'api_token'				=> self::api_token(),
			'sender_name'			=> self::sender_name(),
			'sender_street1'		=> self::sender_street1(),
			'sender_street2'		=> self::sender_street2(),
			'sender_postcode'		=> self::sender_postcode(),
			'sender_city'			=> self::sender_city(),
			'sender_country'		=> self::sender_country(),
			'sender_phone'			=> self::sender_phone(),
			'sender_email'			=> self::sender_email(),
			'sender_attention'		=> self::sender_attention(),
			'label_format'			=> self::label_format(),
		];
	}

	public static function api_username()
	{
		return get_option('coolrunner_api_username');
	}
	
	public static function api_token()
	{
		return get_option('coolrunner_api_token');
	}

	public static function sender_name()
	{
		return get_option('coolrunner_sender_name');
	}

	public static function sender_attention()
	{
		return get_option('coolrunner_sender_attention');
	}

	public static function sender_street1()
	{
		return get_option('coolrunner_sender_street1');
	}

	public static function sender_street2()
	{
		return get_option('coolrunner_sender_street2');
	}

	public static function sender_postcode()
	{
		return get_option('coolrunner_sender_postcode');
	}

	public static function sender_city()
	{
		return get_option('coolrunner_sender_city');
	}

	public static function sender_country()
	{
		//Is is not allowed to send from other countries than DK
		//Coolrunner policy/agreement
		return 'DK';
	}

	public static function sender_phone()
	{
		return get_option('coolrunner_sender_phone');
	}

	public static function sender_email()
	{
		return get_option('coolrunner_sender_email');
	}
	
	public static function label_format()
	{
		return get_option('coolrunner_label_format');
	}
}