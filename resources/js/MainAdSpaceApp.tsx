import React, {Suspense, useCallback, useEffect, useMemo, useState} from 'react';
import {useGetAccountInfo, useGetPendingTransactions, useGetSuccessfulTransactions} from '@multiversx/sdk-dapp/hooks';
import {
    ExtensionLoginButton,
    LedgerLoginButton,
    WalletConnectLoginButton,
    WebWalletLoginButton
} from "@multiversx/sdk-dapp/UI";
import {removeSignedTransaction, sendTransactions} from "@multiversx/sdk-dapp/services";
import {useRecoilState} from "recoil/es/index.mjs";
import {sessionAtom, tempAdAtom} from "./state/dapp";
import {GqlClient} from "./graphql_client";
import {gql} from "graphql-request";
import {decodeSpaceInfo, SpaceInfo} from "./utils/decodeSpaceInfo";
import classnames from 'classnames';
import {logout} from "@multiversx/sdk-dapp/utils";
import {Dialog, Transition} from '@headlessui/react'

const BuySpace = React.lazy(() => import('./components/BuySpace'));
const SpaceEdit = React.lazy(() => import('./components/SpaceEdit'));
// import SpaceEdit from './components/SpaceEdit';
// import {BuySpace} from "./components/BuySpace";
import {contractAddressAd, contractOwner} from "./config";

import formatDate from 'date-fns/format'
import {ArgSerializer, StringValue, Transaction, TransactionPayload} from "@multiversx/sdk-core/out";
import {useLoginButtons} from "./components/LoginButtons";

type  Props = {
    name: string,
    language: string,
    format: 'dark' | 'light',
    html: string,
    sidebar: boolean,
    info: SpaceInfo,
    session: string
}
const MainAdSpaceApp: React.FC<Props> = ({name = '', language, html, format, sidebar, info, session}) => {
    const accountInfo = useGetAccountInfo()
    const [sessionId, setSessionId] = useRecoilState(sessionAtom)
    const [spaceInfo, setSpaceInfo] = useState<SpaceInfo | null>(info)

    const [buyOpen, setBuyOpen] = useState(false)
    const [editOpen, setEditOpen] = useState(false)

    const [initialLoad, setInitialLoad] = useState(true)

    const [tempAd] = useRecoilState<{
        [key: string]: string
    }>(tempAdAtom);
    const tempValue = tempAd?.[name] || '';

    const [rawHtml, setRawHtml] = useState<string>('')

    const [actionAfterLogin, setActionAfterLogin] = useState<string | null>(null)

    useEffect(() => {
        if (html) {
            setRawHtml(html)
        } else {
            setRawHtml('')
        }
    }, [html])

    const sampleTransaction = async () => {
        const transaction = async () => {
            let x = await sendTransactions({
                    transactions: [
                        {
                            value: '0',
                            data: `signComment@1`,
                            receiver: accountInfo.address
                        },
                    ],
                    transactionsDisplayInfo: {
                        processingMessage: 'Asteptam...',
                        successMessage: 'Ai semnat ca primarul',
                        transactionDuration: 1000,
                    },
                    signWithoutSending: true
                }
            )
        }
        console.log(await transaction())
    }

    const withdrawTransaction = async () => {
        const transaction = async () => {
            let x = await sendTransactions({
                    transactions: [
                        {
                            value: '0',
                            data: `withdraw`,
                            receiver: contractAddressAd,
                            gasLimit: '40000000'
                        },
                    ],
                    transactionsDisplayInfo: {
                        processingMessage: language == 'ro' ? 'Asteptam...' : 'Waiting...',
                        successMessage: language == 'ro' ? 'Au intrat banutii' : 'Money is in your wallet',
                        transactionDuration: 10000,
                    },
                }
            )
        }
        console.log(await transaction())
    }

    const {successfulTransactionsArray} = useGetSuccessfulTransactions()
    const {pendingTransactionsArray} = useGetPendingTransactions()

    const selectResetTransaction = (tx) => {
        if (!tx[1].transactions) {
            return false
        }
        const transaction: Transaction = Transaction.fromPlainObject(tx[1].transactions[0])
        if (transaction.getReceiver().bech32() === contractAddressAd) {
            let data = TransactionPayload.fromEncoded(transaction.getData().encoded())
            let args = data.getEncodedArguments();
            if (args.length === 2) {
                if (args[0] === 'resetSpace' && args[1] === (new ArgSerializer()).valuesToString([new StringValue(spaceInfo.name)]).argumentsString) {
                    return true
                }
            }
        }
        return false
    }

    const selectWithdrawTransaction = (tx) => {
        if (!tx[1].transactions) {
            return false
        }
        const transaction: Transaction = Transaction.fromPlainObject(tx[1].transactions[0])
        if (transaction.getReceiver().bech32() === contractAddressAd) {
            let data = TransactionPayload.fromEncoded(transaction.getData().encoded())
            let args = data.getEncodedArguments();
            if (args.length === 1) {
                if (args[0] === 'withdraw') {
                    return true
                }
            }
        }
        return false
    }


    const hasSuccessfulResetTransaction = useMemo(() => {
        return successfulTransactionsArray.some(selectResetTransaction)
    }, [successfulTransactionsArray])

    const hasPendingWithdrawTransaction = useMemo(() => {
        return pendingTransactionsArray.some(selectWithdrawTransaction)
    }, [pendingTransactionsArray])

    useEffect(() => {
        if (hasSuccessfulResetTransaction) {
            // const sucessfulTransactions = successfulTransactionsArray.filter(selectResetTransaction)
            // for (const tx of sucessfulTransactions) {
            //     removeSignedTransaction(tx[0])
            // }
            refresh(spaceInfo.name);
        }
    }, [hasSuccessfulResetTransaction])

    const resetTransaction = async (space: string) => {
        if (!space || space.length === 0) {
            return
        }
        const transaction = async () => {
            const serializer = new ArgSerializer();
            let data = 'resetSpace';

            data += '@' + serializer.valuesToString([new StringValue(space)]).argumentsString;

            let x = await sendTransactions({
                    transactions: [
                        {
                            value: '0',
                            data,
                            receiver: contractAddressAd,
                            gasLimit: '40000000'
                        },
                    ],
                    transactionsDisplayInfo: {
                        processingMessage: language == 'ro' ? 'Asteptam...' : 'Waiting...',
                        successMessage: language == 'ro' ? 'Spatiul a fost resetat' : 'Space was reset',
                        transactionDuration: 10000,
                    },
                }
            )
        }
        console.log(await transaction())
    }

    const refresh = async (name: string) => {
        const resp = await GqlClient.request(gql`
            query adSpaceInfo($spaceName: String!, $initial: Boolean) {
                adSpaceInfo(spaceName: $spaceName, initial: $initial) {
                    name
                    is_new
                    owner
                    paid_amount
                    paid_until
                }
            }
        `, {
            spaceName: name,
            initial: initialLoad
        })
        const info = resp.adSpaceInfo
        // info.name = name
        setSpaceInfo(info)
        process.env.NODE_ENV == 'development' && console.log(info)
    }

    useEffect(() => {
        // get ad info for this ad space
        (async () => {
            if (!spaceInfo?.name) {
                await refresh(name)
                setInitialLoad(true)
            }
        })()
    }, [name])

    const refreshSpace = useCallback(() => {
        refresh(name)
    }, [name])

    /*
    <h1 onClick={e => {
                e.preventDefault()
            }}>React App!</h1>
            AD {name}: <pre>
                {JSON.stringify(spaceInfo, null, 2)}
        </pre>
     */

    const ownerActions = [
        accountInfo?.address == contractOwner && <div style={{flexBasis: '100%', height: 0}}></div>,
        accountInfo?.address == contractOwner && (
            <button className={'p-2 bg-secondary shadow-box hover:shadow-boxhvr text-xs text-black'}
                    onClick={e => {
                        e.preventDefault()
                        withdrawTransaction()
                    }}>
                {!hasPendingWithdrawTransaction && <>
                    {language == 'en' && <>Withdraw</>}
                    {language == 'ro' && <>Retrage banii</>}
                </>}
                {hasPendingWithdrawTransaction && <>
                    ...
                </>}
            </button>),
        accountInfo.address && accountInfo.address == contractOwner && !spaceInfo?.is_new && (
            <button className={'p-2 bg-red-500 text-white shadow-box hover:shadow-boxhvr text-xs text-black'}
                    onClick={e => {
                        e.preventDefault()
                        resetTransaction(spaceInfo?.name || '')
                    }}>
                {language == 'en' && <>Reset space</>}
                {language == 'ro' && <>Reseteaza spatiul</>}
            </button>
        ),
    ]

    const actionsLoggedIn = [

        accountInfo.address && accountInfo.address != spaceInfo?.owner && !spaceInfo?.is_new && (
            <button className={'p-2 bg-secondary shadow-box hover:shadow-boxhvr text-xs text-black'}
                    onClick={e => {
                        e.preventDefault()
                        setBuyOpen(true)
                    }}>
                {language == 'en' && <>Replace with your advertisement</>}
                {language == 'ro' && <>Inlocuieste cu reclama ta</>}
            </button>
        ),
        accountInfo.address && accountInfo.address == spaceInfo?.owner && !spaceInfo?.is_new && !editOpen && (
            <button className={'p-2 bg-secondary shadow-box hover:shadow-boxhvr text-xs text-black'}
                    onClick={e => {
                        e.preventDefault()
                        setEditOpen(true)
                    }}>
                {language == 'en' && <>Edit space</>}
                {language == 'ro' && <>Editeaza spatiul</>}
            </button>
        ),
        accountInfo.address && accountInfo.address == spaceInfo?.owner && !spaceInfo?.is_new && editOpen && (
            <button className={'p-2 bg-secondary shadow-box hover:shadow-boxhvr text-xs text-black'}
                    onClick={e => {
                        e.preventDefault()
                        setEditOpen(false)
                    }}>
                {language == 'en' && <>Cancel edit</>}
                {language == 'ro' && <>Anuleaza editarea</>}
            </button>
        ),
        <button className={'p-2 bg-secondary shadow-box hover:shadow-boxhvr text-xs text-black'}
                onClick={e => {
                    e.preventDefault()
                    // logout from wallet
                    logout()
                }}>
            {language == 'en' && <>Logout</>}
            {language == 'ro' && <>Logout</>}
        </button>,

        ...ownerActions
    ].filter(Boolean)

    const {
        showLogin,
        setShowLogin,
        loginActions
    } = useLoginButtons({sessionId})

    useEffect(() => {
        if (accountInfo?.address && showLogin) {
            setActionAfterLogin(null)
        }
    }, [showLogin, accountInfo])

    useEffect(() => {
        if (accountInfo?.address && actionAfterLogin && actionAfterLogin == 'outbid') {
            if (spaceInfo.owner && spaceInfo.owner != accountInfo.address) {
                setBuyOpen(true)
            }
            setActionAfterLogin(null)
        }
    }, [actionAfterLogin, accountInfo?.address, spaceInfo])

    const actionsLoggedOut = [
        !spaceInfo?.is_new && (
            <button
                className={'dapp-core-component__main__btn dapp-core-component__main__btn-primary dapp-core-component__main__m-1 text-xs p-2 mx-0 bg-yellow-500 shadow-box hover:shadow-boxhvr text-black'}
                onClick={e => {
                    e.preventDefault()
                    if (accountInfo?.address) {
                        setBuyOpen(true)
                    } else {
                        setShowLogin(!showLogin)
                        setActionAfterLogin(showLogin ? null : 'outbid')
                    }
                }}>
                {showLogin && <>
                    {language == 'en' && <>Cancel outbid</>}
                    {language == 'ro' && <>Renunta</>}
                </>}
                {!showLogin && <>{language == 'en' && <>Replace with your advertisement</>}
                    {language == 'ro' && <>Inlocuieste cu reclama ta</>}</>}
            </button>
        ),
        ...loginActions
    ].filter(Boolean)

    // if something is not working on multiversx and we have HTML
    if (!spaceInfo?.name && html && html.length > 1) {
        return <>
            <div className={classnames(
                'bg-primary py-4 px-4',
                {}
            )}>
                {spaceInfo && !spaceInfo?.is_new && <div className="space__content" dangerouslySetInnerHTML={{
                    __html: !editOpen ? rawHtml : tempValue
                }}/>}
            </div>
        </>
    }

    if (spaceInfo?.is_new) {
        return (
            <div className={classnames('py-4 px-4', {
                'bg-primary': format == 'light',
                'bg-splash text-white': format == 'dark',
            })}>
                <div className={'flex flex-col items-center justify-center'}>
                    {/*{language == 'ro' && <>*/}
                    {(!accountInfo.address || (accountInfo.address && (spaceInfo.owner != accountInfo.address))) && <>
                        {language == 'ro' && <>
                            <div className={classnames("font-bold text-center", {
                                'text-2xl': !sidebar,
                                'mb-4': sidebar
                            })}>Acest spatiu publicitar
                                poate fi tau pentru 10
                                de
                                zile.
                            </div>
                            <p className={classnames('text-center', {
                                'mb-4': sidebar
                            })}>
                                Costa doar {spaceInfo?.paid_amount} USDC/USDT / 10 zile.<br />Il inchiriezi instant cu
                                ajutorul
                                blockchain-ului MultiversX si il poti personaliza cum vrei tu.
                            </p></>}
                        {language == 'en' && <>
                            <div className={classnames("font-bold", {
                                'text-2xl': !sidebar,
                                'mb-4': sidebar
                            })}>This ad space can be yours
                                for 10 days.
                            </div>
                            <p className={classnames('text-center', {
                                'mb-4': sidebar
                            })}>
                                It costs only {spaceInfo?.paid_amount} USDC/USDT per 10 days.<br /> You rent it instantly
                                using
                                the MultiversX blockchain and you can customize it as you want.
                            </p></>}
                    </>
                    }

                    {!accountInfo.address && <>

                        <div className={
                            classnames('flex mt-2 flex-col items-center md:flex-row', {
                                'flex flex-col md:flex-col gap-2': sidebar,
                            })
                        }>
                            <WalletConnectLoginButton isWalletConnectV2 token={sessionId}
                                                      logoutRoute={window.location.href}
                                                      className={'text-xs p-2 mx-0 bg-secondary shadow-box hover:shadow-boxhvr text-black border-secondary'}
                                                      showScamPhishingAlert/>
                            <LedgerLoginButton token={sessionId} showScamPhishingAlert
                                               className={'text-xs p-2 ml-4 bg-secondary shadow-box hover:shadow-boxhvr text-black  border-secondary'}/>
                            <ExtensionLoginButton
                                className={'text-xs p-2 mx-0 bg-secondary shadow-box hover:shadow-boxhvr text-black border-secondary'}
                                token={sessionId} loginButtonText={'MultiversX DeFi Wallet'}/>
                            <WebWalletLoginButton token={sessionId} callbackRoute={window.location.pathname}
                                                  className={'text-xs p-2 mx-0 ml-4 bg-secondary shadow-box hover:shadow-boxhvr text-black border-secondary'}
                                                  onLoginRedirect={{
                                                      callbackRoute: window.location.pathname,
                                                  }} nativeAuth={true}/>
                        </div>
                    </>}

                    {accountInfo.address && (spaceInfo.owner != accountInfo.address) && <>
                        <div className={classnames('mt-2', {
                            'mt-4': sidebar
                        })}>
                            <button className={'p-2 bg-secondary text-black shadow-box hover:shadow-boxhvr text-xs'}
                                    onClick={e => {
                                        e.preventDefault()
                                        setBuyOpen(true)
                                    }}>
                                {language == 'ro' && <>Inchiriaza acest spatiu</>}
                                {language == 'en' && <>Rent this space</>}
                            </button>
                        </div>
                    </>}

                    {accountInfo.address && <>
                        <div className={classnames('mt-3 flex flex-wrap self-end justify-end gap-2 ml-auto', {
                            'mt-4 transform scale-75 origin-right': sidebar,
                        })} style={{width: 'fit-content'}}>
                            {actionsLoggedIn}
                        </div>
                    </>}


                    <React.Suspense fallback={<div/>}>
                        <BuySpace open={buyOpen} setOpen={setBuyOpen} spaceInfo={spaceInfo} language={language}
                                  refreshSpace={refreshSpace}/>
                    </React.Suspense>
                    {/*</>}*/}
                </div>
            </div>
        )
    }

    // parse with date-fns from unix timestamp to d.m.y H:m
    const untilRo = spaceInfo?.paid_until ? formatDate(new Date(spaceInfo?.paid_until * 1000), 'dd.MM.yyyy HH:mm') : ''
    // parse with date-fns from unix timestamp to mm-dd-yyyy H:m am/pm
    const untilEn = spaceInfo?.paid_until ? formatDate(new Date(spaceInfo?.paid_until * 1000), 'MM-dd-yyyy HH:mm a') : ''

    return (
        <>
            <div className={classnames(
                'bg-primary py-4 px-4',
                {}
            )}>
                {accountInfo?.address && (spaceInfo?.owner == accountInfo.address) && (
                    <>
                        <div
                            className={classnames("font-bold text-xs bg-secondary py-2 px-4 rounded-2xl max-w border border-black mb-2")}
                            style={{
                                maxWidth: 'fit-content'
                            }}>
                            {language == 'ro' && <>Deti acest spatiu pana in {untilRo}</>}
                            {language == 'en' && <>You own this space until {untilEn}</>}
                        </div>
                    </>
                )}
                {spaceInfo && !spaceInfo?.is_new && <div className="space__content" dangerouslySetInnerHTML={{
                    __html: !editOpen ? rawHtml : tempValue
                }}/>}

                {editOpen && <>
                    <Suspense fallback={<div>Loading</div>}>
                        <SpaceEdit spaceInfo={spaceInfo} close={() => {
                            setEditOpen(false)
                        }} sidebar={sidebar} language={language} refreshSpace={(html) => {
                            refreshSpace()
                            setRawHtml(html)
                        }}/>
                    </Suspense>
                </>}

                {spaceInfo.name && <>
                    {accountInfo.address &&
                        <div className={classnames('mt-3 flex flex-wrap self-end justify-end gap-2 ml-auto', {
                            'mt-4 transform scale-75 origin-right': sidebar,
                        })} style={{width: 'fit-content'}}>
                            {actionsLoggedIn}
                        </div>}
                    {!accountInfo?.address && <div className={classnames('mt-3 self-end flex gap-2 justify-end', {
                        'flex-col mt-4': sidebar,
                    })}>
                        {actionsLoggedOut}
                    </div>}
                </>}
            </div>
            <React.Suspense fallback={<div/>}>
                <BuySpace open={buyOpen} setOpen={setBuyOpen} spaceInfo={spaceInfo} language={language}
                          refreshSpace={refreshSpace}/>
            </React.Suspense>
        </>
    );
}

export default MainAdSpaceApp;
