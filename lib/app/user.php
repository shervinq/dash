<?php
namespace dash\app;


class user
{

	use \dash\app\user\add;
	use \dash\app\user\edit;
	use \dash\app\user\datalist;
	use \dash\app\user\get;
	use \dash\app\user\user_id;



	public static function check_duplicate($_national_code, $_passport_code)
	{
		$result = false;
		if($_national_code)
		{
			// check not duplicate nationalcode only
			$result = \dash\db\users::get(['nationalcode' => "$_national_code",  'limit' => 1]);
		}
		else
		{
			// check pasportcode only
			$result = \dash\db\users::get(['pasportcode' => "$_passport_code", 'limit' => 1]);
		}

		return $result;

	}

	/**
	 * check args
	 *
	 * @return     array|boolean  ( description_of_the_return_value )
	 */
	public static function check($_id = null, $_option = [])
	{
		$args                    = [];

		$default_option =
		[
			'debug'    => true,
		];

		if(!is_array($_option))
		{
			$_option = [];
		}

		$_option = array_merge($default_option, $_option);

		$debug = $_option['debug'];


		// if the force_add is true
		// we not check some of requirement arguments
		// just the supervisor can set this arguments
		$force_add = false;
		if(\dash\app::request('force_add'))
		{
			$force_add = true;
		}

		$mobile = \dash\app::request('mobile');
		if(\dash\app::isset_request('mobile'))
		{
			if(!$mobile && !$force_add)
			{
				if($debug) \dash\notif::error(T_("Mobile is required"), 'mobile');
				return false;
			}
		}

		if($mobile && !\dash\utility\filter::mobile($mobile))
		{
			if($debug) \dash\notif::error(T_("Invalid mobile"), 'mobile');
			return false;
		}

		if($mobile)
		{
			$mobile = \dash\utility\filter::mobile($mobile);
		}

		$firstname = \dash\app::request('firstname');
		if($firstname && mb_strlen($firstname) > 100)
		{
			if($debug) \dash\notif::error(T_("Plese set firstname less than 100 character"), 'firstname');
			return false;
		}

		$lastname = \dash\app::request('lastname');
		if($lastname && mb_strlen($lastname) > 100)
		{
			if($debug) \dash\notif::error(T_("Plese set firstname less than 100 character"), 'lastname');
			return false;
		}

		$father = \dash\app::request('father');
		if($father && mb_strlen($father) > 100)
		{
			if($debug) \dash\notif::error(T_("Invalid father"), 'father');
			return false;
		}

		$nationality = \dash\app::request('nationality');
		if($nationality && !\dash\utility\location\countres::check($nationality))
		{
			if($debug) \dash\notif::error(T_("Invalid nationality"), 'nationality');
			return false;
		}

		$nationalcode = \dash\app::request('nationalcode');
		if($nationalcode && !\dash\utility\filter::nationalcode($nationalcode))
		{
			if($debug) \dash\notif::error(T_("Invalid nationalcode syntax"), 'nationalcode');
			return false;
		}


		$pasportcode = \dash\app::request('pasportcode');
		$pasportcode = mb_strtolower($pasportcode);
		$pasportcode = \dash\utility\convert::to_en_number($pasportcode);
		if($pasportcode && mb_strlen($pasportcode) > 30 )
		{
			if($debug) \dash\notif::error(T_("Invalid pasportcode"), 'pasportcode');
			return false;
		}


		$birthdate = null;
		if(\dash\app::isset_request('birthdate'))
		{
			$birthdate = \dash\app::request('birthdate');
			if($birthdate)
			{
				$birthdate = \dash\date::db($birthdate);
				$birthdate = \dash\date::birthdate($birthdate, true);

				if(!$birthdate)
				{
					return false;
				}
			}
		}

		$pasportdate = \dash\app::request('pasportdate');
		$pasportdate = \dash\date::db($pasportdate);
		if($pasportdate === false)
		{
			if($debug) \dash\notif::error(T_("Invalid pasportdate"), 'pasportdate');
			return false;
		}

		if($pasportdate)
		{
			if(\dash\utility\jdate::is_jalali($pasportdate))
			{
				$pasportdate = \dash\utility\jdate::to_gregorian($pasportdate);
			}
		}

		$gender = \dash\app::request('gender');
		if($gender && !in_array($gender, ['male', 'female']))
		{
			if($debug) \dash\notif::error(T_("Invalid gender"), 'gender');
			return false;
		}

		$marital = \dash\app::request('marital');
		if($marital && !in_array($marital, ['single', 'married']))
		{
			if($debug) \dash\notif::error(T_("Invalid marital"), 'marital');
			return false;
		}

		$website = \dash\app::request('website');
		if($website && mb_strlen($website) > 100)
		{
			if($debug) \dash\notif::error(T_("Please set website less than 100 character"), 'website');
			return false;
		}

		$instagram = \dash\app::request('instagram');
		if($instagram && mb_strlen($instagram) > 100)
		{
			if($debug) \dash\notif::error(T_("Please set instagram less than 100 character"), 'instagram');
			return false;
		}

		$linkedin = \dash\app::request('linkedin');
		if($linkedin && mb_strlen($linkedin) > 100)
		{
			if($debug) \dash\notif::error(T_("Please set linkedin less than 100 character"), 'linkedin');
			return false;
		}

		$facebook = \dash\app::request('facebook');
		if($facebook && mb_strlen($facebook) > 100)
		{
			if($debug) \dash\notif::error(T_("Please set facebook less than 100 character"), 'facebook');
			return false;
		}

		$twitter = \dash\app::request('twitter');
		if($twitter && mb_strlen($twitter) > 100)
		{
			if($debug) \dash\notif::error(T_("Please set twitter less than 100 character"), 'twitter');
			return false;
		}


		$detail = [];

		if($_id)
		{
			$load = \dash\db\users::get_by_id($_id);
			if(isset($load['detail']))
			{
				$detail = json_decode($load['detail'], true);
			}
		}

		if(!$detail || !is_array($detail))
		{
			$detail = [];
		}

		$shcode = \dash\app::request('shcode');
		$shcode = \dash\utility\convert::to_en_number($shcode);
		if($shcode && !is_numeric($shcode))
		{
			if($debug) \dash\notif::error(T_("Invalid shcode"), 'shcode');
			return false;
		}

		if($shcode && intval($shcode) > 1E+10)
		{
			if($debug) \dash\notif::error(T_("Invalid shcode"), 'shcode');
			return false;
		}

		if(\dash\app::isset_request('shcode'))
		{
			$detail['shcode'] = $shcode;
		}


		$birthcity = \dash\app::request('birthcity');
		if($birthcity && mb_strlen($birthcity) > 50)
		{
			if($debug) \dash\notif::error(T_("Invalid birthcity"), 'birthcity');
			return false;
		}

		if(\dash\app::isset_request('birthcity'))
		{
			$detail['birthcity'] = $birthcity;
		}


		$religion = \dash\app::request('religion');
		if($religion && mb_strlen($religion) > 50)
		{
			if($debug) \dash\notif::error(T_("Invalid religion"), 'religion');
			return false;
		}

		if(\dash\app::isset_request('religion'))
		{
			$detail['religion'] = $religion;
		}


		$avatar = \dash\app::request('avatar');
		if($avatar && mb_strlen($avatar) > 2000)
		{
			if($debug) \dash\notif::error(T_("Invalid avatar"), 'avatar');
			return false;
		}

		$education = \dash\app::request('education');
		if($education && mb_strlen($education) > 100)
		{
			if($debug) \dash\notif::error(T_("Invalid education"), 'education');
			return false;
		}

		if(\dash\app::isset_request('education'))
		{
			$detail['education'] = $education;
		}


		$educationcourse = \dash\app::request('educationcourse');
		if($educationcourse && mb_strlen($educationcourse) > 100)
		{
			if($debug) \dash\notif::error(T_("Invalid educationcourse"), 'educationcourse');
			return false;
		}

		if(\dash\app::isset_request('educationcourse'))
		{
			$detail['educationcourse'] = $educationcourse;
		}

		$shfrom = \dash\app::request('shfrom');
		if ($shfrom && mb_strlen($shfrom) > 200)
		{
			if($debug) \dash\notif::error(T_("Invalid issue place"), 'shfrom');
			return false;
		}

		if(\dash\app::isset_request('shfrom'))
		{
			$detail['shfrom'] = $shfrom;
		}

		if(\dash\app::isset_request('file1'))
		{
			$detail['file1'] = \dash\app::request('file1');
		}

		if(\dash\app::isset_request('file2'))
		{
			$detail['file2'] = \dash\app::request('file2');
		}


		$email = \dash\app::request('email');
		if ($email && mb_strlen($email) > 150)
		{
			if($debug) \dash\notif::error(T_("Invalid email"), 'email');
			return false;
		}

		if(\dash\app::isset_request('email'))
		{
			$detail['email'] = $email;
		}

		$phone = \dash\app::request('phone');
		if($phone && mb_strlen($phone) > 50)
		{
			if($debug) \dash\notif::error(T_("Invalid phone"), 'phone');
			return false;
		}

		$status = \dash\app::request('status');
		if($status && !in_array($status, ['active','awaiting','deactive','removed','filter','unreachable']))
		{
			if($debug) \dash\notif::error(T_("Invalid status"), 'status');
			return false;
		}


		if(\dash\app::isset_request('permission'))
		{
			$permission = \dash\app::request('permission');
			if(\dash\permission::check("aMemberPermissionChange"))
			{
				if($permission && !in_array($permission, array_keys(\dash\permission::groups())))
				{
					if($permission === 'supervisor')
					{
						if(!\dash\permission::supervisor())
						{
							if($debug) \dash\notif::error("Permission is incorrect", 'permission');
							return false;
						}
						else
						{
							// no problem
							// supervisor make a new supervisor
						}
					}
					else
					{
						if($debug) \dash\notif::error(T_("Permission is incorrect"), 'permission');
						return false;
					}
				}
			}

			$args['permission']          = $permission;
		}


		if(!empty($detail))
		{
			$args['detail'] = json_encode($detail, JSON_UNESCAPED_UNICODE);
		}

		$twostep       = \dash\app::request('twostep') ? 1 : null;
		$forceremember = \dash\app::request('forceremember') ? 1 : null;

		$password = \dash\app::request('password');

		if(\dash\permission::check("cpUsersPasswordChange"))
		{
			if($password)
			{
				if(mb_strlen($password) < 6)
				{
					if($debug) \dash\notif::error(T_("Plase set password larger than 6 character"), ['element' => ['password', 'repassword']]);
					return false;
				}

				$args['password'] = \dash\utility::hasher($password, null, false);
				if(!\dash\engine\process::status())
				{
					return false;
				}
			}
		}

		$title = \dash\app::request('title');
		if($title && mb_strlen($title) > 100)
		{
			if($debug) \dash\notif::error(T_("Plase set title less than 100 character"), 'title');
			return false;
		}

		$bio = \dash\app::request('bio');
		if($bio && mb_strlen($bio) > 100)
		{
			if($debug) \dash\notif::error(T_("Plase set bio less than 100 character"), 'bio');
			return false;
		}

		$displayname = \dash\app::request('displayname');
		if($displayname && mb_strlen($displayname) > 100)
		{
			if($debug) \dash\notif::error(T_("Plase set displayname less than 100 character"), 'displayname');
			return false;
		}


		$language = \dash\app::request('language');
		if($language && !\dash\language::check($language))
		{
			if($debug) \dash\notif::error(T_("Language is incorrect"), 'language');
			return false;
		}

		$username = \dash\app::request('username');
		if($username)
		{
			if(mb_strlen($username) < 4)
			{
				if($debug) \dash\notif::error(T_("Please set the username larger than 4 character"), 'username');
				return false;
			}

			if(mb_strlen($username) > 50)
			{
				if($debug) \dash\notif::error(T_("Please set the username less than 50 character"), 'username');
				return false;
			}

			if($username && !preg_match("/^[A-Za-z0-9]+$/", $username))
			{
				if($debug) \dash\notif::error(T_("Only [A-Za-z0-9] can use in username"), 'username');
				return false;
			}

			$check_duplicate_username = \dash\db\users::get(['username' => $username, 'limit' => 1]);
			if(isset($check_duplicate_username['id']))
			{
				if(intval($check_duplicate_username['id']) === intval($_id))
				{

				}
				else
				{
					if($debug) \dash\notif::error(T_("Duplicate username"), 'username');
					return false;
				}
				$args['username'] = $username;
			}
		}

		if(\dash\app::isset_request('username') && !$username)
		{
			$args['username'] = null;
		}



		$tgstatus = \dash\app::request('tgstatus');
		if($tgstatus && !in_array($tgstatus, ['active','deactive','spam','bot','block','unreachable','unknown','filter', 'awaiting', 'inline', 'callback']))
		{
			if($_option['debug']) if($debug) \dash\notif::error(T_("Invalid parameter tgstatus"), 'tgstatus');
			return false;
		}


		$type = \dash\app::request('type');
		if($type && mb_strlen($type) > 50)
		{
			if($_option['debug']) if($debug) \dash\notif::error(T_("You must set the type less than 50 character"), 'type');
			return false;
		}


		$parent = \dash\app::request('parent');
		$parent = \dash\coding::decode($parent);
		if(!$parent && \dash\app::request('parent'))
		{
			if($_option['debug']) if($debug) \dash\notif::error(T_("Parent is incorrect"), 'parent');
			return false;
		}



		$tgusername = \dash\app::request('tgusername');
		if($tgusername && mb_strlen($tgusername) > 100)
		{
			$tgusername = null;
		}

		$pin = \dash\app::request('pin');
		if(($pin && mb_strlen($pin) > 4) || ($pin && !is_numeric($pin)))
		{
			if($_option['debug']) if($debug) \dash\notif::error(T_("Pin is incorrect"), 'pin');
			return false;
		}

		$ref = \dash\app::request('ref');
		$ref = \dash\coding::decode($ref);
		if(!$ref && \dash\app::request('ref'))
		{
			if($_option['debug']) if($debug) \dash\notif::error(T_("Ref is incorrect"), 'ref');
			return false;
		}


		$unit_id = \dash\app::request('unit_id');
		if($unit_id && !is_numeric($unit_id))
		{
			if($_option['debug']) if($debug) \dash\notif::error(T_("Unit id is incorrect"), 'unit_id');
			return false;
		}


		$chatid = \dash\app::request('chatid');

		$signature = \dash\app::request('signature');


		if($args['permission'] === 'supervisor')
		{
			unset($args['permission']);
		}

		if(!\dash\permission::check("cpUsersPermission"))
		{
			unset($args['permission']);
		}

		if($_id && isset($load['permission']))
		{
			if($load['permission'] === 'supervisor')
			{
				unset($args['permission']);
			}
		}


		$args['username']      = $username;
		$args['language']      = $language;
		$args['title']         = $title;
		$args['bio']           = $bio;
		$args['displayname']   = $displayname;
		$args['mobile']        = $mobile;
		$args['nationalcode']  = $nationalcode;
		$args['pasportcode']   = $pasportcode;
		$args['firstname']     = $firstname;
		$args['lastname']      = $lastname;
		$args['father']        = $father;
		$args['birthday']      = $birthdate;
		$args['pasportdate']   = $pasportdate;
		$args['gender']        = $gender;
		$args['marital']       = $marital;
		$args['avatar']        = $avatar;
		$args['nationality']   = $nationality;
		$args['phone']         = $phone;
		$args['status']        = $status;
		$args['desc']          = $desc;
		$args['website']       = $website;
		$args['instagram']     = $instagram;
		$args['linkedin']      = $linkedin;
		$args['facebook']      = $facebook;
		$args['twitter']       = $twitter;
		$args['twostep']       = $twostep;
		$args['forceremember'] = $forceremember;
		$args['signature']     = $signature;
		$args['type']          = $type;
		$args['parent']        = $parent;
		$args['pin']           = $pin;
		$args['ref']           = $ref;
		$args['unit_id']       = $unit_id;
		$args['chatid']        = $chatid;
		$args['tgstatus']      = $tgstatus;



		return $args;
	}


	/**
	 * ready data of user to load in api
	 *
	 * @param      <type>  $_data  The data
	 */
	public static function ready($_data, $_id = null)
	{
		$result = [];
		foreach ($_data as $key => $value)
		{

			switch ($key)
			{
				case 'permission':
					if($value === 'supervisor' && !\dash\permission::supervisor())
					{
						return false;
					}
					else
					{
						$result[$key] = $value;
					}

					break;
				case 'id':
				case 'creator':
				case 'parent':
					if(isset($value))
					{
						$result[$key] = \dash\coding::encode($value);
					}
					else
					{
						$result[$key] = null;
					}
					break;

				case 'avatar':
					if($_id)
					{
						$result['avatar'] = $value;
					}
					else
					{
						if(isset($_data['gender']))
						{
							if($_data['gender'] === 'male')
							{
								$avatar = \dash\app::static_avatar_url('male');
							}
							else
							{
								$avatar = \dash\app::static_avatar_url('female');
							}
						}
						else
						{
							$avatar = \dash\app::static_avatar_url();
						}
						$result['avatar'] = $value ? $value : $avatar;
					}
					break;

				case 'sidebar':
					if($value || $value === null)
					{
						$result[$key] = true;
					}
					else
					{
						$result[$key] = false;
					}

					break;

				case 'forceremember':
					if($value === null)
					{
						if(\dash\option::config('enter', 'remember_me'))
						{
							$result[$key] = true;
						}
						else
						{
							$result[$key] = false;
						}
					}
					elseif($value)
					{
						$result[$key] = true;
					}
					else
					{
						$result[$key] = false;
					}
					break;

				default:
					$result[$key] = $value;
					break;
			}
		}

		return $result;
	}

}
?>