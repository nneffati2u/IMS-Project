<?php
namespace App\Services;
use App\Models\Movement;
class ForecastService
{
    public function forecastMonthly(int $productId, int $months = 3, int $horizon = 3): array
    {
        $now = now()->startOfMonth();
        $history = [];
        for ($i = $months; $i >= 1; $i--) {
            $m = (clone $now)->subMonths($i);
            $key = $m->format('Y-m');
            $qty = Movement::where('product_id', $productId)
                ->where('type', 'OUT')
                ->whereBetween('occurred_at', [$m, (clone $m)->endOfMonth()])
                ->sum('quantity');
            $history[$key] = (float) $qty;
        }
        $avg = count($history) ? array_sum($history) / count($history) : 0.0;
        $res = [];
        for ($h = 1; $h <= $horizon; $h++) {
            $res[(clone $now)->addMonths($h)->format('Y-m')] = round($avg, 2);
        }
        return $res;
    }
}
