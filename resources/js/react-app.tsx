// create a root react app
window._global ||= window;
import React from 'react';
import {TransactionsToastList} from "@multiversx/sdk-dapp/UI/TransactionsToastList";
import {SignTransactionsModals} from '@multiversx/sdk-dapp/UI/SignTransactionsModals';
import {NotificationModal} from '@multiversx/sdk-dapp/UI/NotificationModal';
import {Dapp} from "./Dapp";
import {TransactionWatcher} from "./TransactionWatcher";
import {createRoot} from "react-dom/client";

let renderedRequired = false;

const init = async () => {
    let allAdSpaces = document.querySelectorAll('[data-web3-space]');
    if (allAdSpaces.length === 0) {
        return
    }
    const allAds = Array.from(allAdSpaces);

    const parent = document.getElementsByClassName('dapp-required')[0];
    let div = document.createElement('div');
    div.setAttribute('id', 'app');
    parent.appendChild(div);
    const root = createRoot(div);

    root.render(<Dapp allAds={allAds}>
        <TransactionsToastList
            className='transactions-toast-list'
            transactionToastClassName="transactions-toast-class"
            successfulToastLifetime={30000}
        />
        <SignTransactionsModals/>
        <NotificationModal/>
        <TransactionWatcher/>
    </Dapp>)
}

init()
