<?php
namespace dash\engine;


class prepare
{
	public static function requirements()
	{
		self::hi_developers();
		self::minimum_requirement();

		self::error_handler();
		self::debug();
	}


	public static function basics()
	{
		// dont run on some condition
		self::dont_run_exception();
		// check comming soon page
		self::coming_soon();
		// check need redirect for lang or www or https or main domain
		self::fix_url_host();
		self::account_urls();

		// start session
		self::session_start();

		self::user_country_redirect();
	}



	/**
	* if the user use 'en' language of site
	* and her country is "IR"
	* and no referer to this page
	* and no cookie set from this site
	* redirect to 'fa' page
	* WARNING:
	* this function work when the default lanuage of site is 'en'
	* if the default language if 'fa'
	* and the user work by 'en' site
	* this function redirect to tj.com/fa/en
	* and then redirect to tj.com/en
	* so no change to user interface ;)
	*/
	private static function user_country_redirect()
	{
		if(\dash\url::isLocal())
		{
			return null;
		}

		if(\dash\agent::isBot())
		{
			return false;
		}

		$referer = (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER']) ? true : false;
		if($referer)
		{
			return false;
		}

		$cookie = \dash\utility\cookie::read('language');

		if(!$_SESSION && !$cookie && !\dash\url::lang())
		{
			$default_site_language = \dash\language::default();
			$country_is_ir         = (isset($_SERVER['HTTP_CF_IPCOUNTRY']) && mb_strtoupper($_SERVER['HTTP_CF_IPCOUNTRY']) === 'IR') ? true : false;
			$redirect_lang         = null;

			if($default_site_language === 'fa' && !$country_is_ir)
			{
				$redirect_lang = 'en';
			}
			elseif($default_site_language === 'en' && $country_is_ir)
			{
				$redirect_lang = 'fa';
			}
			$cookie_lang = $redirect_lang ? $redirect_lang : $default_site_language;
			$domain = '.'. \dash\url::domain();

			\dash\utility\cookie::write('language', $cookie_lang, (60*60*24*30), $domain);
			$_SESSION['language'] = $cookie_lang;

			if($redirect_lang && array_key_exists($redirect_lang, \dash\option::language('list')))
			{
				$root    = \dash\url::base();
				$full    = \dash\url::pwd();
				$new_url = str_replace($root, $root. '/'. $redirect_lang, $full);
				\dash\redirect::to($new_url, true, 302);
			}
		}
	}


	/**
	 * start session
	 */
	private static function session_start()
	{
		if(is_string(\dash\url::root()))
		{
			session_name(\dash\url::root());
		}

		// set session cookie params
		session_set_cookie_params(0, '/', '.'.\dash\url::domain(), false, true);

		// start sessions
		session_start();
	}


	/**
	 * [account_urls description]
	 * @return [type] [description]
	 */
	private static function account_urls()
	{
		$param = \dash\url::query();
		if($param)
		{
			$param = '?'.$param;
		}

		$myrep = \dash\url::content();
		switch (\dash\url::module())
		{
			case 'signin':
			case 'login':
				$url = \dash\url::base(). '/enter'. $param;
				\dash\redirect::to($url);
				break;

			case 'signup':
				if($myrep !== 'enter')
				{
					$url = \dash\url::base(). '/enter/signup'. $param;
					\dash\redirect::to($url);
				}
				break;

			case 'register':

				$url = \dash\url::base(). '/enter/signup'. $param;
				\dash\redirect::to($url);
				break;

			case 'signout':
			case 'logout':
				if($myrep !== 'enter')
				{
					$url = \dash\url::base(). '/enter/logout'. $param;
					\dash\redirect::to($url);
				}

				break;
		}

		switch (\dash\url::directory())
		{
			case 'account/recovery':
			case 'account/changepass':
			case 'account/verification':
			case 'account/verificationsms':
			case 'account/signin':
			case 'account/login':
				$url = \dash\url::base(). '/enter'. $param;
				\dash\redirect::to($url);
				break;

			case 'account/signup':
			case 'account/register':
				$url = \dash\url::base(). '/enter/signup'. $param;
				\dash\redirect::to($url);
				break;

			case 'account/logout':
			case 'account/signout':
				$url = \dash\url::base(). '/enter/logout'. $param;
				\dash\redirect::to($url);
				break;
		}
	}


	/**
	 * set best domain and url
	 * @return [type] [description]
	 */
	private static function fix_url_host()
	{
		if(\dash\option::url('fix') !== true)
		{
			return null;
		}

		// decalare target url
		$target_host = '';

		// fix protocol
		if(\dash\option::url('protocol'))
		{
			$target_host = \dash\option::url('protocol').'://';
		}
		else
		{
			$target_host = \dash\url::protocol().'://';
		}

		// set www subdomain
		if(\dash\option::url('www'))
		{
			if(\dash\url::subdomain())
			{
				$target_host .= \dash\url::subdomain(). '.';
			}
			else
			{
				$target_host .= 'www.';
			}
		}
		elseif(\dash\url::subdomain() && \dash\url::subdomain() !== 'www')
		{

			$target_host .= \dash\url::subdomain(). '.';
		}

		// fix root domain
		if(\dash\option::url('root'))
		{
			if(\dash\option::url('root') !== \dash\url::root())
			{
				if(is_callable(['\lib\alias', 'url']) && \lib\alias::url())
				{
					$target_host .= \dash\url::root();
				}
				else
				{
					$target_host .= \dash\option::url('root');
				}
			}
			else
			{
				$target_host .= \dash\option::url('root');
			}

		}
		elseif(\dash\url::root())
		{
			$target_host .= \dash\url::root();
		}

		// fix tld
		if(\dash\option::url('tld'))
		{
			if(is_callable(['\lib\alias', 'url']) && \lib\alias::url())
			{
				$target_host .= '.'. \dash\url::tld();
			}
			else
			{
				$target_host .= '.'.\dash\option::url('tld');
			}
		}
		elseif(\dash\url::tld())
		{
			$target_host .= '.'.\dash\url::tld();
		}

		if(\dash\option::url('port') && \dash\option::url('port') !== 80 && \dash\option::url('port') !== 443)
		{
			$target_host .= ':'.\dash\option::url('port');
		}
		elseif(\dash\url::port() && \dash\url::port() !== 80 && \dash\url::port() !== 443)
		{
			$target_host .= ':'.\dash\url::port();
		}

		if(\dash\url::related_url())
		{
			$target_host .= \dash\url::related_url();
		}

		// help new language detect in target site by set /fa
		if(!\dash\url::lang() && \dash\option::url('tld') && \dash\option::url('tld') !== \dash\url::tld())
		{
			switch (\dash\url::tld())
			{
				case 'ir':
					$target_host .= '/fa';
					break;

				default:
					break;
			}
		}

		// set target url with path
		$target_url = $target_host. \dash\url::path();
		$target_url = self::fix_url_slash($target_url);


		// if we have new target url, and dont on force show mode, try to change it
		if(!\dash\request::get('force'))
		{
			if($target_host === \dash\url::base())
			{
				// only check last slash
				if($target_url !== \dash\url::pwd())
				{
					\dash\redirect::to($target_url);
				}
			}
			else
			{
				// change host and slash together
				\dash\redirect::to($target_url);
			}
		}
	}


	/**
	 * fix slash, if needed add it else remove it
	 * @param  [type] $_url [description]
	 * @return [type]       [description]
	 */
	private static function fix_url_slash($_url)
	{
		$myBrowser = \dash\utility\browserDetection::browser_detection('browser_name');
		if($myBrowser === 'samsungbrowser')
		{
			// samsung is stupid!
		}
		else
		{
			// remove slash in normal condition
			$_url = trim($_url, '/');

			if(\dash\option::url('slash'))
			{
				// add slash if set in settings
				$_url .= '/';
			}
			elseif(\dash\url::path() === '/')
			{
				// add slash for homepage
				$_url .= '/';
			}
		}
		return $_url;
	}


	/**
	 * check coming soon status
	 * @return [type] [description]
	 */
	private static function coming_soon()
	{
		/**
		 * in coming soon period show public_html/pages/coming/ folder
		 * developer must set get parameter like site.com/dev=anyvalue
		 * for disable this attribute turn off it from config.php in project root
		 */
		if(\dash\option::config('coming'))
		{
			// if user set dev in get, show the site
			if(isset($_GET['dev']))
			{
				setcookie('preview','yes',time() + 30*24*60*60,'/','.'.\dash\url::domain());
			}
			elseif(\dash\url::dir(0) === 'hook')
			{
				// allow telegram to commiunate on coming soon
			}
			elseif(!isset($_COOKIE["preview"]))
			{
				\dash\redirect::to(\dash\url::site().'/static/page/coming/', true, 302);
			}
		}
	}


	/**
	 * set custom error handler
	 */
	private static function error_handler()
	{
		//Setting for the PHP Error Handler
		set_error_handler( "\\dash\\engine\\error::handle_error" );

		//Setting for the PHP Exceptions Error Handler
		set_exception_handler( "\\dash\\engine\\error::handle_exception" );

		//Setting for the PHP Fatal Error
		register_shutdown_function( "\\dash\\engine\\error::handle_fatal" );
	}


	/**
	 * set debug status
	 * @param  [type] $_status [description]
	 */
	public static function debug($_status = null)
	{
		if($_status === null)
		{
			$_status = \dash\option::config('debug');
		}

		if($_status)
		{
			ini_set('display_startup_errors', 'On');
			ini_set('error_reporting'       , 'E_ALL | E_STRICT');
			ini_set('track_errors'          , 'On');
			ini_set('display_errors'        , 1);
			error_reporting(E_ALL);
		}
		else
		{
			error_reporting(0);
			ini_set('display_errors', 0);
		}
	}


	/**
	 * check current version of server technologies like php and mysql
	 * and if is less than min, show error message
	 * @return [type] [description]
	 */
	private static function minimum_requirement()
	{
		// check php version to upper than 7.0
		if(version_compare(phpversion(), '7.0', '<'))
		{
			\dash\code::die("<p>For using Dash you must update php version to 7.0 or higher!</p>");
		}
	}


	private static function dont_run_exception()
	{
		// files
		if(strpos(\dash\url::path(), '/files') === 0)
		{
			\dash\header::status(404);
		}
		// static
		if(strpos(\dash\url::path(), '/static') === 0)
		{
			\dash\header::status(404);
		}
		// favicon
		if(strpos(\dash\url::path(), '/favicon.ico') === 0)
		{
			\dash\header::status(404);
		}
	}

	/**
	 * set some header and say hi to developers
	 */
	private static function hi_developers()
	{
		// change header and remove php from it
		@header("X-Made-In: Ermile!");
		@header("X-Powered-By: Dash!");
	}
}
?>