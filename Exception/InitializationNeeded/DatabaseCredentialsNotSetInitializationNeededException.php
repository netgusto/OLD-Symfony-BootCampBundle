<?php

namespace Netgusto\BootCampBundle\Exception\InitializationNeeded;

class DatabaseCredentialsNotSetInitializationNeededException
    extends \Exception
    implements
        InitializationNeededExceptionInterface {
}