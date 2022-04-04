<?php

namespace AbTesting\Events;

use AbTesting\Contracts\VisitorInterface;
use AbTesting\Models\Variant;

class VariantNewVisitor
{
    public $variant;
    public $visitor;

    public function __construct(Variant $variant, VisitorInterface $visitor)
    {
        $this->variant = $variant;
        $this->visitor = $visitor;
    }
}
