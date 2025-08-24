<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $appends = ['total_item'];

    public function products()
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function transactions()
    {
        return $this->hasMany(OrderTransaction::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // ✅ Add seller relationship
    public function seller()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getTotalItemAttribute()
    {
        return $this->products()->sum('quantity');
    }
}
