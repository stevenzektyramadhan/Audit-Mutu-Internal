<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$active_group = 'default';
$query_builder = TRUE;

$db['default'] = array(
    'dsn'	=> '',
    'hostname' => '127.0.0.1',
    'username' => 'root',
    'password' => '',
    'database' => 'ami',
    'dbdriver' => 'mysqli',
    'dbprefix' => '',
    'pconnect' => FALSE,
    'db_debug' => (ENVIRONMENT !== 'production'),
    'cache_on' => FALSE,
    'cachedir' => '',
    'char_set' => 'utf8',
    'dbcollat' => 'utf8_general_ci',
    'swap_pre' => '',
    'encrypt' => FALSE,
    'compress' => FALSE,
    'stricton' => FALSE,
    'failover' => array(),
    'save_queries' => TRUE
);

if (ENVIRONMENT === 'production') {
    if (empty($db['default']['hostname']) || empty($db['default']['username']) || empty($db['default']['database'])) {
        header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
        echo 'Production database configuration is incomplete.';
        exit(1);
    }
}
