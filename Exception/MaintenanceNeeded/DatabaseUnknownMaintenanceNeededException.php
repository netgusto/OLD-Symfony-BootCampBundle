<?php

namespace Netgusto\BootCampBundle\Exception\MaintenanceNeeded;

class DatabaseUnkownMaintenanceNeededException
    extends \Exception
    implements MaintenanceNeededExceptionInterface {

    use MaintenanceNeededExceptionTrait;
}