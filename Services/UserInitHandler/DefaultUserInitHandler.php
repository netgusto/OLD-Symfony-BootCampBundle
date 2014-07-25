<?php

namespace Netgusto\BootCampBundle\Services\UserInitHandler;

use Symfony\Component\Security\Core\Encoder\EncoderFactory;

use Doctrine\ORM\EntityManager;

use Netgusto\BootCampBundle\Services\UserInitHandler\UserInitHandlerInterface;

class DefaultUserInitHandler implements UserInitHandlerInterface {

    protected $entityManager;
    protected $passwordencoder_factory;
    protected $userClass;
    protected $roles;
    protected $usernameProp;
    protected $rolesProp;
    protected $passwordProp;
    protected $saltProp;

    public function __construct(
        EntityManager $entityManager,
        EncoderFactory $passwordencoder_factory,
        $userClass,
        array $roles,
        $usernameProp,
        $rolesProp,
        $passwordProp,
        $saltProp
    ) {
        $this->entityManager = $entityManager;
        $this->passwordencoder_factory = $passwordencoder_factory;
        $this->userClass = $userClass;
        $this->roles = $roles;
        $this->usernameProp = $usernameProp;
        $this->rolesProp = $rolesProp;
        $this->passwordProp = $passwordProp;
        $this->saltProp = $saltProp;
    }

    public function createAndPersistUser($username, $password) {

        $usernameSetter = 'set' . ucfirst($this->usernameProp);
        $rolesSetter = 'set' . ucfirst($this->rolesProp);
        $passwordSetter = 'set' . ucfirst($this->passwordProp);
        $saltSetter = 'set' . ucfirst($this->saltProp);
        $saltGetter = 'get' . ucfirst($this->saltProp);

        $userClass = $this->userClass;
        $user = new $userClass();

        $user->$usernameSetter($username);
        $user->$saltSetter(md5(rand() . microtime()));
        $user->$rolesSetter($this->roles);
        $user->$passwordSetter(
            $this->passwordencoder_factory
                ->getEncoder($user)
                ->encodePassword(
                    $password,
                    $user->$saltGetter()
                )
        );

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}