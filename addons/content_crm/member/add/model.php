<?php
namespace content_crm\member\add;


class model
{

	public static function getPost()
	{
		$post =
		[
			'mobile'      => \dash\request::post('mobile'),
		];

		return $post;
	}


	public static function post()
	{
		// ready request
		$request = self::getPost();

		$result = \dash\app\member::add($request);

		if(\dash\engine\process::status())
		{
			if(isset($result['member_id']))
			{
				\dash\redirect::to(\dash\url::here(). '/member/general?id='. $result['member_id']);
			}
			else
			{
				\dash\redirect::to(\dash\url::here(). '/member');
			}
		}
	}
}
?>