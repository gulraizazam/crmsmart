<?php

/**
 * Laravel 8 / opis/closure emit PHP 8.4 parameter-nullability deprecations
 * at file-load time (before the framework error handler exists).
 */
if (PHP_VERSION_ID >= 80400) {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
}
