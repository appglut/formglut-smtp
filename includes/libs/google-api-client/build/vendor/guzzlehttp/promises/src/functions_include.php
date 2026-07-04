<?php

namespace FormglutSmtpLib;

// Don't redefine the functions if included multiple times.
if (!\function_exists('FormglutSmtpLib\\GuzzleHttp\\Promise\\promise_for')) {
    require __DIR__ . '/functions.php';
}
