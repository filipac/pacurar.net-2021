import React, {Suspense, useEffect} from "react";
import {DappProvider} from "@multiversx/sdk-dapp/wrappers/DappProvider";
import {RecoilRoot} from "recoil/es/index.mjs";
import {createPortal} from "react-dom";
import {activeNetwork, WalletConnectKey} from "./config";
import {SessionManagement} from "./components/SessionManagement";
import {GameCounter} from "./components/GameCounter";

const MainAdSpaceApp = React.lazy(() => import('./MainAdSpaceApp'));

type Props = {
    children: React.ReactNode,
    allAds: Element[]
}
export const Dapp: React.FC<Props> = ({children, allAds}) => {
    // forceUpdate hook
    const [, updateState] = React.useState();
    const forceUpdate = React.useCallback(() => updateState({}), []);

    const [key, setKey] = React.useState(0);

    let allStateManagementApps = Array.from(document.querySelectorAll('[data-web3-state-management]'));
    let gameCounterApp = document.querySelector('[data-game-counter-app]');

    window.renderGameCounter = () => {
        setKey(old => old + 1)
        forceUpdate()
    }

    console.log({gameCounterApp})

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
                    const html = div.innerHTML;
                    div.innerHTML = '';
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
                })}
                {allStateManagementApps.map(div => {
                    return createPortal(<Suspense fallback={<div></div>}>
                        <SessionManagement />
                    </Suspense>, div)
                })}
                {gameCounterApp && createPortal(<Suspense fallback={<div></div>}>
                    {/* @ts-ignore */}
                    <GameCounter nfts={JSON.parse(gameCounterApp.dataset.nfts)} ikey={key}/>
                </Suspense>, gameCounterApp, `game-counter-${key}`)}
            </DappProvider>
        </RecoilRoot>
    )
}
