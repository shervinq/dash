<?php
namespace content_api\v5\enter;


class controller
{
	private static $user_code;
	private static $user_token;
	private static $mobile;
	private static $verify_code;
	private static $x_app_request;
	private static $user_id;
	private static $user_android;
	private static $mobile_user_id;

	private static $life_time = 60 * 5;


	public static function routing()
	{
		\content_api\controller::check_authorization_v5();

		$subchild = \dash\url::subchild();

		if(!$subchild)
		{
			self::login();
			\dash\code::end();
		}
		elseif($subchild === 'verify')
		{
			self::verify();
			\dash\code::end();
		}

	}

	private static function verify()
	{
		$check_input = self::check_input();
		if(!$check_input)
		{
			return false;
		}

		if(!self::$verify_code)
		{
			\dash\notif::error(T_("Verification code not set"), 'verify_code');
			return false;
		}

		$check_true = self::check_true_user();
		if(!$check_true)
		{
			return false;
		}

		$user_id = \dash\db\users::signup(['mobile' => self::$mobile]);
		if(!$user_id)
		{
			\dash\log::set('API-canNotSignupUserEnterVerify');
			\dash\notif::error(T_("Can not signup this mobile"));
			return false;
		}

		self::$mobile_user_id = $user_id;

		$check_log =
		[
			'caller' => 'enter_apiverificationcode',
			'to'     => $user_id,
			'limit'  => 1,
		];

		$check_log = \dash\db\logs::get($check_log, ['order' => 'ORDER BY logs.id DESC']);

		$generate_new_code = false;

		if(!isset($check_log['id']))
		{
			\dash\notif::error(T_("No verifycation code sended to this phone number"));
			return false;
		}
		else
		{
			if(isset($check_log['status']) && in_array($check_log['status'], ['enable', 'notif', 'notifread']))
			{
				if(isset($check_log['datecreated']))
				{
					$old_time = strtotime($check_log['datecreated']);
					if((time() - $old_time) < self::$life_time)
					{
						if(isset($check_log['code']))
						{
							if(intval($check_log['code']) === intval(self::$verify_code))
							{
								\dash\db\logs::update(['status' => 'expire'], $check_log['id']);
								self::user_login_true();
								return true;
							}
							else
							{
								\dash\notif::error(T_("Invalid code"));
								return false;
							}
						}
						else
						{
							\dash\notif::error(T_("Verification code not set"));
							return false;
						}
					}
					else
					{
						\dash\notif::error(T_("Verification code was expired"));
						return false;
					}
				}
				else
				{
					\dash\notif::error(T_("Verification code not found"));
					return false;
				}
			}
			else
			{
				\dash\notif::error(T_("Verification code not found"));
				return false;
			}
		}
	}

	private static function user_login_true()
	{
		$result               = [];
		$result['user_token'] = self::$user_token;


		if(intval(self::$user_id) === intval(self::$mobile_user_id))
		{
			$result['user_code'] = self::$user_code;
		}
		else
		{
			\dash\db\user_android::update_where(['user_id' => self::$mobile_user_id], ['uniquecode' => self::$user_token, 'user_id' => self::$user_id]);
			$result['user_code'] = \dash\coding::encode(self::$mobile_user_id);
		}

		\dash\notif::result($result);
		\dash\notif::ok(T_("Code ok"));
	}


	private static function login()
	{
		$check_input = self::check_input();
		if(!$check_input)
		{
			return false;
		}

		$check_true = self::check_true_user();
		if(!$check_true)
		{
			return false;
		}

		$user_id = \dash\db\users::signup(['mobile' => self::$mobile]);
		if(!$user_id)
		{
			\dash\log::set('API-canNotSignupUserEnter');
			\dash\notif::error(T_("Can not signup this mobile"));
			return false;
		}

		self::$mobile_user_id = $user_id;

		$check_log =
		[
			'caller' => 'enter_apiverificationcode',
			'to'     => $user_id,
			'limit'  => 1,
		];

		$check_log = \dash\db\logs::get($check_log, ['order' => 'ORDER BY logs.id DESC']);

		$generate_new_code = false;

		if(!isset($check_log['id']))
		{
			$generate_new_code = true;
		}
		else
		{
			// 'enable','disable','expire','deliver','awaiting','deleted','cancel','block','notif','notifread','notifexpire'
			if(isset($check_log['status']) && in_array($check_log['status'], ['enable', 'notif', 'notifread']))
			{
				if(isset($check_log['datecreated']))
				{
					$old_time = strtotime($check_log['datecreated']);
					if((time() - $old_time) > self::$life_time)
					{
						$generate_new_code = true;
					}
				}
				else
				{
					$generate_new_code = true;
				}
			}
			else
			{
				$generate_new_code = true;
			}
		}


		if($generate_new_code)
		{
			$myCode = rand(10000, 99999);

			$log =
			[
				'to'     => $user_id,
				'code'   => $myCode,
				'mycode' => $myCode,
			];

			\dash\log::set('enter_apiverificationcode', $log);
			\dash\notif::ok(T_("The verification code sended to phone number"));
			return true;
		}
		else
		{
			\dash\notif::error(T_("A verification code was sended to user"));
			return false;
		}
	}


	private static function check_true_user()
	{
		if(self::$x_app_request === 'android')
		{
			$get =
			[
				'user_id'    => self::$user_id,
				'uniquecode' => self::$user_token,
				'limit'      => 1,
			];

			$load               = \dash\db\user_android::get($get);
			self::$user_android = $load;

			if(isset($load['id']))
			{
				self::check_last_update($load, 'user_android');
				return true;
			}
			else
			{
				\dash\log::set('API-InvalidUserCodeAndToken');
				\dash\notif::error(T_("Invalid user_code and user_token"), ['element' => ['user_code', 'user_token']]);
				return false;
			}
		}
		else
		{
			\dash\notif::error(T_("This method was not supported"));
			return false;
		}
	}


	private static function check_last_update($_data, $_table)
	{
		if(array_key_exists('lastupdate', $_data))
		{
			$need_update = false;

			if(!$_data['lastupdate'])
			{
				$need_update = true;
			}
			else
			{
				$lastupdate = strtotime($_data['lastupdate']);
				if((time() - $lastupdate) > (60*60*2))
				{
					$need_update = true;
				}
			}

			if($need_update)
			{
				\dash\db\user_android::update(['lastupdate' => date("Y-m-d H:i:s")], $_data['id']);
			}
		}
	}


	private static function check_input()
	{

		$v5 = \content_api\controller::$v5;

		if(!isset($v5['x_app_request']))
		{
			\dash\notif::error("x_app_request not set", 'header');
			return false;
		}

		if(!in_array($v5['x_app_request'], ['android']))
		{
			\dash\notif::error("invalid x_app_request", 'header');
			return false;
		}

		$x_app_request = $v5['x_app_request'];

		$mobile = \dash\request::post('mobile');
		if(!$mobile)
		{
			\dash\notif::error(T_("Mobile not set"), 'mobile');
			return false;
		}

		$mobile = \dash\utility\filter::mobile($mobile);
		if(!$mobile)
		{
			\dash\notif::error(T_("Invalid mobile"), 'mobile');
			return false;
		}

		$user_code = \dash\request::post('user_code');
		if(!$user_code)
		{
			\dash\notif::error(T_("User code not set"), 'user_code');
			return false;
		}

		$user_id = \dash\coding::decode($user_code);
		if(!$user_id)
		{
			\dash\notif::error(T_("Invalid user_code"), 'user_code');
			return false;
		}


		$user_token = \dash\request::post('user_token');
		if(!$user_token)
		{
			\dash\notif::error(T_("User token not set"), 'user_token');
			return false;
		}

		if(mb_strlen($user_token) !== 32)
		{
			\dash\notif::error(T_("Invalid user_token"), 'user_token');
			return false;
		}

		$verify_code = \dash\request::post('verify_code');
		if($verify_code)
		{
			if(!is_numeric($verify_code))
			{
				\dash\notif::error(T_("Invalid verify_code"), 'verify_code');
				return false;
			}

			$verify_code = intval($verify_code);

			if($verify_code < 10000 || $verify_code > 99999)
			{
				\dash\notif::error(T_("Verification code is out of range"), 'verify_code');
				return false;
			}

		}

		self::$x_app_request = $x_app_request;
		self::$mobile        = $mobile;
		self::$user_code     = $user_code;
		self::$user_id       = $user_id;
		self::$user_token    = $user_token;
		self::$verify_code   = $verify_code;


		return true;
	}
}
?>