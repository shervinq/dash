<?php
namespace addons\content_enter\verify\call;


class model extends \addons\content_enter\main\model
{

	/**
	 * send verification by call
	 *
	 * @return     boolean  ( description_of_the_return_value )
	 */
	public function send_call_code()
	{
		$code = self::get_enter_session('verification_code');

		$my_mobile = null;

		if(self::user_data('mobile'))
		{
			$my_mobile = self::user_data('mobile');
		}
		elseif(self::get_enter_session('mobile'))
		{
			$my_mobile = self::get_enter_session('mobile');
		}
		elseif(self::get_enter_session('temp_mobile'))
		{
			$my_mobile = self::get_enter_session('temp_mobile');
		}

		if(!$my_mobile)
		{
			return false;
		}

		if(!\lib\option::config('enter', 'call'))
		{
			return false;
		}

		$language     = \lib\language::get_language();
		// find template to call by it
		if(\lib\option::config('enter', "call_template_$language"))
		{
			$template   = \lib\option::config('enter', "call_template_$language");
		}
		else
		{
			return false;
		}

		// ready to save log
		$log_meta =
		[
			'data' => $code,
			'meta' =>
			[
				'session' => $_SESSION,
			]
		];


		if(self::$dev_mode)
		{
			$kavenegar_send_result = true;
		}
		else
		{
			$kavenegar_send_result = \lib\utility\call::send($my_mobile, $template, $code);
		}

		if($kavenegar_send_result === 411 && substr($my_mobile, 0, 2) === '98')
		{
			// invalid user mobil
			\lib\db\logs::set('kavenegar:service:411:call', self::user_data('id'), $log_meta);
			return false;
		}
		elseif($kavenegar_send_result === false)
		{
			\lib\db\logs::set('kavenegar:service:down:call', self::user_data('id'), $log_meta);
			// the kavenegar service is down!!!
		}
		elseif($kavenegar_send_result)
		{

			$log_meta['meta']['response'] = [];

			if(is_string($kavenegar_send_result))
			{
				$log_meta['meta']['response'] = $kavenegar_send_result;
			}
			elseif(is_array($kavenegar_send_result))
			{
				foreach ($kavenegar_send_result as $key => $value)
				{
					$log_meta['meta']['response'][$key] = str_replace("\n", ' ', $value);
				}
			}

			\lib\db\logs::set('enter:send:call:result', self::user_data('id'), $log_meta);

			return true;
		}
		else
		{
			\lib\db\logs::set('enter:send:cannot:send:call', self::user_data('id'), $log_meta);
		}

		// why?!
		return false;
	}


	/**
	* cehck sended code
	*
	*/
	public function post_verify()
	{
		// runcall
		if(mb_strtolower(\lib\utility::post('verify')) === 'true')
		{
			if(!self::get_enter_session('run_call_to_user'))
			{
				\lib\debug::result("Call sended");
				self::set_enter_session('run_call_to_user', true);
				$this->send_call_code();
			}
			return;
		}
		self::check_code('call');
	}

}
?>
