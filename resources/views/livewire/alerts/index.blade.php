<div>
    <x-page-head title="Alertes" subtitle="Ruptures, bas de stock et produits dormants." />

    <div class='card mt-3'>
        <div class='table-responsive'>
            <table class="ims-table table table-striped mb-0" data-ims-table>
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>État</th>
                        <th>Déclenchée</th>
                        <th>Dernier email</th>
                        <th>Résolue</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($alerts as $a)
                        <tr>
                            <td>{{ $a->product->name }}</td>
                            <td>{{ $a->current_state }}</td>
                            <td>{{ $a->triggered_at?->timezone('Europe/Paris')?->format('d/m/Y H:i') }}</td>
                            <td>{{ $a->last_email_sent_at?->timezone('Europe/Paris')?->format('d/m/Y H:i') }}</td>
                            <td>{{ $a->resolved_at?->timezone('Europe/Paris')?->format('d/m/Y H:i') }}</td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $alerts->onEachSide(2)->links(view: 'livewire::bootstrap') }}
        </div>
    </div>
</div>
