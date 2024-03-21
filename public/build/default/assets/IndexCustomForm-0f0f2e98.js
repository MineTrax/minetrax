import{u as w,A as k,I as v}from"./AppLayout-2410b8b9.js";import{q as D,o,c,w as s,k as T,b as a,u as e,_ as j,a as r,t as i,Z as h,f as m,j as d,d as b,e as x}from"./app-2128deda.js";import{D as u,_ as A}from"./DtRowItem-f26e307b.js";import{r as N}from"./UserIcon-f77750a9.js";import{r as S}from"./EyeIcon-c6cdbf70.js";import"./_plugin-vue_export-helper-c27b6911.js";import"./useAuthorizable-dcdf76ce.js";import"./XMarkIcon-7c97216f.js";const B={class:"py-4 px-2 md:py-12 md:px-10 max-w-7xl mx-auto"},C={class:"flex justify-between mb-8"},I={class:"font-bold text-3xl text-gray-500 dark:text-gray-300"},O={class:"flex"},V={class:"flex flex-col md:flex-row md:space-x-4"},F=["title"],R=["title"],$={class:"px-6 py-4 space-x-2 text-sm font-medium text-right whitespace-nowrap"},K={__name:"IndexCustomForm",props:{forms:Object,filters:Object},setup(_){const{__:t}=T(),{formatTimeAgoToNow:p,formatToDayDateString:g}=w(),y=[{key:"title",sortable:!0,label:t("Title")},{key:"flags",sortable:!1,label:"",class:"w-1/12 text-right"},{key:"status",label:t("Status"),sortable:!0,class:"w-1/12 hidden text-right md:table-cell"},{key:"created_at",label:t("Added"),sortable:!0,class:"w-1/12 hidden text-right md:table-cell"},{key:"actions",label:t("Actions"),sortable:!1,class:"w-1/12 text-right"}];return(f,H)=>{const n=D("tippy");return o(),c(k,null,{default:s(()=>[a(j,{title:e(t)("Custom Forms")},null,8,["title"]),r("div",B,[r("div",C,[r("h1",I,i(e(t)("Forms")),1),r("div",O,[a(e(h),{href:f.route("home"),class:"inline-flex items-center px-4 py-2 bg-gray-400 dark:bg-cool-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 active:bg-gray-600 focus:outline-none focus:border-gray-500 focus:shadow-outline-gray transition ease-in-out duration-150"},{default:s(()=>[r("span",null,i(e(t)("Homepage")),1)]),_:1},8,["href"])])]),r("div",V,[a(A,{class:"bg-white rounded shadow dark:bg-gray-800 w-full",header:y,data:_.forms,filters:_.filters},{default:s(({item:l})=>[a(u,null,{default:s(()=>[a(e(h),{href:f.route("custom-form.show",l.slug),class:"hover:text-light-blue-400 hover:underline"},{default:s(()=>[m(i(l.title),1)]),_:2},1032,["href"])]),_:2},1024),a(u,{class:"text-right"},{default:s(()=>[d((o(),b("span",{title:e(t)("Registered Users Only")},[l.can_create_submission==="auth"?(o(),c(e(N),{key:0,class:"inline-block w-4 h-4 text-light-blue-400"})):x("",!0)],8,F)),[[n]]),l.can_create_submission==="staff"?d((o(),c(v,{key:0,name:"shield-check-fill",title:e(t)("Staff Only"),class:"inline mb-1 text-amber-400 h-4 fill-current focus:outline-none"},null,8,["title"])),[[n]]):x("",!0)]),_:2},1024),a(u,{class:"text-right hidden md:table-cell"},{default:s(()=>[m(i(l.status.value),1)]),_:2},1024),a(u,{class:"whitespace-nowrap hidden md:table-cell text-right"},{default:s(()=>[d((o(),b("span",{title:e(g)(l.created_at)},[m(i(e(p)(l.created_at)),1)],8,R)),[[n]])]),_:2},1024),r("td",$,[d((o(),c(e(h),{as:"a",href:f.route("custom-form.show",l.slug),class:"inline-flex items-center justify-center text-light-blue-600 dark:text-light-blue-500 hover:text-light-blue-800 dark:hover:text-light-blue-800",title:e(t)("Show Details")},{default:s(()=>[a(e(S),{class:"inline-block w-5 h-5"})]),_:2},1032,["href","title"])),[[n]])])]),_:1},8,["data","filters"])])])]),_:1})}}};export{K as default};