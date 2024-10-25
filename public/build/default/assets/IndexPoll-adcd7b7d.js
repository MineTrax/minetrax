import{_ as A}from"./AdminLayout-2ffdcf09.js";import{u as C}from"./useAuthorizable-db63a772.js";import{u as I}from"./AppLayout-180f66c1.js";import{o,d as i,a as n,l as w,q as v,c,w as r,k as B,b as l,u as e,t as a,e as y,g as L,F as T,j as u,f as g}from"./app-d03084a3.js";import{D as p,_ as E}from"./DtRowItem-d6e395fe.js";import{I as D}from"./Icon-09cf2959.js";import{r as V}from"./LockOpenIcon-827abb95.js";import{r as $}from"./TrashIcon-50fe1ad0.js";import"./use-resolve-button-type-d5bb55c5.js";import"./open-closed-db295aa4.js";import"./CloudArrowDownIcon-c01292c4.js";import"./index-dedae3a2.js";import"./_plugin-vue_export-helper-c27b6911.js";import"./vue-multiselect.esm-733a639e.js";import"./XMarkIcon-63f73fcf.js";import"./hidden-44eecd86.js";function q(m,_){return o(),i("svg",{xmlns:"http://www.w3.org/2000/svg",fill:"none",viewBox:"0 0 24 24","stroke-width":"1.5",stroke:"currentColor","aria-hidden":"true"},[n("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"})])}const M={class:"px-10 py-8 mx-auto text-gray-400"},O={class:"flex justify-between mb-4"},S={class:"text-3xl font-bold text-gray-500 dark:text-gray-300"},z={class:"flex"},F={class:"hidden md:inline"},H={class:"px-4 py-4 text-sm font-medium text-center text-gray-800 whitespace-nowrap dark:text-gray-200"},R={class:"text-sm font-medium text-gray-900 dark:text-gray-300"},Q={class:"px-4 text-sm"},U={key:1,class:"italic text-gray-500"},G=["content"],J=["content"],K={key:1,class:"italic"},W=["content"],X={key:0},Y={key:1,class:"italic text-red-600"},Z={class:"px-6 py-4 space-x-2 text-sm font-medium text-right whitespace-nowrap"},me={__name:"IndexPoll",props:{polls:Object,filters:Object},setup(m){const{can:_}=C(),{__:t}=B(),{formatTimeAgoToNow:x,formatToDayDateString:k}=I(),N=[{key:"id",label:t("ID"),sortable:!0,class:"text-center"},{key:"question",sortable:!0,label:t("Question")},{key:"options",sortable:!1,label:t("Options")},{key:"started_at",sortable:!0,label:t("Started At")},{key:"closed_at",label:t("End At"),sortable:!0},{key:"created_at",label:t("Created At"),sortable:!0},{key:"created_by",label:t("Created By"),sortable:!0},{key:"is_closed",sortable:!0,label:t("Locked")},{key:"actions",label:t("Actions"),sortable:!1,class:"w-1/12 text-right"}];return(f,ee)=>{const j=w("app-head"),h=w("InertiaLink"),d=v("tippy"),P=v("confirm");return o(),c(A,null,{default:r(()=>[l(j,{title:e(t)("Manage Polls")},null,8,["title"]),n("div",M,[n("div",O,[n("h1",S,a(e(t)("Manage Polls")),1),n("div",z,[e(_)("create polls")?(o(),c(h,{key:0,href:f.route("admin.poll.create"),class:"inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-gray-800 border border-transparent rounded-md hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:shadow-outline-gray"},{default:r(()=>[n("span",null,a(e(t)("Create New")),1),n("span",F," "+a(e(t)("Poll")),1)]),_:1},8,["href"])):y("",!0)])]),l(E,{class:"bg-white rounded shadow dark:bg-gray-800",header:N,data:m.polls,filters:m.filters},{default:r(({item:s})=>[n("td",H,a(s.id),1),l(p,null,{default:r(()=>[n("div",R,a(s.question),1)]),_:2},1024),n("td",Q,[s.options.length>0?(o(!0),i(T,{key:0},L(s.options,b=>(o(),i("span",{key:b.id,class:"px-2 mr-1 mb-1 inline-flex text-xs leading-5 font-semibold rounded bg-gray-100 text-gray-800 dark:bg-cool-gray-700 dark:text-gray-300"},a(b.name),1))),128)):(o(),i("span",U,a(e(t)("No options.")),1))]),l(p,null,{default:r(()=>[u((o(),i("span",{class:"focus:outline-none",content:e(k)(s.started_at)},[g(a(e(x)(s.started_at)),1)],8,G)),[[d]])]),_:2},1024),l(p,null,{default:r(()=>[s.closed_at?u((o(),i("span",{key:0,class:"focus:outline-none",content:e(k)(s.closed_at)},[g(a(e(x)(s.closed_at)),1)],8,J)),[[d]]):(o(),i("span",K,a(e(t)("None")),1))]),_:2},1024),l(p,null,{default:r(()=>[u((o(),i("span",{class:"focus:outline-none",content:e(k)(s.created_at)},[g(a(e(x)(s.created_at)),1)],8,W)),[[d]])]),_:2},1024),l(p,null,{default:r(()=>[s.creator?(o(),i("span",X,a(s.creator.username),1)):(o(),i("span",Y,a(e(t)("None")),1))]),_:2},1024),l(p,null,{default:r(()=>[s.is_closed?(o(),c(D,{key:0,class:"text-green-500",name:"check-circle"})):(o(),c(D,{key:1,class:"text-red-500",name:"cross-circle"}))]),_:2},1024),n("td",Z,[e(_)("update polls")&&!s.is_closed?u((o(),c(h,{key:0,as:"button",method:"put",href:f.route("admin.poll.lock",s.id),class:"inline-flex items-center justify-center text-yellow-600 dark:text-yellow-500 hover:text-yellow-800 dark:hover:text-yellow-800",title:e(t)("Lock Poll")},{default:r(()=>[l(e(q),{class:"inline-block w-5 h-5"})]),_:2},1032,["href","title"])),[[d]]):y("",!0),e(_)("update polls")&&s.is_closed?u((o(),c(h,{key:1,as:"button",method:"put",href:f.route("admin.poll.unlock",s.id),class:"inline-flex items-center justify-center text-green-600 dark:text-green-500 hover:text-green-800 dark:hover:text-green-800",title:e(t)("Unlock Poll")},{default:r(()=>[l(e(V),{class:"inline-block w-5 h-5"})]),_:2},1032,["href","title"])),[[d]]):y("",!0),e(_)("delete polls")?u((o(),c(h,{key:2,as:"button",method:"DELETE",href:f.route("admin.poll.delete",s.id),class:"inline-flex items-center justify-center text-red-600 hover:text-red-900 focus:outline-none",title:e(t)("Delete Poll")},{default:r(()=>[l(e($),{class:"inline-block w-5 h-5"})]),_:2},1032,["href","title"])),[[P,{message:"Are you sure you want to delete this Poll permanently?"}],[d]]):y("",!0)])]),_:1},8,["data","filters"])])]),_:1})}}};export{me as default};