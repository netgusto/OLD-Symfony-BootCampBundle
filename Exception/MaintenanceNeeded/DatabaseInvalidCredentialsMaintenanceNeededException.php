<?php

namespace Netgusto\BootCampBundle\Exception\MaintenanceNeeded;

class DatabaseInvalidCredentialsMaintenanceNeededException
    extends \Exception
    implements MaintenanceNeededExceptionInterface {

    use MaintenanceNeededExceptionTrait;
}