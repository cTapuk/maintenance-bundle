<?php

/**
 * @return null
 */
function get_client_ip()
{
    if ($_SERVER['HTTP_CLIENT_IP']) return $_SERVER['HTTP_CLIENT_IP'];
    if ($_SERVER['HTTP_X_FORWARDED_FOR']) return $_SERVER['HTTP_X_FORWARDED_FOR'];
    if ($_SERVER['HTTP_X_FORWARDED']) return $_SERVER['HTTP_X_FORWARDED'];
    if ($_SERVER['HTTP_FORWARDED_FOR']) return $_SERVER['HTTP_FORWARDED_FOR'];
    if ($_SERVER['HTTP_FORWARDED']) return $_SERVER['HTTP_FORWARDED'];
    if ($_SERVER['REMOTE_ADDR']) return $_SERVER['REMOTE_ADDR'];
    if ($_SERVER['HTTP_CLIENT_IP']) return $_SERVER['HTTP_CLIENT_IP'];

    return null;
}

/**
 * @param $allowedIp
 * @param $pagePath
 * @param null $startTimestamp
 * @param null $endTimestamp
 */
function maintenance($allowedIp, $pagePath, $startTimestamp = null, $endTimestamp = null)
{
    $started = true;
    $ended = false;
    $now = time();

    if ($startTimestamp) {
        $started = $now > $startTimestamp;
    }

    if ($endTimestamp) {
        $ended = $now > $endTimestamp;
    }

    if ((!in_array(get_client_ip(), $allowedIp)) && $started && (!$ended)) {
        header('HTTP/1.1 503 Service Unavailable');

        if (is_readable($pagePath)) {
            include $pagePath;
        } else {
            echo 'This site is currently under maintenance';
        }

        exit();
    }
}