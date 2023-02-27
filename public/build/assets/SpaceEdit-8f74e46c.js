import{t as j,a as r,F as c,j as t,G as w}from"./react-app-0bc19372.js";import{c as i,i as $,U as E,V as q,y as R,M as u,N,R as S,d as C}from"./vendor-271676b3.js";import"./lodash-87d1c6bb.js";import"./bignumber-d824444f.js";const z=({spaceInfo:a,sidebar:g,close:y,language:h,refreshSpace:k})=>{const l=i.useRef(null),[o,f]=i.useState("code"),[n,v]=$(j),d=(n==null?void 0:n[a.name])||"",m=e=>{v({...n,[a.name]:e})},[s,b]=i.useState(null),_=async()=>{let e=await w.request(C.gql`mutation UpdateSpace($spaceName: String!, $content: String!) {
            updateSpace(spaceName: $spaceName, content: $content) {
                id
                name
                content
                last_modified_by
                created_at
                updated_at
            }
        }`,{spaceName:a.name,content:d});e.updateSpace&&(b(e.updateSpace),k(e.updateSpace.content),y())},F=async()=>{let e=await w.request(C.gql`query GetSpace($spaceName: String!) {
            space(spaceName: $spaceName) {
                id
                name
                content
                last_modified_by
                created_at
                updated_at
            }
        }`,{spaceName:a.name});b(e.space)};i.useEffect(()=>(F(),()=>{v({...n,[a.name]:void 0})}),[a.name]),i.useEffect(()=>{s&&l.current&&l.current.setContent(s.content),s&&m(s.content)},[s,l]);const x=r(c,{children:[r("div",{className:"flex flex-row justify-center mt-4 mb-4",children:[t("button",{className:`px-4 py-2 rounded-l-md ${o==="wysiwyg"?"bg-gray-400":"bg-gray-100"}`,onClick:()=>f("wysiwyg"),children:"WYSIWYG"}),t("button",{className:`px-4 py-2 rounded-r-md ${o==="code"?"bg-gray-400":"bg-gray-100"}`,onClick:()=>f("code"),children:"Code"})]}),o==="wysiwyg"&&t(E,{tinymceScriptSrc:window.location.origin+"/tinymce/tinymce.min.js",onInit:(e,p)=>l.current=p,initialValue:d||"",onChange:(e,p)=>{m(e.target.getContent())},init:{height:500,menubar:!1,plugins:["advlist","autolink","lists","link","image","charmap","preview","anchor","searchreplace","visualblocks","code","fullscreen","insertdatetime","media","table","code","help","wordcount"],toolbar:"undo redo | blocks | bold italic forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help",content_style:"body { font-family:Helvetica,Arial,sans-serif; font-size:14px }"}}),o==="code"&&t(q,{height:"30vh",language:"html",theme:"vs-dark",value:d||"",options:{minimap:{enabled:!1}},onChange:(e,p)=>{m(e)}}),t("div",{className:R("mt-3 self-end flex gap-2 justify-end",{"flex-col mt-4":g}),children:r("button",{className:"p-2 bg-green-600 shadow-box hover:shadow-boxhvr text-lg text-black",onClick:e=>{e.preventDefault(),_()},children:[h=="en"&&t(c,{children:"Save"}),h=="ro"&&t(c,{children:"Salvează"})]})})]});return g?t(c,{children:t(u.Root,{show:!0,as:"div",children:t(N,{as:"div",className:"fixed z-10 inset-0 overflow-y-auto",open:!0,onClose:e=>{y()},children:r("div",{className:"flex items-start justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0",children:[t(u.Child,{as:S.Fragment,enter:"ease-out duration-300",enterFrom:"opacity-0",enterTo:"opacity-100",leave:"ease-in duration-200",leaveFrom:"opacity-100",leaveTo:"opacity-0",children:t(N.Overlay,{className:"fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"})}),t("span",{className:"hidden sm:inline-block sm:align-middle sm:h-screen","aria-hidden":"true",children:"​"}),t(u.Child,{as:S.Fragment,enter:"ease-out duration-300",enterFrom:"opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95",enterTo:"opacity-100 translate-y-0 sm:scale-100",leave:"ease-in duration-200",leaveFrom:"opacity-100 translate-y-0 sm:scale-100",leaveTo:"opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95",children:t("div",{className:`
                            inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left shadow-xl transform
                            transition-all sm:mt-32 sm:align-middle sm:w-full sm:p-6 relative
                            md:max-w-md lg:max-w-lg xl:max-w-2xl
                            self-end mb-12
                            `,children:x})})]})})})}):x};export{z as default};
