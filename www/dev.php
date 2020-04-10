<?php

/**
 * Simply require development entry point, framework will take care of things.
 */
define('__ROOT__', rtrim($_SERVER['DOCUMENT_ROOT'] ?? dirname(dirname(__FILE__)), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
require_once __ROOT__ . "vendor/pckg/framework/src/development.php";
