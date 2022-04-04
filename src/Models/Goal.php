<?php

namespace AbTesting\Models;

use Illuminate\Database\Eloquent\Model;

class Goal extends Model
{
    protected $table = 'ab_goals';

    protected $fillable = [
        'name',
        'hit',
    ];

    protected $casts = [
        'hit' => 'integer',
    ];

    public function incrementHit()
    {
        $this->increment('hit');
    }
}
