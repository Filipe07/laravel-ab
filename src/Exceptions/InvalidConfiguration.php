<?php

namespace AbTesting\Exceptions;

use Exception;

class InvalidConfiguration extends Exception
{
    public static function noVariant(): self
    {
        return new static('There are no variants set.');
    }

    public static function variant(): self
    {
        return new static('The variant names should be unique.');
    }

    public static function goal(): self
    {
        return new static('The goal names should be unique.');
    }
}
