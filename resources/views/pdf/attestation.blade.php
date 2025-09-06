<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<style>
  @page { margin: 24mm 18mm; }
  body { font-family: "DejaVu Sans", Arial, sans-serif; font-size: 12px; color: #111; }
  h1 { font-size: 18px; margin: 0 0 12px 0; }
  .mt-3 { margin-top: 18px; }
  .text-right { text-align: right; }
  table { width: 100%; border-collapse: collapse; }
  th, td { border: 1px solid #444; padding: 6px 8px; vertical-align: middle; }
  th { background: #f2f2f2; text-align: left; }
  .footer { position: fixed; bottom: 8mm; left: 18mm; right: 18mm; font-size: 10px; color: #666; }
</style>
</head>
<body>
  <h1>Attestation d'inventaire n° {{ $inventory->id }}</h1>
  <div>Émis par : <strong>{{ $company }}</strong></div>
  <div>Date : <strong>{{ $generated_at->format('d/m/Y H:i') }}</strong></div>
  <div>Statut : <strong>{{ ucfirst($inventory->status) }}</strong></div>

  <table class="mt-3">
    <thead>
      <tr>
        <th>Produit</th>
        <th class="text-right">Théorique</th>
        <th class="text-right">Réel saisi</th>
        <th class="text-right">Écart</th>
        <th>Notes</th>
      </tr>
    </thead>
    <tbody>
  @foreach($items as $item)
    @php
      // Fallback : si le snapshot est nul, on prend la valeur du produit
      $theo = ($item->theoretical_qty ?? null);
      if ($theo === null) {
          $theo = $item->product?->qty_theoretical ?? 0;
      }
      $real = $item->real_qty ?? 0;
      $diff = $real - $theo;
    @endphp
    <tr>
      <td>{{ $item->product?->name }}</td>
      <td class="text-right">{{ number_format($theo, 3, ',', ' ') }}</td>
      <td class="text-right">{{ number_format($real, 3, ',', ' ') }}</td>
      <td class="text-right">{{ number_format($diff, 3, ',', ' ') }}</td>
      <td>{{ $item->notes }}</td>
    </tr>
  @endforeach
</tbody>

  </table>

  <div class="footer">
    Document généré automatiquement — {{ $company }} — {{ $generated_at->format('d/m/Y H:i') }}
  </div>
</body>
</html>
