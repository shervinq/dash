<?php
namespace content_enter\okay;


class view
{
	public static function config()
	{
		\dash\data::page_title(T_('Horray!'));
		\dash\data::page_special(true);
		\dash\data::page_desc(T_('Live and learn'));

		\dash\data::redirectUrl(\dash\url::base());
		if(\dash\utility\enter::get_session('redirect_url'))
		{
			\dash\data::redirectUrl(\dash\utility\enter::get_session('redirect_url'));
		}
	}
}
?>