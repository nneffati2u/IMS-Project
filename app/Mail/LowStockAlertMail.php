<?php
namespace App\Mail;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
class LowStockAlertMail extends Mailable
{
    use Queueable, SerializesModels;
    public Product $product;
    public float $stock;
    public function __construct(Product $product, float $stock)
    {
        $this->product = $product;
        $this->stock = $stock;
    }
    public function build()
    {
        return $this->subject('Low stock alert: ' . $this->product->name)->view('emails.low_stock');
    }
}
