export type Network = 'testnet' | 'mainnet' | 'devnet'

export const activeNetwork: Network = process.env.NODE_ENV == 'development' ? 'devnet' : 'mainnet'
export const WalletConnectKey = '6849db0c57e4bf778ed8bdd2d721ee4f'

// export const contractAddress = 'erd1qqqqqqqqqqqqqpgqq72p4p73sx6wkurl3epefzt4dkvwh7frvp3s73l9k6'
export const contractAddress = process.env.NODE_ENV == 'development' ?
    'erd1qqqqqqqqqqqqqpgqzq3sve9cavz8dq00smeul6ne8j0w6428eg8q9crjxs'
    : 'erd1qqqqqqqqqqqqqpgqq72p4p73sx6wkurl3epefzt4dkvwh7frvp3s73l9k6'
// export const contractOwner = 'erd158lk5s2m3cpjyg5fwgm0pwnt8ugnc29mj4nafkrvcrhfdfpgvp3swpmnrj'
export const contractOwner = process.env.NODE_ENV == 'development' ?
    'erd1ex7u5wkseyl2e3ytzh2sjvrrt4azjgxyghuctvf5d2hr2vkdeg8qh5zh50'
    : 'erd158lk5s2m3cpjyg5fwgm0pwnt8ugnc29mj4nafkrvcrhfdfpgvp3swpmnrj'
