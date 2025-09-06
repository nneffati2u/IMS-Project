@props([
  'title' => '',
  'subtitle' => null,
])

<div class="card page-head border-0 shadow-sm mb-3">
  <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
    <div class="pe-3">
      <h2 class="h4 mb-1">{{ $title }}</h2>
      @if ($subtitle)
        <p class="text-muted mb-0">{{ $subtitle }}</p>
      @endif
    </div>

    @isset($actions)
      <div class="mt-2 mt-sm-0">
        {{ $actions }} {{-- boutons à droite (optionnel) --}}
      </div>
    @endisset
  </div>
</div>
