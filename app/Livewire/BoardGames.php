<?php

namespace App\Livewire;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Http;
use Livewire\Component;
use Livewire\WithPagination;

enum FilterType
{
    case owned_now;
    case owned_ever;
}

class BoardGames extends Component
{
    use WithPagination {
        WithPagination::gotoPage as parentGotoPage;
    }

    public $nfts = [];
    public $count = 0;

    public $perPage = 1;

    public $type = FilterType::owned_ever->name;

    public int $owned = 0;

    protected $queryString = ['type' => ['except' => 'owned_ever']];

    private LengthAwarePaginator $paginator;

    public function getNfts($type)
    {
        $page = $this->getPage();
        $from = ($page - 1) * $this->perPage;
        $to   = $page * $this->perPage;

        $col = config('multiversx.counter_contract.nft');


        $respAll = Http::get(
            config('multiversx.urls.api') . '/accounts/' . config('multiversx.counter_contract.owner') . '/nfts/count',
            [
                'collections' => $col,
            ]
        );
        $owned   = (int)$respAll->body();

        $this->owned = $owned;

        if ($type === FilterType::owned_now->name) {
            $resp = Http::get(
                config('multiversx.urls.api') . '/accounts/' . config('multiversx.counter_contract.owner') . '/nfts',
                [
                    'from' => $from,
                    'size' => $this->perPage,
                ]
            );
            return [
                'items' => collect($resp->json())
                    ->filter(fn($el) => $el['collection'] === $col)
                    ->values(),
                'total' => $owned,
            ];
        } else {
            $resp    = Http::get(
                config('multiversx.urls.api') . '/collections/' . $col . '/nfts',
                [
                    'from'      => $from,
                    'size'      => $this->perPage,
                    'withOwner' => 'true',
                ]
            );
            $respAll = Http::get(
                config('multiversx.urls.api') . '/collections/' . $col . '/nfts/count',
                [
                    'identifiers' => $col,
                ]
            );
            return [
                'items' => collect($resp->json())->values(),
                'total' => (int)$respAll->body(),
            ];
        }
    }

    public function getPaginator(): LengthAwarePaginator
    {
        return $this->paginator;
    }

    public function mount()
    {
        $this->_set_data();
    }

    private function _set_data()
    {
        $resp = $this->getNfts($this->type);
        $nfts = $resp['items']
            ->shuffle()
            ->toArray();

        $this->nfts = $nfts;

        $this->paginator = new LengthAwarePaginator($nfts, $resp['total'], $this->perPage, $this->getPage());

        $this->count = count($nfts);
    }

    public function gotoPage($page, $pageName = 'page')
    {
        $this->parentGotoPage($page, $pageName);

        $this->mount();
    }

    public function render()
    {
        $this->dispatch('gamesLoaded');
        return view('livewire.board-games');
    }

    public function setType($type)
    {
        $this->type = $type;
        $this->_set_data();
    }
}
