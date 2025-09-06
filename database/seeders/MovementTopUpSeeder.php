<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use App\Models\Product;
use App\Models\Movement;

class MovementTopUpSeeder extends Seeder
{
    // Cible raisonnable par mois
    private int $months = 12;
    private int $targetOutsPerMonth = 5;
    private int $targetInsPerMonth  = 2;

    public function run(): void
    {
        $now = now()->startOfMonth();
        $products = Product::query()->get();

        foreach ($products as $p) {
            for ($i = $this->months; $i >= 1; $i--) {
                $start = (clone $now)->subMonths($i);
                $end   = (clone $start)->endOfMonth();

                $outsCount = Movement::where('product_id', $p->id)
                    ->where('type', 'OUT')
                    ->whereBetween('occurred_at', [$start, $end])
                    ->count();

                $insCount = Movement::where('product_id', $p->id)
                    ->where('type', 'IN')
                    ->whereBetween('occurred_at', [$start, $end])
                    ->count();

                // Compléter OUT (ventes)
                for ($n = 0; $n < max(0, $this->targetOutsPerMonth - $outsCount); $n++) {
                    $qtyBase = max(1.0, (float)($p->qty_theoretical ?? 50));
                    $qty = round(max(0.1, $qtyBase * (mt_rand(5, 30) / 1000)), 3);

                    Movement::create([
                        'product_id'     => $p->id,
                        'type'           => 'OUT',
                        'quantity'       => $qty,
                        'unit_price_ht'  => $p->price_ht ?? 0,
                        'note'           => '[demo] topup OUT',
                        'occurred_at'    => fake()->dateTimeBetween($start, $end),
                    ]);
                }

                // Compléter IN (réassorts)
                for ($n = 0; $n < max(0, $this->targetInsPerMonth - $insCount); $n++) {
                    $qty = round(mt_rand(5, 40) / 1.0, 3);

                    Movement::create([
                        'product_id'     => $p->id,
                        'type'           => 'IN',
                        'quantity'       => $qty,
                        'unit_price_ht'  => 0,
                        'note'           => '[demo] topup IN',
                        'occurred_at'    => fake()->dateTimeBetween($start, $end),
                    ]);
                }
            }

            // IMPORTANT : on ne touche PAS à qty_theoretical ici pour ne pas casser tes chiffres actuels.
            // Si tu veux recalculer plus tard, on le fera explicitement et séparément.
        }
    }
}
