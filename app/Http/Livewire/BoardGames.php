<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Http;
use Livewire\Component;

class BoardGames extends Component
{
    public $nfts = [];
    public $count = 0;

    public function mount()
    {
        $nfts = \Cache::remember('board_games', now()->addminutes(30), function() {
            $resp = Http::get(
                'https://api.multiversx.com/accounts/erd158lk5s2m3cpjyg5fwgm0pwnt8ugnc29mj4nafkrvcrhfdfpgvp3swpmnrj/nfts',
                [
                    'from' => 0,
                    'size' => 300,
                ]
            );
            return collect($resp->json())
                ->filter(fn($el) => $el['collection'] === 'BOARD-25bcd6')
                ->shuffle()
                ->values()
                ->toArray();
        });
        $this->nfts = $nfts;

        $this->count = \Cache::remember('board_games_count', now()->addminutes(30), function() {
            $resp = Http::get(
                'https://api.multiversx.com/accounts/erd158lk5s2m3cpjyg5fwgm0pwnt8ugnc29mj4nafkrvcrhfdfpgvp3swpmnrj/collections/BOARD-25bcd6',
            );
            return $resp->json()['count'];
        });
    }
    public function render()
    {
        return view('livewire.board-games');
    }
}
