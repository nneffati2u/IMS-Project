<?php
namespace App\Livewire;
use Livewire\Component;
use App\Models\Product;
use App\Models\Movement;
use App\Models\Alert;
use App\Models\Inventory;
class Dashboard extends Component
{
    public array $stockTop10 = ['labels' => [], 'values' => []]; // ← propriété

    public function mount(): void
    {
        $this->loadStockTop10();
    }
    /** Top 10 des stocks théoriques (état actuel) */
    private function loadStockTop10(): void
    {
        // Version simple : colonne qty_theoretical
        $rows = Product::query()
            ->orderByDesc('qty_theoretical')
            ->take(10)
            ->get(['name', 'qty_theoretical']);

        $this->stockTop10 = [
            'labels' => $rows->pluck('name')->map(fn($s) => (string) $s)->all(),
            'values' => $rows->pluck('qty_theoretical')->map(fn($v) => (float) $v)->all(),
        ];
    }

    public function render()
    {
        $productsCount = Product::count();
        $alertsActive = Alert::where('current_state', 'Below')->count();
        $inventoriesDraft = Inventory::where('status', 'Draft')->count();
        //$stockValue = Product::sum('price_ht');
        $stockValue = Product::query()->selectRaw('SUM(price_ht * qty_theoretical) as v')->value('v');

        $topMovers = Movement::selectRaw('product_id, SUM(ABS(quantity)) as qty')->groupBy('product_id')->orderByDesc('qty')->with('product')->limit(5)->get();
        return view('livewire.dashboard', compact('productsCount', 'alertsActive', 'inventoriesDraft', 'stockValue', 'topMovers'))->layout('layouts.app');
    }
}
