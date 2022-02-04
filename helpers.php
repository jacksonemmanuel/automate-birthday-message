<?php

function formatPhoneNumber($phone) {
    switch (substr($phone, 0, 1)) {
        case '0':
            return str_replace_first('0', '234', $phone);
            break;
        case '7':
        case '8':
        case '9':
            return substr_replace($phone, '234', 0, 0);
            break;
        default:
            return $phone;
    }
}

function str_replace_first($search, $replace, $subject) {
    $search = '/'.preg_quote($search, '/').'/';
    return preg_replace($search, $replace, $subject, 1);
}

?>