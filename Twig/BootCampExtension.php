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
            'siteconfig' => new \Twig_SimpleFunction('siteconfig', array($this, 'siteconfig')),
            'config' => new \Twig_SimpleFunction('config', array($this, 'config')),
        );
    }

    public function siteconfig() {
        return $this->container->get('config.site');
    }

    public function config($configname) {
        if(!preg_match('/^[a-z0-9\.]+$/i', $configname)) {
            throw new \Exception("Cannot access requested config in BootCamp Twig extension.");
        }

        return $this->container->get('config.' . $configname);
    }
}