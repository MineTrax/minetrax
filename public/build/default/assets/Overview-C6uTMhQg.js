import{o as p,d as f,a as t,r as k,i as B,j as A,b as o,u as s,f as d,t as e,J as I,p as N,Q as U,c as S,w as M,_ as R,e as V}from"./app-BDqFx3nJ.js";import{r as T,_ as q,a as Y}from"./AdminLayout-CH0XHXWF.js";import{_ as H}from"./ServerIntelServerSelector-BqVoYP3f.js";import{_ as z}from"./Chart-Cvwgk-B4.js";import{i as E}from"./index.es-DO5oTivk.js";import{s as v,a as F,u as P}from"./AppLayout-_QfSG3gX.js";import{e as g,s as b,a as O,b as L,c as K}from"./subMonths-raCiKyNE.js";import{a as W,r as G}from"./UserGroupIcon-BGNFfWO8.js";import{I as y}from"./Icon-Bn9n1IU_.js";import{m as x}from"./millify-BN40_pGg.js";import{L as J}from"./LoadingSpinner-CFw8TACB.js";import{_ as Q}from"./AlertCard-B2TtXSIh.js";import"./keyboard-B8FqkKla.js";import"./open-closed-j5RvPIdu.js";import"./useAuthorizable-CnlAWZWI.js";import"./CloudArrowDownIcon-qQUAiaKd.js";import"./index-DLGdJHO1.js";import"./XSelect-B83QMKfN.js";import"./_plugin-vue_export-helper-DlAUqK2U.js";import"./XMarkIcon-HHXPXwYa.js";function $(r,l){return p(),f("svg",{xmlns:"http://www.w3.org/2000/svg",fill:"none",viewBox:"0 0 24 24","stroke-width":"1.5",stroke:"currentColor","aria-hidden":"true","data-slot":"icon"},[t("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"})])}function C(r,l){return p(),f("svg",{xmlns:"http://www.w3.org/2000/svg",fill:"none",viewBox:"0 0 24 24","stroke-width":"1.5",stroke:"currentColor","aria-hidden":"true","data-slot":"icon"},[t("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z"}),t("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M12 18a3.75 3.75 0 0 0 .495-7.468 5.99 5.99 0 0 0-1.925 3.547 5.975 5.975 0 0 1-2.133-1.001A3.75 3.75 0 0 0 12 18Z"})])}function X(r,l){return p(),f("svg",{xmlns:"http://www.w3.org/2000/svg",fill:"none",viewBox:"0 0 24 24","stroke-width":"1.5",stroke:"currentColor","aria-hidden":"true","data-slot":"icon"},[t("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M5.636 5.636a9 9 0 1 0 12.728 0M12 3v9"})])}function tt(r,l){return p(),f("svg",{xmlns:"http://www.w3.org/2000/svg",fill:"none",viewBox:"0 0 24 24","stroke-width":"1.5",stroke:"currentColor","aria-hidden":"true","data-slot":"icon"},[t("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z"}),t("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z"})])}const st={class:"w-full h-full p-3 space-y-8 bg-white rounded shadow dark:bg-cool-gray-800"},et={class:"flex justify-between"},at={class:"font-extrabold text-gray-800 dark:text-gray-200 flex items-center"},lt={__name:"ServerOnlineActivityOverTimeMetricBox",props:{servers:{type:Array,required:!1}},setup(r){const{__:l}=N();let n=k({}),a=k(null),h=k(!0),u=k(null);const c=r,w=B(()=>u.value===null||u.value.length<=0||u.value[0]===null||u.value[1]===null);async function D(){h.value=!0;let i={};w.value||(i.from_date=u.value[0],i.to_date=u.value[1]),c.servers&&c.servers.length>0&&(i.servers=c.servers);const _=await U.get(route("admin.graph.server-online-activity",i));h.value=!1,a.value=_.data,n.value={tooltip:{trigger:"axis",position:function(m){return[m[0],"10%"]}},legend:{},toolbox:{feature:{dataZoom:{yAxisIndex:"none"},restore:{},saveAsImage:{}}},xAxis:{type:"time"},yAxis:{type:"value",boundaryGap:[0,"10%"]},dataZoom:[{type:"inside",start:90,end:100,zoomLock:!0},{start:90,end:100}],series:a.value.labels.map((m,j)=>({name:m,type:"line",smooth:!0,symbol:"none",seriesLayoutBy:"column",encode:{y:j+1},emphasis:{focus:"series"}})),dataset:{source:a.value.data}}}A(async()=>{await D()});const Z=[{text:l("Today"),onClick(){const i=new Date;return[v(i),g(i)]}},{text:l("Yesterday"),onClick(){const i=b(new Date,1);return[v(i),g(i)]}},{text:l("Last 7 Days"),onClick(){const i=new Date,_=b(i,7);return[v(_),g(i)]}},{text:l("Last 30 Days"),onClick(){const i=new Date,_=b(i,30);return[v(_),g(i)]}},{text:l("This Month"),onClick(){const i=new Date,_=O(i);return[v(_),g(i)]}},{text:l("Last Month"),onClick(){const i=new Date,_=O(L(i,1)),m=K(L(i,1));return[v(_),g(m)]}},{text:l("This Year"),onClick(){const i=new Date,_=F(i);return[v(_),g(i)]}}];return(i,_)=>(p(),f("div",st,[t("div",et,[t("h3",at,[o(s(W),{class:"w-6 mr-1"}),d(" "+e(s(l)("Server Online Activity")),1)]),o(s(E),{value:s(u),"onUpdate:value":_[0]||(_[0]=m=>I(u)?u.value=m:u=m),type:"date",range:"",placeholder:s(l)("View for date range"),"input-class":"block w-full p-2 text-sm border-gray-300 rounded-md focus:border-light-blue-300 focus:ring focus:ring-light-blue-200 focus:ring-opacity-50 dark:bg-cool-gray-900 dark:text-gray-300 dark:border-gray-900",shortcuts:Z,onChange:_[1]||(_[1]=m=>D())},null,8,["value","placeholder"])]),o(z,{options:s(n),height:"350px",loading:s(h),autoresize:!0},null,8,["options","loading"])]))}},ot={class:"bg-white dark:bg-cool-gray-800 rounded w-full h-full p-3 shadow"},rt={class:"font-extrabold text-gray-800 dark:text-gray-200 flex mt-2 items-center"},nt={key:0,class:"h-80 flex justify-center items-center"},it={key:1,class:"m-0 p-0"},dt={class:"table-auto min-w-full text-sm dark:text-gray-300 text-gray-700"},ct={class:"border-b dark:border-gray-700"},_t={scope:"col",class:"text-left p-2"},ut={scope:"col",class:"text-left p-2"},ht={scope:"col",class:"text-left p-2"},pt={scope:"col",class:"text-left p-2"},mt={class:"p-2 flex"},yt={class:"p-2"},ft={class:"p-2"},vt={class:"p-2"},gt={class:"p-2"},xt={class:"p-2 flex"},wt={class:"p-2"},kt={class:"p-2"},bt={class:"p-2"},$t={class:"p-2"},Dt={class:"p-2 flex"},St={class:"p-2"},Mt={class:"p-2"},Tt={class:"p-2"},Ot={class:"p-2"},Lt={class:"p-2 flex"},Ct={class:"p-2"},At={class:"p-2"},Pt={class:"p-2"},Zt={class:"p-2"},jt={class:"p-2 flex"},Bt={class:"p-2"},It={class:"p-2"},Nt={class:"p-2"},Ut={class:"p-2"},Rt={class:"p-2 flex"},Vt={class:"p-2"},qt={class:"p-2"},Yt={class:"p-2"},Ht={class:"p-2"},zt={class:"p-2 flex"},Et={class:"p-2"},Ft={class:"p-2"},Kt={class:"p-2"},Wt={class:"p-2"},Gt={class:"p-2 flex"},Jt={class:"p-2"},Qt={class:"p-2"},Xt={class:"p-2"},ts={class:"p-2"},ss={class:"p-2 flex"},es={class:"p-2"},as={class:"p-2"},ls={class:"p-2"},os={class:"p-2"},rs={__name:"ServerIntelOverviewNumbersBox",props:{servers:{type:Array,required:!1}},setup(r){const{secondsToHMS:l}=P(),n=r;let a=k(null),h=k(!0);async function u(){h.value=!0;let c={};n.servers&&n.servers.length>0&&(c.servers=n.servers);const w=await axios.get(route("admin.intel.server.index.numbers",c));h.value=!1,a.value=w.data.numbers}return A(async()=>{await u()}),(c,w)=>(p(),f("div",ot,[t("h3",rt,[o(s(tt),{class:"w-6 mr-1"}),d(" "+e(c.__("Numbers")),1)]),s(h)?(p(),f("div",nt,[o(J,{loading:s(h)},null,8,["loading"])])):(p(),f("div",it,[t("table",dt,[t("thead",ct,[t("tr",null,[w[0]||(w[0]=t("th",{scope:"col",class:"text-left p-2"},null,-1)),t("th",_t,e(c.__("Last 24 hours")),1),t("th",ut,e(c.__("Last 7 days")),1),t("th",ht,e(c.__("Last 30 days")),1),t("th",pt,e(c.__("All Time")),1)])]),t("tbody",null,[t("tr",null,[t("td",mt,[o(s(G),{class:"w-5 text-indigo-500 mr-1"}),d(" "+e(c.__("Total Players")),1)]),t("td",yt,e(s(a).total_players.last_24h),1),t("td",ft,e(s(a).total_players.last_7days),1),t("td",vt,e(s(a).total_players.last_30days),1),t("td",gt,e(s(a).total_players.all_time),1)]),t("tr",null,[t("td",xt,[o(s($),{class:"w-5 text-green-500 mr-1"}),d(" "+e(c.__("Total Play Time")),1)]),t("td",wt,e(s(l)(s(a).total_playtime.last_24h,!0)),1),t("td",kt,e(s(l)(s(a).total_playtime.last_7days,!0)),1),t("td",bt,e(s(l)(s(a).total_playtime.last_30days,!0)),1),t("td",$t,e(s(l)(s(a).total_playtime.all_time,!0)),1)]),t("tr",null,[t("td",Dt,[o(s($),{class:"w-5 text-red-500 mr-1"}),d(" "+e(c.__("Total Afk Time")),1)]),t("td",St,e(s(l)(s(a).total_afktime.last_24h,!0)),1),t("td",Mt,e(s(l)(s(a).total_afktime.last_7days,!0)),1),t("td",Tt,e(s(l)(s(a).total_afktime.last_30days,!0)),1),t("td",Ot,e(s(l)(s(a).total_afktime.all_time,!0)),1)]),t("tr",null,[t("td",Lt,[o(s(T),{class:"w-5 text-green-500 mr-1"}),d(" "+e(c.__("Total Sessions")),1)]),t("td",Ct,e(s(a).total_sessions.last_24h),1),t("td",At,e(s(a).total_sessions.last_7days),1),t("td",Pt,e(s(a).total_sessions.last_30days),1),t("td",Zt,e(s(a).total_sessions.all_time),1)]),t("tr",null,[t("td",jt,[o(s(T),{class:"w-5 text-lime-500 mr-1"}),d(" "+e(c.__("Avg Session / Player")),1)]),t("td",Bt,e(s(x)(s(a).avg_session_per_player.last_24h,{precision:2})),1),t("td",It,e(s(x)(s(a).avg_session_per_player.last_7days,{precision:2})),1),t("td",Nt,e(s(x)(s(a).avg_session_per_player.last_30days,{precision:2})),1),t("td",Ut,e(s(x)(s(a).avg_session_per_player.all_time,{precision:2})),1)]),t("tr",null,[t("td",Rt,[o(s($),{class:"w-5 text-amber-500 mr-1"}),d(" "+e(c.__("Avg Session Playtime")),1)]),t("td",Vt,e(s(l)(s(a).avg_session_playtime.last_24h,!0)),1),t("td",qt,e(s(l)(s(a).avg_session_playtime.last_7days,!0)),1),t("td",Yt,e(s(l)(s(a).avg_session_playtime.last_30days,!0)),1),t("td",Ht,e(s(l)(s(a).avg_session_playtime.all_time,!0)),1)]),t("tr",null,[t("td",zt,[o(s(C),{class:"w-5 text-pink-500 mr-1"}),d(" "+e(c.__("Total Player Kills")),1)]),t("td",Et,e(s(a).total_player_kills.last_24h),1),t("td",Ft,e(s(a).total_player_kills.last_7days),1),t("td",Kt,e(s(a).total_player_kills.last_30days),1),t("td",Wt,e(s(a).total_player_kills.all_time),1)]),t("tr",null,[t("td",Gt,[o(s(C),{class:"w-5 text-green-500 mr-1"}),d(" "+e(c.__("Total Mob Kills")),1)]),t("td",Jt,e(s(a).total_mob_kills.last_24h),1),t("td",Qt,e(s(a).total_mob_kills.last_7days),1),t("td",Xt,e(s(a).total_mob_kills.last_30days),1),t("td",ts,e(s(a).total_mob_kills.all_time),1)]),t("tr",null,[t("td",ss,[o(y,{name:"skull-outline",class:"w-5 text-red-500 mr-1"}),d(" "+e(c.__("Total Deaths")),1)]),t("td",es,e(s(a).total_deaths.last_24h),1),t("td",as,e(s(a).total_deaths.last_7days),1),t("td",ls,e(s(a).total_deaths.last_30days),1),t("td",os,e(s(a).total_deaths.all_time),1)])])])]))]))}},ns={class:"p-4 mx-auto space-y-4 px-10"},is={id:"row1"},ds={id:"row2",class:"flex justify-between flex-1 space-x-4"},cs={class:"bg-white dark:bg-cool-gray-800 rounded w-full shadow basis-2/6 p-3"},_s={class:"font-extrabold text-gray-800 dark:text-gray-200 flex mt-2 items-center"},us={class:"flex flex-col text-sm mt-5"},hs={class:"table-auto min-w-full dark:text-gray-300 text-gray-700"},ps={class:"py-2 flex"},ms={class:"p-2 text-right"},ys={class:"py-2 flex"},fs={class:"p-2 text-right"},vs={class:"py-2 flex"},gs={class:"p-2 text-right"},xs={class:"py-2 flex"},ws={class:"p-2 text-right"},ks={class:"py-2 flex"},bs={class:"p-2 text-right"},$s={class:"py-2 flex"},Ds={class:"p-2 text-right"},Ss={class:"py-2 flex"},Ms={class:"p-2 text-right"},Ts={class:"py-2 flex"},Os={class:"p-2 text-right"},Ls={class:"py-2 flex"},Cs={class:"p-2 pb-5 text-right"},Qs={__name:"Overview",props:{serverList:{type:Object},filters:{type:Object},last7DaysStats:{type:Object},noIntelForOverWeek:{type:Boolean}},setup(r){const{secondsToHMS:l}=P();return(n,a)=>(p(),S(q,null,{default:M(()=>{var h,u;return[o(R,{title:n.__("Overview - ServerIntel")},null,8,["title"]),t("div",ns,[o(H,{title:n.__("Server Overview"),"server-list":r.serverList,filters:r.filters},null,8,["title","server-list","filters"]),r.noIntelForOverWeek?(p(),S(Q,{key:0,"title-class":"flex items-center","text-color":"text-orange-800 dark:text-orange-500","border-color":"border-orange-500"},{default:M(()=>[d(e(n.__("Server haven't sent Intel data for over 7 days.")),1)]),_:1})):V("",!0),t("div",is,[o(lt,{servers:(h=r.filters)==null?void 0:h.servers},null,8,["servers"])]),t("div",ds,[o(rs,{servers:(u=r.filters)==null?void 0:u.servers},null,8,["servers"]),t("div",cs,[t("h3",_s,[o(s(Y),{class:"w-6 mr-1"}),d(" "+e(n.__("Last 7 Days")),1)]),t("div",us,[t("table",hs,[t("tbody",null,[t("tr",null,[t("td",ps,[o(y,{name:"users",class:"w-5 text-indigo-500 mr-1"}),d(" "+e(n.__("Unique Players")),1)]),t("td",ms,e(r.last7DaysStats.uniquePlayersCount),1)]),t("tr",null,[t("td",ys,[o(y,{name:"users",class:"w-5 text-lime-500 mr-1"}),d(" "+e(n.__("New Players")),1)]),t("td",fs,e(r.last7DaysStats.newPlayersCount),1)]),t("tr",null,[t("td",vs,[o(y,{name:"users",class:"w-5 text-gray-500 mr-1"}),d(" "+e(n.__("Old Players")),1)]),t("td",gs,e(r.last7DaysStats.oldPlayersCount),1)]),t("tr",null,[t("td",xs,[o(y,{name:"users",class:"w-5 text-green-500 mr-1"}),d(" "+e(n.__("Peak Online Players")),1)]),t("td",ws,e(r.last7DaysStats.peekOnlinePlayersCount??n.__("none")),1)]),t("tr",null,[t("td",ks,[o(y,{name:"joystick",class:"w-5 text-lime-500 mr-1"}),d(" "+e(n.__("Avg TPS")),1)]),t("td",bs,e(r.last7DaysStats.averageTps?s(x)(r.last7DaysStats.averageTps,{precision:2}):n.__("none")),1)]),t("tr",null,[t("td",$s,[o(y,{name:"joystick",class:"w-5 text-red-500 mr-1"}),d(" "+e(n.__("Lowest TPS")),1)]),t("td",Ds,e(r.last7DaysStats.lowestTps?s(x)(r.last7DaysStats.lowestTps,{precision:2}):n.__("none")),1)]),t("tr",null,[t("td",Ss,[o(y,{name:"cpu",class:"w-5 text-blue-500 mr-1"}),d(" "+e(n.__("Avg CPU Load")),1)]),t("td",Ms,e(r.last7DaysStats.averageCpuLoad?s(x)(r.last7DaysStats.averageCpuLoad,{precision:2}):n.__("none")),1)]),t("tr",null,[t("td",Ts,[o(s(X),{class:"w-5 text-green-500 mr-1"}),d(" "+e(n.__("Longest Uptime")),1)]),t("td",Os,e(r.last7DaysStats.longestUptime?s(l)(r.last7DaysStats.longestUptime):n.__("none")),1)]),t("tr",null,[t("td",Ls,[o(y,{name:"toggle-off",class:"w-5 text-red-500 mr-1"}),d(" "+e(n.__("Restarts")),1)]),t("td",Cs,e(r.last7DaysStats.noOfRestarts??n.__("none")),1)])])])])])])])]}),_:1}))}};export{Qs as default};