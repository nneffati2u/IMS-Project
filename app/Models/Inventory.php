<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Inventory extends Model
{
    use HasFactory;
    protected $fillable = ['inventory_date', 'status', 'attestation_path'];
    protected $casts = ['inventory_date' => 'date'];
    public function items()
    {
        return $this->hasMany(InventoryItem::class);
    }
}
