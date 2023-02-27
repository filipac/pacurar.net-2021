import {SignableMessage} from "@multiversx/sdk-core/out";
import {signMessage} from "@multiversx/sdk-dapp/utils";
import {gql} from "graphql-request";
import {GqlClient} from "./graphql_client";
import {useGetAccountInfo} from "@multiversx/sdk-dapp/hooks/account/useGetAccountInfo";

// return type of useGetAccountInfo function
type AccountInfo = typeof useGetAccountInfo extends () => infer R ? R : never;

const sign = async (accountInfo: AccountInfo) => {
            const commentId = 1;
            const message = new SignableMessage({
                message: Buffer.from(`signComment@${commentId}`),
            })
            console.log({message})
            let x = await signMessage({
                // @ts-ignore
                message: message.message,
            })
            console.log(x)
            if (!x || !x.signature.hex()) {
                return
            }
            const query = gql`
                query($signature: String!, $address: String!) {
                    valid: validatePing(signature: $signature, address: $address)
                }
            `
            let res = await GqlClient.request(query, {
                signature: x.signature.hex(),
                address: accountInfo.address
            })
            console.log({res})
        }
