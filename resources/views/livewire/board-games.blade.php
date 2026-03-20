<div>
    {{-- Full-page loading overlay --}}
    <div wire:loading.delay wire:target="setType, gotoPage, previousPage, nextPage" style="display: none; position: fixed; inset: 0; z-index: 50; background: rgba(0, 0, 0, 0.5); align-items: center; justify-content: center;" id="bg-loading">
        <svg style="animation: spin 1s linear infinite; height: 3rem; width: 3rem; color: #5ec4db;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle style="opacity: 0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path style="opacity: 0.75;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </div>
    <style>
        @keyframes spin { to { transform: rotate(360deg); } }
        div[wire\:loading][id="bg-loading"] { display: flex !important; }
    </style>

    <div class="px-1 md:px-6 lg:px-0 mt-12">
        <div class="px-2 md:px-12 py-3 md:py-6" style="background: var(--color-surface-container-low); border: 1px solid var(--color-outline-variant); border-radius: 0.25rem;">
            <h1 class="font-headline text-3xl font-bold text-on-surface mb-4 text-center lg:text-left">
                My Board Games
            </h1>
            <div class="entry-content pb-2 prose dark:prose-invert prose-lg max-w-none">
                <p>Because I am a true geek, I own quite a few board games.</p>
                <p>One day I thought to myself... what if I would upload all my current board games as NFTs on the
                    MultiversX
                    Blockchain? So that is exactly what I did.</p>
                <p>You can view all my current board games on the <a
                        href="{{config('multiversx.urls.spotlight')}}/collections/{{config('multiversx.counter_contract.nft')}}"
                        target="_blank">xSpotlight website</a>, but I will also
                    list those below, fetched directly from the blockchain, sorted randomly and cached for 30 minutes to
                    avoid
                    repeated calls.</p>
                <p>
                    <strong>Total board games currently owned: {{$owned}}</strong> <br />
                    <strong>NFT Collection: </strong> <a
                        href="{{config('multiversx.urls.explorer')}}/collections/{{config('multiversx.counter_contract.nft')}}"
                        target="_blank">{{config('multiversx.counter_contract.nft')}}</a>
                </p>
                <p class="text-xs p-4" style="background: var(--color-surface-container);">
                    You can read the "behind the scenes" of this page and the NFTs <a
                        href="https://twitter.com/filipacro/status/1625897421069852673"
                        target="_blank">on this Twitter thread</a>.
                </p>

                <p class="text-sm p-4" style="background: var(--color-surface-container);">
                    Each board game tile containing an NFT behind the scene has a "play count" that is stored in the <strong>Smart Contract</strong> with the addresss
                    <a href="{{config('multiversx.urls.explorer')}}/address/{{config('multiversx.counter_contract.address')}}"
                        target="_blank">{{config('multiversx.counter_contract.address')}}</a>. <br />
                    The "play count" is incremented each time the "Play" button is clicked and the value is stored on the blockchain. <br />
                    <br />
                    <strong>Note: </strong> The play count is only taken into account from the
                    <a href="{{config('multiversx.urls.explorer')}}/transactions/{{config('multiversx.counter_contract.genesis')}}" target="_blank">moment I deployed the smart contract on the blockchain</a>.
                    The real play count is higher mostly for every game ;).
                </p>
            </div>
        </div>
    </div>

    <div class="px-2 lg:px-0">
        <div data-web3-state-management></div>
    </div>

    <div data-game-counter-app data-nfts='{{ json_encode($nfts) }}'></div>

    <div class="flex flex-col md:flex-row mt-4 px-6 lg:px-0 gap-2" wire:key="tabs-{{ $type }}">
        <div class="flex-1 text-center p-4 cursor-pointer font-label text-sm uppercase tracking-wider transition-colors" wire:click="setType('owned_ever')" style="{{ $type == 'owned_ever' ? 'background: var(--color-primary); color: #fff;' : 'background: var(--color-surface-container-low); border: 1px solid var(--color-outline-variant);' }} border-radius: 0.25rem;">Lifetime owned</div>
        <div class="flex-1 text-center p-4 cursor-pointer font-label text-sm uppercase tracking-wider transition-colors" wire:click="setType('owned_now')" style="{{ $type == 'owned_now' ? 'background: var(--color-primary); color: #fff;' : 'background: var(--color-surface-container-low); border: 1px solid var(--color-outline-variant);' }} border-radius: 0.25rem;">Currently owned</div>
    </div>

    <div class="mt-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 px-6 lg:px-0">
            @foreach($this->getPaginator() as $nft)
                <div class="flex flex-col" wire:key="{{$nft['identifier']}}" style="background: var(--color-surface-container-low); border: 1px solid var(--color-outline-variant); border-radius: 0.25rem;">
                    <div class="font-headline text-base lg:text-lg font-semibold w-full text-center my-4 px-4">
                        <a href="{{config('multiversx.urls.spotlight')}}/nfts/{{$nft['identifier']}}" target="_blank" class="hover:text-primary transition-colors">
                            @if(isset($nft['metadata']['description']))
                                {{ $nft['metadata']['description'] }}
                            @else
                                {{ $nft['name'] }}
                            @endif
                        </a>
                    </div>
                    <div>
                        <a href="{{config('multiversx.urls.spotlight')}}/nfts/{{$nft['identifier']}}" target="_blank">
                            <img src="{{ $nft['url'] }}" alt="{{ $nft['name'] }}" />
                        </a>
                    </div>
                    <div data-game-counter-mini-app data-nft="{{json_encode($nft)}}"
                         data-owner="{{config("multiversx.counter_contract.owner")}}"></div>
                    <div class="flex flex-col justify-end items-center mt-6 pb-6 flex-1 px-4 gap-3">
                        <a href="{{config('multiversx.urls.spotlight')}}/nfts/{{$nft['identifier']}}" target="_blank"
                           class="inline-flex items-center gap-2 px-4 py-2 font-label text-xs uppercase tracking-wider transition-colors" style="background: var(--color-mx-yellow); border-radius: 0.125rem;">
                            View on xSpotlight
                        </a>
                        <a href="{{config('multiversx.urls.explorer')}}/nfts/{{$nft['identifier']}}" target="_blank"
                           class="inline-flex items-center gap-2 px-4 py-2 font-label text-xs uppercase tracking-wider transition-colors" style="background: var(--color-mx); border-radius: 0.125rem;">
                            View on MultiversX Explorer
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4 pb-4">
            {!! $this->getPaginator() !!}
        </div>
    </div>

</div>
