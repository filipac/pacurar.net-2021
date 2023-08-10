<?php

return [

    /**
     * The MultiversX blockchain network you want to interact with:
     * - Mainnet: 1
     * - Testnet: T
     * - Devnet: D
     */
    'chain_id' => env('MULTIVERSX_CHAIN_ID', '1'),

    'urls' => [

        /**
         * The base url of the MultiversX API (not Proxy).
         * e.g:
         * - Mainnet: https://api.elrond.com
         * - Testnet: https://testnet-api.elrond.com
         * - Devnet: https://devnet-api.elrond.com
         */
        'api' => env('MULTIVERSX_URL_API', 'https://devnet-api.multiversx.com'),

        'explorer' => env('MULTIVERSX_URL_EXPLORER', 'https://devnet-explorer.multiversx.com'),

        'spotlight' => env('MULTIVERSX_URL_SPOTLIGHT', 'https://devnet.xspotlight.com'),
    ],

    'ad_contract' => [
        'owner' => env('MULTIVERSX_AD_CONTRACT_OWNER', 'erd1ex7u5wkseyl2e3ytzh2sjvrrt4azjgxyghuctvf5d2hr2vkdeg8qh5zh50'),
        'address' => env('MULTIVERSX_AD_CONTRACT_ADDRESS', 'erd1qqqqqqqqqqqqqpgqzq3sve9cavz8dq00smeul6ne8j0w6428eg8q9crjxs'),
    ],

    'counter_contract' => [
        'owner' => env('MULTIVERSX_COUNTER_CONTRACT_OWNER', 'erd1ex7u5wkseyl2e3ytzh2sjvrrt4azjgxyghuctvf5d2hr2vkdeg8qh5zh50'),
        'address' => env('MULTIVERSX_COUNTER_CONTRACT_ADDRESS', 'erd1qqqqqqqqqqqqqpgqzlxygk5732740ze88pshy9l309fa4t9weg8qlw0ene'),
        'nft' => env('MULTIVERSX_COUNTER_CONTRACT_NFT', 'BOARD-10b367'),
        'genesis' => env('MULTIVERSX_COUNTER_CONTRACT_GENESIS', 'b3a9a2e73de36c003b6cbc82c235987ec57c29851171203d38d3b4954176ddfb'),
    ],

    'access_contract' => [
        'owner' => env('MULTIVERSX_ACCESS_CONTRACT_OWNER', 'erd1kjkxdhx3xf3rmtqveclztj4zwrn4y2fd7tkeajrtezsurs36fx9qp2jas9'),
        'address' => env('MULTIVERSX_ACCESS_CONTRACT_ADDRESS', 'erd1qqqqqqqqqqqqqpgqjxv0jymkkarde9wjkjegjeh7jndrht5dfx9qrt4yl0'),
    ],

    'jwt_secret' => env('MULTIVERSX_JWT_SECRET', 'IIfFDszOvasz9Eb/AaOX1qpC2jImDxTxuDj2lwKxPyE='),
];
