<?php
namespace dash\social\telegram\commands;
// use telegram class as bot
use \dash\social\telegram\tg as bot;

class ticket
{
	public static function run($_cmd)
	{
		switch ($_cmd['command'])
		{
			case 'cb_ticket':
			case '/ticket':
			case 'ticket':
			case T_('/ticket'):
			case T_('ticket'):

				if(isset($_cmd['optional']))
				{
					if(is_numeric($_cmd['optional']))
					{
						if(isset($_cmd['argument']))
						{
							switch ($_cmd['argument'])
							{
								case 'answer':
									// go to step of answer
									step_ticketAnswer::start($_cmd);
									break;

								case 'solved':
									break;

								case 'close':
									break;

								case 'open':
									break;

								case 'spam':
									break;

								default:
									// do nothing
									break;
							}
						}
						else
						{
							// want to see ticket
							self::show($_cmd);
						}
					}
					else
					{
						// show message of need numeric code
						return self::requireCode();
					}
				}
				else
				{
					step_ticketCreate::start();
				}
				break;
		}
	}



	public static function show($_cmd)
	{
		bot::ok();

		$ticketNo = \dash\utility\convert::to_en_number($_cmd['optional']);
		$txt_text = \dash\app\tg\ticket::list($ticketNo);

		if($txt_text)
		{
			$result =
			[
				'text'         => $txt_text,
				'reply_markup' =>
				[
					'inline_keyboard' =>
					[
						[
							[
								'text' => T_("Visit in site"),
								'url'  => \dash\url::base(). '/!'. $ticketNo,
							],
						],
						[
							[
								'text'          => 	T_("Answer"),
								'callback_data' => 'ticket '. $ticketNo. ' answer',
							],
						],
					]
				]
			];

			// if start with callback answer callback
			if(bot::isCallback())
			{
				$result =
				[
					'text' => T_("Check ticket "). $ticketNo,
				];
				bot::answerCallbackQuery($result);
			}
			else
			{
				$result['reply_to_message_id'] = true;
			}

			bot::sendMessage($result);
		}
		else
		{
			$result =
			[
				'text' => T_("We can't find detail of this ticket!"),
				'show_alert' => true,
			];
			bot::answerCallbackQuery($result);
		}
	}



	public static function requireCode()
	{
		bot::ok();

		// $result =
		// [
		// 	'text' => T_("We need ticket number!")." 🙁",
		// 	'show_alert' => true,
		// ];
		// bot::answerCallbackQuery($result);

		$result =
		[
			'text' => T_("We need ticket number!")." 🙁",
		];
		bot::sendMessage($result);
	}

}
?>