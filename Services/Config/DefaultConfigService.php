<?php

namespace Netgusto\BootCampBundle\Services\Config;

use Doctrine\ORM\EntityManager;

use Netgusto\BootCampBundle\Entity\ConfigContainerInterface;

class DefaultConfigService implements ConfigServiceInterface {

    protected $entityManager;
    protected $config;

    public function __construct(EntityManager $entityManager) {
        $this->entityManager = $entityManager;
    }

    public function initialize(ConfigContainerInterface $config) {
        $this->config = $config;
    }

    public function __call($name, $arguments) {

        if(preg_match('/^get.+$/', $name)) {
            $prop = lcfirst(substr($name, 3));
            return $this->config->get($prop);
        }

        if(preg_match('/^set.+$/', $name)) {
            $prop = lcfirst(substr($name, 3));
            $this->config->set($prop, $arguments[0]);
            $this->entityManager->persist($this->config);
            $this->entityManager->flush();
            return $this;
        }

        throw new \RuntimeException(get_class($this) . ': Call to undefined method ' . $name);
    }
}