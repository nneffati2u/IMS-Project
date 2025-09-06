<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class InventoryItem extends Model
{
    use HasFactory;
    protected $fillable = ['inventory_id', 'product_id', 'theoretical_qty_at_snapshot', 'real_qty', 'notes'];
    protected $casts = ['theoretical_qty_at_snapshot' => 'float', 'real_qty' => 'float'];
    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
