<?php

namespace Netgusto\BootCampBundle\Exception\InitializationNeeded;

class DatabaseMissingInitializationNeededException
    extends \Exception
    implements
        InitializationNeededExceptionInterface {
}