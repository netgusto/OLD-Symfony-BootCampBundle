<?php

use Netgusto\BootCampBundle\Services\DatabaseUrlResolverService;

$_bootdb = function($container) {

    $env = $container->getParameter('environment_resolved');
    $databaseurl = isset($env["DATABASE_URL"]) ? str_replace('%kernel.root_dir%', $container->getParameter('kernel.root_dir'), $env["DATABASE_URL"]) : FALSE;

    if($databaseurl !== FALSE) {
        $dbresolver = new DatabaseUrlResolverService();
        $dbparameters = $dbresolver->resolve($databaseurl);

        foreach($dbparameters as $parametername => $parametervalue) {
            $container->setParameter('database_' . $parametername, $parametervalue);
        }
    } else {
        $container->setParameter('database_driver', 'pdo_mysql');
        $container->setParameter('database_user', null);
        $container->setParameter('database_password', null);
        $container->setParameter('database_host', null);
        $container->setParameter('database_port', null);
        $container->setParameter('database_name', rand());
    }
};

$_bootdb($container);
unset($_bootdb);