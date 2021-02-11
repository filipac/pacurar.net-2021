<?php

namespace App\Http\Livewire;

use Livewire\Component;

class PortfolioFilters extends Component
{
    public array $techs = [];
    public array $narrowTo = [];

    protected $queryString = ['narrowTo'];

    protected $listeners = ['toggle' => 'toggle'];

    public function mount()
    {
    }

    public function render()
    {
        $this->techs = get_terms('technology', ['hide_empty' => false]);

        return view('livewire.portfolio-filters');
    }

    public function toggle($id)
    {
        if (in_array($id, $this->narrowTo)) {
            unset($this->narrowTo[array_search($id, $this->narrowTo)]);
        } else {
            $this->narrowTo[] = $id;
        }

        $this->emit('portfolioFiltered', $this->narrowTo);
    }
}
