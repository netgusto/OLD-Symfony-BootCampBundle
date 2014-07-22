<?php

namespace Netgusto\BootCampBundle\Exception\MaintenanceNeeded;

class AdministrativeAccountMissingMaintenanceNeededException
    extends \Exception
    implements MaintenanceNeededExceptionInterface {

    use MaintenanceNeededExceptionTrait;
}