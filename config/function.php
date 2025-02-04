<?php
if (!function_exists('getBaseURL')) {
    function getBaseURL()
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $folder = dirname($_SERVER['SCRIPT_NAME']);
        return $protocol . '://' . $host . $folder;
    }
}
