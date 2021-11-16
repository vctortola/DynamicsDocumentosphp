<?php

/**
 * DB user name
 */
define('SCHEMA', env('DB_USERNAMEORACLE'));

/**
 * DB Password.
 */
define('PASSWORD', env('DB_PASSWORDORACLE'));

/**
 * DB connection identifier
 */
define('DATABASE', env('DB_HOSTORACLE').":".env('DB_PORTORACLE')."/".env('DB_DATABASEORACLE'));

/**
 * DB character set for returned data
 */
define('CHARSET', 'UTF8');

/**
 * Client Information text for DB tracing
 */
define('CLIENT_INFO', 'apiDocentes - InformaticaUG');

?>
