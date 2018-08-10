<?php
namespace content_su\tg\sendphoto;

class model
{
	public static function post()
	{
		$chatid = \dash\request::post('chatid');
		$text   = \dash\request::post('text');

		$myFile = \dash\app\file::upload_quick('file2');
		if(!$myFile)
		{
			$myFile = \dash\request::post('file1');
		}
		if(!$myFile)
		{
			\dash\notif::error(T_('Please add url or choose file'));
			return false;
		}


		$myData   = ['chat_id' => $chatid, 'photo' => $myFile, 'caption' => $text];
		$myResult = \dash\social\telegram\tg::sendPhoto($myData);

		\dash\session::set('tg_send', json_encode(, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
		\dash\session::set('tg_response', json_encode($myResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

		\dash\redirect::pwd();
	}
}
?>