import React, {Suspense} from "react";
import {createPortal} from "react-dom";
import {SessionManagement} from "./components/SessionManagement";
import {GameCounter} from "./components/GameCounter";

export const LivewireableComponents = () => {
        // forceUpdate hook
    const [, updateState] = React.useState();
    const forceUpdate = React.useCallback(() => updateState({}), []);

    const [key, setKey] = React.useState(0);

    let allStateManagementApps = Array.from(document.querySelectorAll('[data-web3-state-management]'));
    let gameCounterApp = document.querySelector('[data-game-counter-app]');

    window.rerenderLivewireableComponents = () => {
        setKey(old => old + 1)
        // document.dispatchEvent(new CustomEvent('rerender-livewireable-components')
        forceUpdate()
    }

    return <>
        {allStateManagementApps.map(div => {
                    return createPortal(<Suspense fallback={<div></div>}>
                        <SessionManagement />
                    </Suspense>, div)
                })}
                {gameCounterApp && createPortal(<Suspense fallback={<div></div>}>
                    {/* @ts-ignore */}
                    <GameCounter nfts={JSON.parse(gameCounterApp.dataset.nfts)} ikey={key}/>
                </Suspense>, gameCounterApp, `game-counter-${key}`)}
    </>
}
