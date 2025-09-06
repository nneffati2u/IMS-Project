
<div>
<x-page-head title="Produits" subtitle="Catalogue des articles et niveaux de stock." />
    {{-- Alertes (un seul bloc en haut, non collant) --}}
    @if ($flashError)
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ $flashError }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @elseif ($flashSuccess)
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ $flashSuccess }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            {{-- Recherche (large) --}}
            <input type="text" class="form-control" placeholder="Rechercher (nom ou catégorie)…" style="width: 380px"
                wire:model.live.debounce.300ms="q">

            {{-- Catégories (compact) --}}
            <select class="form-select" style="width: 220px" wire:model.live="categoryId">
                <option value="">Toutes catégories</option>
                @foreach ($categories as $c)
                    <option value="{{ $c['id'] }}">{{ $c['name'] }}</option>
                @endforeach
            </select>
        </div>

        <a href="{{ route('products.create') }}" class="btn btn-primary">
            + Créer un produit
        </a>
    </div>


    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nom</th>
                        <th>Catégorie</th>
                        <th class="text-end">Prix HT</th>
                        <th class="text-end">TVA</th>
                        <th class="text-end">Stock théorique</th>
                        <th class="text-end">Seuil</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $p)
                        <tr>
                            <td>{{ $p->name }}</td>
                            <td>{{ $p->category->name ?? '—' }}</td>
                            <td class="text-end">
                                {{ number_format((float) ($p->price_ht ?? 0), 2, ',', ' ') }}
                            </td>
                            <td class="text-end">
                                {{ rtrim(rtrim(number_format((float) ($p->tva_rate ?? 0), 1, ',', ' '), '0'), ',') }} %
                            </td>

                            <td class="text-end">
                                {{ (int) ($p->qty_theoretical ?? 0) }}
                            </td>
                            <td class="text-end">
                                @php
                                    $seuil = '—';

                                    if ($p->is_threshold_percent) {
                                        // seuil en %
                                        if ($p->low_stock_threshold_percent !== null) {
                                            $seuil =
                                                rtrim(
                                                    rtrim(
                                                        number_format(
                                                            (float) $p->low_stock_threshold_percent,
                                                            1,
                                                            ',',
                                                            ' ',
                                                        ),
                                                        '0',
                                                    ),
                                                    ',',
                                                ) . ' %';
                                        } else {
                                            // valeur null -> laisse "—"
                                        }
                                    } else {
                                        // seuil en unités
                                        if ($p->low_stock_threshold_value !== null) {
                                            $seuil = (int) $p->low_stock_threshold_value . ' u';
                                        }
                                    }
                                @endphp
                                {{ $seuil }}
                            </td>

                            <td class="text-end">
                                <a href="{{ route('products.edit', $p) }}"
                                    class="btn btn-sm btn-outline-secondary">Éditer</a>
                                <button type="button" class="btn btn-sm btn-danger ms-1"
                                    wire:click="delete({{ $p->id }})" wire:confirm="Supprimer ce produit ?">
                                    Supprimer
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Aucun produit</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- FOOTER : pagination façon "Mouvements" (alignée à gauche) --}}
        <div class="card-footer" id="products-pager">
            {{-- Rangée 1 : Prev/Next (gros) --}}
            {{ $products->links('livewire::simple-bootstrap') }}



            {{-- Rangée 3 : numéros de pages --}}
            {{ $products->onEachSide(2)->links('livewire::bootstrap') }}


        </div>

        @once
            <style>
                /* Scope local au pager Produits */
                #products-pager .pagination {
                    margin-bottom: .25rem;
                }

                #products-pager .page-link {
                    color: #212529;
                    /* texte gris foncé */
                    border-radius: .5rem;
                    /* pastilles arrondies */
                }

                #products-pager .page-item.active .page-link {
                    background-color: #fff;
                    /* plus de bleu */
                    color: #212529;
                    /* texte normal */
                    border-color: #dee2e6;
                    /* bord gris clair */
                }

                #products-pager .page-link:focus {
                    box-shadow: none;
                }
            </style>
        @endonce



    </div>
</div>
