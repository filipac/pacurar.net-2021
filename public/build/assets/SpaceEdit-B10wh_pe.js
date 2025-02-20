import{r as i,b as C,j as e,U as F,X as _,c as E,N as m,O as b,f as v,d as j}from"./vendor-DZp4qUgB.js";import{t as $,G as w}from"./react-app-DjWCPCj7.js";import"./bignumber-L0kgG1Q3.js";import"./lodash-C-76dkfd.js";const z=({spaceInfo:n,sidebar:p,close:u,language:g,refreshSpace:N})=>{const l=i.useRef(null),[s,x]=i.useState("code"),[r,y]=C($),o=(r==null?void 0:r[n.name])||"",c=t=>{y({...r,[n.name]:t})},[a,h]=i.useState(null),S=async()=>{let t=await w.request(j.gql`mutation UpdateSpace($spaceName: String!, $content: String!) {
            updateSpace(spaceName: $spaceName, content: $content) {
                id
                name
                content
                last_modified_by
                created_at
                updated_at
            }
        }`,{spaceName:n.name,content:o});t.updateSpace&&(h(t.updateSpace),N(t.updateSpace.content),u())},k=async()=>{let t=await w.request(j.gql`query GetSpace($spaceName: String!) {
            space(spaceName: $spaceName) {
                id
                name
                content
                last_modified_by
                created_at
                updated_at
            }
        }`,{spaceName:n.name});h(t.space)};i.useEffect(()=>(k(),()=>{y({...r,[n.name]:void 0})}),[n.name]),i.useEffect(()=>{a&&l.current,a!=null&&a.content&&c(a.content)},[(a==null?void 0:a.content)||""]),i.useEffect(()=>{s==="wysiwyg"&&l.current&&setTimeout(()=>{try{l.current.setContent(o)}catch(t){console.log(t)}},1)},[s,l.current]);const f=e.jsxs(e.Fragment,{children:[e.jsxs("div",{className:"flex flex-row justify-center mt-4 mb-4",children:[e.jsx("button",{className:`px-4 py-2 rounded-l-md ${s==="wysiwyg"?"bg-gray-400":"bg-gray-100"}`,onClick:()=>x("wysiwyg"),children:"WYSIWYG"}),e.jsx("button",{className:`px-4 py-2 rounded-r-md ${s==="code"?"bg-gray-400":"bg-gray-100"}`,onClick:()=>x("code"),children:"Code"})]}),e.jsx("div",{style:{display:s==="wysiwyg"?"block":"none"},children:e.jsx(F,{tinymceScriptSrc:window.location.origin+"/tinymce/tinymce.min.js",onInit:(t,d)=>l.current=d,onEditorChange:(t,d)=>{c(t)},init:{height:500,menubar:!1,plugins:["advlist","autolink","lists","link","image","charmap","preview","anchor","searchreplace","visualblocks","code","fullscreen","insertdatetime","media","table","code","help","wordcount"],toolbar:"undo redo | blocks | bold italic forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help",content_style:"body { font-family:Helvetica,Arial,sans-serif; font-size:14px }"}})}),s==="code"&&e.jsx(_,{height:"30vh",language:"html",theme:"vs-dark",value:o||"",options:{minimap:{enabled:!1}},onChange:(t,d)=>{c(t)}}),e.jsx("div",{className:E("mt-3 self-end flex gap-2 justify-end",{"flex-col mt-4":p}),children:e.jsxs("button",{className:"p-2 bg-green-600 shadow-box hover:shadow-boxhvr text-lg text-black",onClick:t=>{t.preventDefault(),S()},children:[g=="en"&&e.jsx(e.Fragment,{children:"Save"}),g=="ro"&&e.jsx(e.Fragment,{children:"Salvează"})]})})]});return p?e.jsx(e.Fragment,{children:e.jsx(m.Root,{show:!0,as:"div",children:e.jsx(b,{as:"div",className:"fixed z-10 inset-0 overflow-y-auto",open:!0,onClose:t=>{u()},children:e.jsxs("div",{className:"flex items-start justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0",children:[e.jsx(m.Child,{as:v.Fragment,enter:"ease-out duration-300",enterFrom:"opacity-0",enterTo:"opacity-100",leave:"ease-in duration-200",leaveFrom:"opacity-100",leaveTo:"opacity-0",children:e.jsx(b.Overlay,{className:"fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"})}),e.jsx("span",{className:"hidden sm:inline-block sm:align-middle sm:h-screen","aria-hidden":"true",children:"​"}),e.jsx(m.Child,{as:v.Fragment,enter:"ease-out duration-300",enterFrom:"opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95",enterTo:"opacity-100 translate-y-0 sm:scale-100",leave:"ease-in duration-200",leaveFrom:"opacity-100 translate-y-0 sm:scale-100",leaveTo:"opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95",children:e.jsx("div",{className:`
                            inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left shadow-xl transform
                            transition-all sm:mt-32 sm:align-middle sm:w-full sm:p-6 relative
                            md:max-w-md lg:max-w-lg xl:max-w-2xl
                            self-end mb-12
                            `,children:f})})]})})})}):f};export{z as default};
