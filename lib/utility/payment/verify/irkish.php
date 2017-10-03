<?php
namespace lib\utility\payment\verify;
use \lib\debug;
use \lib\option;
use \lib\utility;
use \lib\db\logs;

trait irkish
{

    /**
     * { function_description }
     *
     * @param      <type>  $_args  The arguments
     */
    public static function irkish($_args)
    {
        self::config();

        $log_meta =
        [
            'data' => self::$log_data,
            'meta' =>
            [
                'input'   => func_get_args(),
                'session' => $_SESSION,
            ]
        ];

        if(!option::config('irkish', 'status'))
        {
            logs::set('pay:irkish:status:false', self::$user_id, $log_meta);
            debug::error(T_("The irkish payment on this service is locked"));
            return self::turn_back();
        }

        if(!option::config('irkish', 'merchantId'))
        {
            logs::set('pay:irkish:merchantId:not:set', self::$user_id, $log_meta);
            debug::error(T_("The irkish payment merchantId not set"));
            return self::turn_back();
        }

        $token         = isset($_REQUEST['token'])          ? (string) $_REQUEST['token']           : null;
        $merchantId    = isset($_REQUEST['merchantId'])     ? (string) $_REQUEST['merchantId']      : null;
        $resultCode    = isset($_REQUEST['resultCode'])     ? (string) $_REQUEST['resultCode']      : null;
        $paymentId     = isset($_REQUEST['paymentId'])      ? (string) $_REQUEST['paymentId']       : null;
        $InvoiceNumber = isset($_REQUEST['InvoiceNumber'])  ? (string) $_REQUEST['InvoiceNumber']   : null;
        $referenceId   = isset($_REQUEST['referenceId'])    ? (string) $_REQUEST['referenceId']     : null;
        $amount        = isset($_REQUEST['amount'])         ? (string) $_REQUEST['amount']          : null;
        $amount        = str_replace(',', '', $amount);

        if(!$token)
        {
            logs::set('pay:irkish:token:verify:not:found', self::$user_id, $log_meta);
            debug::error(T_("The irkish payment token not set"));
            return self::turn_back();
        }

        if(!$resultCode)
        {
            logs::set('pay:irkish:resultCode:verify:not:found', self::$user_id, $log_meta);
            debug::error(T_("The irkish payment resultCode not set"));
            return self::turn_back();
        }

        if(isset($_SESSION['amount']['irkish'][$token]['transaction_id']))
        {
            $transaction_id  = $_SESSION['amount']['irkish'][$token]['transaction_id'];
        }
        else
        {
            logs::set('pay:irkish:SESSION:transaction_id:not:found', self::$user_id, $log_meta);
            debug::error(T_("Your session is lost! We can not find your transaction"));
            return self::turn_back();
        }

        $log_meta['data'] = self::$log_data = $transaction_id;

        $irkish                    = [];
        $irkish['merchantId']      = option::config('irkish', 'merchantId');
        $irkish['token']           = $token;
        $irkish['amount']          = $amount;
        // $irkish['referenceNumber'] = (string) $transaction_id;
        $irkish['sha1Key']         = \lib\option::config('irkish', 'sha1');

        if(isset($_SESSION['amount']['irkish'][$token]['amount']))
        {
            $amount_SESSION  = floatval($_SESSION['amount']['irkish'][$token]['amount']);
        }
        else
        {
            logs::set('pay:irkish:SESSION:amount:not:found', self::$user_id, $log_meta);
            debug::error(T_("Your session is lost! We can not find amount"));
            return self::turn_back();
        }

        if($amount_SESSION != $amount)
        {
            logs::set('pay:irkish:amount_SESSION:amount:is:not:equals', self::$user_id, $log_meta);
            debug::error(T_("Your session is lost! We can not find amount"));
            return self::turn_back();
        }

        $update =
        [
            'amount_end'       => $amount / 10,
            'condition'        => 'pending',
            'payment_response' => json_encode((array) $_args, JSON_UNESCAPED_UNICODE),
        ];

        \lib\db\transactions::update($update, $transaction_id);

        logs::set('pay:irkish:pending:request', self::$user_id, $log_meta);

        // $msg = \lib\utility\payment\payment\irkish::msg($resultCode);

        if(intval($resultCode) === 100)
        {
            \lib\utility\payment\payment\irkish::$user_id = self::$user_id;

            \lib\utility\payment\payment\irkish::$log_data = self::$log_data;

            $is_ok = \lib\utility\payment\payment\irkish::verify($irkish);

            $payment_response = \lib\utility\payment\payment\irkish::$payment_response;

            $log_meta['meta']['payment_response'] = (array) $payment_response;

            $payment_response = json_encode((array) $payment_response, JSON_UNESCAPED_UNICODE);

            if($is_ok)
            {
                $update =
                [
                    'amount_end'       => $amount_SESSION / 10,
                    'condition'        => 'ok',
                    'verify'           => 1,
                    'payment_response' => $payment_response,
                ];

                \lib\db\transactions::update($update, $transaction_id);
                logs::set('pay:irkish:ok:request', self::$user_id, $log_meta);
                return self::turn_back($transaction_id);
            }
            else
            {
                $update =
                [
                    'amount_end'       => $amount_SESSION / 10,
                    'condition'        => 'verify_error',
                    'payment_response' => $payment_response,
                ];
                \lib\db\transactions::update($update, $transaction_id);
                logs::set('pay:irkish:verify_error:request', self::$user_id, $log_meta);
                return self::turn_back($transaction_id);
            }
        }
        else
        {
            $update =
            [
                'amount_end'       => $amount_SESSION / 10,
                'condition'        => 'error',
                'payment_response' => json_encode((array) $_args, JSON_UNESCAPED_UNICODE),
            ];
            \lib\db\transactions::update($update, $transaction_id);
            logs::set('pay:irkish:error:request', self::$user_id, $log_meta);
            return self::turn_back($transaction_id);
        }
    }
}
?>