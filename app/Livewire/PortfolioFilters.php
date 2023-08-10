<?php

namespace App\Livewire;

use Livewire\Component;

class PortfolioFilters extends Component
{
    public array $narrowTo = [];

    protected $queryString = ['narrowTo'];

    protected $listeners = ['toggle' => 'toggle'];

    public function mount()
    {
    }

    public function render()
    {
//        $this->techs = ;

        return view('livewire.portfolio-filters', [
            'techs' => get_terms('technology', ['hide_empty' => false]),
        ]);
    }

    public function toggle($id)
    {
        if (in_array($id, $this->narrowTo)) {
            unset($this->narrowTo[array_search($id, $this->narrowTo)]);
        } else {
            $this->narrowTo[] = $id;
        }

        $this->dispatch('portfolioFiltered', narrowTo: $this->narrowTo);
    }
}
