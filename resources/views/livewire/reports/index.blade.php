<div> {{-- wrapper racine Livewire --}}

  <x-page-head title="Graphiques" subtitle="Analyses : mouvements, dormants, stock et prévisions." />

  <style>
    .ims-chart-body{ overflow:hidden; }
    .ims-chart-canvas{ display:block; width:100% !important; height:320px !important; }
    @media (max-width: 991.98px){
      .ims-chart-canvas{ height:260px !important; }
    }
    .filters-bar .form-label{ font-size:.825rem; color:#6b7280; margin-bottom:.25rem; }
  </style>

  {{-- ================== Barre de filtres (1 ligne) ================== --}}
  <div class="card mb-3">
    <div class="card-body filters-bar">
      <div class="row g-3 align-items-end">

        {{-- Période des mouvements (Top 5) --}}
        <div class="col-xl-3 col-lg-4 col-md-4 col-sm-6">
          <label class="form-label">Période des mouvements (Top 5)</label>
          <select class="form-select" wire:model.live="periodDays">
            <option value="30">30 jours</option>
            <option value="90">90 jours</option>
            <option value="180">180 jours</option>
          </select>
        </div>

        {{-- Produit (prévision) --}}
        <div class="col-xl-4 col-lg-5 col-md-5 col-sm-6">
          <label class="form-label">Produit (prévision)</label>
          <select class="form-select" wire:model.live="productId">
            <option value="">— Sélectionner —</option>
            @foreach ($products as $p)
              <option value="{{ $p['id'] }}">{{ $p['name'] }}</option>
            @endforeach
          </select>
        </div>

        {{-- Moyenne sur (mois) --}}
        <div class="col-xl-2 col-lg-2 col-md-2 col-sm-6">
          <label class="form-label">Moyenne sur (mois)</label>
          <input type="number" min="1" max="12" class="form-input w-100" wire:model.live="monthsAvg">
        </div>

        {{-- Prévision avec tendance --}}
        <div class="col-xl-3 col-lg-3 col-md-3 col-sm-6">
          <div class="form-check mt-4">
            <input type="checkbox" id="useTrend" class="form-checkbox" wire:model.live="useTrend">
            <label for="useTrend" class="form-check-label">Prévision avec tendance</label>
          </div>
        </div>

      </div>
    </div>
  </div>

  {{-- ================== Ligne 1 : Top 5 + Dormants ================== --}}
  <div class="row g-3">
    {{-- Top 5 mouvements --}}
    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="card-title">Top 5 mouvements (entrées & sorties, {{ $periodDays }} jours)</h5>
        </div>
        <div class="card-body ims-chart-body">
          @if (empty($top5['labels']))
            <div class="p-3 text-muted">Pas de données pour la période sélectionnée.</div>
          @else
            <canvas id="chartTop5" class="ims-chart-canvas"></canvas>
          @endif
        </div>
      </div>
    </div>

    {{-- Produits dormants --}}
    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="card-title">Produits dormants (jours depuis dernière vente)</h5>
        </div>
        <div class="card-body ims-chart-body">
          @if (empty($dormants['labels']))
            <div class="p-3 text-muted">Aucun produit dormant identifié.</div>
          @else
            <canvas id="chartDormants" class="ims-chart-canvas"></canvas>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- ================== Ligne 2 : Stock + Prévision ================== --}}
  <div class="row g-3 mt-1">
    {{-- État du stock (Top 10) --}}
    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="card-title">État du stock — Top 10 stocks théoriques</h5>
        </div>
        <div class="card-body ims-chart-body">
          @if (empty($stockState['labels']))
            <div class="p-3 text-muted">Pas encore de stock enregistré.</div>
          @else
            <canvas id="chartStock" class="ims-chart-canvas"></canvas>
          @endif
        </div>
      </div>
    </div>

    {{-- Prévision simple (M+1..M+3) --}}
    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="card-title">Prévision de consommation (M+1 à M+3)</h5>
        </div>
        <div class="card-body ims-chart-body">
          @if (empty($forecast['labels']))
            <div class="p-3 text-muted">Sélectionnez un produit et une période.</div>
          @else
            <canvas id="chartForecast" class="ims-chart-canvas"></canvas>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- ================== JS Charts (conserve ta logique, ajoute sizing) ================== --}}
  <script>
    (function () {
      // ===== Réglages globaux Chart.js : net, fin, sans zoom =====
      if (window.Chart) {
        Chart.defaults.devicePixelRatio = Math.max(1, window.devicePixelRatio || 1);
        Chart.defaults.responsive = false;
        Chart.defaults.maintainAspectRatio = false;
        Chart.defaults.animation = false;
        Chart.defaults.animations = {};
        Chart.defaults.interaction = Chart.defaults.interaction || {};
        Chart.defaults.interaction.mode = 'nearest';
        Chart.defaults.interaction.intersect = false;

        Chart.defaults.font = Chart.defaults.font || {};
        Chart.defaults.font.family = "'Inter', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif";
        Chart.defaults.font.size = 11;
        Chart.defaults.font.weight = 'normal';

        Chart.defaults.scale = Chart.defaults.scale || {};
        Chart.defaults.scale.grid = Chart.defaults.scale.grid || {};
        Chart.defaults.scale.grid.lineWidth = 0.5;
      }

      // Réfs globales pour détruire/recréer proprement
      window.reportCharts = window.reportCharts || { top5:null, dormants:null, stock:null, forecast:null };

      function destroyChart(c){ if(c && typeof c.destroy==='function'){ c.destroy(); } }
      function fmtNumber(v){ return (Math.round((v ?? 0) * 100)/100).toString(); }

      function mkCommonOptions(){
        return {
          responsive:false, maintainAspectRatio:false, animation:false,
          plugins:{ legend:{display:false} },
          scales:{ y:{ beginAtZero:true, ticks:{ precision:0, callback:(v)=>fmtNumber(v) } } },
          interaction:{ mode:'nearest', intersect:false }
        };
      }

      function mkBarConfig(labels, data, title){
        const opts = mkCommonOptions();
        opts.plugins.tooltip = { enabled:true, callbacks:{ label:(ctx)=> fmtNumber(ctx.parsed.y) } };
        return {
          type:'bar',
          data:{ labels, datasets:[{ label:title||'', data, borderWidth:1, borderRadius:6, categoryPercentage:.6, barPercentage:.9 }] },
          options:opts
        };
      }

      // Horizontal pour Dormants (noms à gauche, jours en abscisse)
      function mkDormantsConfig(labels, values){
        const points = labels.map((lbl,i)=>({ x: values[i] ?? 0, y: String(lbl) }));
        const opts = mkCommonOptions();
        opts.indexAxis='y';
        opts.scales = {
          x:{ type:'linear', beginAtZero:true, ticks:{ precision:0, callback:(v)=>fmtNumber(v) } },
          y:{ type:'category', ticks:{ autoSkip:false } }
        };
        opts.plugins.tooltip = {
          enabled:true,
          callbacks:{ title:(items)=> items?.[0]?.raw?.y ?? '', label:(ctx)=> `${fmtNumber(ctx.raw.x)} jours` }
        };
        return { type:'bar', data:{ datasets:[{ label:'Jours sans vente', data:points, borderWidth:1, borderRadius:6 }] }, options:opts };
      }

      function mkLineConfig(labels, data, title){
        const opts = mkCommonOptions();
        opts.plugins.tooltip = { enabled:true, callbacks:{ label:(ctx)=> `${fmtNumber(ctx.parsed.y)} / mois` } };
        const ds = { label:title||'', data, tension:.2, pointRadius:4, pointHoverRadius:6, hitRadius:12, borderWidth:2 };
        return { type:'line', data:{ labels, datasets:[ds] }, options:opts };
      }

      // ---- NEW: dimensionner les canvas à la largeur de leur carte
      function sizeCanvas(el){
        if(!el || !el.parentElement) return;
        el.width  = el.parentElement.clientWidth;
        el.height = parseInt(getComputedStyle(el).height, 10) || 320;
      }

      function updateAllCharts(payload){
        payload = payload || {};
        const top5      = payload.top5      || { labels:[], values:[] };
        const dormants  = payload.dormants  || { labels:[], values:[] };
        const stock     = payload.stockState|| { labels:[], values:[] };
        const forecast  = payload.forecast  || { labels:[], values:[] };

        // Top 5
        const c1 = document.getElementById('chartTop5');
        if(c1 && top5.labels.length){
          sizeCanvas(c1);
          destroyChart(window.reportCharts.top5);
          window.reportCharts.top5 = new Chart(c1.getContext('2d'), mkBarConfig(top5.labels, top5.values, 'Quantités'));
        }else{ destroyChart(window.reportCharts.top5); window.reportCharts.top5 = null; }

        // Dormants
        const c2 = document.getElementById('chartDormants');
        if(c2 && dormants.labels.length){
          sizeCanvas(c2);
          destroyChart(window.reportCharts.dormants);
          window.reportCharts.dormants = new Chart(c2.getContext('2d'), mkDormantsConfig(dormants.labels, dormants.values));
        }else{ destroyChart(window.reportCharts.dormants); window.reportCharts.dormants = null; }

        // Stock
        const c3 = document.getElementById('chartStock');
        if(c3 && stock.labels.length){
          sizeCanvas(c3);
          destroyChart(window.reportCharts.stock);
          window.reportCharts.stock = new Chart(c3.getContext('2d'), mkBarConfig(stock.labels, stock.values, 'Stock théorique'));
        }else{ destroyChart(window.reportCharts.stock); window.reportCharts.stock = null; }

        // Prévision
        const c4 = document.getElementById('chartForecast');
        if(c4 && forecast.labels.length){
          sizeCanvas(c4);
          destroyChart(window.reportCharts.forecast);
          window.reportCharts.forecast = new Chart(c4.getContext('2d'), mkLineConfig(forecast.labels, forecast.values, 'Prévision'));
        }else{ destroyChart(window.reportCharts.forecast); window.reportCharts.forecast = null; }
      }

      // Attendre le DOM + morph Livewire pour (re)peindre
      function scheduleUpdateCharts(payload, tries=10){
        function attempt(){
          requestAnimationFrame(function(){
            const ok = document.getElementById('chartTop5') || document.getElementById('chartDormants') ||
                       document.getElementById('chartStock') || document.getElementById('chartForecast');
            if(!ok && tries>0){ setTimeout(()=>{ tries--; attempt(); }, 50); }
            else { updateAllCharts(payload); }
          });
        }
        attempt();
      }

      // 1) premier rendu
      @php
        $chartsPayload = [
          'top5'       => $top5,
          'dormants'   => $dormants,
          'stockState' => $stockState,
          'forecast'   => $forecast,
          'periodDays' => $periodDays,
        ];
      @endphp
      document.addEventListener('DOMContentLoaded', function(){
        const payload = {!! Illuminate\Support\Js::from($chartsPayload) !!};
        scheduleUpdateCharts(payload);
      });

      // 2) updates Livewire
      window.addEventListener('chartsPayload', function(e){
        const payload = e.detail?.payload ?? e.detail;
        scheduleUpdateCharts(payload);
      });
    })();
  </script>
</div>
