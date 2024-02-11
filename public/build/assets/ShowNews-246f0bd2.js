import{A as _,u}from"./AppLayout-bc98d524.js";import{N as w}from"./NewsBox-0916eb18.js";import{S as f}from"./ServerStatusBox-454b7aca.js";import{_ as x}from"./ShowNewsCard-811f1a94.js";import{_ as h,l as t,o as y,c as g,w as r,b as s,a as e,t as b}from"./app-219916e6.js";import"./useAuthorizable-f6fcaee4.js";import"./CopyToClipboard-da0e6391.js";import"./Comments-eff677de.js";import"./UserDisplayname-7135d858.js";const v={components:{ServerStatusBox:f,NewsBox:w,AppLayout:_,ShowNewsCard:x},props:{news:Object,newslist:Array},setup(){const{formatTimeAgoToNow:o,formatToDayDateString:a}=u();return{formatTimeAgoToNow:o,formatToDayDateString:a}}},N={class:"py-4 px-2 md:py-12 md:px-10 max-w-7xl mx-auto"},S={class:"flex justify-end mb-8"},k={class:"flex"},B={class:"flex flex-col md:flex-row md:space-x-4"},C={class:"-my-2 md:w-9/12 overflow-x-auto md:-mx-6 lg:-mx-8"},A={class:"md:w-3/12 flex-1 space-y-4 mt-4 md:mt-0"};function D(o,a,n,T,j,H){const i=t("app-head"),l=t("inertia-link"),c=t("ShowNewsCard"),m=t("server-status-box"),p=t("news-box"),d=t("app-layout");return y(),g(d,null,{default:r(()=>[s(i,{title:o.__(":title - News",{title:n.news.title})},null,8,["title"]),e("div",N,[e("div",S,[e("div",k,[s(l,{href:o.route("home"),class:"inline-flex items-center px-4 py-2 bg-gray-400 dark:bg-cool-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 active:bg-gray-600 focus:outline-none focus:border-gray-500 focus:shadow-outline-gray transition ease-in-out duration-150"},{default:r(()=>[e("span",null,b(o.__("Homepage")),1)]),_:1},8,["href"])])]),e("div",B,[e("div",C,[s(c,{news:n.news},null,8,["news"])]),e("div",A,[s(m),s(p,{newslist:n.newslist},null,8,["newslist"])])])])]),_:1})}const I=h(v,[["render",D]]);export{I as default};