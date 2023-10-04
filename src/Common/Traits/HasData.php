<?php

namespace LaravelPay\MyFatoorah\Common\Traits;

use Exception;

class HasData
{
    public static function getPhone($inputString): array
    {
        //remove any arabic digit
        $newNumbers = range(0, 9);

        $persianDecimal = ['&#1776;', '&#1777;', '&#1778;', '&#1779;', '&#1780;', '&#1781;', '&#1782;', '&#1783;', '&#1784;', '&#1785;']; // 1. Persian HTML decimal
        $arabicDecimal = ['&#1632;', '&#1633;', '&#1634;', '&#1635;', '&#1636;', '&#1637;', '&#1638;', '&#1639;', '&#1640;', '&#1641;']; // 2. Arabic HTML decimal
        $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩']; // 3. Arabic Numeric
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹']; // 4. Persian Numeric

        $string0 = str_replace($persianDecimal, $newNumbers, $inputString);
        $string1 = str_replace($arabicDecimal, $newNumbers, $string0);
        $string2 = str_replace($arabic, $newNumbers, $string1);
        $string3 = str_replace($persian, $newNumbers, $string2);

        //Keep Only digits
        $string4 = preg_replace('/[^0-9]/', '', $string3);

        //remove 00 at start
        if (str_starts_with($string4, '00')) {
            $string4 = substr($string4, 2);
        }

        if (! $string4) {
            return ['', ''];
        }

        //check for the allowed length
        $len = strlen($string4);
        if ($len < 3 || $len > 14) {
            throw new Exception('Phone Number lenght must be between 3 to 14 digits');
        }

        //get the phone arr
        if (strlen(substr($string4, 3)) > 3) {
            return [
                substr($string4, 0, 3),
                substr($string4, 3),
            ];
        } else {
            return [
                '',
                $string4,
            ];
        }
    }

    public static function getWeightRate($unit): float
    {

        $unit1 = strtolower($unit);
        if ($unit1 == 'kg' || $unit1 == 'kgs' || $unit1 == 'كج' || $unit1 == 'كلغ' || $unit1 == 'كيلو جرام' || $unit1 == 'كيلو غرام') {
            $rate = 1; //kg is the default
        } elseif ($unit1 == 'g' || $unit1 == 'جرام' || $unit1 == 'غرام' || $unit1 == 'جم') {
            $rate = 0.001;
        } elseif ($unit1 == 'lbs' || $unit1 == 'lb' || $unit1 == 'رطل' || $unit1 == 'باوند') {
            $rate = 0.453592;
        } elseif ($unit1 == 'oz' || $unit1 == 'اوقية' || $unit1 == 'أوقية') {
            $rate = 0.0283495;
        } else {
            throw new Exception('Weight units must be in kg, g, lbs, or oz. Default is kg');
        }

        return $rate;
    }

    /**
     * @throws Exception
     */
    public static function getDimensionRate($unit): float
    {

        $unit1 = strtolower($unit);
        if ($unit1 == 'cm' || $unit1 == 'سم') {
            $rate = 1; //cm is the default
        } elseif ($unit1 == 'm' || $unit1 == 'متر' || $unit1 == 'م') {
            $rate = 100;
        } elseif ($unit1 == 'mm' || $unit1 == 'مم') {
            $rate = 0.1;
        } elseif ($unit1 == 'in' || $unit1 == 'انش' || $unit1 == 'إنش' || $unit1 == 'بوصه' || $unit1 == 'بوصة') {
            $rate = 2.54;
        } elseif ($unit1 == 'yd' || $unit1 == 'يارده' || $unit1 == 'ياردة') {
            $rate = 91.44;
        } else {
            throw new Exception('Dimension units must be in cm, m, mm, in, or yd. Default is cm');
        }

        return $rate;
    }
}
