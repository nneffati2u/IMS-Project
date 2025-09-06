<?php
namespace App\Livewire\Alerts;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Alert;
class Index extends Component
{
    use WithPagination;
    public function render()
    {
        $alerts = Alert::with('product')->orderByDesc('updated_at')->paginate(15);
        return view('livewire.alerts.index', compact('alerts'))->layout('layouts.app');
    }
}
