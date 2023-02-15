<div class="bg-white px-12 py-6">
    <div class="sigmar text-3xl w-full text-center text-shadow my-6 md:my-2 hidden lg:block">
        My Board Games
    </div>
    <div class="entry-content pb-2 prose prose-lg max-w-none">
        <p>Because I am a true geek, I own quite a few board games.</p>
        <p>One day I thought to myself... what if I would upload all my current board games as NFTs on the MultiversX
            Blockchain? So that is exactly what I did.</p>
        <p>You can view all my current board games on the <a href="https://xspotlight.com/collections/BOARD-25bcd6"
                                                             target="_blank">xSpotlight website</a>, but I will also
            list those below, fetched directly from the blockchain, sorted randomly and cached for 30 minutes to avoid
            repeated calls.</p>
    </div>
</div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
    @foreach($nfts as $nft)
        <div class="bg-white">
            <div class="sigmar text-xl w-full text-center text-shadow my-6 md:my-2 hidden lg:block">
                <a href="https://xspotlight.com/nfts/{{$nft['identifier']}}" target="_blank">
                    @if(isset($nft['metadata']['description']))
                        {{ $nft['metadata']['description'] }}
                    @else
                        {{ $nft['name'] }}
                    @endif
                </a>
            </div>
            <div>
                <a href="https://xspotlight.com/nfts/{{$nft['identifier']}}" target="_blank">
                    <img src="{{ $nft['url'] }}" alt="{{ $nft['name'] }}"/>
                </a>
            </div>
            <div class="flex flex-col justify-center items-center mt-6 pb-6">
                <div>
                    <a href="https://xspotlight.com/nfts/{{$nft['identifier']}}" target="_blank"
                       class="inline-block group bg-mxYellow hover:shadow-boxhvr p-2 hover:pb-4 shadow-box border-2 border-black">
                        <span class="relative show-border">View on xSpotlight</span>
                    </a>
                </div>
                <div class="mt-4">
                    <a href="https://explorer.multiversx.com/nfts/{{$nft['identifier']}}" target="_blank"
                       class="inline-block group bg-mx hover:shadow-boxhvr p-2 hover:pb-4 shadow-box border-2 border-black">
                        <span class="relative show-border">View on MultiversX Explorer</span>
                    </a>
                </div>
            </div>
        </div>
    @endforeach
</div>
