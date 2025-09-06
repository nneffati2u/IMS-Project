<?php
namespace App\Livewire\Inventories;
use Livewire\Component;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\InventoryItem;
class Index extends Component
{
    public function create()
    {
        $inv = Inventory::create(['inventory_date' => now()->toDateString(), 'status' => 'Draft']);
        foreach (Product::all() as $p) {
            \App\Models\InventoryItem::firstOrCreate(['inventory_id' => $inv->id, 'product_id' => $p->id], ['theoretical_qty_at_snapshot' => $p->qty_theoretical]);
        }
        return redirect()->route('inventories.edit', $inv);
    }
    public function render()
    {
        $inventories = Inventory::orderByDesc('inventory_date')->paginate(10);
        return view('livewire.inventories.index', compact('inventories'))->layout('layouts.app');
    }
}
