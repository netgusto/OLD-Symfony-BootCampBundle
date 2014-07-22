<?php

namespace Netgusto\BootCampBundle\Exception\MaintenanceNeeded;

class DatabaseUpdateMaintenanceNeededException
    extends \Exception
    implements MaintenanceNeededExceptionInterface {

    use MaintenanceNeededExceptionTrait;
}