<?php
namespace content_cp\sms\send;


class model
{
	public static function post()
	{
		$template_post     = \dash\request::post('template');
		$mobile            = \dash\request::post('mobile');
		if(\dash\request::post('changeTemplate'))
		{
			$query             = [];
			$query['mobile']   = $mobile;
			$query['template'] = $template_post;

			\dash\redirect::to(\dash\url::this(). '/send?'. http_build_query($query));

			return;
		}


		$msg = \dash\request::post('msg');
		if(!$msg)
		{
			\dash\notif::error(T_("No message was sended"), 'msg');
			return false;
		}

		if(!$mobile)
		{
			$mobile = \dash\request::post('mobile');
		}

		$mobile = \dash\utility\filter::mobile($mobile);

		if(!$mobile)
		{
			\dash\notif::error(T_("Invalid mobile number"), 'mobile');
			return false;
		}

		\dash\utility\sms::send($mobile, $msg);

		\dash\notif::ok("SMS was sended");

	}
}
?>
