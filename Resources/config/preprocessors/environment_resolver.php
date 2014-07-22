<?php

use Habitat\Habitat;

$_bootenv = function($container) {

    $env_default_app = $container->hasParameter('environment.application.defaults') ? $container->getParameter('environment.application.defaults') : array();
    $env_default_user = $container->hasParameter('environment.defaults') ? $container->getParameter('environment.defaults') : array();
    $merged_env = array_merge(
        $env_default_app,       # application defaults
        $env_default_user,      # user defaults
        Habitat::getAll()       # the real environment
    );

    $authorized_keys = array(
        'DATABASE_URL',
        'INITIALIZATION_MODE',
        'STORAGE',
        'S3_BUCKET',
        'S3_KEYID',
        'S3_SECRET'
    );

    $container->setParameter(
        'environment_resolved',
        array_filter($merged_env, function($var) use (&$merged_env, &$authorized_keys) {
            $res = in_array(key($merged_env), $authorized_keys);
            next($merged_env);
            return $res;
        })
    );
};

$_bootenv($container);