<?php

namespace Netgusto\BootCampBundle\Services\Config;

use Netgusto\BootCampBundle\Entity\ConfigContainerInterface;

interface ConfigServiceInterface {
    public function initialize(ConfigContainerInterface $config);
}