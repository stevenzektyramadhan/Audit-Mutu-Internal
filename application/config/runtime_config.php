<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('ami_load_production_config')) {
    function ami_load_production_config($path, $web_root)
    {
        if (!is_string($path) || $path === '' || $path[0] !== '/') {
            throw new RuntimeException('Production configuration file is not set.');
        }

        $resolved = realpath($path);
        $resolved_web_root = realpath($web_root);
        if ($resolved === FALSE || !is_file($resolved) || !is_readable($resolved)
            || ($resolved_web_root !== FALSE && strpos($resolved . DIRECTORY_SEPARATOR, $resolved_web_root . DIRECTORY_SEPARATOR) === 0)) {
            throw new RuntimeException('Production configuration file is unavailable.');
        }

        $mode = fileperms($resolved);
        if ($mode === FALSE || ($mode & 0022) !== 0) {
            throw new RuntimeException('Production configuration file permissions are unsafe.');
        }

        $config = require $resolved;
        if (!is_array($config)) {
            throw new RuntimeException('Production configuration file is invalid.');
        }

        return $config;
    }
}

if (!function_exists('ami_runtime_config_item')) {
    function ami_runtime_config_item($section, $key, $development_environment_key = null, $default = '')
    {
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'production') {
            $config = isset($GLOBALS['ami_production_config']) && is_array($GLOBALS['ami_production_config'])
                ? $GLOBALS['ami_production_config']
                : array();
            $value = isset($config[$section]) && is_array($config[$section]) && array_key_exists($key, $config[$section])
                ? $config[$section][$key]
                : null;

            return is_scalar($value) || $value === null ? $value : $default;
        }

        if ($development_environment_key !== null) {
            $value = getenv($development_environment_key);
            if ($value !== FALSE) {
                return $value;
            }
        }

        return $default;
    }
}
