<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'phone',
    ];

    /**
     * A customer can have many orders.
     */
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
