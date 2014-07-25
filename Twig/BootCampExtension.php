<?php

namespace Netgusto\BootCampBundle\Twig;

use Symfony\Bundle\FrameworkBundle\Routing\Router,
    Symfony\Component\DependencyInjection\ContainerInterface;

class BootCampExtension extends \Twig_Extension {

    protected $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }
    
    public function getName() {
        return 'bootcamp';
    }

    public function getFunctions() {
        return array(
            'config' => new \Twig_SimpleFunction('config', array($this, 'config')),
        );
    }

    # Returns the config Service, not the ConfigContainer entity
    public function config($configname = null) {

        if(!is_null($configname) && !preg_match('/^[a-z0-9\.]+$/i', $configname)) {
            throw new \Exception("Cannot access requested config in BootCamp Twig extension.");
        }

        if(is_null($configname)) {
            $configname = 'main';
        }

        return $this->container->get('config.' . $configname);
    }
}