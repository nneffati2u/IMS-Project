<?php
namespace App\Livewire\Movements;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Movement;
use App\Models\Product;
use Illuminate\Validation\Rule;
class Index extends Component
{
    use WithPagination;
    public string $filterType = 'ALL';
    public ?int $productId = null;
    public string $from = '';
    public string $to = '';
    public ?int $dlg_product_id = null;
    public string $dlg_type = 'IN';
    public float $dlg_quantity = 0;
    public ?float $dlg_unit_price_ht = null;
    public string $dlg_note = '';
    public bool $showDialog = false;

    // Conserver les filtres dans l’URL et pendant la pagination
    protected $queryString = [
        'filterType' => ['except' => 'ALL'],
        'productId' => ['except' => null],
        'from' => ['except' => ''],
        'to' => ['except' => ''],
    ];

    // Revenir en page 1 quand un filtre change
    public function updatingFilterType()
    {
        $this->resetPage();
    }
    public function updatingProductId()
    {
        $this->resetPage();
    }
    public function updatingFrom()
    {
        $this->resetPage();
    }
    public function updatingTo()
    {
        $this->resetPage();
    }

    public function openDialog(string $type = 'IN')
    {
        $this->dlg_type = $type;
        $this->showDialog = true;
    }
    public function saveMovement()
    {
        $data = $this->validate(['dlg_product_id' => ['required', 'integer', 'exists:products,id'], 'dlg_type' => ['required', Rule::in(['IN', 'OUT'])], 'dlg_quantity' => ['required', 'numeric', 'gt:0'], 'dlg_unit_price_ht' => ['nullable', 'numeric', 'gte:0'], 'dlg_note' => ['nullable', 'string', 'max:255']]);
        $m = Movement::create(['product_id' => $data['dlg_product_id'], 'type' => $data['dlg_type'], 'quantity' => $data['dlg_quantity'], 'unit_price_ht' => $data['dlg_unit_price_ht'] ?? null, 'note' => $data['dlg_note'] ?: null, 'occurred_at' => now()]);
        $p = $m->product;
        if ($m->type === 'IN') {
            $p->qty_theoretical += $m->quantity;
        } else {
            $p->qty_theoretical -= $m->quantity;
        }
        if ($p->qty_theoretical < 0) {
            $p->qty_theoretical = 0;
        }
        $p->save();
        $this->reset(['dlg_product_id', 'dlg_quantity', 'dlg_unit_price_ht', 'dlg_note', 'showDialog']);
        session()->flash('success', 'Movement saved');
    }
    public function render()
    {
        $q = Movement::with('product')->orderByDesc('occurred_at');
        if ($this->filterType === 'IN') {
            $q->where('type', 'IN');
        }
        if ($this->filterType === 'OUT') {
            $q->where('type', 'OUT');
        }
        if ($this->productId) {
            $q->where('product_id', $this->productId);
        }
        if ($this->from) {
            $q->where('occurred_at', '>=', $this->from);
        }
        
        if ($this->to) {
            // inclure toute la journée "to"
            $q->where('occurred_at', '<=', $this->to . ' 23:59:59');
        }

        $movements = $q->paginate(12)->withQueryString();
        $products = Product::orderBy('name')->get();
        return view('livewire.movements.index', compact('movements', 'products'))->layout('layouts.app');
    }
}
