import{_ as c}from"./Modal-zPf4Ty0C.js";import{o as r,c as m,w as d,a as t,T as o}from"./app-_ki-Ar5w.js";const h={class:"bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4"},f={class:"sm:flex sm:items-start"},x={class:"mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left"},u={class:"text-lg"},p={class:"mt-2"},w={class:"flex flex-row justify-end px-6 py-4 bg-gray-100 text-right"},v={__name:"ConfirmationModal",props:{show:{type:Boolean,default:!1},maxWidth:{type:String,default:"2xl"},closeable:{type:Boolean,default:!0}},emits:["close"],setup(s,{emit:a}){const i=a,n=()=>{i("close")};return(e,l)=>(r(),m(c,{show:s.show,"max-width":s.maxWidth,closeable:s.closeable,onClose:n},{default:d(()=>[t("div",h,[t("div",f,[l[0]||(l[0]=t("div",{class:"mx-auto shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10"},[t("svg",{class:"h-6 w-6 text-red-600",stroke:"currentColor",fill:"none",viewBox:"0 0 24 24"},[t("path",{"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"})])],-1)),t("div",x,[t("h3",u,[o(e.$slots,"title")]),t("div",p,[o(e.$slots,"content")])])])]),t("div",w,[o(e.$slots,"footer")])]),_:3},8,["show","max-width","closeable"]))}};export{v as _};