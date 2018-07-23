<?php
namespace content_support\ticket\contact_ticket;

class model
{

	public static function check_input_time()
	{
		$session_name = 'contact_ticket_count';
		$time         = 60 * 3; // 3 min
		$count        = 3;      // 3 times

		$count = \dash\session::get($session_name);
		if($count)
		{
			\dash\session::set($session_name, $count + 1, null, $time);
		}
		else
		{
			\dash\session::set($session_name, 1, null, $time);
		}

		if($count >= $count && !\dash\permission::supervisor())
		{
			\dash\log::db('tryCount>inMins');
			\dash\notif::error(T_("You hit our maximum try limit."). ' '. T_("Try again later!"));
			return false;
		}

		return true;
	}

	/**
	 * save contact form
	 */
	public static function post()
	{
		if(!self::check_input_time())
		{
			return false;
		}

		// get the content
		$content = \dash\request::post("content");

		// check content
		if($content == '' || !trim($content))
		{
			\dash\notif::error(T_("Please try type something!"), "content");
			return false;
		}

		// check login
		if(\dash\user::login())
		{
			// add new ticket
			$user_id = \dash\user::id();

			// get mobile from user login session
			$mobile = \dash\user::detail('mobile');

			if(!$mobile)
			{
				$mobile = \dash\request::post('mobile');
			}

			// get display name from user login session
			$displayname = \dash\user::detail("displayname");
			// user not set users display name, we get display name from contact form
			if(!$displayname)
			{
				$displayname = \dash\request::post("name");
			}
			// get email from user login session
			$email = \dash\user::detail('email');
			// user not set users email, we get email from contact form
			if(!$email)
			{
				$email = \dash\request::post("email");
			}

		}
		else
		{
			// users not registered
			$user_id     = null;
			$displayname = \dash\request::post("name");
			$email       = \dash\request::post("email");
			$mobile      = \dash\request::post("mobile");
		}

		/**
		 * register user if set mobile and not register
		 */
		if($mobile && !\dash\user::login())
		{
			// check valid mobile
			if(\dash\utility\filter::mobile($mobile))
			{
				// check existing mobile
				$exists_user = \dash\db\users::get_by_mobile($mobile);
				// register if the mobile is valid
				if(!$exists_user || empty($exists_user))
				{
					// signup user by site_guest
					$user_id = \dash\db\users::signup(['mobile' => $mobile, 'displayname' => $displayname]);

					if(!$user_id)
					{
						$user_id = null;
					}

					// save log by caller 'user:send:contact:register:by:mobile'
					\dash\log::db('contactRegisterByMobile');
				}
			}
		}

		if($user_id)
		{
			$args =
			[
				'author'  => $displayname,
				'email'   => $email,
				'type'    => 'ticket',
				'content' => $content,
				'title'   => T_("Contact Us"),
				'mobile'  => $mobile,
				'user_id' => $user_id,

			];

			$result = \dash\app\comment::add($args);
			if($result)
			{
				$ticket_link = '<a href="'. \dash\url::site(). '/support">'. T_("You can check your contacting answer here") .'</a>';
				\dash\notif::ok(T_("Thank You For contacting us"). ' '. $ticket_link);
			}
			else
			{
				\dash\log::db('contactUsLoginNotSave');
				\dash\notif::error(T_("We could'nt save the contact"));
			}
			return;
		}
		else
		{
			// ready to insert comments
			$args =
			[
				'author'  => $displayname,
				'email'   => $email,
				'type'    => 'contact',
				'content' => $content,
				'user_id' => null,
			];
			// insert comments
			$result = \dash\db\comments::insert($args);
			if($result)
			{
				\dash\notif::ok(T_("Thank You For contacting us"));
			}
			else
			{
				\dash\log::db('contactFail');
				\dash\notif::error(T_("We could'nt save the contact"));
			}

		}
	}
}
?>