<?php
namespace App\Livewire\Settings;
use Livewire\Component;
use App\Models\Setting;
class Index extends Component
{
    public $default_vat = 0;
    public function mount()
    {
        $this->default_vat = Setting::get('default_vat', 5.5);
    }
    public function save()
    {
        Setting::set('default_vat', $this->default_vat);
        session()->flash('success', 'Settings saved.');
    }
    public function render()
    {
        return view('livewire.settings.index')->layout('layouts.app');
    }
}
