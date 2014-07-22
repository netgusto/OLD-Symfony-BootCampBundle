<?php

namespace Netgusto\BootCampBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Netgusto\DevServerBundle\NetgustoDevServerBundle;
use Netgusto\AutorouteBundle\NetgustoAutorouteBundle;

class NetgustoBootCampBundle extends Bundle
{
    public function __construct(array &$bundles) {
        $bundles[] = $this;
        $bundles[] = new NetgustoDevServerBundle();
        $bundles[] = new NetgustoAutorouteBundle();
    }
}
