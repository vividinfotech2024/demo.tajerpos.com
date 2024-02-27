<?php

namespace App\Models\StoreAdmin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipping extends Model
{
    use HasFactory;
    protected $fillable = [
        'shipping_method', 'flat_rate', 'shipping_cost', 'store_id','created_by', 'updated_by'
    ];
    protected $primaryKey = 'shipping_id';
    protected $table = 'store_shipping';
}
