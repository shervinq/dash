<?php
namespace content_account\profile\signature;


class view
{

	public static function config()
	{
		\dash\data::page_title(T_('Edit your profile'));
		\dash\data::page_desc(T_('You can edit your profile.'));

		\dash\data::badge_link(\dash\url::kingdom(). '/a');
		\dash\data::badge_text(T_('Back to dashbaord'));

		$id = \dash\user::id();

		if(!$id)
		{
			\dash\header::status(404, T_("Invalid user id"));
		}

		$user_detail = \dash\db\users::get_by_id($id);

		if(!$user_detail)
		{
			\dash\header::status(404, T_("User id not found"));
		}

		\dash\data::dataRow(\dash\app\user::ready($user_detail, true));
	}
}
?>
