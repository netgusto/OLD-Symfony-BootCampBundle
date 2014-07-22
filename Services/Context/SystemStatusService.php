<?php

namespace Netgusto\BootCampBundle\Services\Context;

use Doctrine\ORM\EntityManager;

use Pulpy\CoreBundle\Entity\SystemStatus,
    Pulpy\CoreBundle\Exception\MaintenanceNeeded\SystemStatusMissingMaintenanceNeededException;

class SystemStatusService {

    protected $em;
    protected $systemstatus;

    public function __construct(EntityManager $em) {
        $this->em = $em;
        $this->systemstatus = null;
    }

    protected function fetch() {
        
        if(!is_null($this->systemstatus)) {
            return;
        }

        # Initialize system status if needed
        $results = $this->em->getRepository('Netgusto\BootCampBundle\Entity\SystemStatus')->findAll();

        if(!empty($results)) {
            $this->systemstatus = $results[0];
            return;
        }

        throw new SystemStatusMissingMaintenanceNeededException();
    }

    public function getPostCacheLastUpdate() {
        $this->fetch();
        return $this->systemstatus->getPostcachelastupdate();
    }

    public function setPostCacheLastUpdate(\DateTime $postlastcacheupdate) {
        $this->fetch();
        $this->systemstatus->setPostcachelastupdate($postlastcacheupdate);
        $this->em->persist($this->systemstatus);
        $this->em->flush();

        return $this;
    }

    public function getInitialized() {
        $this->fetch();
        return $this->systemstatus->getInitialized();
    }

    public function setInitialized($initialized) {
        $this->fetch();
        $this->systemstatus->setInitialized($initialized);
        $this->em->persist($this->systemstatus);
        $this->em->flush();

        return $this;
    }

    public function getConfiguredversion() {
        $this->fetch();
        return $this->systemstatus->getConfiguredversion();
    }

    public function setConfiguredversion($configuredversion) {
        $this->fetch();
        $this->systemstatus->setConfiguredversion($configuredversion);
        $this->em->persist($this->systemstatus);
        $this->em->flush();

        return $this;
    }
}