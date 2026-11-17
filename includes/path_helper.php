<?php

$scriptDirectory = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
if (preg_match('~/(?:admin|auth)$~i', $scriptDirectory)) {
    $scriptDirectory = dirname($scriptDirectory);
}
$siteBasePath = ($scriptDirectory === '/' || $scriptDirectory === '.')
    ? ''
    : rtrim($scriptDirectory, '/');

if (!function_exists('sitePath')) {
    function sitePath($path = '') {
        global $siteBasePath;
        return $siteBasePath . '/' . ltrim((string)$path, '/');
    }
}
