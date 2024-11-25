<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $table = 'orders';

    protected $fillable = [
        'customer_id',
        'user_id',
        'order_date',
        'tax_rate',
        'discount',
        'total_amount',
    ];

    // Relación con detalles de la orden
    public function details()
    {
        return $this->hasMany(OrderDetail::class, 'order_id');
    }

    // Relación con cliente
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
