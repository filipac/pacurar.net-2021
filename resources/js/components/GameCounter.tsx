import React, {useCallback, useEffect} from 'react'
import {GqlClient} from "../graphql_client";
import {gql} from "graphql-request";
import {useGetAccountInfo} from "@multiversx/sdk-dapp/hooks";
import {useRecoilState} from "recoil/es/index.mjs";
import {timesPlayedAtom} from "../state/dapp";
import {createPortal} from "react-dom";
import {contractAddressAd, contractAddressCounter, contractOwner} from "../config";
import {Address, AddressValue, ArgSerializer, BigUIntValue, StringValue} from "@multiversx/sdk-core/out";
import {refreshAccount} from "@multiversx/sdk-dapp/utils";
import {sendTransactions} from "@multiversx/sdk-dapp/services";
import {U64Value} from "@multiversx/sdk-core/out/smartcontracts/typesystem/numerical";

type Props = {
    nfts: any[]
    ikey: number
}

export const GameCounter: React.FC<Props> = (props) => {

    const accountInfo = useGetAccountInfo()

    const [state, setState] = useRecoilState(timesPlayedAtom)

    const divs = Array.from(document.querySelectorAll('[data-game-counter-mini-app]'))

    const reload = useCallback(async () => {
        let resp = await GqlClient.batchRequests(props.nfts.map(nft => {
            return {
                document: gql`
                    query timesPlayedNft($identifier: String!, $nonce: Int!, $guestAddress: String) {
                        timesPlayedNft(identifier: $identifier, nonce: $nonce, guestAddress: $guestAddress) {
                            nonce
                            played_by_filip
                            played_by_you
                        }
                    }
                `,
                variables: {
                    identifier: nft.identifier,
                    nonce: nft.nonce,
                    guestAddress: accountInfo?.address || null
                }
            }
        }))
        let newState = {}
        for (let r of resp) {
            newState[r.data.timesPlayedNft.nonce] = r.data.timesPlayedNft
        }
        setState(newState)
    }, [props.nfts, accountInfo.address])

    useEffect(() => {
        const listener = () => {
            reload()
        }
        window.addEventListener('clearPlayCount', listener)
        return () => {
            window.removeEventListener('clearPlayCount', listener)
        }
    }, [])

    useEffect(() => {
        if (props.nfts && props.nfts.length > 0) {
            reload()
        }
    }, [props.nfts, accountInfo.address])

    return (
        <>
            {divs.map(div => {
                //  @ts-ignore
                const nft = JSON.parse(div.dataset.nft)
                // @ts-ignore
                return createPortal(<NftMiniApp nft={nft} key={`mini-app-${props.ikey}-${nft.identifier}`}
                                                owner={div.dataset.owner} />, div)
            })}
        </>
    )
}

type NftMiniAppProps = {
    nft: any;
    owner: string;
}

export const NftMiniApp: React.FC<NftMiniAppProps> = (props) => {
    const [state, setState] = useRecoilState(timesPlayedAtom)
    const playData = state[props.nft.nonce] || {played_by_filip: 0, played_by_you: 0}

    const account = useGetAccountInfo()

    const increment = useCallback(async () => {
        const serializer = new ArgSerializer();
        let data = 'ESDTNFTTransfer';

        data += '@' + serializer.valuesToString([new StringValue(props.nft.collection)]).argumentsString;

        data +=
            '@' +
            serializer.valuesToString([new U64Value(props.nft.nonce)]).argumentsString;

        data +=
            '@' +
            serializer.valuesToString([new U64Value(1)]).argumentsString;

        data += '@' + serializer.valuesToString([new AddressValue(new Address(contractAddressCounter))]).argumentsString;

        data += '@' + serializer.valuesToString([new StringValue('markPlayed')]).argumentsString;

        const rentTransaction = {
            value: '0',
            data: data,
            receiver: account.address,
            gasLimit: '60000000'
        };
        await refreshAccount();

        const {sessionId /*, error*/} = await sendTransactions({
            transactions: rentTransaction,
            transactionsDisplayInfo: {
                processingMessage: 'Processing increment game play counter transaction...',
                errorMessage: 'Error incrementing game play counter',
                successMessage: 'Successfully incremented game play counter'
            },
            redirectAfterSign: false
        });
    }, [props.nft])

    return (
        <div className={'flex flex-col items-center'}>
            <div className={'font-bold text-sm mt-2 mb-2'}>Number of times played by
                Filip: {playData?.played_by_filip}</div>
            {account.address && <div className={'flex flex-col items-center'}>
                {account.address != props.owner && <div className={'font-bold text-sm mb-2'}>Number of times played by
                    you: {playData?.played_by_you}</div>}
                {account.address == props.nft.owner && <div>
                    <button
                        className={'inline-block group bg-mxYellow hover:shadow-boxhvr p-1 hover:pb-3 shadow-box border-2 border-black text-xs'}
                        onClick={increment}>
                        <span className={'relative show-border smaller'}>++ Increment play count</span>
                    </button>
                </div>}
            </div>}
        </div>
    )
}
