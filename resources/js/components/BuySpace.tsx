import React, {useEffect, useMemo, useState} from 'react';
import {Dialog, Transition} from "@headlessui/react";
import {
    useGetAccountInfo,
    useGetFailedTransactions,
    useGetNetworkConfig,
    useGetPendingTransactions, useGetSuccessfulTransactions
} from "@multiversx/sdk-dapp/hooks";
import axios from "axios";
import {SpaceInfo} from "../utils/decodeSpaceInfo";
import {object, string} from "yup";
import BigNumber from "bignumber.js";
import {denominate, formatAmount, nominate, parseAmount, refreshAccount} from "@multiversx/sdk-dapp/utils";
import {connect, Formik} from "formik";
import classnames from "classnames";
import {
    Address,
    ArgSerializer,
    BigUIntValue, ContractFunction,
    SmartContract,
    StringValue,
    Transaction,
    TransactionPayload
} from "@multiversx/sdk-core/out";
import {contractAddress, contractOwner} from "../config";
import {removeSignedTransaction, sendTransactions} from "@multiversx/sdk-dapp/services";
import {Loader} from "@multiversx/sdk-dapp/UI";
import {
    removeTransactionToast,
    updateSignedTransactions,
    updateSignedTransactionStatus
} from "@multiversx/sdk-dapp/reduxStore/slices";
import {TransactionBatchStatusesEnum, TransactionServerStatusesEnum} from "@multiversx/sdk-dapp/types";
import {useDispatch} from "@multiversx/sdk-dapp/reduxStore/DappProviderContext";
import {GqlClient} from "../graphql_client";
import {gql} from "graphql-request";
import {ApiNetworkProvider} from "@multiversx/sdk-network-providers/out";

type Props = {
    open: boolean,
    setOpen: (truth: boolean) => void,
    spaceInfo: SpaceInfo,
    language: string,
    refreshSpace: () => void
}

// const AcceptedTokens = process.env.NODE_ENV === 'development' ? ["USDT-188935", "USDC-8d4068"] : ["USDT-188935", "USDC-8d4068"]
export const BuySpace: React.FC<Props> = ({
                                              open,
                                              setOpen,
                                              spaceInfo,
                                              language,
                                              refreshSpace,
                                          }) => {

    const {address} = useGetAccountInfo();
    const {network} = useGetNetworkConfig();

    const accountInfo = useGetAccountInfo()
    const dispatch = useDispatch()

    const [accountBalances, setAccountBalances] = useState<{ token: string, balance: BigNumber }[]>([])
    const [AcceptedTokens, setAcceptedTokens] = useState<string[]>([])


    const {pendingTransactionsArray} = useGetPendingTransactions()
    const {failedTransactionsArray} = useGetFailedTransactions()
    const {successfulTransactionsArray} = useGetSuccessfulTransactions()

    const formikRef = React.useRef<any>(null);

    const selectRentTransaction = (tx) => {
        if (!tx[1].transactions) {
            return false
        }
        const transaction: Transaction = Transaction.fromPlainObject(tx[1].transactions[0])
        if (transaction.getReceiver().bech32() === contractAddress) {
            let data = TransactionPayload.fromEncoded(transaction.getData().encoded())
            let args = data.getEncodedArguments();
            if (args.length === 5) {
                if (
                    args[3] === (new ArgSerializer()).valuesToString([new StringValue('buySpace')]).argumentsString
                    && args[4] === (new ArgSerializer()).valuesToString([new StringValue(spaceInfo.name)]).argumentsString
                ) {
                    return true
                }
            } else if (args.length > 0) {
                if (args[0] === 'buySpace' && args[1] === (new ArgSerializer()).valuesToString([new StringValue(spaceInfo.name)]).argumentsString) {
                    return true
                }
            }
        }
        return false
    }

    const hasPendingRentTransaction = useMemo(() => {
        return pendingTransactionsArray.some(selectRentTransaction)
    }, [pendingTransactionsArray])

    const hasFailedRentTransaction = useMemo(() => {
        return failedTransactionsArray.some(selectRentTransaction)
    }, [failedTransactionsArray])

    const hasSuccessfulRentTransaction = useMemo(() => {
        return successfulTransactionsArray.some(selectRentTransaction)
    }, [successfulTransactionsArray])

    const firstFailedRentTransaction = useMemo(() => {
        return failedTransactionsArray.find(selectRentTransaction)
    }, [failedTransactionsArray])

    const schema = useMemo(() => {
        let min = (spaceInfo?.is_new) ? spaceInfo?.paid_amount || 7 : (spaceInfo?.paid_amount == undefined ? 7 : spaceInfo.paid_amount) * 2
        // min = 10;

        if (accountInfo?.address == contractOwner) {
            min = 0
        }

        const msg = language == 'en' ? `Value must be greater than or equal to ${min}.` : `Valoarea trebuie să fie mai mare sau egală cu ${min}.`

        return string()
            .required('Required')
            .test('minimum', msg, (value = '0') =>
                new BigNumber(parseAmount(value, 6)).isGreaterThanOrEqualTo(
                    new BigNumber(parseAmount(`${min}`, 6))
                )
            );
    }, [spaceInfo, accountBalances])

    useEffect(() => {
        if (!refreshSpace) {
            return;
        }
        if (hasSuccessfulRentTransaction) {
            const sucessfulTransactions = successfulTransactionsArray.filter(selectRentTransaction)
            for (const tx of sucessfulTransactions) {
                removeSignedTransaction(tx[0])
            }
            refreshSpace();
            setOpen(false)
        }
    }, [hasSuccessfulRentTransaction, refreshSpace])

    const [loadingAcceptedTokens, setLoadingAcceptedTokens] = useState<boolean>(false)
    const [loadingAccountBalances, setLoadingAccountBalances] = useState<boolean>(false)

    useEffect(() => {
        if (!open) return;
        (async () => {
            try {
                setLoadingAcceptedTokens(true)
                const resp1 = await (new ApiNetworkProvider(network.apiAddress)).queryContract(
                    (new SmartContract({
                        address: Address.fromBech32(contractAddress)
                    })).createQuery({
                        func: new ContractFunction('getAcceptedTokens'),
                    })
                )

                if (resp1.returnCode == 'ok') {
                    let data = resp1.returnData.map(token => {
                        return atob(token)
                    })
                    setAcceptedTokens(data)
                }
                // const resp = await GqlClient.request(gql`{
                //     acceptedTokens
                // }`)
                // setAcceptedTokens(resp.acceptedTokens)
            } catch (e) {

            }
            setLoadingAcceptedTokens(false)
        })();
    }, [open])

    useEffect(() => {
        if (!open) return;

        (async () => {
            try {
                setLoadingAccountBalances(true)
                const {data} = await axios.get(
                    `${network.apiAddress}/accounts/${address}/tokens`
                );
                if (!data || data.length === 0) {
                    setAccountBalances([])

                    setLoadingAccountBalances(false)
                    return;
                }
                const balances = AcceptedTokens.map(token => {
                    return {token, balance: new BigNumber(0)}
                })
                for (const token of data) {
                    if (AcceptedTokens.includes(token.identifier)) {
                        const index = AcceptedTokens.indexOf(token.identifier)
                        balances[index].balance = new BigNumber(token.balance)
                    }
                }
                setAccountBalances(balances)
            } catch (e) {
            }

            setLoadingAccountBalances(false)
        })();
    }, [address, AcceptedTokens])

    const sendRentTransaction = async (token: string, amount: string) => {

        if (accountInfo?.address !== contractOwner) {
            if (accountBalances.length === 0) return;
            const index = AcceptedTokens.indexOf(token)
            if (index === -1) return;
            const balance = accountBalances[index].balance
            if (balance.isLessThan(amount)) return;
        }

        const serializer = new ArgSerializer();
        let data = 'ESDTTransfer';

        data += '@' + serializer.valuesToString([new StringValue(token)]).argumentsString;

        data +=
            '@' +
            serializer.valuesToString([new BigUIntValue(amount)]).argumentsString;

        data +=
            '@' +
            serializer.valuesToString([new StringValue('buySpace')]).argumentsString;

        data += '@' + serializer.valuesToString([new StringValue(spaceInfo.name)]).argumentsString;

        if (accountInfo?.address == contractOwner) {
            data = 'buySpace@' + serializer.valuesToString([new StringValue(spaceInfo.name)]).argumentsString;
        }

        const rentTransaction = {
            value: '0',
            data: data,
            receiver: contractAddress,
            gasLimit: '60000000'
        };
        await refreshAccount();

        const {sessionId /*, error*/} = await sendTransactions({
            transactions: rentTransaction,
            transactionsDisplayInfo: {
                processingMessage: language == 'en' ? 'Processing space rental transaction' : 'Procesare tranzacție închiriere spațiu',
                errorMessage: language == 'en' ? 'Space rental transaction failed' : 'Tranzacție închiriere spațiu eșuată',
                successMessage: language == 'en' ? 'You now own this space for 30 days' : 'Acum deții acest spațiu pentru 30 de zile',
            },
            redirectAfterSign: false
        });
    }

    const parseScError = (error) => {
        // replace _[0-9]+_ with denominated value
        const regex = /_[0-9]+_/g;
        const matches = error.match(regex);
        if (matches) {
            for (const match of matches) {
                const value = match.replace(/_/g, '');
                const d = denominated(value, {
                    denomination: 6,
                });
                error = error.replace(match, d);
            }
        }

        error = error.replace('(/10^6) ', '')

        return error
    }

    const padding = 'rounded-lg px-4 pt-5 pb-4 sm:p-6 ';

    return (
        <Transition.Root show={open} as={'div'}>
            <Dialog
                as="div"
                className="fixed z-10 inset-0 overflow-y-auto"
                open={open}
                onClose={(truth) => {
                    setOpen(false)
                }}
            >
                <div
                    className="flex items-start justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
                    <Transition.Child
                        as={React.Fragment}
                        enter="ease-out duration-300"
                        enterFrom="opacity-0"
                        enterTo="opacity-100"
                        leave="ease-in duration-200"
                        leaveFrom="opacity-100"
                        leaveTo="opacity-0"
                    >
                        <Dialog.Overlay
                            className="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"/>
                    </Transition.Child>

                    {/* This element is to trick the browser into centering the modal contents. */}
                    <span className="hidden sm:inline-block sm:align-middle sm:h-screen"
                          aria-hidden="true">
            &#8203;
          </span>
                    <Transition.Child
                        as={React.Fragment}
                        enter="ease-out duration-300"
                        enterFrom="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        enterTo="opacity-100 translate-y-0 sm:scale-100"
                        leave="ease-in duration-200"
                        leaveFrom="opacity-100 translate-y-0 sm:scale-100"
                        leaveTo="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    >
                        <div
                            className="inline-block align-bottom bg-white text-left shadow-xl transform transition-all sm:mt-32 sm:align-middle sm:max-w-sm sm:w-full w-full md:max-w-md lg:max-w-lg xl:max-w-[55rem] relative">
                            {hasPendingRentTransaction && <div
                                className={`w-full h-full ${padding} bg-white bg-opacity-50 flex items-center justify-center`}>
                                <div className={'flex flex-col items-center justify-center'}>
                                    <div className={'text-center'}>
                                        <Loader noText className={'p-0'}/>
                                        <p className={'text-lg font-bold mb-0'}>
                                            {language == 'en' ? 'Pending rent transaction' : 'Tranzacție închiriere în curs'}
                                        </p>
                                        <p className={'text-sm text-gray-500'}>
                                            {language == 'en' ? 'Please wait for the transaction to complete' : 'Așteaptă finalizarea tranzacției'}
                                        </p>
                                    </div>
                                </div>
                            </div>}
                            {(loadingAcceptedTokens || loadingAccountBalances) && <div
                                className={`w-full h-full ${padding} bg-white bg-opacity-50 flex items-center justify-center`}>
                                <div className={'flex flex-col items-center justify-center'}>
                                    <div className={'text-center'}>
                                        <Loader noText className={'p-0'}/>
                                        <p className={'text-lg font-bold mb-0'}>
                                            {language == 'en' ? 'Loading required data from the blockchain' : 'Încărcare date necesare de pe blockchain'}
                                        </p>
                                        <p className={'text-sm text-gray-500'}>
                                            {language == 'en' ? 'Checking your account balance and accepted tokens' : 'Verificare sold cont și tokenuri acceptate'}
                                        </p>
                                    </div>
                                </div>
                            </div>}
                            {!(loadingAcceptedTokens || loadingAccountBalances) && !hasPendingRentTransaction && <>
                                <div
                                    className={'bg-splash px-4 py-3 text-white font-bold flex items-center justify-between'}>
                                    <div>{language == 'en' ? 'Rent space for 10 days' : 'Închiriază spațiu pentru 10 zile'}</div>
                                    <div>
                                        <button
                                            type="button"
                                            className="text-2xl"
                                            onClick={() => {
                                                setOpen(false)
                                            }}
                                        >&times;</button>
                                    </div>
                                </div>
                                <div className={`${padding}`}>
                                    <Formik
                                        validationSchema={object().shape({
                                            token: accountInfo?.address === contractOwner ? string().nullable() : string().required('Required'),
                                            amount: schema
                                        })}
                                        onSubmit={(x) => {
                                            sendRentTransaction(
                                                x.token,
                                                new BigNumber(x.amount).multipliedBy(new BigNumber(10).pow(6))
                                            );
                                        }}
                                        initialValues={{
                                            token: accountBalances.length >= 1 ? accountBalances?.find(x => x.balance.gt(0))?.token || '' : '',
                                            amount: (spaceInfo?.is_new) ? spaceInfo?.paid_amount || 7 : spaceInfo.paid_amount * 2
                                        }}
                                    >
                                        {({
                                              errors,
                                              values,
                                              touched,
                                              handleChange,
                                              handleBlur,
                                              handleSubmit,
                                              setFieldValue
                                          }) => {
                                            if ((!accountBalances.length || !accountBalances.some(x => x.balance.gt(0))) && accountInfo?.address !== contractOwner) {
                                                return <div className={'text-center'}>
                                                    <p className={'text-lg font-bold'}>
                                                        {language == 'en' ? 'No tokens available' : 'Nu există token-uri disponibile'}
                                                    </p>
                                                    <p className={'text-sm text-gray-500'}>
                                                        {language == 'en' ? 'You need to have at least one of the following tokens in your wallet to rent this space:' : 'Ai nevoie de cel puțin unul dintre următoarele token-uri în portofel pentru a închiria acest spațiu:'}
                                                    </p>
                                                    <ul className={'text-sm text-gray-500'}>
                                                        {AcceptedTokens.map((x, i) => <li key={i}>{x}</li>)}
                                                    </ul>
                                                    <p className={'text-sm text-gray-500'}>
                                                        {language == 'en' ? 'The minimumm amount to rent this space is ' : 'Suma minimă pentru închiriere este '}
                                                        {(spaceInfo?.is_new) ? spaceInfo?.paid_amount || 7 : spaceInfo.paid_amount * 2}.
                                                    </p>
                                                </div>
                                            }

                                            return <>
                                                {hasFailedRentTransaction && <div
                                                    className={'w-full h-full bg-red-500 bg-opacity-50 flex items-center justify-center mb-4 p-4'}>
                                                    <div className={'flex flex-col items-center justify-center'}>
                                                        <div className={'text-center'}>
                                                            <p className={'text-lg font-bold mb-0'}>
                                                                {language == 'en' ? 'Rent transaction failed' : 'Tranzacție închiriere eșuată'}
                                                            </p>
                                                            <p className={'text-sm'}>
                                                                {language == 'en' ? 'Check that you have enough balance and try again. For more information, check the blockchain explorer.' : 'Verifică dacă ai suficientă balanță și încearcă din nou. Pentru mai multe informații, verifică explorer-ul blockchain-ului.'}
                                                            </p>
                                                            {firstFailedRentTransaction && firstFailedRentTransaction[1]?.errorMessage &&
                                                                <p className={'text-xs mt-2'}>
                                                                    Smart Contract
                                                                    error: <br/>{parseScError(firstFailedRentTransaction[1].errorMessage)}
                                                                </p>}
                                                        </div>
                                                    </div>
                                                </div>}
                                                {/*            <pre>*/}
                                                {/*    /!*{JSON.stringify(spaceInfo, null, 2)}*!/*/}
                                                {/*</pre>*/}
                                                <div className="grid gap-4 md:grid-cols-2">
                                                    <div>
                                                        <div>
                                                            <label htmlFor={'token'}
                                                                   className={'text-sm text-gray-500'}>
                                                                {language == 'en' ? 'Select token' : 'Selectează token'}
                                                            </label>
                                                        </div>
                                                        {/* select with tokens where value > 0 */}
                                                        <div>
                                                            <select
                                                                name={'token'}
                                                                id={'token'}
                                                                className={'d-select d-select-primary w-full max-w-xs'}
                                                                value={values.token}
                                                                onChange={(e) => {
                                                                    setFieldValue('token', e.target.value)
                                                                }}>
                                                                <option
                                                                    value={''}>{language == 'en' ? 'Select token' : 'Selectează token'}</option>
                                                                {accountBalances.map((x, i) => {
                                                                    if (x.balance.eq(0)) {
                                                                        return null;
                                                                    }
                                                                    return <option key={i} value={x.token}
                                                                                   selected={values.token === x.token}>{x.token}
                                                                        ({denominated(x.balance.toString())})</option>
                                                                })}
                                                            </select>
                                                        </div>
                                                        {errors.token && (
                                                            <div className={'text-red-500 text-xs mt-1'}>
                                                                {errors.token}
                                                            </div>
                                                        )}
                                                    </div>

                                                    <div>
                                                        <div>
                                                            <label htmlFor={'token'}
                                                                   className={'text-sm text-gray-500'}>
                                                                {language == 'en' ? 'Amount' : 'Sumă'} (min. {(spaceInfo?.is_new) ? spaceInfo?.paid_amount || 7 : spaceInfo.paid_amount * 2} USD)
                                                            </label>
                                                        </div>
                                                        <div>
                                                            <input
                                                                type='number'
                                                                id={'amount'}
                                                                name='amount'
                                                                step='any'
                                                                required={true}
                                                                autoComplete='off'
                                                                min={(spaceInfo?.is_new) ? spaceInfo.paid_amount || 7 : spaceInfo.paid_amount * 2}
                                                                className={classnames(
                                                                    'd-input d-input-bordered d-input-primary w-full max-w-xs',
                                                                    {invalid: errors.amount && touched.amount}
                                                                )}
                                                                value={values.amount}
                                                                onBlur={handleBlur}
                                                                onChange={e => {
                                                                    let val = e.target.value
                                                                    if (val.indexOf(',') > -1) {
                                                                        val = val.replace(',', '.')
                                                                    }
                                                                    // e.target.value = val
                                                                    console.log({val, target: e.target})
                                                                    setFieldValue('amount', val);
                                                                }}
                                                            />
                                                        </div>
                                                        {errors.amount && touched.amount && (
                                                            <div className={'text-red-500 text-xs mt-1'}>
                                                                {errors.amount}
                                                            </div>
                                                        )}
                                                    </div>
                                                </div>

                                                <div className={'mt-4 pb-4'}>

                                                    <button onClick={handleSubmit} className="d-btn d-btn-primary"
                                                            type={'submit'}>
                                                        {language == 'en' ? 'Rent' : 'Închiriază'}
                                                    </button>

                                                </div>
                                                {/*                <pre>*/}
                                                {/*{JSON.stringify({values, errors}, null, 2)}*/}
                                                {/*        </pre>*/}
                                            </>
                                        }}
                                    </Formik>
                                </div>
                            </>}
                        </div>
                    </Transition.Child>
                </div>
            </Dialog>
        </Transition.Root>
    )
        ;
};

interface DenominatedType {
    denomination?: number;
    decimals?: number;
    showLastNonZeroDecimal?: boolean;
    addCommas?: boolean;
}

export const denominated = (
    input: string,
    parameters?: DenominatedType
): string =>
    formatAmount({
        input,
        denomination: 6,
        decimals: 6,
        ...parameters
    })

export default (BuySpace);
