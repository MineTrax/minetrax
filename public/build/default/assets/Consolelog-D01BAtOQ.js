import{y as h,c as v,w as o,p as w,o as c,b as a,u as i,_ as x,a as s,t as l,m as p,d,f as _}from"./app-_ki-Ar5w.js";import{u as b}from"./AppLayout-eDmNAsPw.js";import{_ as g}from"./AdminLayout-BoKh1YjX.js";import{_ as k}from"./ServerIntelServerSelector-DbrxpuVJ.js";import{_ as D}from"./DataTable-SccKRBRi.js";import{D as m}from"./DtRowItem-CMMh99dr.js";import"./_plugin-vue_export-helper-DlAUqK2U.js";import"./Icon-C1ozQT9U.js";import"./useAuthorizable-1fkLwajf.js";import"./keyboard-BEzvmP1D.js";import"./open-closed-CrutJkQn.js";import"./CloudArrowDownIcon-BS1KhqYM.js";import"./index-CZsT1EYE.js";import"./XSelect-DNiWZdxS.js";import"./vue-multiselect.esm-BRZ6v-65.js";import"./XMarkIcon-CVj17J4w.js";import"./hidden-nRc2_VCu.js";const C={class:"p-4 mx-auto space-y-4 px-10"},S={class:"px-4 py-4 text-sm font-medium text-gray-800 whitespace-nowrap dark:text-gray-200"},T={class:"px-4"},$={class:"text-sm text-gray-800 dark:text-gray-200"},B={class:"whitespace-pre-wrap"},I=["title"],L=["title"],X={__name:"Consolelog",props:{serverList:{type:Object},filters:{type:Object},consoleHistory:{type:Object}},setup(r){const{__:e}=w(),{formatTimeAgoToNow:f,formatToDayDateString:u}=b(),y=[{key:"id",label:e("ID"),sortable:!0,class:"text-left"},{key:"data",label:e("Data"),sortable:!1},{key:"server_id",label:e("Server"),sortable:!0},{key:"created_at",label:e("Created"),sortable:!0}];return(N,j)=>{const n=h("tippy");return c(),v(g,null,{default:o(()=>[a(x,{title:i(e)("ConsoleLog - ServerIntel")},null,8,["title"]),s("div",C,[a(k,{title:i(e)("ConsoleLog"),"server-list":r.serverList,filters:r.filters},null,8,["title","server-list","filters"]),s("div",null,[a(D,{class:"bg-white rounded shadow dark:bg-gray-800",header:y,data:r.consoleHistory,filters:r.filters},{default:o(({item:t})=>[s("td",S,l(t.id),1),s("td",T,[s("div",$,[s("pre",B,l(t.data),1)])]),a(m,null,{default:o(()=>[p((c(),d("span",{class:"whitespace-nowrap",title:`Server ID: ${t.server.id}`},[_(l(t.server.name),1)],8,I)),[[n]])]),_:2},1024),a(m,null,{default:o(()=>[p((c(),d("span",{class:"whitespace-nowrap",title:i(u)(t.created_at)},[_(l(i(f)(t.created_at)),1)],8,L)),[[n]])]),_:2},1024)]),_:1},8,["data","filters"])])])]),_:1})}}};export{X as default};