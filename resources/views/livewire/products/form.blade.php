<div>
    <div class="mb-3">
        <a href="{{ route('inventories.index') }}" class="btn btn-outline-secondary btn-sm">← Retour</a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nom <span class="text-danger">*</span></label>
                    <input class="form-control" wire:model.defer="name">
                    @error('name')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Catégorie <span class="text-danger">*</span></label>
                    <select class="form-select" wire:model.defer="category_id">
                        <option value="">— Choisir une catégorie —</option>
                        @foreach ($categories as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" rows="2" wire:model.defer="description"></textarea>
                    @error('description')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Prix HT <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" class="form-control"
                        wire:model.defer="price_ht">
                    @error('price_ht')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">TVA % <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" max="100" class="form-control"
                        wire:model.defer="tva_rate">
                    @error('tva_rate')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Stock théorique <span class="text-danger">*</span></label>
                    <input type="number" step="1" min="0" class="form-control"
                        wire:model.defer="qty_theoretical">
                    @error('qty_theoretical')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Type de seuil --}}
                <div class="col-md-4">
                    <label class="form-label">Type de seuil</label>
                    {{-- synchro immédiate pour recalculer l'état disabled des inputs --}}
                    <select class="form-select" wire:model.live="is_threshold_percent" wire:key="threshold-type">
                        <option value="1">Pourcentage</option>
                        <option value="0">Valeur (u)</option>
                    </select>
                    @error('is_threshold_percent')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Seuil % --}}
                <div class="col-md-4">
                    <label class="form-label">Seuil %</label>
                    <input type="number" step="0.01" min="0.01" max="100" class="form-control"
                        wire:model.defer="low_stock_threshold_percent" @disabled((int) $is_threshold_percent !== 1)>
                    @error('low_stock_threshold_percent')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Seuil (valeur) --}}
                <div class="col-md-4">
                    <label class="form-label">Seuil (valeur)</label>
                    <input type="number" step="0.01" min="0.01" class="form-control"
                        wire:model.defer="low_stock_threshold_value" @disabled((int) $is_threshold_percent === 1)>
                    @error('low_stock_threshold_value')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

            </div>
        </div>

        <div class="card-footer d-flex justify-content-end gap-2">
            <a href="{{ route('products.index') }}" class="btn btn-light">Annuler</a>
            <button class="btn btn-primary" wire:click="save">Enregistrer</button>
        </div>
    </div>
</div>
