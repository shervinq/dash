<?php
namespace dash\app\log\caller\ticket;

class ticket_seeTicket
{
	public static function site($_args = [])
	{
		$code = isset($_args['code']) ? $_args['code'] : null;

		$result              = [];
		$result['title']     = T_("See ticket");
		$result['icon']      = 'life-ring';
		$result['cat']       = T_("Support");
		$result['iconClass'] = 'fc-red';

		$excerpt  = '<span class="fc-green">'.\dash\app\log\msg::displayname($_args). '</span> ';

		$excerpt .= T_("see ticket");

		$excerpt .= ' ';
		$excerpt .=	'<a href="'.\dash\url::kingdom(). '/!'. $code. '">';
		$excerpt .= T_("Show ticket");
		$excerpt .= ' ';
		$excerpt .= \dash\utility\human::fitNumber($code, false);
		$excerpt .= '</a>';

		$result['txt'] = $excerpt;

		return $result;
	}

	public static function send_to()
	{
		return ['supportTicketAnswer'];
	}

	public static function expire()
	{
		return date("Y-m-d H:i:s", strtotime("+100 days"));
	}

	public static function is_notif()
	{
		return true;
	}

	public static function telegram()
	{
		return true;
	}

	public static function telegram_text($_args, $_chat_id)
	{
		$code = isset($_args['code']) ? $_args['code'] : null;

		$tg_msg = '';
		$tg_msg .= "🆔#Ticket".$code;
		$tg_msg .= "\n🙄 ". \dash\log::from_name(). " #user". \dash\log::from_id();
		$tg_msg .= "\n⏳ ". \dash\datetime::fit(date("Y-m-d H:i:s"), true);

		$tg                 = [];
		$tg['chat_id']      = $_chat_id;
		$tg['caption']      = $tg_msg;
		$tg['method']       = 'sendDocument';
		$tg['document']     = "https://media.giphy.com/media/3oz8xyBP22S5b6gmsw/giphy.gif";
		$tg['reply_markup'] = \dash\app\log\support_tools::tg_btn2($code);

		// $tg = json_encode($tg, JSON_UNESCAPED_UNICODE);

		return $tg;
	}
}
?>