<?php

namespace FormglutSmtpLib;

// Don't redefine the functions if included multiple times.
if (!\function_exists('FormglutSmtpLib\\GuzzleHttp\\Psr7\\str')) {
    require __DIR__ . '/functions.php';
}
