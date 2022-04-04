<?php

namespace AbTesting\Models;

use AbTesting\Contracts\VisitorInterface;

class SessionVisitor implements VisitorInterface
{
    const SESSION_KEY_VARIANT = 'ab_testing_variant';

    public function hasVariant()
    {
        return (bool) session(self::SESSION_KEY_VARIANT);
    }

    public function getVariant()
    {
        return session(self::SESSION_KEY_VARIANT);
    }

    public function setVariant(Variant $next)
    {
        session([self::SESSION_KEY_VARIANT => $next]);
    }
}
