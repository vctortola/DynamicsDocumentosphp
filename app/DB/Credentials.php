<?php
 
/**
 * DB user name
 */
define('SCHEMA', env('DB_USERNAME'));
 
/**
 * DB Password.
 */
define('PASSWORD', env('DB_PASSWORD'));
 
/**
 * DB connection identifier
 */
define('DATABASE', env('DB_HOST').":".env('DB_PORT')."/".env('DB_DATABASE'));
 
/**
 * DB character set for returned data
 */
define('CHARSET', 'UTF8');
 
/**
 * Client Information text for DB tracing
 */
define('CLIENT_INFO', 'apiDocentes - InformaticaUG');
 
?>