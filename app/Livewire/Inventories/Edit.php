<?php
namespace App\Livewire\Inventories;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Inventory;
use App\Models\InventoryItem;
class Edit extends Component
{
    use WithPagination;
    public Inventory $inventory;
    public string $backUrl;
    public function mount(Inventory $inventory)
    {
        $this->inventory = $inventory;

        $this->backUrl = url()->previous();

        // Sécurise : si previous == current (ou une requête livewire), fallback vers l'index
        if ($this->backUrl === url()->current() || str_contains($this->backUrl, '/livewire/')) {
            $this->backUrl = route('inventories.index');
        }
    }
    public function saveItem(int $itemId, $value)
    {
        $item = InventoryItem::findOrFail($itemId);
        $item->real_qty = is_numeric($value) ? (float) $value : null;
        $item->save();
    }
    public function saveItemNote(int $itemId, $value)
    {
        $item = \App\Models\InventoryItem::findOrFail($itemId);
        $item->notes = $value;
        $item->save();
    }

    public function close()
    {
        $this->inventory->status = 'Closed';
        $this->inventory->save();
        session()->flash('success', 'Inventory closed. You can now download the PDF.');
    }
    public function render()
    {
        $items = InventoryItem::where('inventory_id', $this->inventory->id)->with('product')->orderBy('id')->paginate(20);
        return view('livewire.inventories.edit', compact('items'))->layout('layouts.app');
    }
}
