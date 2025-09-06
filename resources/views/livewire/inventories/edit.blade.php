<div>
    <div class="mb-3">
        <a href="{{ $backUrl }}" class="btn btn-outline-secondary btn-sm">← Retour</a>
    </div>

    <div class='d-flex justify-content-between align-items-center mb-3'>
        <h5 class="mb-3">
            Inventaire du {{ \Illuminate\Support\Carbon::parse($inventory->inventory_date)->format('d/m/Y') }}
            — Statut: {{ $inventory->status }}
            @if (strtolower($inventory->status) === 'closed')
                <span class="text-muted">
                    (clôturé le
                    {{ optional($inventory->updated_at)->timezone(config('app.timezone'))->format('d/m/Y H:i') }})
                </span>
            @endif
        </h5>

        <div>
            @if ($inventory->status === 'Draft')
            <button class='btn btn-success' wire:click='close'>Clôturer & Générer PDF</button>@else<a
                    class='btn btn-primary' href='{{ route('inventories.attestation', $inventory) }}'
                    target='_blank'>Télécharger PDF</a>
            @endif
        </div>
    </div>
    <div class='card'>
        <div class='table-responsive'>
            @php
                // true si l’inventaire est clôturé (insensible à la casse)
                $isClosed = isset($inventory) && strtolower((string) $inventory->status) === 'closed';
            @endphp

            <table class="ims-table table table-striped mb-0" data-ims-table>
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Théorique</th>
                        <th>Réel saisi</th>
                        <th>Écart</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        @php
                            $diff = is_null($item->real_qty)
                                ? null
                                : $item->real_qty - $item->theoretical_qty_at_snapshot;
                        @endphp
                        <tr>
                            <td>{{ $item->product->name }}</td>

                            <td>{{ number_format($item->theoretical_qty_at_snapshot, 3) }}</td>

                            {{-- Réel saisi : input, désactivé si Closed --}}
                            <td style="width:220px">
                                <input type="number" step="0.001" class="form-control" value="{{ $item->real_qty }}"
                                    wire:change="saveItem({{ $item->id }}, $event.target.value)" @readonly($isClosed)>
                            </td>

                            {{-- Écart : texte, pas d’input --}}
                            <td>{{ is_null($diff) ? '—' : number_format($diff, 3) }}</td>

                            {{-- Notes : texte (ou ce que tu veux afficher) --}}
                            <td>
                                @if ($inventory->status === 'Draft')
                                    <input type="text" class="form-control form-control-sm"
                                        wire:change="saveItemNote({{ $item->id }}, $event.target.value)"
                                        value="{{ $item->notes }}">
                                @else
                                    {{ $item->notes }}
                                @endif
                            </td>

                        </tr>
                    @endforeach
                </tbody>

            </table>
        </div>
        <div class='card-footer'>{{ $items->links() }}</div>
    </div>
</div>
