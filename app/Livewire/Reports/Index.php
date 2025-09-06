<?php

declare(strict_types=1);

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\Product;
use App\Models\Movement;
use Illuminate\Support\Carbon;

class Index extends Component
{
    /** Fenêtre d'analyse (jours) : 30 / 90 / 180 */
    public int $periodDays = 90;

    /** Prévision : nombre de mois pour la moyenne/tendance */
    public int $monthsAvg = 3;

    /** Prévision : utiliser la tendance (true) ou la moyenne plate (false) */
    public bool $useTrend = true;

    /** Produit sélectionné pour la prévision */
    public ?int $productId = null;

    /** Liste des produits {id, name} pour les selects */
    public array $products = [];

    /** Datasets graphiques */
    public array $top5 = ['labels' => [], 'values' => []];
    public array $dormants = ['labels' => [], 'values' => []];
    public array $stockState = ['labels' => [], 'values' => []];
    public array $forecast = ['labels' => [], 'values' => []];

    public function mount(): void
    {
        $this->products = Product::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn($p) => ['id' => (int) $p->id, 'name' => (string) $p->name])
            ->all();

        $this->productId = $this->productId ?? ($this->products[0]['id'] ?? null);

        $this->computeAll();
    }

    public function updated(string $name): void
    {
        if (in_array($name, ['periodDays', 'productId', 'monthsAvg', 'useTrend'], true)) {
            if ($this->periodDays <= 0) {
                $this->periodDays = 30;
            }
            if ($this->monthsAvg <= 0) {
                $this->monthsAvg = 1;
            }
            $this->computeAll();
        }
    }

    private function computeAll(): void
    {
        $this->computeTop5();
        $this->computeDormants();
        $this->computeStockState();
        $this->computeForecast();
        $this->emitCharts();
    }

    /** TOP 5 des mouvements sur la période (occurred_at -> fallback created_at) */
    private function computeTop5(): void
    {
        $since = now()->subDays($this->periodDays);

        $movements = Movement::query()
            ->where(function ($q) use ($since) {
                $q->where(function ($qq) use ($since) {
                    $qq->whereNotNull('occurred_at')->where('occurred_at', '>=', $since);
                })->orWhere(function ($qq) use ($since) {
                    $qq->whereNull('occurred_at')->where('created_at', '>=', $since);
                });
            })
            ->get(['product_id', 'qty', 'quantity', 'qte', 'occurred_at', 'created_at']);

        $sums = [];
        foreach ($movements as $m) {
            $pid = (int) $m->product_id;
            $qty = abs($this->movementQty($m));
            if ($qty <= 0) {
                continue;
            }
            $sums[$pid] = ($sums[$pid] ?? 0) + $qty;
        }

        if (!$sums) {
            $this->top5 = ['labels' => [], 'values' => []];
            return;
        }

        arsort($sums);
        $top = array_slice($sums, 0, 5, true);

        $names = Product::whereIn('id', array_keys($top))->pluck('name', 'id')->all();

        $labels = [];
        $values = [];
        foreach ($top as $pid => $val) {
            $labels[] = (string) ($names[$pid] ?? 'Produit #' . $pid);
            $values[] = (float) $val;
        }

        $this->top5 = ['labels' => $labels, 'values' => $values];
    }

    /** Dormants : dernière vente (OUT) uniquement */
    

    private function computeDormants(): void
{
    $now = now();

    // Dernière vente (OUT) par produit (occurred_at -> fallback created_at)
    $lastOuts = \App\Models\Movement::query()
        ->selectRaw('product_id, MAX(COALESCE(occurred_at, created_at)) as last_out_at')
        ->where('type', 'OUT')
        ->groupBy('product_id')
        ->pluck('last_out_at', 'product_id'); // [product_id => datetime|null]

    $rows = \App\Models\Product::select(['id','name','created_at'])->get()
        ->map(function ($p) use ($lastOuts, $now) {
            $lastSale = $lastOuts[$p->id] ?? null;

            if ($lastSale) {
                // Jours depuis la dernière vente
                $d = Carbon::parse($lastSale);
                if ($d->gt($now)) { $d = $now; }
                $days = $d->diffInDays($now);
            } else {
                // Jamais vendu -> Jours depuis la création
                $created = $p->created_at ?: $now;
                $days = Carbon::parse($created)->diffInDays($now);
            }

            return [
                'id'   => (int)$p->id,
                'name' => (string)$p->name,  // nom seul (les valeurs ne sont que dans le tooltip)
                'days' => (float)$days,
            ];
        })
        ->sortByDesc('days')
        ->take(5)
        ->values();

    $this->dormants = [
        'labels' => $rows->pluck('name')->all(),
        'values' => $rows->pluck('days')->all(),
    ];
}

    /** Top 10 stocks théoriques */
    private function computeStockState(): void
    {
        $rows = Product::query()
            ->orderByDesc('qty_theoretical')
            ->take(10)
            ->get(['name', 'qty_theoretical']);

        $this->stockState = [
            'labels' => $rows->pluck('name')->map(fn($s) => (string) $s)->all(),
            'values' => $rows->pluck('qty_theoretical')->map(fn($v) => (float) $v)->all(),
        ];
    }

    /**
     * Prévision M+1..M+3 :
     * - useTrend=false  -> moyenne mensuelle (ligne plate, comme avant)
     * - useTrend=true   -> régression linéaire sur la série mensuelle (valeurs distinctes)
     */
    private function computeForecast(): void
    {
        $this->forecast = ['labels' => [], 'values' => []];
        if (!$this->productId) {
            return;
        }

        $months = max(1, (int) $this->monthsAvg);
        $since = now()->startOfDay()->subMonths($months);

        // OUT du produit dans la fenêtre (occurred_at -> fallback created_at)
        $outs = Movement::query()
            ->where('product_id', $this->productId)
            ->where('type', 'OUT')
            ->where(function ($q) use ($since) {
                $q->where(function ($qq) use ($since) {
                    $qq->whereNotNull('occurred_at')->where('occurred_at', '>=', $since);
                })->orWhere(function ($qq) use ($since) {
                    $qq->whereNull('occurred_at')->where('created_at', '>=', $since);
                });
            })
            ->get(['occurred_at', 'created_at', 'qty', 'quantity', 'qte']);

        $qtyOf = static function ($m): float {
            return abs((float) ($m->qty ?? ($m->quantity ?? ($m->qte ?? 0))));
        };

        // ---- Série mensuelle (YYYY-MM) ----
        $byMonth = [];
        foreach ($outs as $m) {
            $dt = \Illuminate\Support\Carbon::parse($m->occurred_at ?? $m->created_at);
            $key = $dt->format('Y-m');
            $byMonth[$key] = ($byMonth[$key] ?? 0.0) + $qtyOf($m);
        }

        $series = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $k = now()->copy()->subMonths($i)->format('Y-m');
            $series[] = (float) ($byMonth[$k] ?? 0.0);
        }

        // Moyenne sur N mois
        $K = max(1, count($series));
        $avg = array_sum($series) / $K;

        // Pas assez de données "vivantes" -> moyenne plate (comme avant)
        $nonZero = array_sum(array_map(fn($v) => $v > 0 ? 1 : 0, $series));
        if ($nonZero < 2) {
            $this->forecast = [
                'labels' => ['M+1', 'M+2', 'M+3'],
                'values' => [(float) $avg, (float) $avg, (float) $avg],
            ];
            return;
        }

        // ---- Pente via régression linéaire (x=1..K) ----
        $sumX = ($K * ($K + 1)) / 2;
        $sumX2 = ($K * ($K + 1) * (2 * $K + 1)) / 6;
        $sumY = array_sum($series);
        $sumXY = 0.0;
        for ($i = 1; $i <= $K; $i++) {
            $sumXY += $i * $series[$i - 1];
        }
        $den = $K * $sumX2 - $sumX * $sumX;
        $b = $den != 0 ? ($K * $sumXY - $sumX * $sumY) / $den : 0.0; // pente

        // ---- Projection "centrée" sur la moyenne (évite 0 brutal) ----
        $pred = [];
        for ($h = 1; $h <= 3; $h++) {
            $yhat = $avg + $b * $h; // tendance autour de la moyenne
            $pred[] = max(0.0, (float) $yhat); // clamp >= 0
        }

        $this->forecast = [
            'labels' => ['M+1', 'M+2', 'M+3'],
            'values' => $pred,
        ];
    }

    /** Récupère la quantité d'un mouvement, quel que soit le nom de colonne. */
    private function movementQty($movement): float
    {
        $val = $movement->qty ?? ($movement->quantity ?? ($movement->qte ?? 0));
        return (float) $val;
    }

    private function emitCharts(): void
    {
        $this->dispatch(
            'chartsPayload',
            payload: [
                'top5' => $this->top5,
                'dormants' => $this->dormants,
                'stockState' => $this->stockState,
                'forecast' => $this->forecast,
                'periodDays' => $this->periodDays,
            ],
        );
    }

    public function render()
    {
        return view('livewire.reports.index');
    }
}
