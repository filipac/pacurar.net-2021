// create a root react app

import React, {Suspense} from 'react';
import {createRoot} from 'react-dom/client';
const App = React.lazy(() => import('./App.tsx'));
// import App from './App.tsx';

const init = async () => {
    let allDivs = document.querySelectorAll('[data-replace-vue-app]');
    if (allDivs.length === 0) {
        return
    }
    allDivs.forEach((div) => {
        try {
            const root = createRoot(div);
            root.render(
                <div>
                    <Suspense fallback={<div>Loading...</div>}><App/></Suspense>
                </div>
            );
        } catch (e) {
            console.error(e);
        }
    })
}

init()
