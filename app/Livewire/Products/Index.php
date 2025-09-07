<?php

namespace App\Livewire\Products;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Product;
use App\Models\Category;
use App\Models\Movement;

class Index extends Component
{
    use WithPagination;

    public function updatingQ()
    {
        $this->resetPage();
    }
    public function updatingCategoryId()
    {
        $this->resetPage();
    }
    public function updatingPerPage()
    {
        $this->resetPage();
    }

    /** Rendu des liens en Bootstrap */
    protected string $paginationTheme = 'bootstrap';

    /** Synchronisation de la page avec ?page=... (corrige l’erreur $page) */
    #[Url(as: 'page')]
    public int $page = 1;

    /** Filtres / UI */
    public ?string $q = null; // recherche texte (nom/catégorie)
    public ?int $categoryId = null; // filtre catégorie (nullable = toutes)
    public int $perPage = 10; // 10/25/50...

    /** Données de la combobox */
    public array $categories = [];

    /** Messages inline */
    public ?string $flashSuccess = null;
    public ?string $flashError = null;

    public array $queryString = [
        'q' => ['except' => ''],
        'categoryId' => ['except' => null],
        'perPage' => ['except' => 10],
        'page' => ['except' => 1], // évite l’erreur quand on clique "2"
    ];

    public function mount(): void
    {
        $this->loadCategories();

        // Filet de sécurité si un lien externe arrive sur une page > 1
        /*if ($this->page < 1) {
            $this->page = 1;
        } */

        if (session()->has('product_success')) {
            $this->flashSuccess = session()->pull('product_success');
        }

        if ($this->page < 1) {
            $this->page = 1;
        }
    }

    private function loadCategories(): void
    {
        $this->categories = Category::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn($c) => ['id' => (int) $c->id, 'name' => (string) $c->name])
            ->all();
    }

    /** Remettre à la 1ère page quand un filtre change */
    public function updated($name): void
    {
        if (in_array($name, ['q', 'categoryId', 'perPage'], true)) {
            $this->resetPage();
        }
    }

    /** Suppression avec garde-fou (mouvements existants) */
    public function delete(int $productId): void
    {
        $product = Product::find($productId);
        if (!$product) {
            $this->flashError = 'Produit introuvable.';
            return;
        }

        $hasMovements = Movement::where('product_id', $productId)->exists();
        if ($hasMovements) {
            $this->flashError = 'Impossible de supprimer : des mouvements existent pour ce produit.';
            return;
        }

        $product->delete();
        $this->flashSuccess = 'Produit supprimé.';
        // On reste sur la même page; Livewire recalcule le listing.
    }

    /** Requête filtrée (nom catégorie, texte, etc.) */

    private function queryFiltered()
    {
        $q = trim($this->q ?? '');

        return \App\Models\Product::query()
            ->with('category:id,name')
            ->when($q, function ($query) use ($q) {
                $query->where(function ($qr) use ($q) {
                    $qr->where('products.name', 'like', "%{$q}%")->orWhereHas('category', fn($c) => $c->where('name', 'like', "%{$q}%"));
                });
            })
            ->when($this->categoryId, fn($query) => $query->where('products.category_id', $this->categoryId))
            ->orderBy('products.name')
            ->select('products.*'); // <-- on garde tout, pas de colonnes fantômes
    }

    public function render()
    {
        // Recharge si nécessaire
        if (empty($this->categories)) {
            $this->loadCategories();
        }

        $products = $this->queryFiltered()->paginate($this->perPage)->withQueryString(); // conserve les filtres quand on change de page

        return view('livewire.products.index', [
            'products' => $products,
            'categories' => $this->categories,
        ])->layout('components.layouts.app');
    }
}
