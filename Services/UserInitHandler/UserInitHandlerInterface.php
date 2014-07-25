<?php

namespace Netgusto\BootCampBundle\Services\UserInitHandler;

interface UserInitHandlerInterface {
    public function createAndPersistUser($username, $password);
}