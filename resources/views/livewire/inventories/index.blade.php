<div>
    <x-page-head title="Inventaires" subtitle="Comptages physiques et rapprochements." />

    <div class='d-flex justify-content-end mb-3'><button class='btn btn-primary' wire:click='create'>Nouvel
            inventaire</button></div>
    <div class='card'>
        <div class='table-responsive'>
            <table class="ims-table table table-striped mb-0" data-ims-table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Statut</th>
                        <th>Attestation</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($inventories as $inv)
                        <tr>
                            <td>
                                {{ \Illuminate\Support\Carbon::parse($inv->inventory_date)->format('d/m/Y') }}
                                @if ($inv->status === 'Closed')
                                    <small class="text-muted">
                                        à
                                        {{ optional($inv->updated_at)->timezone(config('app.timezone'))->format('H:i') }}
                                    </small>
                                @endif
                            </td>

                            <td>{{ $inv->status }}</td>
                            <td>
                                @if ($inv->status === 'Closed')
                                    <a href='{{ route('inventories.attestation', $inv) }}' target='_blank'>Télécharger
                                        PDF</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class='text-end'><a href='{{ route('inventories.edit', $inv) }}'
                                    class='btn btn-sm btn-outline-secondary'>Ouvrir</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class='card-footer'>{{ $inventories->links() }}</div>
    </div>
</div>
