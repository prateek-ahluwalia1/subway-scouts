<?php
/*
 * Class for generalized helper functions.
 * @author Faizan Noor <Fnoor.dic@emaar.ae>
 */

namespace App\Helpers;

use Carbon\Carbon;

class Helper
{
    public function mySimpleCrypt($string, $action = 'e')
    {
        // you may change these values to your own
        $secret_key = 'mysecretkey';
        $secret_iv  = 'mysecretiv';

        $output         = false;
        $encrypt_method = "AES-256-CBC";
        $key            = hash('sha256', $secret_key);
        $iv             = substr(hash('sha256', $secret_iv), 0, 16);

        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);

        return $output;
    } // mySimpleCrypt :: END

    public function formatDateMonth($monthNum, $format = 'd-m-Y')
    {
         $monthName = date($format, mktime(0, 0, 0, $monthNum, 10));
         return $monthName;
    }

    public function formatDate($dateObj, $format = 'd-m-Y')
    {
        return \Carbon\Carbon::parse($dateObj)->format($format);
    }
}
