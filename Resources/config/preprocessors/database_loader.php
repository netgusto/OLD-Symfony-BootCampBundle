<?php

use Netgusto\BootCampBundle\Services\DatabaseUrlResolverService;

$_bootdb = function($container) {

    $env = $container->getParameter('environment_resolved');
    $databaseurl = isset($env["DATABASE_URL"]) ? $env["DATABASE_URL"] : FALSE;

    if($databaseurl === FALSE) {
        $databaseurl = $container->getParameter('default_database_url');
    }

    $dbresolver = new DatabaseUrlResolverService();
    $dbparameters = $dbresolver->resolve($databaseurl);
    
    foreach($dbparameters as $parametername => $parametervalue) {
        $container->setParameter('database_' . $parametername, $parametervalue);
    }
};

$_bootdb($container);
unset($_bootdb);