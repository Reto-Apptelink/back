<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class orderDetail extends Model
{
    use HasFactory;
    protected $table = 'order_details';

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    // Relación con producto
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    // Relación con orden
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
    
}
