import React, {useEffect} from 'react';
import {Transaction, TransactionPayload} from "@multiversx/sdk-core/out";
import {updateSignedTransactions, updateSignedTransactionStatus} from "@multiversx/sdk-dapp/reduxStore/slices";
import {TransactionBatchStatusesEnum, TransactionServerStatusesEnum} from "@multiversx/sdk-dapp/types";
import {useGetAccountInfo, useGetLoginInfo, useGetSignedTransactions} from "@multiversx/sdk-dapp/hooks";
import {useDispatch} from "@multiversx/sdk-dapp/reduxStore/DappProviderContext";
import {GqlClient} from "./graphql_client";
import {gql} from "graphql-request";
import {useRecoilState} from "recoil/es/index.mjs";
import {sessionAtom} from "./state/dapp";

export const TransactionWatcher = () => {
    const signed = useGetSignedTransactions()
    const dispatch = useDispatch()

    const loginInfo = useGetLoginInfo();
    const accountInfo = useGetAccountInfo()

    const [sessionId, setSessionId] = useRecoilState(sessionAtom)

    useEffect(() => {
        let one = document.querySelector('[data-web3-space]')
        if (one) {
            // @ts-ignore
            setSessionId(one.dataset.sessionId)
            return
        }
        GqlClient.request(gql`
            { sessionInfo { id } }
        `).then(x => {
            setSessionId(x.sessionInfo.id)
        })
    }, [])

    useEffect(() => {
        if (!loginInfo?.tokenLogin?.loginToken || !accountInfo.address) {
            GqlClient.setHeader('Authorization', undefined)
            return
        }
        (async () => {
            if (!sessionStorage.getItem(`authToken${accountInfo.address}${loginInfo.tokenLogin.loginToken}`)) {
                const query = gql`
                    mutation($address: String!, $signature: String!, $token: String) {
                        verifyLogin(address: $address, signature: $signature, token: $token) {
                            success
                            token
                        }
                    }
                `
                const result = await GqlClient.request(query, {
                    address: accountInfo.address,
                    signature: loginInfo.tokenLogin.signature,
                    token: loginInfo.tokenLogin.loginToken
                })


                if (result.verifyLogin.success) {
                    GqlClient.setHeader('Authorization', 'Bearer ' + result.verifyLogin.token)
                    sessionStorage.setItem(`authToken${accountInfo.address}${loginInfo.tokenLogin.loginToken}`, result.verifyLogin.token)
                }
            } else {
                let token = sessionStorage.getItem(`authToken${accountInfo.address}${loginInfo.tokenLogin.loginToken}`)
                GqlClient.setHeader('Authorization', 'Bearer ' + token)
            }
        })()

    }, [loginInfo?.tokenLogin?.loginToken, accountInfo.address])


    useEffect(() => {
        let all = signed.signedTransactionsArray.filter(x => x[1].status === 'signed')
        for (const t of all) {
            const [id, trans] = t
            for (let transaction of trans.transactions) {
                const originalTransaction = transaction
                transaction = new Transaction(transaction)
                const payload = TransactionPayload.fromEncoded(transaction.getData())
                let args = payload.getEncodedArguments()
                if (args.length > 0 && args[0] === 'signComment') {
                    setTimeout(() => {
                        dispatch(updateSignedTransactionStatus({
                            transactionHash: originalTransaction.hash,
                            sessionId: id,
                            status: TransactionServerStatusesEnum.success
                        }))
                        dispatch(updateSignedTransactions({
                            sessionId: id,
                            status: TransactionBatchStatusesEnum.success
                        }))
                    }, 1000)
                }
            }
        }
    }, [signed])

    return null;
}
