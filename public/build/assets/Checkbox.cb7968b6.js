import{h as l,s as r,E as n,u as d,o as i,d as p,A as h}from"./app.50c0f8cb.js";const f=["value"],g={__name:"Checkbox",props:{checked:{type:[Array,Boolean],default:!1},value:{type:String,default:null}},emits:["update:checked"],setup(t,{emit:u}){const a=t,e=l({get(){return a.checked},set(o){u("update:checked",o)}});return(o,s)=>r((i(),p("input",{"onUpdate:modelValue":s[0]||(s[0]=c=>h(e)?e.value=c:null),type:"checkbox",value:t.value,class:"rounded border-gray-300 text-light-blue-500 shadow-sm focus:border-light-blue-300 focus:ring focus:ring-light-blue-200 focus:ring-opacity-50"},null,8,f)),[[n,d(e)]])}};export{g as _};