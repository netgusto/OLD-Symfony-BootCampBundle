<?php

namespace Netgusto\BootCampBundle\Exception\MaintenanceNeeded;

class SystemStatusMissingMaintenanceNeededException
    extends \Exception
    implements MaintenanceNeededExceptionInterface {

    use MaintenanceNeededExceptionTrait;
}