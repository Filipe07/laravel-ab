<?php

namespace AbTesting\Models;

use Illuminate\Database\Eloquent\Model;

class Variant extends Model
{
    protected $table = 'ab_variants';

    protected $fillable = [
        'name',
        'visitors',
    ];

    protected $casts = [
        'visitors' => 'integer',
    ];

    public function goals()
    {
        return $this->hasMany(Goal::class);
    }

    public function incrementVisitor()
    {
        $this->increment('visitors');
    }
}
