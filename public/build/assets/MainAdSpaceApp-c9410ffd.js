import{_ as Y,s as oe,t as le,G as de,c as T,j as t,a as i,F as a,b as f}from"./react-app-0bc19372.js";import{R as _,m as ce,i as H,c as o,s as he,t as me,d as ue,z as xe,u as V,v as $,w as q,x as F,y as l,A as U,o as u,B as X}from"./vendor-271676b3.js";import"./lodash-87d1c6bb.js";import"./bignumber-d824444f.js";const G=_.lazy(()=>Y(()=>import("./BuySpace-c67a4c75.js"),["assets/BuySpace-c67a4c75.js","assets/react-app-0bc19372.js","assets/vendor-271676b3.js","assets/bignumber-d824444f.js","assets/lodash-87d1c6bb.js"])),be=_.lazy(()=>Y(()=>import("./SpaceEdit-8f74e46c.js"),["assets/SpaceEdit-8f74e46c.js","assets/react-app-0bc19372.js","assets/vendor-271676b3.js","assets/bignumber-d824444f.js","assets/lodash-87d1c6bb.js"])),Se=({name:w="",language:n,html:p,format:E,sidebar:d,info:J,session:we})=>{const s=ce(),[m,pe]=H(oe),[e,K]=o.useState(J),[C,b]=o.useState(!1),[v,S]=o.useState(!1),[Q,Z]=o.useState(!0),[k]=H(le),L=(k==null?void 0:k[w])||"",[M,g]=o.useState(""),[N,A]=o.useState(null);o.useEffect(()=>{g(p||"")},[p]);const I=async()=>{console.log(await(async()=>{await X({transactions:[{value:"0",data:"withdraw",receiver:f,gasLimit:"40000000"}],transactionsDisplayInfo:{processingMessage:n=="ro"?"Asteptam...":"Waiting...",successMessage:n=="ro"?"Au intrat banutii":"Money is in your wallet",transactionDuration:1e4}})})())},{successfulTransactionsArray:z}=he(),{pendingTransactionsArray:O}=me(),ee=r=>{if(!r[1].transactions)return!1;const x=u.Transaction.fromPlainObject(r[1].transactions[0]);if(x.getReceiver().bech32()===f){let h=u.TransactionPayload.fromEncoded(x.getData().encoded()).getEncodedArguments();if(h.length===2&&h[0]==="resetSpace"&&h[1]===new u.ArgSerializer().valuesToString([new u.StringValue(e.name)]).argumentsString)return!0}return!1},te=r=>{if(!r[1].transactions)return!1;const x=u.Transaction.fromPlainObject(r[1].transactions[0]);if(x.getReceiver().bech32()===f){let h=u.TransactionPayload.fromEncoded(x.getData().encoded()).getEncodedArguments();if(h.length===1&&h[0]==="withdraw")return!0}return!1},P=o.useMemo(()=>z.some(ee),[z]),j=o.useMemo(()=>O.some(te),[O]);o.useEffect(()=>{P&&D(e.name)},[P]);const se=async r=>{if(!r||r.length===0)return;console.log(await(async()=>{const y=new u.ArgSerializer;let h="resetSpace";h+="@"+y.valuesToString([new u.StringValue(r)]).argumentsString,await X({transactions:[{value:"0",data:h,receiver:f,gasLimit:"40000000"}],transactionsDisplayInfo:{processingMessage:n=="ro"?"Asteptam...":"Waiting...",successMessage:n=="ro"?"Spatiul a fost resetat":"Space was reset",transactionDuration:1e4}})})())},D=async r=>{const y=(await de.request(ue.gql`
            query adSpaceInfo($spaceName: String!, $initial: Boolean) {
                adSpaceInfo(spaceName: $spaceName, initial: $initial) {
                    name
                    is_new
                    owner
                    paid_amount
                    paid_until
                }
            }
        `,{spaceName:r,initial:Q})).adSpaceInfo;K(y)};o.useEffect(()=>{(async()=>e!=null&&e.name||(await D(w),Z(!0)))()},[w]);const R=o.useCallback(()=>{D(w)},[w]),ae=[(s==null?void 0:s.address)==T&&t("div",{style:{flexBasis:"100%",height:0}}),(s==null?void 0:s.address)==T&&i("button",{className:"p-2 bg-secondary shadow-box hover:shadow-boxhvr text-xs text-black",onClick:r=>{r.preventDefault(),I()},children:[!j&&i(a,{children:[n=="en"&&t(a,{children:"Withdraw"}),n=="ro"&&t(a,{children:"Retrage banii"})]}),j&&t(a,{children:"..."})]}),s.address&&s.address==T&&!(e!=null&&e.is_new)&&i("button",{className:"p-2 bg-red-500 text-white shadow-box hover:shadow-boxhvr text-xs text-black",onClick:r=>{r.preventDefault(),se((e==null?void 0:e.name)||"")},children:[n=="en"&&t(a,{children:"Reset space"}),n=="ro"&&t(a,{children:"Reseteaza spatiul"})]})],W=[s.address&&s.address!=(e==null?void 0:e.owner)&&!(e!=null&&e.is_new)&&i("button",{className:"p-2 bg-secondary shadow-box hover:shadow-boxhvr text-xs text-black",onClick:r=>{r.preventDefault(),b(!0)},children:[n=="en"&&t(a,{children:"Outbid this space"}),n=="ro"&&t(a,{children:"Cumpara acest spatiu"})]}),s.address&&s.address==(e==null?void 0:e.owner)&&!(e!=null&&e.is_new)&&!v&&i("button",{className:"p-2 bg-secondary shadow-box hover:shadow-boxhvr text-xs text-black",onClick:r=>{r.preventDefault(),S(!0)},children:[n=="en"&&t(a,{children:"Edit space"}),n=="ro"&&t(a,{children:"Editeaza spatiul"})]}),s.address&&s.address==(e==null?void 0:e.owner)&&!(e!=null&&e.is_new)&&v&&i("button",{className:"p-2 bg-secondary shadow-box hover:shadow-boxhvr text-xs text-black",onClick:r=>{r.preventDefault(),S(!1)},children:[n=="en"&&t(a,{children:"Cancel edit"}),n=="ro"&&t(a,{children:"Anuleaza editarea"})]}),i("button",{className:"p-2 bg-secondary shadow-box hover:shadow-boxhvr text-xs text-black",onClick:r=>{r.preventDefault(),xe()},children:[n=="en"&&t(a,{children:"Logout"}),n=="ro"&&t(a,{children:"Logout"})]}),...ae].filter(Boolean),[c,B]=o.useState(!1);o.useEffect(()=>{s!=null&&s.address&&c&&(B(!1),A(null))},[c,s]),o.useEffect(()=>{s!=null&&s.address&&N&&N=="outbid"&&(e.owner&&e.owner!=s.address&&b(!0),A(null))},[N,s==null?void 0:s.address,e]);const re=[!(e!=null&&e.is_new)&&i("button",{className:"dapp-core-component__main__btn dapp-core-component__main__btn-primary dapp-core-component__main__m-1 text-xs p-2 mx-0 bg-yellow-500 shadow-box hover:shadow-boxhvr text-black",onClick:r=>{r.preventDefault(),s!=null&&s.address?b(!0):(B(!c),A(c?null:"outbid"))},children:[c&&i(a,{children:[n=="en"&&t(a,{children:"Cancel outbid"}),n=="ro"&&t(a,{children:"Renunta"})]}),!c&&i(a,{children:[n=="en"&&t(a,{children:"Outbid this space"}),n=="ro"&&t(a,{children:"Cumpara acest spatiu"})]})]}),c&&t(V,{isWalletConnectV2:!0,token:m,logoutRoute:window.location.href,showScamPhishingAlert:!0,className:"text-xs p-2 mx-0 bg-secondary shadow-box hover:shadow-boxhvr text-black"}),c&&t($,{token:m,showScamPhishingAlert:!0,className:"text-xs p-2 mx-0 bg-secondary shadow-box hover:shadow-boxhvr text-black"}),c&&t(q,{className:"text-xs p-2 mx-0 bg-secondary shadow-box hover:shadow-boxhvr text-black",token:m,loginButtonText:"MultiversX DeFi Wallet"}),c&&t(F,{token:m,callbackRoute:window.location.pathname,className:"text-xs p-2 mx-0 bg-secondary shadow-box hover:shadow-boxhvr text-black",onLoginRedirect:{callbackRoute:window.location.pathname},nativeAuth:!0})].filter(Boolean);if(!(e!=null&&e.name)&&p&&p.length>1)return t(a,{children:t("div",{className:l("bg-primary py-4 px-4",{}),children:e&&!(e!=null&&e.is_new)&&t("div",{className:"space__content",dangerouslySetInnerHTML:{__html:v?L:M}})})});if(e!=null&&e.is_new)return t("div",{className:l("py-4 px-4",{"bg-primary":E=="light","bg-splash text-white":E=="dark"}),children:i("div",{className:"flex flex-col items-center justify-center",children:[(!s.address||s.address&&e.owner!=s.address)&&i(a,{children:[n=="ro"&&i(a,{children:[t("div",{className:l("font-bold text-center",{"text-2xl":!d,"mb-4":d}),children:"Acest spatiu publicitar poate fi tau pentru 10 de zile."}),i("p",{className:l("text-center",{"mb-4":d}),children:["Costa doar ",e==null?void 0:e.paid_amount," USDC/USDT / 10 zile.",t("br",{}),"Il inchiriezi instant cu ajutorul blockchain-ului MultiversX si il poti personaliza cum vrei tu."]})]}),n=="en"&&i(a,{children:[t("div",{className:l("font-bold",{"text-2xl":!d,"mb-4":d}),children:"This ad space can be yours for 10 days."}),i("p",{className:l("text-center",{"mb-4":d}),children:["It costs only ",e==null?void 0:e.paid_amount," USDC/USDT per 10 days.",t("br",{})," You rent it instantly using the MultiversX blockchain and you can customize it as you want."]})]})]}),!s.address&&t(a,{children:i("div",{className:l("flex mt-2 flex-col items-center md:flex-row",{"flex flex-col md:flex-col gap-2":d}),children:[t(V,{isWalletConnectV2:!0,token:m,logoutRoute:window.location.href,className:"text-xs p-2 mx-0 bg-secondary shadow-box hover:shadow-boxhvr text-black border-secondary",showScamPhishingAlert:!0}),t($,{token:m,showScamPhishingAlert:!0,className:"text-xs p-2 ml-4 bg-secondary shadow-box hover:shadow-boxhvr text-black  border-secondary"}),t(q,{className:"text-xs p-2 mx-0 bg-secondary shadow-box hover:shadow-boxhvr text-black border-secondary",token:m,loginButtonText:"MultiversX DeFi Wallet"}),t(F,{token:m,callbackRoute:window.location.pathname,className:"text-xs p-2 mx-0 ml-4 bg-secondary shadow-box hover:shadow-boxhvr text-black border-secondary",onLoginRedirect:{callbackRoute:window.location.pathname},nativeAuth:!0})]})}),s.address&&e.owner!=s.address&&t(a,{children:t("div",{className:l("mt-2",{"mt-4":d}),children:i("button",{className:"p-2 bg-secondary text-black shadow-box hover:shadow-boxhvr text-xs",onClick:r=>{r.preventDefault(),b(!0)},children:[n=="ro"&&t(a,{children:"Inchiriaza acest spatiu"}),n=="en"&&t(a,{children:"Rent this space"})]})})}),s.address&&t(a,{children:t("div",{className:l("mt-3 flex flex-wrap self-end justify-end gap-2 ml-auto",{"mt-4 transform scale-75 origin-right":d}),style:{width:"fit-content"},children:W})}),t(_.Suspense,{fallback:t("div",{}),children:t(G,{open:C,setOpen:b,spaceInfo:e,language:n,refreshSpace:R})})]})});const ne=e!=null&&e.paid_until?U(new Date((e==null?void 0:e.paid_until)*1e3),"dd.MM.yyyy HH:mm"):"",ie=e!=null&&e.paid_until?U(new Date((e==null?void 0:e.paid_until)*1e3),"MM-dd-yyyy HH:mm a"):"";return i(a,{children:[i("div",{className:l("bg-primary py-4 px-4",{}),children:[(s==null?void 0:s.address)&&(e==null?void 0:e.owner)==s.address&&t(a,{children:i("div",{className:l("font-bold text-xs bg-secondary py-2 px-4 rounded-2xl max-w border border-black mb-2"),style:{maxWidth:"fit-content"},children:[n=="ro"&&i(a,{children:["Deti acest spatiu pana in ",ne]}),n=="en"&&i(a,{children:["You own this space until ",ie]})]})}),e&&!(e!=null&&e.is_new)&&t("div",{className:"space__content",dangerouslySetInnerHTML:{__html:v?L:M}}),v&&t(a,{children:t(o.Suspense,{fallback:t("div",{children:"Loading"}),children:t(be,{spaceInfo:e,close:()=>{S(!1)},sidebar:d,language:n,refreshSpace:r=>{R(),g(r)}})})}),e.name&&i(a,{children:[s.address&&t("div",{className:l("mt-3 flex flex-wrap self-end justify-end gap-2 ml-auto",{"mt-4 transform scale-75 origin-right":d}),style:{width:"fit-content"},children:W}),!(s!=null&&s.address)&&t("div",{className:l("mt-3 self-end flex gap-2 justify-end",{"flex-col mt-4":d}),children:re})]})]}),t(_.Suspense,{fallback:t("div",{}),children:t(G,{open:C,setOpen:b,spaceInfo:e,language:n,refreshSpace:R})})]})};export{Se as default};