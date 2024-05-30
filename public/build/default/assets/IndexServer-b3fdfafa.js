import{_ as q}from"./AdminLayout-39a088b1.js";import{u as V}from"./useAuthorizable-4eb80770.js";import{u as $}from"./AppLayout-61780049.js";import{h as B,s as L,l as C,q as j,o as n,c as x,w as l,k as Q,b as d,u as e,a as s,f as g,t as a,e as y,j as v,d as f,a2 as D}from"./app-36941795.js";import{D as k,_ as W}from"./DtRowItem-847b574c.js";import{u as M}from"./index-fd5cea4d.js";import{_ as O}from"./AlertCard-fc880be4.js";import{r as R}from"./EyeIcon-ca3942bc.js";import{r as Y}from"./PencilSquareIcon-7bd8ec4a.js";import{r as z}from"./TrashIcon-51b0a2c1.js";import"./use-resolve-button-type-3019aa78.js";import"./open-closed-d7f55052.js";import"./CloudArrowDownIcon-cef84ce3.js";import"./_plugin-vue_export-helper-c27b6911.js";import"./Icon-64bdcc8f.js";import"./vue-multiselect.esm-cd21c9ae.js";import"./XMarkIcon-3276e35f.js";import"./hidden-15c50a1b.js";const F={class:"px-10 py-8 mx-auto text-gray-400"},H={class:"italic text-gray-400 dark:text-gray-500"},G={class:"flex justify-between mb-8"},J={class:"font-bold text-3xl text-gray-500 dark:text-gray-300 flex items-center"},K={class:"flex"},U={class:"hidden md:inline"},X={class:"px-4 py-4 text-sm text-center text-gray-800 whitespace-nowrap dark:text-gray-200 font-bold"},Z={class:"flex items-center"},ee={class:"flex-shrink-0 h-10 w-10"},te=["src"],re={class:"ml-4"},ae={class:"text-sm font-medium text-gray-900 dark:text-gray-300"},se={class:"text-sm text-gray-500 dark:text-gray-400"},oe={class:"text-sm text-gray-900 dark:text-gray-300"},ne={class:"text-sm text-gray-500 dark:text-gray-400"},le={class:"px-4 space-y-1 whitespace-nowrap"},ie={class:"flex"},de={key:0,class:"px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-opacity-25 dark:text-gray-400"},ce={key:1,class:"px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-opacity-25 dark:text-green-400"},ue={key:2,class:"px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-opacity-25 dark:text-red-400"},xe={class:"flex"},ye={key:0,class:"px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-opacity-25 dark:text-gray-400"},fe={key:1,class:"px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-opacity-25 dark:text-green-400"},pe={key:2,class:"px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-opacity-25 dark:text-red-400"},he={key:3},ge=["title"],be={class:"px-6 py-4 space-x-2 text-sm font-medium text-right whitespace-nowrap"},$e={__name:"IndexServer",props:{canCreateBungeeServer:Boolean,servers:Object,filters:Object},setup(b){const I=b,{can:_}=V(),{__:t}=Q(),{formatTimeAgoToNow:E,formatToDayDateString:N}=$(),A=M("show-bungee-server-missing-alert",!0),P=[{key:"id",label:t("ID"),sortable:!0,class:"text-center font-bold"},{key:"name",sortable:!0,label:t("Name"),filterable:{type:"text"}},{key:"ip_address",label:t("IP:Port"),sortable:!0},{key:"type",label:t("Type"),sortable:!0},{key:"status",sortable:!1,label:t("Status")},{key:"minecraft_version",sortable:!0,label:t("Version")},{key:"created_at",sortable:!0,label:t("Added")},{key:"actions",label:t("Actions"),sortable:!1,class:"w-1/12 text-right"}],T=B({}),w=B({});L(()=>{I.servers.data.forEach(o=>{axios.get(route("server.ping.get",o.id)).then(c=>{D(()=>{var p,i,u,S;T[o.id]={online:1,players:(i=(p=c.data)==null?void 0:p.players)==null?void 0:i.online,max:(S=(u=c.data)==null?void 0:u.players)==null?void 0:S.max}})}).catch(()=>{D(()=>T[o.id]={online:0})}),o.webquery_port?axios.get(route("server.webquery.get",o.id)).then(c=>{D(()=>{var p,i;w[o.id]={online:1,players:(p=c.data)==null?void 0:p.online_players,max:(i=c.data)==null?void 0:i.max_players}})}).catch(()=>{D(()=>w[o.id]={online:0})}):w[o.id]={online:-1}})});function m(o){return T[o]||null}function h(o){return w[o]||null}return(o,c)=>{const p=C("app-head"),i=C("InertiaLink"),u=j("tippy"),S=j("confirm");return n(),x(q,null,{default:l(()=>[d(p,{title:e(t)("Manage Servers")},null,8,["title"]),s("div",F,[b.canCreateBungeeServer&&e(A)?(n(),x(O,{key:0,"close-button":!0,"text-color":"text-light-blue-800 dark:text-light-blue-500","border-color":"border-light-blue-500",onClose:c[0]||(c[0]=r=>A.value=!1)},{body:l(()=>[g(a(e(t)("Adding Proxy server is recommended if you are adding more than 1 server to MineTrax, so that it can be used to show whole network player count in homepage."))+" ",1),s("p",H,a(e(t)("Note: This is not an error. You can safely ignore this message if you don't want to add proxy server.")),1)]),default:l(()=>[g(a(e(t)("You don't have Bungee/Proxy Server Added!"))+" ",1)]),_:1})):y("",!0),s("div",G,[s("h1",J,[g(a(e(t)("Servers"))+" ",1),e(_)("create servers")?v((n(),x(i,{key:0,as:"button",title:e(t)("MineTrax automatically sync player stats every hour or as per schedule define in .env file. Click here to force a sync now."),href:o.route("admin.server.force-scan"),method:"post",class:"ml-2 inline-flex items-center px-4 py-2 border-2 border-red-600 rounded-md font-semibold text-xs text-red-600 uppercase tracking-widest focus:outline-none transition ease-in-out duration-150 dark:text-red-500 dark:border-red-700 hover:border-red-300 dark:hover:border-red-500 hover:bg-red-500 hover:text-white dark:hover:text-white"},{default:l(()=>[s("span",null,a(e(t)("Sync Player Statistics")),1)]),_:1},8,["title","href"])),[[u]]):y("",!0)]),s("div",K,[e(_)("create servers")?(n(),x(i,{key:0,href:o.route("admin.server.create"),class:"mr-1 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:shadow-outline-gray transition ease-in-out duration-150"},{default:l(()=>[s("span",null,a(e(t)("Add")),1),s("span",U," "+a(e(t)("Server")),1)]),_:1},8,["href"])):y("",!0),e(_)("create servers")&&b.canCreateBungeeServer?(n(),x(i,{key:1,href:o.route("admin.server.create-bungee"),class:"inline-flex items-center px-4 py-2 border-2 border-gray-800 rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest focus:outline-none focus:border-gray-900 transition ease-in-out duration-150 dark:text-gray-300 dark:border-gray-700 dark:hover:border-gray-500"},{default:l(()=>[s("span",null,a(e(t)("Add Proxy Server")),1)]),_:1},8,["href"])):y("",!0)])]),d(W,{class:"bg-white rounded shadow dark:bg-gray-800",header:P,data:b.servers,filters:b.filters},{default:l(({item:r})=>[s("td",X,a(r.id),1),d(k,null,{default:l(()=>[s("div",Z,[s("div",ee,[s("img",{class:"h-10 w-10",src:r.country.photo_path,alt:"Country Flag"},null,8,te)]),s("div",re,[s("div",ae,a(r.name),1),s("div",se,a(r.hostname),1)])])]),_:2},1024),d(k,null,{default:l(()=>[s("div",oe,a(r.ip_address)+" : "+a(r.join_port),1),s("div",ne,a(e(t)("WebQuery: :webquery_port",{webquery_port:r.webquery_port||e(t)("not set")})),1)]),_:2},1024),d(k,null,{default:l(()=>[g(a(r.type.key),1)]),_:2},1024),s("td",le,[s("div",ie,[m(r.id)?m(r.id).online===1?(n(),f("span",ce,a(e(t)("Server"))+" 🟢 "+a(m(r.id).players+"/"+m(r.id).max),1)):m(r.id).online===0?(n(),f("span",ue,a(e(t)("Server"))+" 🔴 ",1)):y("",!0):(n(),f("span",de,a(e(t)("Loading"))+"...",1))]),s("div",xe,[h(r.id)?h(r.id).online===1?(n(),f("span",fe,a(e(t)("WebQuery"))+" 🟢 "+a(h(r.id).players+"/"+h(r.id).max),1)):h(r.id).online===0?(n(),f("span",pe,a(e(t)("WebQuery"))+" 🔴 ",1)):h(r.id).online===-1?(n(),f("span",he)):y("",!0):(n(),f("span",ye,a(e(t)("Loading"))+"...",1))])]),d(k,null,{default:l(()=>[g(a(r.minecraft_version),1)]),_:2},1024),d(k,null,{default:l(()=>[v((n(),f("span",{title:e(N)(r.created_at)},[g(a(e(E)(r.created_at)),1)],8,ge)),[[u]])]),_:2},1024),s("td",be,[v((n(),x(i,{as:"a",title:e(t)("View Server Intel"),href:o.route("admin.server.show",r.id),class:"inline-flex items-center justify-center text-blue-500 hover:text-blue-800"},{default:l(()=>[d(e(R),{class:"inline-block w-5 h-5"})]),_:2},1032,["title","href"])),[[u]]),e(_)("update servers")?v((n(),x(i,{key:0,as:"a",href:o.route("admin.server.edit",r.id),class:"inline-flex items-center justify-center text-yellow-600 dark:text-yellow-500 hover:text-yellow-800 dark:hover:text-yellow-800",title:e(t)("Edit Server")},{default:l(()=>[d(e(Y),{class:"inline-block w-5 h-5"})]),_:2},1032,["href","title"])),[[u]]):y("",!0),e(_)("delete servers")?v((n(),x(i,{key:1,as:"button",method:"DELETE",href:o.route("admin.server.delete",r.id),class:"inline-flex items-center justify-center text-red-600 hover:text-red-900 focus:outline-none",title:e(t)("Delete Server")},{default:l(()=>[d(e(z),{class:"inline-block w-5 h-5"})]),_:2},1032,["href","title"])),[[S,{title:"Delete Server?",message:"Are you sure you want to delete this Server permanently? Deleting a Server will also delete all of its associated data including all of its Player & Server Intel data. This action cannot be undone."}],[u]]):y("",!0)])]),_:1},8,["data","filters"])])]),_:1})}}};export{$e as default};