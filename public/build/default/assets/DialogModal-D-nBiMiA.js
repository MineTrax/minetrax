import{_ as i}from"./Modal-DLYNQJic.js";import{o as r,c as d,w as n,a as e,T as o}from"./app-BDqFx3nJ.js";const h={class:"px-6 py-4 dark:bg-gray-800 dark:text-gray-300"},m={class:"text-lg"},f={class:"mt-4"},x={class:"flex flex-row justify-end px-6 py-4 bg-gray-100 dark:bg-gray-800 text-right"},y={__name:"DialogModal",props:{show:{type:Boolean,default:!1},maxWidth:{type:String,default:"2xl"},closeable:{type:Boolean,default:!0}},emits:["close"],setup(t,{emit:a}){const l=a,c=()=>{l("close")};return(s,_)=>(r(),d(i,{show:t.show,"max-width":t.maxWidth,closeable:t.closeable,onClose:c},{default:n(()=>[e("div",h,[e("div",m,[o(s.$slots,"title")]),e("div",f,[o(s.$slots,"content")])]),e("div",x,[o(s.$slots,"footer")])]),_:3},8,["show","max-width","closeable"]))}};export{y as _};