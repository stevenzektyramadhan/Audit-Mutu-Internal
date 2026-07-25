<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Application feature flags
|--------------------------------------------------------------------------
|
| Versioned SPMI is implemented but deferred for the MVP. The legacy
| `standar` workflow remains canonical until a product-approved cutover.
| Unknown or missing environment values fail closed.
|
*/
$versioned_spmi = getenv('FEATURE_VERSIONED_SPMI');
$config['feature_versioned_spmi'] = $versioned_spmi !== FALSE
    && filter_var($versioned_spmi, FILTER_VALIDATE_BOOLEAN);
