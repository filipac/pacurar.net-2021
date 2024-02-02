import React, {useEffect, useState} from 'react'
import {useGetAccountInfo} from "@multiversx/sdk-dapp/hooks";
import {
    ExtensionLoginButton,
    LedgerLoginButton,
    WalletConnectLoginButton,
    WebWalletLoginButton
} from "@multiversx/sdk-dapp/UI";

type LoginButtonsProps = {
    sessionId?: string
    initialShowLogin?: boolean
}

export const useLoginButtons: (props: LoginButtonsProps) => {
    showLogin: boolean;
    loginActions: JSX.Element[];
    setShowLogin: (value: (((prevState: boolean) => boolean) | boolean)) => void
} = ({
    sessionId,
    initialShowLogin
                                }) => {
    const [showLogin, setShowLogin] = useState(initialShowLogin || false)

    const accountInfo = useGetAccountInfo()

    useEffect(() => {
        if (accountInfo?.address && showLogin) {
            setShowLogin(initialShowLogin)
        }
    }, [showLogin, accountInfo])

    const loginActions = [
        showLogin && <WalletConnectLoginButton key={'wallet-connect'} nativeAuth token={sessionId}
                                               logoutRoute={window.location.href}
                                               loginButtonText={'xPortal'}
                                               className={'text-xs p-2 mx-0 bg-secondary shadow-box hover:shadow-boxhvr text-black'}
        />,
        showLogin && <LedgerLoginButton key={'ledger'} token={sessionId}
                                        className={'text-xs p-2 mx-0 bg-secondary shadow-box hover:shadow-boxhvr text-black'}/>,
        showLogin &&
        <ExtensionLoginButton key={'ext'} className={'text-xs p-2 mx-0 bg-secondary shadow-box hover:shadow-boxhvr text-black'}
                              token={sessionId} loginButtonText={'MultiversX DeFi Wallet'}/>,
        showLogin && <WebWalletLoginButton key='web' token={sessionId} callbackRoute={window.location.pathname}
                                                  className={'text-xs p-2 mx-0 bg-secondary shadow-box hover:shadow-boxhvr text-black'}
                                                  onLoginRedirect={{
                                                      callbackRoute: window.location.pathname,
                                                  }} nativeAuth={true}/>
    ].filter(Boolean)

    return {
        showLogin,
        setShowLogin,
        loginActions,
    }
}
