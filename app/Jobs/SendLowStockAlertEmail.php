<?php
namespace App\Jobs;
use App\Mail\LowStockAlertMail;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
class SendLowStockAlertEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public Product $product;
    public float $stock;
    public string $to;
    public function __construct(Product $product, float $stock, string $to)
    {
        $this->product = $product;
        $this->stock = $stock;
        $this->to = $to;
    }
    public function handle(): void
    {
        Mail::to($this->to)->send(new LowStockAlertMail($this->product, $this->stock));
    }
}
