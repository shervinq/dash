<?php
namespace dash;


class safe
{
	/**
	 * safe string for sql injection and XSS
	 * @param  string $_string unsafe string
	 * @return string          safe string
	 */
	public static function safe($_string, $_remove_inject = null)
	{
		if(is_array($_string) || is_object($_string))
		{
			return self::walk($_string, $_remove_inject);
		}

		if(gettype($_string) == 'integer' || gettype($_string) == 'double' || gettype($_string) == 'boolean' ||	$_string === null)
		{
			return $_string;
		}

		if($_remove_inject === 'get_url')
		{
			$_remove_inject = ["'", '"', '\\\\\\', '`', '\*', ';'];
		}

		if($_remove_inject === 'sqlinjection')
		{
			$_remove_inject = ["'", '"', '\\\\\\', '`', '\*', "\\?", ';'];
		}

		if(is_array($_remove_inject))
		{
			$_string = preg_replace("/\s?[" . join('', $_remove_inject) . "]/", "", $_string);
		}

		$_string = trim($_string);

		$_string = self::persian_char($_string);

		$_string = self::remove_2s($_string);

		$_string = self::remove_2nl($_string);

		$_string = htmlspecialchars($_string, ENT_QUOTES | ENT_HTML5);

		$_string = addslashes($_string);

		return $_string;
	}


	public static function persian_char($_string)
	{
		if(\dash\language::current() === 'fa')
		{
			$_string = str_replace(['ي', 'ك'], ['ی', 'ک'], $_string);
			$_string = \dash\utility\convert::ar_to_fa_number($_string);
		}
		return $_string;
	}


	public static function remove_2nl($_string)
	{
		$_string = preg_replace("/[\r\n]{2,}/", "\n", $_string);
		return $_string;
	}


	public static function remove_2s($_string)
	{
		$_string = preg_replace("/\h+/", " ", $_string);
		return $_string;
	}


	/**
	 * Nested function for walk array or object
	 * @param  array or object $_value unpack array or object
	 * @return array or object         safe array or object
	 */
	private static function walk($_value, $_remove_inject = null)
	{
		foreach ($_value as $key => $value)
		{
			if(is_array($value) || is_object($value))
			{
				if(is_array($_value))
				{
					$_value[$key] = self::walk($value, $_remove_inject);
				}
				elseif(is_object($_value))
				{
					$_value->$key = self::walk($value, $_remove_inject);
				}
			}
			else
			{
				if(is_array($_value))
				{
					$_value[$key] = self::safe($value, $_remove_inject);
				}
				elseif(is_object($_value))
				{
					$_value->$key = self::safe($value, $_remove_inject);
				}
			}
		}
		return $_value;
	}
}
?>