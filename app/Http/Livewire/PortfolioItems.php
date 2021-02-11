<?php

namespace App\Http\Livewire;

use Livewire\Component;
use WP_Query;

class PortfolioItems extends Component
{
    protected $listeners = ['portfolioFiltered' => 'applyFilters'];

    public array $active = [];

    public function mount()
    {
        if (request()->narrowTo) {
            $this->active = request()->narrowTo;
        }
    }

    public function render()
    {
        $query = [
            'post_type' => 'work',
            'nopaging' => true,
            'tax_query' => ['relation' => 'AND'],
            'orderby' => 'menu_order',
            'order' => 'ASC',
        ];

        if (count($this->active) > 0) {
            $query['tax_query'][] = ['taxonomy' => 'technology', 'field' => 'slug', 'terms' => $this->active];
        }


        $q = new WP_Query($query);

        return view('livewire.portfolio-items', [
            'items' => $q->posts,
        ]);
    }

    public function applyFilters($active)
    {
        $this->active = $active;
    }
}
