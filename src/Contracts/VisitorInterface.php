<?php

namespace AbTesting\Contracts;

use AbTesting\Models\Variant;

interface VisitorInterface
{
    public function hasVariant();

    public function getVariant();

    public function setVariant(Variant $variant);
}
