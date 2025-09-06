<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Movement extends Model
{
    use HasFactory;
    protected $fillable = ['product_id', 'type', 'quantity', 'unit_price_ht', 'occurred_at', 'note'];
    protected $casts = ['quantity' => 'float', 'unit_price_ht' => 'float', 'occurred_at' => 'datetime'];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
