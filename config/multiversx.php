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

        'explorer' => env('MULTIVERSX_URL_EXPLORER', 'https://devnet-explorer.elrond.com'),
    ],

    'ad_contract' => [
        'owner' => env('MULTIVERSX_AD_CONTRACT_OWNER', 'erd1ex7u5wkseyl2e3ytzh2sjvrrt4azjgxyghuctvf5d2hr2vkdeg8qh5zh50'),
        'address' => env('MULTIVERSX_AD_CONTRACT_ADDRESS', 'erd1qqqqqqqqqqqqqpgqzq3sve9cavz8dq00smeul6ne8j0w6428eg8q9crjxs'),
    ],

    'jwt_secret' => env('MULTIVERSX_JWT_SECRET', 'IIfFDszOvasz9Eb/AaOX1qpC2jImDxTxuDj2lwKxPyE='),
];
