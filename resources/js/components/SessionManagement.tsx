import React from 'react';
import {useLoginButtons} from "./LoginButtons";
import {sessionAtom} from "../state/dapp";
import {useRecoilState} from "recoil/es/index.mjs";
import classnames from "classnames";
import {useGetAccountInfo} from "@multiversx/sdk-dapp/hooks";
import {logout} from "@multiversx/sdk-dapp/utils";

export const SessionManagement = () => {
    const [sessionId, setSessionId] = useRecoilState(sessionAtom)

    const accountInfo = useGetAccountInfo()

    const {
        loginActions
    } = useLoginButtons({sessionId, initialShowLogin: true})

    const actionsLoggedOut = [
        ...loginActions
    ].filter(Boolean)

    return <>
        <div className={classnames('mt-3 self-end flex gap-2 justify-end', {
            'flex-col mt-4': false, //sidebar version
        })}>
            {!accountInfo?.address && <>{actionsLoggedOut}</>}
            {accountInfo.address && <>
                <button className={'p-2 bg-secondary shadow-box hover:shadow-boxhvr text-xs text-black'}
                        onClick={e => {
                            e.preventDefault()
                            // logout from wallet
                            logout()
                        }}>
                    Logout
                </button>
            </>}
        </div>
    </>
}
