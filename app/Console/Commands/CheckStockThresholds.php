<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\Alert;
use App\Jobs\SendLowStockAlertEmail;
class CheckStockThresholds extends Command
{
    protected $signature = 'stock:check-thresholds';
    protected $description = 'Check thresholds & send throttled emails';
    public function handle(): int
{
    $emailTo = config('mail.from.address', 'alerts@example.test');
    $now = now();

    Product::chunk(200, function ($products) use ($emailTo, $now) {
        foreach ($products as $p) {
            $stock = (float) $p->qty_theoretical;

            // 1) Déterminer le seuil ou None
            $threshold = null;
            if ($p->is_threshold_percent) {
                $percent = $p->low_stock_threshold_percent;
                if (is_numeric($percent) && (float)$percent > 0) {
                    $threshold = $stock * ((float)$percent / 100.0);
                }
            } else {
                $value = $p->low_stock_threshold_value;
                if (is_numeric($value) && (float)$value >= 0) { // (>=0) à débattre selon ta règle
                    $threshold = (float)$value;
                }
            }

            // 2) Si pas de seuil => toujours Normal (pas d’alerte)
            if ($threshold === null) {
                // On clôt une éventuelle alerte encore marquée Below
                $alert = Alert::firstOrNew(['product_id' => $p->id]);
                if ($alert->exists && $alert->current_state === 'Below') {
                    $alert->resolved_at = $now;
                    $alert->current_state = 'Normal';
                    $alert->save();
                }
                continue;
            }

            // 3) Détection “Below” (choix < ou <=)
            $below = $stock < $threshold;

            $alert = Alert::firstOrNew(['product_id' => $p->id]);
            $state = $alert->current_state;

            if ($below) {
                if (!$alert->exists || $state !== 'Below') {
                    $alert->triggered_at = $now;
                    $alert->current_state = 'Below';
                }
                $alert->resolved_at = null;

                $canSend = !$alert->last_email_sent_at || $alert->last_email_sent_at->diffInMinutes($now) >= 60;
                if ($canSend) {
                    dispatch(new SendLowStockAlertEmail($p, $stock, $emailTo));
                    $alert->last_email_sent_at = $now;
                }
                $alert->save();
            } else {
                if ($alert->exists && $state === 'Below') {
                    $alert->resolved_at = $now;
                    $alert->current_state = 'Normal';
                    $alert->save();
                }
            }
        }
    });

    $this->info('Thresholds checked.');
    return self::SUCCESS;
}

}
