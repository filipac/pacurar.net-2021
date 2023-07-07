import {atom} from "recoil/es/index.mjs";

export const sessionAtom = atom({
    key: 'session',
    default: '',
});

export const tempAdAtom = atom({
    key: 'tempAd',
    default: {
        _title: '',
    },
})

export const timesPlayedAtom = atom({
    key: 'timesPlayed',
    default: {}
})
