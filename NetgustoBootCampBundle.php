<?php

namespace Netgusto\BootCampBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class NetgustoBootCampBundle extends Bundle
{
    public function __construct(array &$bundles) {
        $bundles[] = $this;
        $bundles[] = new NetgustoDevServerBundle();
    }
}
