import React, { useEffect } from "react";
import {
    ArgSerializer,
    EndpointParameterDefinition,
    StringValue,
    Transaction,
    TransactionPayload,
    U64Type,
} from "@multiversx/sdk-core/out";
import {
    updateSignedTransactions,
    updateSignedTransactionStatus,
} from "@multiversx/sdk-dapp/reduxStore/slices";
import {
    TransactionBatchStatusesEnum,
    TransactionServerStatusesEnum,
} from "@multiversx/sdk-dapp/types";
import {
    useGetAccountInfo,
    useGetLoginInfo,
    useGetSignedTransactions,
} from "@multiversx/sdk-dapp/hooks";
import { useDispatch } from "@multiversx/sdk-dapp/reduxStore/DappProviderContext";
import { GqlClient } from "./graphql_client";
import { gql } from "graphql-request";
import { useRecoilState } from "recoil/es/index.mjs";
import { sessionAtom } from "./state/dapp";
import { U64Value } from "@multiversx/sdk-core/out/smartcontracts/typesystem/numerical";

export const TransactionWatcher = () => {
    const signed = useGetSignedTransactions();
    const dispatch = useDispatch();

    const loginInfo = useGetLoginInfo();
    const accountInfo = useGetAccountInfo();

    const [sessionId, setSessionId] = useRecoilState(sessionAtom);

    const unsetLocalStorage = () => {
        // delete all session storage keys that start with authToken
        for (let i = 0; i < sessionStorage.length; i++) {
            let key = sessionStorage.key(i);
            if (key?.startsWith("authToken")) {
                sessionStorage.removeItem(key);
            }
        }
    };

    const hasTokenInStorage = () => {
        for (let i = 0; i < sessionStorage.length; i++) {
            let key = sessionStorage.key(i);
            if (key?.startsWith("authToken")) {
                return true;
            }
        }

        return false;
    };

    useEffect(() => {
        let one = document.querySelector("[data-web3-space]");
        if (one) {
            // @ts-ignore
            setSessionId(one.dataset.sessionId);
            return;
        }
        GqlClient.request(
            gql`
                {
                    sessionInfo {
                        id
                    }
                }
            `
        ).then((x) => {
            setSessionId(x.sessionInfo.id);
        });
    }, []);

    useEffect(() => {
        const cookies = document.cookie.split(";");
        let _token = null;
        let blogTokenCookie = cookies.filter((x) =>
            x.trim().startsWith("blog_token=")
        );
        if (blogTokenCookie.length > 0) {
            blogTokenCookie = blogTokenCookie[0].split("=");
            if (blogTokenCookie.length > 1) {
                _token = blogTokenCookie[1];
            }
        }

        if (!loginInfo?.tokenLogin?.loginToken || !accountInfo.address) {
            const query = gql`
                mutation {
                    logout
                }
            `;
            if (_token) {
                GqlClient.request(query).then(() => {
                    window.location.reload();
                });
                // GqlClient.setHeader('Authorization', undefined)
                unsetLocalStorage();
            }
            return;
        }

        const has = _token;

        (async () => {
            if (!has) {
                const query = gql`
                    mutation (
                        $address: String!
                        $signature: String!
                        $token: String
                    ) {
                        verifyLogin(
                            address: $address
                            signature: $signature
                            token: $token
                        ) {
                            success
                            # token
                        }
                    }
                `;
                const result = await GqlClient.request(query, {
                    address: accountInfo.address,
                    signature: loginInfo.tokenLogin.signature,
                    token: loginInfo.tokenLogin.loginToken,
                });

                if (result.verifyLogin.success) {
                    // GqlClient.setHeader('Authorization', 'Bearer ' + result.verifyLogin.token)
                    const ev = new CustomEvent("loginVerified", {
                        detail: {
                            accountInfo,
                            loginInfo,
                        },
                    });
                    document.dispatchEvent(ev);
                }
            }
            // } else {
            //     let token = sessionStorage.getItem(`authToken${accountInfo.address}${loginInfo.tokenLogin.loginToken}`)
            //     GqlClient.setHeader('Authorization', 'Bearer ' + token)
            // }
        })();
    }, [loginInfo?.tokenLogin?.loginToken, accountInfo.address]);

    useEffect(() => {
        let all = signed.signedTransactionsArray.filter(
            (x) => x[1].status === "signed" || x[1].status === "success"
        );

        for (const t of all) {
            const [id, trans] = t;
            for (let transaction of trans.transactions) {
                const originalTransaction = transaction;
                transaction = new Transaction(transaction);
                const payload = TransactionPayload.fromEncoded(
                    transaction.getData()
                );
                let args = payload.getEncodedArguments();
                if (args.length > 0) {
                    if (args[0] === "signComment") {
                        setTimeout(() => {
                            dispatch(
                                updateSignedTransactionStatus({
                                    transactionHash: originalTransaction.hash,
                                    sessionId: id,
                                    status: TransactionServerStatusesEnum.success,
                                })
                            );
                            dispatch(
                                updateSignedTransactions({
                                    sessionId: id,
                                    status: TransactionBatchStatusesEnum.success,
                                })
                            );
                        }, 1000);
                    }
                }
                // if last arg is increment
                if (
                    args.length > 0 &&
                    args[args.length - 1] === "6d61726b506c61796564" &&
                    trans.status === "success"
                ) {
                    const collection = StringValue.fromHex(args[1]);
                    const serializer = new ArgSerializer();
                    let nonce = serializer.stringToValues(args[2], [
                        new EndpointParameterDefinition(
                            "nonce",
                            "nonce of nft",
                            new U64Type()
                        ),
                    ]);
                    nonce = nonce[0].value.toNumber();
                    GqlClient.request(
                        gql`
                            query clearCache(
                                $identifier: String!
                                $nonce: Int!
                                $guestAddress: String
                            ) {
                                clearPlayCount(
                                    identifier: $identifier
                                    nonce: $nonce
                                    guestAddress: $guestAddress
                                )
                            }
                        `,
                        {
                            identifier: collection.valueOf(),
                            nonce,
                            guestAddress: accountInfo.address,
                        }
                    ).then(() => {
                        window.dispatchEvent(
                            new CustomEvent("clearPlayCount", {})
                        );
                    });
                }
            }
        }
    }, [signed]);

    return null;
};
