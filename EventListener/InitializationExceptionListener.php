<?php

namespace Netgusto\BootCampBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

use Doctrine\DBAL\Connection;

use Netgusto\BootCampBundle\Services\Context\SystemStatusService,
    Netgusto\BootCampBundle\Services\Context\EnvironmentService,
    Netgusto\BootCampBundle\Controller\InitializationController,
    Netgusto\BootCampBundle\Exception as CoreException;

class InitializationExceptionListener {

    protected $container;
    protected $connection;
    protected $environment;
    protected $request;

    public function __construct(
        ContainerInterface $container,
        Connection $connection,
        EnvironmentService $environment,
        Request $request
    ) {
        $this->container = $container;
        $this->connection = $connection;
        $this->environment = $environment;
        $this->request = $request;
    }

    public function onKernelException(GetResponseForExceptionEvent $event) {
        
        $exception = $event->getException();

        # It's not an InitializationNeeded exception
        # We check if it's an exception that indicates that the application needs initialization

        if(
            $exception instanceof \Doctrine\DBAL\DBALException ||
            $exception instanceof \PDOException
        ) {
            $exception = $this->resolveDBException(
                $exception,
                $this->connection
            );
        }

        if($exception instanceof CoreException\InitializationNeeded\InitializationNeededExceptionInterface) {
            $event->setResponse($this->container->get('initialization.controller')->reactToExceptionAction(
                $this->request,
                $exception
            ));

            return;
        }

        if($exception instanceof CoreException\MaintenanceNeeded\MaintenanceNeededExceptionInterface) {
            die('Maintenance needed ! (' . get_class($exception) . ')');
        }
    }

    protected function resolveDBException(\Exception $exception, Connection $connection) {

        # We check if the database exists
        try {
            $tables = $connection->getSchemaManager()->listTableNames();
        } catch(\PDOException $pdoexception) {
            if(strpos($pdoexception->getMessage(), 'Access denied') !== FALSE) {
                return new CoreException\MaintenanceNeeded\DatabaseInvalidCredentialsMaintenanceNeededException();
            } else {
                return new CoreException\InitializationNeeded\DatabaseMissingInitializationNeededException();
            }
        }

        if(
            stripos($exception->getMessage(), 'Invalid table name') !== FALSE ||
            stripos($exception->getMessage(), 'no such table') !== FALSE ||
            stripos($exception->getMessage(), 'Base table or view not found') !== FALSE ||
            stripos($exception->getMessage(), 'Undefined table') !== FALSE
        ) {
            if(empty($tables)) {
                return new CoreException\InitializationNeeded\DatabaseEmptyInitializationNeededException();
            } else {
                return new CoreException\MaintenanceNeeded\DatabaseUpdateMaintenanceNeededException();
            }
        }

        if(stripos($exception->getMessage(), 'Unknown column') !== FALSE) {
            return new CoreException\MaintenanceNeeded\DatabaseUpdateMaintenanceNeededException();
        }

        return $exception;
    }
}