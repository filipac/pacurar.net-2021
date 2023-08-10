import React, {Suspense, useEffect} from "react";
import {DappProvider} from "@multiversx/sdk-dapp/wrappers/DappProvider";
import {RecoilRoot} from "recoil/es/index.mjs";
import {createPortal} from "react-dom";
import {activeNetwork, WalletConnectKey} from "./config";
import {SessionManagement} from "./components/SessionManagement";
import {GameCounter} from "./components/GameCounter";
import {LivewireableComponents} from "./LivewireableComponents";

const MainAdSpaceApp = React.lazy(() => import('./MainAdSpaceApp'));

type Props = {
    children: React.ReactNode,
    allAds: Element[]
}
export const Dapp: React.FC<Props> = ({children, allAds}) => {
    const [, updateState] = React.useState();
    const forceUpdate = React.useCallback(() => updateState({}), []);

    // useEffect(() => {
    //     let listener =
    // })

    return (
        <RecoilRoot>
            <DappProvider
                environment={activeNetwork}
                customNetworkConfig={{
                    walletConnectV2ProjectId: WalletConnectKey
                }}
                dappConfig={{
                    logoutRoute: window.location.href,

                }}
            >
                {children}
                {allAds.map(div => {
                    if (div.dataset.rendered === 'true') {
                        return false;
                    }
                    const html = div.innerHTML;
                    div.innerHTML = '';
                    setTimeout(() => {
                        div.dataset.rendered = 'true'
                    })
                    return createPortal(<Suspense fallback={<div></div>}>
                        <MainAdSpaceApp
                            // @ts-ignore
                            name={div.dataset.spaceName}
                            // @ts-ignore
                            language={div.dataset.language}
                            // @ts-ignore
                            format={div.dataset.format}
                            // @ts-ignore
                            sidebar={div.dataset.sidebar == 1 ? true : false}
                            // @ts-ignore
                            info={div.dataset.initialInfo ? JSON.parse(div.dataset.initialInfo) : {}}
                            // @ts-ignore
                            session={div.dataset.sessionId}
                            html={html}
                        />
                    </Suspense>, div)
                }).filter(el => el !== false)}
                <LivewireableComponents />
            </DappProvider>
        </RecoilRoot>
    )
}
