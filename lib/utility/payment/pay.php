<?php
namespace lib\utility\payment;
use \lib\debug;
use \lib\option;
use \lib\utility;

class pay
{

    /**
     * default callback url
     *
     * @var        string
     */
    public static $default_callback_url = 'enter/payment/verify';

    public static $user_id = null;

    public static $log_data = null;

    use pay\zarinpal;
    use pay\parsian;
    use pay\irkish;

    /**
    * start pay
    */
    public static function start($_user_id, $_bank, $_amount, $_option = [])
    {
        $_bank = mb_strtolower($_bank);

        if(method_exists("\\lib\\utility\\payment\\pay", $_bank))
        {
            return \lib\utility\payment\pay::$_bank($_user_id, $_amount, $_option);
        }
        else
        {
            debug::error(T_("This payment is not supported in this system"));
            return false;
        }
    }


    /**
     * Gets the callbck url.
     * for example for parsian payment redirect
     * http://tejarak.com/fa/enter/payment/verify/parsian
     *
     * @param      <type>  $_payment  The payment
     */
    private static function get_callbck_url($_payment)
    {
        $host = Protocol."://" . \lib\router::get_root_domain();
        $lang = \lib\define::get_current_language_string();
        $callback_url =  $host;
        $callback_url .= $lang;

        if($_payment === 'redirect_page')
        {
            $callback_url .= '/enter/autoredirect';
        }
        else
        {
            $callback_url .= '/'. self::$default_callback_url;
            $callback_url .= '/'. $_payment;
        }
        return $callback_url;
    }
}
?>