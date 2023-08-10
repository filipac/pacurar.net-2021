import React, { useEffect } from "react";
import { useLoginButtons } from "./LoginButtons";
import { sessionAtom } from "../state/dapp";
import { useRecoilState } from "recoil/es/index.mjs";
import classnames from "classnames";
import { useGetAccountInfo, useGetLoginInfo } from "@multiversx/sdk-dapp/hooks";
import { logout } from "@multiversx/sdk-dapp/utils";

type Props = {
    login?: boolean;
    intended?: string;
};

export const SessionManagement: React.FC<Props> = (props) => {
    const [sessionId, setSessionId] = useRecoilState(sessionAtom);

    const accountInfo = useGetAccountInfo();
    const provider = useGetLoginInfo();

    const { loginActions } = useLoginButtons({
        sessionId,
        initialShowLogin: true,
    });

    const actionsLoggedOut = [...loginActions].filter(Boolean);

    // console.log('props', props)

    useEffect(() => {
        if (!props.login) {
            return;
        }

        let listener = function (e) {
            if (props.intended) {
                window.location.href = props.intended;
            } else {
                // console.log('provider', e)
                if (e.detail.loginInfo.loginMethod !== "wallet") {
                    window.location.reload();
                }
            }
        };

        document.addEventListener("loginVerified", listener);

        return () => {
            document.removeEventListener("loginVerified", listener);
        };
    }, [props.login, props.intended]);

    return (
        <>
            <div
                className={classnames("mt-3 flex gap-2", {
                    "flex-col mt-4": false, //sidebar version
                    "self-end justify-end": !props.login,
                    "self-center justify-center": props.login,
                })}
            >
                {!accountInfo?.address && <>{actionsLoggedOut}</>}
                {accountInfo.address && (
                    <>
                        <button
                            className={
                                "p-2 bg-secondary shadow-box hover:shadow-boxhvr text-xs text-black"
                            }
                            onClick={(e) => {
                                e.preventDefault();
                                // logout from wallet
                                logout();
                            }}
                        >
                            Logout
                        </button>
                    </>
                )}
            </div>
        </>
    );
};
