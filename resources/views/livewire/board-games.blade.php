<div>
    <div class="px-6 lg:px-0">
        <div class="bg-white px-12 py-6">
            <div class="sigmar text-3xl w-full text-center text-shadow my-6 lg:my-2 hidden lg:block">
                My Board Games
            </div>
            <div class="entry-content pb-2 prose prose-lg max-w-none">
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
                <p class="text-xs bg-primary p-4">
                    You can read the "behind the scenes" of this page and the NFTs <a
                        href="https://twitter.com/filipacro/status/1625897421069852673"
                        target="_blank">on this Twitter thread</a>.
                </p>

                <p class="text-sm bg-blue-300 p-4">
                    Each board game tile containing an NFT behind the scene has a "play count" that is stored in the <strong>Smart Contract</strong> with the addresss
                    <a href="{{config('multiversx.urls.explorer')}}/address/{{config('multiversx.counter_contract.address')}}"
                        target="_blank">{{config('multiversx.counter_contract.address')}}</a>. <br />
                    The "play count" is incremented each time the "Play" button is clicked and the value is stored on the blockchain. <br />
                    <br />
                    <strong>Note: </strong> The play count is only taken into account from the
                    <a href="{{config('multiversx.urls.explorer')}}/transactions/{{config('multiversx.counter_contract.genesis')}}" target="_blank">moment I deployed the smart contract on the blockchain</a>.
                    The real play count is higher mostly for every game ;).
                </p>

{{--                <x-web3-ad spaceName="page-top" format="dark" />--}}
            </div>
        </div>
    </div>

    <div class="px-6 lg:px-0">
        <div data-web3-state-management></div>
    </div>

    <div data-game-counter-app data-nfts='{{ json_encode($nfts) }}'></div>

    <div class="flex flex-col md:flex-row mt-4 px-6 lg:px-0">
        <div class="flex-1 text-center {{ $type === 'owned_ever' ? 'bg-mxYellow' : 'bg-white' }} p-4 cursor-pointer" wire:click="setType('owned_ever')")>Lifetime owned</div>
        <div class="flex-1 text-center {{ $type === 'owned_now' ? 'bg-mxYellow' : 'bg-white' }} p-4 cursor-pointer" wire:click="setType('owned_now')">Currently owned</div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6 px-6 lg:px-0">
        @foreach($this->getPaginator() as $nft)
            <div class="bg-white flex flex-col" wire:key="{{$nft['identifier']}}">
                <div class="sigmar text-base lg:text-xl w-full text-center text-shadow my-6 lg:my-2 block px-4">
                    <a href="{{config('multiversx.urls.spotlight')}}/nfts/{{$nft['identifier']}}" target="_blank">
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
                <div class="flex flex-col justify-end items-center mt-6 pb-6 flex-1 px-4">
                    <div>
                        <a href="{{config('multiversx.urls.spotlight')}}/nfts/{{$nft['identifier']}}" target="_blank"
                           class="inline-block group bg-mxYellow hover:shadow-boxhvr p-2 hover:pb-4 shadow-box border-2 border-black">
                            <span class="relative show-border">View on xSpotlight</span>
                        </a>
                    </div>
                    <div class="mt-4">
                        <a href="{{config('multiversx.urls.explorer')}}/nfts/{{$nft['identifier']}}" target="_blank"
                           class="inline-block group bg-mx hover:shadow-boxhvr p-2 hover:pb-4 shadow-box border-2 border-black">
                            <span class="relative show-border">View on MultiversX Explorer</span>
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-4 text-white pb-4">
        {!! $this->getPaginator()->render() !!}
    </div>

</div>
