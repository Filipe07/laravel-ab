<?php

namespace AbTesting\Models;

use AbTesting\Contracts\VisitorInterface;
use Illuminate\Database\Eloquent\Model;

class DatabaseVisitor extends Model implements VisitorInterface
{
    protected $primaryKey = 'visitor_id';
    protected $table = 'ab_visitors';
    protected $fillable = [
        'visitor_id',
        'variant_id',
    ];

    public function variant()
    {
        return $this->belongsTo(Variant::class);
    }

    public function getVariant()
    {
        return $this->variant;
    }

    public function setVariant(Variant $next)
    {
        $this->variant_id = $next->id;
        $this->save();
    }

    public function hasVariant()
    {
        return !is_null($this->variant_id) && $this->variant_id;
    }
}
