<div>
    <x-page-head title="Mouvements" subtitle="Entrées et sorties du stock." />

    <div class='row g-2 mb-3'>
        <div class='col-md-2'>
            <select class='form-select' wire:model.live='filterType'>
                <option value='ALL'>Tous</option>
                <option value='IN'>Entrées</option>
                <option value='OUT'>Sorties</option>
            </select>
        </div>
        <div class='col-md-4'><select class='form-select' wire:model.live.number='productId'>
                <option value=''>Produit</option>
                @foreach ($products as $p)
                    <option value='{{ $p->id }}'>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div class='col-md-2'></div>
        <div class='col-md-2'></div>
        <div class='col-md-2 text-end d-flex gap-2 flex-nowrap text-nowrap'>
            <button class='btn btn-success' wire:click="openDialog('IN')">+ Entrée</button>
            <button class='btn btn-outline-danger' wire:click="openDialog('OUT')">+ Sortie</button>
        </div>
    </div>
    <div class='card'>
        <div class='table-responsive'>
            <table class="ims-table table table-striped mb-0" data-ims-table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Produit</th>
                        <th>Type</th>
                        <th>Qté</th>
                        <th>PU HT</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($movements as $m)
                        <tr>
                            <td>{{ \Illuminate\Support\Carbon::parse($m->occurred_at)->format('d/m/Y') }}</td>
                            <td>{{ $m->product->name }}</td>
                            <td>{{ $m->type }}</td>
                            <td>{{ number_format($m->quantity, 3) }}</td>
                            <td>{{ $m->unit_price_ht !== null ? number_format($m->unit_price_ht, 2) : '—' }}</td>
                            <td>{{ $m->note }}</td>
                        </tr>
                    @empty
                        <tr>
                            {{-- ajuste le colspan au nombre de colonnes du thead (ici 6) --}}
                            <td colspan="6" class="text-center text-muted py-4">
                                Aucun mouvement.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>
        <div class="card-footer" id="movements-pager">
            {{ $movements->onEachSide(2)->links(view: 'livewire::bootstrap') }}
        </div>

        @once
            <style>
                /* Scope local au pager Produits */
                #movements-pager .pagination {
                    margin-bottom: .25rem;
                }

                #movements-pager .page-link {
                    color: #212529;
                    /* texte gris foncé */
                    border-radius: .5rem;
                    /* pastilles arrondies */
                }

                #movements-pager .page-item.active .page-link {
                    background-color: #fff;
                    /* plus de bleu */
                    color: #212529;
                    /* texte normal */
                    border-color: #dee2e6;
                    /* bord gris clair */
                }

                #movements-pager .page-link:focus {
                    box-shadow: none;
                }
            </style>
        @endonce

    </div>
    @if ($showDialog)
        <div class='modal fade show' style='display:block;background:rgba(0,0,0,.5)'>
            <div class='modal-dialog'>
                <div class='modal-content'>
                    <div class='modal-header'>
                        <h5 class='modal-title'>Ajouter {{ $dlg_type === 'IN' ? 'Entrée' : 'Sortie' }}</h5>
                    </div>
                    <div class='modal-body'>
                        <div class='mb-2'><label>Produit</label><select class='form-select'
                                wire:model='dlg_product_id'>
                                <option value=''>—</option>
                                @foreach ($products as $p)
                                    <option value='{{ $p->id }}'>{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class='mb-2'><label>Quantité</label><input type='number' step='0.001'
                                class='form-control' wire:model='dlg_quantity'></div>
                        <div class='mb-2'><label>PU HT (optionnel)</label><input type='number' step='0.01'
                                class='form-control' wire:model='dlg_unit_price_ht'></div>
                        <div class='mb-2'><label>Note</label><input class='form-control' wire:model='dlg_note'></div>
                    </div>
                    <div class='modal-footer'><button class='btn btn-light'
                            wire:click="$set('showDialog', false)">Annuler</button><button class='btn btn-primary'
                            wire:click='saveMovement'>Valider</button></div>
                </div>
            </div>
        </div>
    @endif
</div>
