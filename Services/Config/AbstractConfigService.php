<?php

namespace Netgusto\BootCampBundle\Services\Config;

abstract class AbstractConfigService {

    protected $config;

    public function __construct(array $config) {
        $this->config = $config;
    }

    public function __call($name, $arguments) {

        if(preg_match('/^get.+$/', $name)) {
            $prop = lcfirst(substr($name, 3));
            if(array_key_exists($prop, $this->config)) {
                return $this->config[$prop];
            }
        }

        return null;
    }
}