<?php
namespace lib\app\user;


trait user_id
{
	/**
	 * find user id
	 *
	 * @return     boolean  ( description_of_the_return_value )
	 */
	public static function find_user_id($_args, $_options = [], $_edit_mode = false, $_old_user_id = null)
	{
		$user_id     = null;

		$new_mobile = false;

		if(isset($_args['mobile']) && $_args['mobile'])
		{
			$new_mobile = true;
		}
		// insert mode
		if(!$_edit_mode)
		{
			// INSERT NEW USER
			if(!$new_mobile)
			{
				$user_id = \lib\db\users::signup($_args);
			}
			else
			{
				$check_mobile_exist = \lib\db\users::get_by_mobile($_args['mobile']);
				if(isset($check_mobile_exist['id']) && is_numeric($check_mobile_exist['id']))
				{
					$user_id = $check_mobile_exist['id'];
				}
				else
				{
					$user_id = \lib\db\users::signup($_args);
				}
			}
		}
		else
		{
			// EDIT OLD USER
			$old_user_detail = \lib\db\users::get_by_id($_old_user_id);
			if(!isset($old_user_detail['id']))
			{
				\lib\debug::error(T_("User not found."));
				return false;
			}

			$old_mobile = false;
			if(isset($old_user_detail['mobile']) && $old_user_detail['mobile'])
			{
				$old_mobile = true;
			}

			if($new_mobile)
			{
				if($old_mobile)
				{
					if(\lib\utility\filter::mobile($_args['mobile']) === \lib\utility\filter::mobile($old_user_detail['mobile']))
					{
						$user_id = $old_user_detail['id'];
					}
					else
					{
						$check_mobile_exist = \lib\db\users::get_by_mobile($_args['mobile']);
						if(isset($check_mobile_exist['id']) && is_numeric($check_mobile_exist['id']))
						{
							$user_id = $check_mobile_exist['id'];
						}
						else
						{
							$user_id = \lib\db\users::signup($_args);
						}
					}
				}
				else
				{
					$check_mobile_exist = \lib\db\users::get_by_mobile($_args['mobile']);
					if(isset($check_mobile_exist['id']) && is_numeric($check_mobile_exist['id']))
					{
						\lib\db\users::update(['status' => 'unreachable'], $old_user_detail['id']);
						$user_id = $check_mobile_exist['id'];
					}
					else
					{
						// set this mobile to this user
						\lib\db\users::update(['mobile' => $_args['mobile']], $old_user_detail['id']);
						$user_id = $old_user_detail['id'];
					}
				}
			}
			else
			{
				// new mobile not set
				if($old_mobile)
				{
					$user_id = \lib\db\users::signup($_args);
				}
				else
				{
					$user_id = $old_user_detail['id'];
				}
			}
		}

		return intval($user_id);
	}
}
?>