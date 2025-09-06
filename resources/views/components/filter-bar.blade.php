{{-- resources/views/components/filter-bar.blade.php --}}
@props([
  'applyId' => null,
  'resetId' => null,
  'showSavedViews' => false,
  'resultCount' => null,
])
<div class="ims-filterbar" role="region" aria-label="Filtres">
  <div class="d-flex flex-wrap gap-2 align-items-center">
    {{ $slot }}
  </div>
  <div class="ms-auto d-flex gap-2 align-items-center">
    @if($resultCount !== null)
      <span class="text-muted">{{ $resultCount }} résultats</span>
    @endif
    <button {{ $applyId ? "form='$applyId'" : '' }} class="btn btn-primary">Appliquer</button>
    <button {{ $resetId ? "form='$resetId'" : '' }} class="btn btn-outline-secondary">Réinitialiser</button>
  </div>
</div>
