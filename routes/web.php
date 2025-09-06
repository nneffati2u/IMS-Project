<?php
use Illuminate\Support\Facades\Route;
use App\Livewire\Dashboard;
use App\Livewire\Products\Index as ProductsIndex;
use App\Livewire\Products\Form as ProductForm;
use App\Livewire\Movements\Index as MovementsIndex;
use App\Livewire\Inventories\Index as InventoriesIndex;
use App\Livewire\Inventories\Edit as InventoryEdit;
use App\Livewire\Alerts\Index as AlertsIndex;
use App\Livewire\Reports\Index as ReportsIndex;
use App\Livewire\Settings\Index as SettingsIndex;
use App\Http\Controllers\PdfController;

Route::get('/', Dashboard::class)->name('dashboard');
Route::get('/products', ProductsIndex::class)->name('products.index');
Route::get('/products/create', ProductForm::class)->name('products.create');
Route::get('/products/{product}/edit', ProductForm::class)
    ->whereNumber('product')             // évite des collisions bizarres
    ->name('products.edit');
Route::get('/movements', MovementsIndex::class)->name('movements.index');
Route::get('/inventories', InventoriesIndex::class)->name('inventories.index');
Route::get('/inventories/{inventory}', InventoryEdit::class)->name('inventories.edit');
Route::get('/alerts', AlertsIndex::class)->name('alerts.index');
Route::get('/reports', ReportsIndex::class)->name('reports.index');
Route::get('/settings', SettingsIndex::class)->name('settings.index');
Route::get('/inventories/{inventory}/attestation', [PdfController::class, 'attestation'])->name('inventories.attestation');

