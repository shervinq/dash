<?php
namespace content_api;


class controller
{
	public static $v5 = [];

	public static function routing()
	{

	}


	public static function end5($_result = [])
	{
		if($_result)
		{
			\dash\notif::result($_result);
		}
		\dash\code::jsonBoom(\dash\notif::get());
	}


	public static function check_authorization_v5()
	{
		$authorization = \dash\header::get('authorization');

		if(!isset($authorization))
		{
			\dash\header::status(400, T_("Authorization not set"));
		}

		self::$v5['authorization'] = $authorization;

		$x_app_request = \dash\header::get('x-app-request');

		if(!isset($x_app_request))
		{
			\dash\header::status(401, T_("x-app-request not found"));
		}

		self::$v5['x_app_request'] = $x_app_request;

		$token = \dash\option::config('app_token', $x_app_request);

		if(!$token)
		{
			\dash\header::status(401, T_("Token not found"));
		}

		if($token !== $authorization)
		{
			\dash\header::status(401, T_("Invalid token"));
		}

		self::$v5['app_token'] = $token;

	}


	public static function check_authorization2_v5()
	{
		self::check_authorization_v5();

		$auth2 = \dash\header::get('auth2');

		if(!$auth2 || mb_strlen($auth2) !== 32)
		{
			\dash\header::status(401, T_("Invalid auth2"));
		}

		$get =
		[
			'status'  => 'enable',
			'user_id' => null,
			'type'    => 'guest',
			'auth'    => $auth2,
			'limit'   => 1,
		];

		$get = \dash\db\user_auth::get($get);

		if(!isset($get['id']) || !isset($get['datecreated']))
		{
			\dash\header::status(401, T_("Invalid auth2"));
		}

		$time_left = time() - strtotime($get['datecreated']);

		$life_time = 60 * 3;

		if($time_left > $life_time)
		{
			\dash\db\user_auth::update(['status' => 'expire'], $get['id']);
			\dash\header::status(401, T_("Auth2 is expire"));
		}
	}


	public static function check_authorization3_v5()
	{
		self::check_authorization_v5();

	}



}
?>