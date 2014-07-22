<?php

namespace Netgusto\BootCampBundle\Exception\InitializationNeeded;

class DatabaseEmptyInitializationNeededException
    extends \Exception
    implements
        InitializationNeededExceptionInterface {
}