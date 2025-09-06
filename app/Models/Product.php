<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Product extends Model
{
    
    //protected $fillable = ['name', 'description', 'category_id', 'price_ht', 'tva_rate', 'qty_theoretical', 'low_stock_threshold_value', 'low_stock_threshold_percent', 'is_threshold_percent'];
    //protected $casts = ['is_threshold_percent' => 'boolean', 'price_ht' => 'float', 'tva_rate' => 'float', 'qty_theoretical' => 'float', 'low_stock_threshold_value' => 'float', 'low_stock_threshold_percent' => 'float'];
    protected $fillable = [
        'name',
        'description',
        'category_id',
        'price_ht',
        'tva_rate',
        'qty_theoretical',
        'low_stock_threshold_value',
        'low_stock_threshold_percent',
        'is_threshold_percent',
    ];

    protected $casts = [
        'price_ht'                    => 'float',
        'tva_rate'                    => 'float',
        'qty_theoretical'             => 'float',
        'low_stock_threshold_value'   => 'float',
        'low_stock_threshold_percent' => 'float',
        'is_threshold_percent'        => 'boolean',
    ];
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function movements()
    {
        return $this->hasMany(Movement::class);
    }


}
