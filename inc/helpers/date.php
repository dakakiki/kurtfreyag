<?php
if ( ! defined('ABSPATH') ) exit;

function monthToLocalized($date, $format = 'F')
{
    $months_sr = [
        'January'   => 'Januar',
        'February'  => 'Februar',
        'March'     => 'Mart',
        'April'     => 'April',
        'May'       => 'Maj',
        'June'      => 'Jun',
        'July'      => 'Jul',
        'August'    => 'Avgust',
        'September' => 'Septembar',
        'October'   => 'Oktobar',
        'November'  => 'Novembar',
        'December'  => 'Decembar',
    ];

    $month = (new DateTime($date))->format($format);

    // WPML current language
    $lang = apply_filters('wpml_current_language', null);

    // Return English month on English version
    if ($lang === 'en') {
        return $month;
    }

    // Default Serbian
    return $months_sr[$month] ?? $month;
}