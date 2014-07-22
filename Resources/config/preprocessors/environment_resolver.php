<?php

use Habitat\Habitat;

$_bootenv = function($container) {
    $merged_env = array_merge(
        $container->getParameter('environment.application.defaults'),   # application defaults
        $container->getParameter('environment.defaults'),               # user defaults
        Habitat::getAll()                                               # the real environment
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