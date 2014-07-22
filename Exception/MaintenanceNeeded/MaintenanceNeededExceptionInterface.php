<?php

namespace Netgusto\BootCampBundle\Exception\MaintenanceNeeded;

interface MaintenanceNeededExceptionInterface {
    public function setInformationalLabel($label);
    public function getInformationalLabel();
}