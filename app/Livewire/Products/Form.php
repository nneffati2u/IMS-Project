<?php

namespace App\Livewire\Products;

use Livewire\Component;
use App\Models\Product;
use App\Models\Category;

class Form extends Component
{
    public ?Product $product = null;

    public string $name = '';
    public ?string $description = '';
    public $category_id = null;
    public float $price_ht = 0;
    public float $tva_rate = 0;
    public float $qty_theoretical = 0;

    public $low_stock_threshold_value = null; // float|null
    public $low_stock_threshold_percent = null; // float|null

    //  NE PAS typer en bool: Livewire envoie "0"/"1" => on garde int 0/1
    public $is_threshold_percent = 1;

    protected $messages = [
        'price_ht.gt' => 'Le prix HT doit être strictement supérieur à 0.',
        'price_ht.min' => 'Le prix HT ne peut pas être négatif.',
        'tva_rate.gt' => 'La TVA % doit être strictement supérieure à 0.',
        'tva_rate.min' => 'La TVA % ne peut pas être négative.',
        'tva_rate.lte' => 'La TVA % ne peut pas dépasser 100.',
        'qty_theoretical.gt' => 'Le stock théorique doit être strictement supérieur à 0.',
        'qty_theoretical.min' => 'Le stock théorique ne peut pas être négatif.',
        'low_stock_threshold_percent.gt' => 'Le seuil (%) doit être strictement supérieur à 0.',
        'low_stock_threshold_value.gt' => 'Le seuil (valeur) doit être strictement supérieur à 0.',
        'low_stock_threshold_percent.required_if' => 'Veuillez saisir un seuil en % (strictement > 0).',
        'low_stock_threshold_value.required_if' => 'Veuillez saisir un seuil en valeur (strictement > 0).',
    ];

    protected $validationAttributes = [
        'price_ht' => 'prix HT',
        'tva_rate' => 'TVA %',
        'qty_theoretical' => 'stock théorique',
        'low_stock_threshold_percent' => 'seuil (%)',
        'low_stock_threshold_value' => 'seuil (valeur)',
        'is_threshold_percent' => 'type de seuil',
        'category_id' => 'catégorie',
    ];

    public function mount($product = null): void
    {
        if ($product instanceof Product) {
            $this->product = $product;
        } elseif (!is_null($product)) {
            $this->product = Product::findOrFail($product);
        }

        if ($this->product) {
            $this->fill([
                'name' => (string) $this->product->name,
                'description' => $this->product->description,
                'category_id' => $this->product->category_id,
                'price_ht' => (float) $this->product->price_ht,
                'tva_rate' => (float) $this->product->tva_rate,
                'qty_theoretical' => (float) $this->product->qty_theoretical,
                'low_stock_threshold_value' => $this->product->low_stock_threshold_value,
                'low_stock_threshold_percent' => $this->product->low_stock_threshold_percent,
                // cast explicite en 0/1 pour le <select>
                'is_threshold_percent' => (int) ($this->product->is_threshold_percent ? 1 : 0),
            ]);

            // Auto-cohérence si données anciennes
            if (!is_null($this->low_stock_threshold_value) && is_null($this->low_stock_threshold_percent)) {
                $this->is_threshold_percent = 0;
            } elseif (!is_null($this->low_stock_threshold_percent) && is_null($this->low_stock_threshold_value)) {
                $this->is_threshold_percent = 1;
            }
        } else {
            $this->is_threshold_percent = 1; // % par défaut en création
        }
    }

    public function updatedIsThresholdPercent($value): void
    {
        // Normaliser "0"/"1" -> 0/1
        $this->is_threshold_percent = (string) $value === '1' ? 1 : 0;

        if ($this->is_threshold_percent === 1) {
            // On passe à % => on vide la valeur absolue
            $this->low_stock_threshold_value = null;
        } else {
            // On passe à valeur => on vide le %
            $this->low_stock_threshold_percent = null;
        }
    }

    public function save()
    {
        $isUpdate = (bool) $this->product;

        $priceRule = $isUpdate ? ['required', 'numeric', 'min:0'] : ['required', 'numeric', 'gt:0'];
        $tvaRule = $isUpdate ? ['required', 'numeric', 'min:0', 'lte:100'] : ['required', 'numeric', 'gt:0', 'lte:100'];
        $qtyRule = $isUpdate ? ['required', 'numeric', 'min:0'] : ['required', 'numeric', 'gt:0'];

        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],

            'price_ht' => $priceRule,
            'tva_rate' => $tvaRule,
            'qty_theoretical' => $qtyRule,

            // 0 ou 1 uniquement (évite l’état “vide” => retombe à 1)
            'is_threshold_percent' => ['required', 'in:0,1'],

            // Valide uniquement le champ actif
            'low_stock_threshold_percent' => ['exclude_unless:is_threshold_percent,1', 'required_if:is_threshold_percent,1', 'numeric', 'gt:0', 'lte:100'],
            'low_stock_threshold_value' => ['exclude_unless:is_threshold_percent,0', 'required_if:is_threshold_percent,0', 'numeric', 'gt:0'],
        ]);

        // Exclusivité & nettoyage serveur
        if ((int) $this->is_threshold_percent === 1) {
            $data['low_stock_threshold_value'] = null;
        } else {
            $data['low_stock_threshold_percent'] = null;
        }

        // cast int → bool pour l’ORM si besoin
        $data['is_threshold_percent'] = (int) $this->is_threshold_percent === 1;

        if ($this->product) {
            $this->product->update($data);
        } else {
            $this->product = Product::create($data);
        }

        //session()->flash('success', 'Produit enregistré avec succès.');
        session()->flash('product_success', 'Produit enregistré avec succès.');
        return redirect()->route('products.index');

        return redirect()->route('products.index');
    }

    public function render()
    {
        $categories = Category::orderBy('name')->get();
        return view('livewire.products.form', compact('categories'))->layout('layouts.app');
    }
}
