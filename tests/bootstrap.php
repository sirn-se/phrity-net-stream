<?php

require dirname(__DIR__) . '/vendor/autoload.php';

// Temporary workaround for phpunit-coverage-tools
$_SERVER['argv'][] = '--min-coverage=100';
