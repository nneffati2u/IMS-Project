<div>  {{-- wrapper racine unique requis par Livewire --}}

  <style>
    /* Zone principale : descend légèrement le contenu */
    .dashboard-area { padding-top: 18px; min-height: calc(100vh - 180px); }

    /* Chart responsive dans sa carte, net sans zoom */
    .chart-wrap { padding: 12px 0; }
    .chart100  { display:block; width:100% !important; height:300px !important; }

    /* Carte d’en-tête (title/subtitle) */
    .page-head {
      background: linear-gradient(180deg, #ffffff, #f9fbfd);
      border: 1px solid #edf2f7;
      border-radius: .75rem;
    }
  </style>

  <div class="dashboard-area">

    {{-- EN-TÊTE DE PAGE (sans "Dernière mise à jour") --}}
    <x-page-head title="Tableau de bord" subtitle="Vue d’ensemble du stock, alertes et actions rapides." />


    {{-- KPIs --}}
    <div class="row g-3">
      <div class="col-md-3">
        <div class="card"><div class="card-body">
          <h6>Produits</h6><h3>{{ $productsCount }}</h3>
        </div></div>
      </div>
      <div class="col-md-3">
        <div class="card"><div class="card-body">
          <h6>Alertes actives</h6><h3>{{ $alertsActive }}</h3>
        </div></div>
      </div>
      <div class="col-md-3">
        <div class="card"><div class="card-body">
          <h6>Inventaires (Draft)</h6><h3>{{ $inventoriesDraft }}</h3>
        </div></div>
      </div>
      <div class="col-md-3">
        <div class="card"><div class="card-body">
          <h6>Valeur stock (HT)</h6><h3>€{{ number_format($stockValue,2) }}</h3>
        </div></div>
      </div>
    </div>

    {{-- Graphe + Actions rapides --}}
    <div class="row g-3 mt-3">
      {{-- Col gauche : graphe --}}
      <div class="col-lg-8 col-md-12">
        <div class="card h-100">
          <div class="card-body">
            <h5 class="mb-3">État du stock — Top 10 stocks théoriques</h5>

            @php
              $__stockTop10 = $stockTop10 ?? ['labels'=>[], 'values'=>[]];
            @endphp

            @if (empty($__stockTop10['labels']))
              <div class="p-3 text-muted">Pas de données de stock pour l’instant.</div>
            @else
              <div class="chart-wrap">
                <canvas id="dashStockTop10" class="chart100"></canvas>
              </div>
            @endif
          </div>
        </div>
      </div>

      {{-- Col droite : actions rapides --}}
     <div class="col-lg-4 col-md-12">
  <div class="card h-100">
    <div class="card-body">
      <h5 class="mb-1">Actions rapides</h5>
      {{-- Espace supplémentaire avant les boutons --}}
      <div class="d-grid gap-2 mt-5">   {{-- ↑ mt-3/4/5 selon l’espace voulu --}}
        <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm">+ Produit</a>
        <a href="{{ route('movements.index') }}" class="btn btn-outline-secondary btn-sm">+ Entrée/Sortie</a>
        <a href="{{ route('inventories.index') }}" class="btn btn-outline-secondary btn-sm">Nouvel inventaire</a>
      </div>
    </div>
  </div>
</div>

    </div>

  </div> {{-- /.dashboard-area --}}

  {{-- Script Chart.js pour le Top 10 --}}
  <script>
    (function () {
      var el = document.getElementById('dashStockTop10');
      if (!el || !window.Chart) return;

      // Taille = largeur réelle de la carte
      var parent = el.parentElement;
      el.width  = parent.clientWidth;
      el.height = 300;

      // Réglages globaux : net (Retina), pas de resize/animation
      Chart.defaults.devicePixelRatio = Math.max(1, window.devicePixelRatio || 1);
      Chart.defaults.responsive = false;
      Chart.defaults.maintainAspectRatio = false;
      Chart.defaults.animation = false;
      Chart.defaults.animations = {};
      Chart.defaults.font = Chart.defaults.font || {};
      Chart.defaults.font.family = "'Inter', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif";
      Chart.defaults.font.size = 11;
      Chart.defaults.font.weight = 'normal';
      Chart.defaults.scale = Chart.defaults.scale || {};
      Chart.defaults.scale.grid = Chart.defaults.scale.grid || {};
      Chart.defaults.scale.grid.lineWidth = 0.5;

      var payload = {!! Illuminate\Support\Js::from($__stockTop10) !!};

      new Chart(el.getContext('2d'), {
        type: 'bar',
        data: {
          labels: payload.labels,
          datasets: [{ label: 'Stock théorique', data: payload.values, borderWidth: 1 }]
        },
        options: {
          responsive: false,
          maintainAspectRatio: false,
          animation: false,
          plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: (ctx) => String(ctx.parsed.y) } }
          },
          scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
      });
    })();
  </script>
</div>
