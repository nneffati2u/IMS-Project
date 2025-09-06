<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Alert extends Model
{
    use HasFactory;
    protected $fillable = ['product_id', 'triggered_at', 'resolved_at', 'last_email_sent_at', 'current_state'];
    protected $casts = ['triggered_at' => 'datetime', 'resolved_at' => 'datetime', 'last_email_sent_at' => 'datetime'];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
