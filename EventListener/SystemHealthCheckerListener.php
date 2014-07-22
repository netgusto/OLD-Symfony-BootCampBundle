<?php

namespace Netgusto\BootCampBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent,
    Symfony\Component\DependencyInjection\ContainerInterface;

use Doctrine\DBAL\Connection;

use Netgusto\BootCampBundle\Services\Context\SystemStatusService,
    Netgusto\BootCampBundle\Services\Context\EnvironmentService,
    Netgusto\BootCampBundle\Exception as CoreException;

class SystemHealthCheckerListener {

    protected $container;
    protected $environment;
    protected $connection;

    public function __construct(
        ContainerInterface $container,
        EnvironmentService $environment,
        Connection $connection

        # LAZY LOADED SERVICES (through $container)
        #SystemStatusService $systemstatus,
    ) {
        $this->container = $container;
        $this->environment = $environment;
        $this->connection = $connection;
    }

    public function onKernelRequest(GetResponseEvent $event) {
        
        $exception = null;

        if($this->environment->getInitializationMode() === TRUE) {
            throw new CoreException\InitializationNeeded\InstallModeActivatedInitializationNeededException();
        } else {
            
            # Checking systemstatus (db-based) will trigger a database (DBAL or PDO) exception if database is not initialized
            # We do not catch it, as it will be caught by the InitializationExceptionListener
            if($this->container->get('system.status')->getInitialized() !== TRUE) {

                # System status is marked as uninitialized
                throw new CoreException\InitializationNeeded\SystemStatusMarkedAsUninitializedInitializationNeededException();
            }
        }
    }
}