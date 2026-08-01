<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	https://codeigniter.com/userguide3/general/hooks.html
|
*/
$hook['post_controller_constructor'][] = [
    'class' => 'Response_security',
    'function' => 'set_headers',
    'filename' => 'Response_security.php',
    'filepath' => 'hooks',
];

$hook['post_controller_constructor'][] = [
    'class' => 'Audit_mutation',
    'function' => 'log_post',
    'filename' => 'Audit_mutation.php',
    'filepath' => 'hooks',
];
