<?php

namespace Netgusto\BootCampBundle\Services;

class ScalarInterpreterService {

    public function __construct() {
    }

    public function toBooleanDefaultFalse($value) {

        if(is_string($value)) {
            $value = strtolower($value);
        }

        if(in_array(
            $value,
            array(TRUE, 'true', 'on', 'yes'),
            TRUE    # strict comparison
        )) {
            return TRUE;
        }

        return FALSE;
    }

    public function toBooleanDefaultTrue($value) {

        if(is_string($value)) {
            $value = strtolower($value);
        }

        if(in_array(
            $value,
            array(FALSE, 'false', 'off', 'no'),
            TRUE    # strict comparison
        )) {
            return FALSE;
        }

        return TRUE;
    }
}