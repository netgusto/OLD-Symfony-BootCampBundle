<?php

namespace Netgusto\BootCampBundle\Services\Maintenance;

use Doctrine\DBAL\DriverManager,
    Doctrine\DBAL\Connection;

class DatabaseCreatorService {

    public function createDatabase(Connection $connection) {

        $params = $connection->getParams();
        $name = isset($params['path']) ? $params['path'] : $params['dbname'];

        unset($params['dbname']);

        $tmpConnection = DriverManager::getConnection($params);

        // Only quote if we don't have a path
        if (!isset($params['path'])) {
            $name = $tmpConnection->getDatabasePlatform()->quoteSingleIdentifier($name);
        }

        $error = FALSE;

        try {
            $tmpConnection->getSchemaManager()->createDatabase($name);
        } catch (\Exception $e) {
            $error = TRUE;
        }

        $tmpConnection->close();

        return !$error;
    }
}