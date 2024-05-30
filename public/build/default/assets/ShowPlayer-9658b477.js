import{A as k,u as z}from"./AppLayout-61780049.js";import{I as M}from"./Icon-64bdcc8f.js";import{S as j,W as A}from"./viewer-90f975be.js";import{_ as C}from"./millify-2865c83f.js";import{_ as V}from"./PlayerSubMenu-6c943f8e.js";import{_ as B}from"./_plugin-vue_export-helper-c27b6911.js";import{l as r,q as L,o as a,c as _,w as y,b as f,a as e,j as i,d as o,f as d,t,e as S}from"./app-36941795.js";import"./useAuthorizable-4eb80770.js";const P={components:{PlayerSubMenu:V,Icon:M,AppLayout:k},props:{player:Object,canShowPlayerIntel:Boolean,canChangePlayerSkin:Boolean},setup(){const{secondsToHMS:l,formatTimeAgoToNow:h,formatToDayDateString:s}=z();return{secondsToHMS:l,formatTimeAgoToNow:h,formatToDayDateString:s,millify:C}},data(){return{playerAnimationEnabled:!0,skinViewer:null}},mounted(){this.skinViewer=new j({canvas:document.getElementById("skin_container"),width:300,height:500,skin:route("player.skin.get",{uuid:this.player.uuid,username:this.player.username,textureid:this.player.skin_texture_id})}),this.skinViewer.autoRotate=!0,this.skinViewer.animation=new A,this.skinViewer.animation.speed=.1,this.skinViewer.autoRotateSpeed=.5},unmounted(){this.skinViewer.dispose()},methods:{toggle3dPlayerAnimation:function(){this.playerAnimationEnabled?(this.skinViewer.animation.paused=!0,this.playerAnimationEnabled=!1):(this.skinViewer.animation.paused=!1,this.playerAnimationEnabled=!0)}}},N={class:"px-2 py-4 md:py-12 md:px-10 max-w-7xl mx-auto space-y-4"},T={class:"flex justify-between items-center shadow bg-white dark:bg-cool-gray-800 rounded p-3"},H={id:"position",class:"flex flex-col items-center justify-center"},D={class:"flex items-center text-light-blue-400 font-extrabold"},R=["title"],I=["title"],E={class:"text-sm text-gray-400 dark:text-gray-300 font-bold mt-1"},U={id:"rating",class:"flex flex-col items-center justify-center"},W={class:"flex"},K=["title"],q={class:"text-sm text-gray-400 dark:text-gray-300 font-bold mt-1"},F={id:"rank",class:"flex flex-col items-center justify-center"},J={class:"flex items-center justify-center space-x-2"},O=["alt","src","title"],G=["title"],Q={class:"text-sm text-gray-400 dark:text-gray-300 font-bold mt-1"},X={id:"country",class:"flex flex-col items-center justify-center"},Y=["alt","src","title"],Z=["title"],$={class:"text-sm text-gray-400 dark:text-gray-300 font-bold mt-1"},e1={class:"flex flex-col-reverse md:flex-row md:justify-between md:space-x-4"},t1={class:"shadow mt-4 md:mt-0 bg-white dark:bg-cool-gray-800 rounded relative flex items-center justify-center"},s1=e("canvas",{id:"skin_container"},null,-1),l1={class:"flex flex-col w-full space-y-4"},a1={key:0,class:"shadow bg-white dark:bg-cool-gray-800 rounded w-full p-2 md:p-5 text-red-400 dark:text-red-500 italic text-center"},o1={class:"shadow bg-white dark:bg-cool-gray-800 rounded w-full p-2 md:p-5"},n1={class:"flex justify-between space-x-2 items-center mb-2"},i1={class:"text-2xl text-gray-800 dark:text-gray-200 font-extrabold"},d1={class:"text-gray-400 font-semibold text-xs md:text-sm"},c1={id:"stats",class:"text-xs md:text-sm font-semibold text-gray-700 dark:text-gray-400 space-y-4"},r1={id:"first",class:"flex justify-between pb-4 border-b border-gray-200 dark:border-gray-700"},_1={class:"flex-1 space-y-4"},h1={class:"flex justify-between"},v1={class:"flex"},u1=e("svg",{class:"h-5 w-5 text-green-500",fill:"none",stroke:"currentColor",viewBox:"0 0 24 24"},[e("path",{d:"M7 20l4-16m2 16l4-16M6 9h14M4 15h14","stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2"})],-1),y1={class:"ml-1"},f1={class:"font-bold"},w1={class:"flex justify-between"},m1={class:"flex"},x1=e("svg",{class:"w-5 h-5 text-light-blue-500",fill:"currentColor",viewBox:"0 0 20 20"},[e("path",{d:"M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"})],-1),p1={class:"ml-1"},g1={class:"font-bold"},b1={class:"flex justify-between"},k1={class:"flex"},z1=e("svg",{class:"w-5 h-5 text-orange-500",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg"},[e("path",{d:"M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"}),e("path",{d:"M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"})],-1),M1={class:"ml-1"},j1={key:0,class:"font-bold"},A1={key:1,class:"italic text-gray-500"},C1={class:"flex flex-1 justify-center items-center"},V1=["src"],B1={class:"flex-1 space-y-4"},L1={class:"flex justify-between"},S1={class:"flex"},P1=e("svg",{class:"w-5 h-5 text-green-500",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg"},[e("path",{"fill-rule":"evenodd",d:"M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z","clip-rule":"evenodd"})],-1),N1={class:"ml-1"},T1={class:"font-bold"},H1={class:"flex justify-between"},D1={class:"flex"},R1=e("svg",{class:"w-5 h-5 text-pink-500",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg"},[e("path",{"fill-rule":"evenodd",d:"M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z","clip-rule":"evenodd"})],-1),I1={class:"ml-1"},E1={class:"font-bold"},U1={class:"flex justify-between"},W1={class:"flex"},K1=e("svg",{class:"w-5 h-5 text-red-500",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg"},[e("path",{"fill-rule":"evenodd",d:"M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z","clip-rule":"evenodd"})],-1),q1={class:"ml-1"},F1={class:"font-bold"},J1={id:"second",class:"flex justify-between pb-4 border-b border-gray-200 dark:border-gray-700 space-x-8"},O1={class:"flex-1 space-y-4"},G1={class:"flex justify-between"},Q1={class:"flex"},X1=e("svg",{class:"w-5 h-5 text-light-blue-500",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg"},[e("path",{d:"M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"})],-1),Y1={class:"ml-1"},Z1={key:0},$1={key:1,class:"italic text-gray-500"},ee={class:"flex justify-between"},te={class:"flex"},se=e("svg",{class:"w-5 h-5 text-green-500",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg"},[e("path",{"fill-rule":"evenodd",d:"M10 18a8 8 0 100-16 8 8 0 000 16zM4.332 8.027a6.012 6.012 0 011.912-2.706C6.512 5.73 6.974 6 7.5 6A1.5 1.5 0 019 7.5V8a2 2 0 004 0 2 2 0 011.523-1.943A5.977 5.977 0 0116 10c0 .34-.028.675-.083 1H15a2 2 0 00-2 2v2.197A5.973 5.973 0 0110 16v-2a2 2 0 00-2-2 2 2 0 01-2-2 2 2 0 00-1.668-1.973z","clip-rule":"evenodd"})],-1),le={class:"ml-1"},ae={class:"flex justify-between"},oe={class:"flex"},ne=e("svg",{class:"w-5 h-5 text-light-blue-500",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg"},[e("path",{"fill-rule":"evenodd",d:"M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z","clip-rule":"evenodd"})],-1),ie={class:"ml-1"},de={class:"flex justify-between"},ce={class:"flex"},re=e("svg",{class:"w-5 h-5 text-gray-700 dark:text-gray-200",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg"},[e("path",{d:"M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"})],-1),_e={class:"ml-1"},he={class:"flex-1 space-y-4"},ve={class:"flex justify-between"},ue={class:"flex"},ye=e("svg",{class:"w-5 h-5 text-orange-500",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg"},[e("path",{d:"M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"})],-1),fe={class:"ml-1"},we={key:0},me={key:1,class:"italic text-gray-500"},xe={class:"flex justify-between"},pe={class:"flex"},ge=e("svg",{class:"w-5 h-5 text-light-blue-500",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg"},[e("path",{"fill-rule":"evenodd",d:"M10 18a8 8 0 100-16 8 8 0 000 16zM4.332 8.027a6.012 6.012 0 011.912-2.706C6.512 5.73 6.974 6 7.5 6A1.5 1.5 0 019 7.5V8a2 2 0 004 0 2 2 0 011.523-1.943A5.977 5.977 0 0116 10c0 .34-.028.675-.083 1H15a2 2 0 00-2 2v2.197A5.973 5.973 0 0110 16v-2a2 2 0 00-2-2 2 2 0 01-2-2 2 2 0 00-1.668-1.973z","clip-rule":"evenodd"})],-1),be={class:"ml-1"},ke={class:"flex justify-between"},ze={class:"flex"},Me=e("svg",{class:"w-5 h-5 text-yellow-500",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg"},[e("path",{"fill-rule":"evenodd",d:"M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z","clip-rule":"evenodd"})],-1),je={class:"ml-1"},Ae={class:"flex justify-between"},Ce={class:"flex"},Ve=e("svg",{class:"w-5 h-5 text-green-500",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg"},[e("path",{"fill-rule":"evenodd",d:"M2 5a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm14 1a1 1 0 11-2 0 1 1 0 012 0zM2 13a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H4a2 2 0 01-2-2v-2zm14 1a1 1 0 11-2 0 1 1 0 012 0z","clip-rule":"evenodd"})],-1),Be={class:"ml-1"},Le={id:"third",class:"flex justify-between pb-4 border-b border-gray-200 dark:border-gray-700 space-x-8"},Se={class:"flex-1 space-y-4"},Pe={class:"flex justify-between"},Ne={class:"flex"},Te=e("svg",{class:"w-5 h-5 text-light-blue-500",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg"},[e("path",{"fill-rule":"evenodd",d:"M9.504 1.132a1 1 0 01.992 0l1.75 1a1 1 0 11-.992 1.736L10 3.152l-1.254.716a1 1 0 11-.992-1.736l1.75-1zM5.618 4.504a1 1 0 01-.372 1.364L5.016 6l.23.132a1 1 0 11-.992 1.736L4 7.723V8a1 1 0 01-2 0V6a.996.996 0 01.52-.878l1.734-.99a1 1 0 011.364.372zm8.764 0a1 1 0 011.364-.372l1.733.99A1.002 1.002 0 0118 6v2a1 1 0 11-2 0v-.277l-.254.145a1 1 0 11-.992-1.736l.23-.132-.23-.132a1 1 0 01-.372-1.364zm-7 4a1 1 0 011.364-.372L10 8.848l1.254-.716a1 1 0 11.992 1.736L11 10.58V12a1 1 0 11-2 0v-1.42l-1.246-.712a1 1 0 01-.372-1.364zM3 11a1 1 0 011 1v1.42l1.246.712a1 1 0 11-.992 1.736l-1.75-1A1 1 0 012 14v-2a1 1 0 011-1zm14 0a1 1 0 011 1v2a1 1 0 01-.504.868l-1.75 1a1 1 0 11-.992-1.736L16 13.42V12a1 1 0 011-1zm-9.618 5.504a1 1 0 011.364-.372l.254.145V16a1 1 0 112 0v.277l.254-.145a1 1 0 11.992 1.736l-1.735.992a.995.995 0 01-1.022 0l-1.735-.992a1 1 0 01-.372-1.364z","clip-rule":"evenodd"})],-1),He={class:"ml-1"},De={class:"flex justify-between"},Re={class:"flex"},Ie=e("svg",{class:"w-5 h-5 text-green-500",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg"},[e("path",{"fill-rule":"evenodd",d:"M4.293 15.707a1 1 0 010-1.414l5-5a1 1 0 011.414 0l5 5a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414 0zm0-6a1 1 0 010-1.414l5-5a1 1 0 011.414 0l5 5a1 1 0 01-1.414 1.414L10 5.414 5.707 9.707a1 1 0 01-1.414 0z","clip-rule":"evenodd"})],-1),Ee={class:"ml-1"},Ue={class:"flex justify-between"},We={class:"flex"},Ke=e("svg",{class:"w-5 h-5 text-purple-500",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg"},[e("path",{"fill-rule":"evenodd",d:"M4.293 15.707a1 1 0 010-1.414l5-5a1 1 0 011.414 0l5 5a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414 0zm0-6a1 1 0 010-1.414l5-5a1 1 0 011.414 0l5 5a1 1 0 01-1.414 1.414L10 5.414 5.707 9.707a1 1 0 01-1.414 0z","clip-rule":"evenodd"})],-1),qe={class:"ml-1"},Fe={class:"flex justify-between"},Je={class:"flex"},Oe=e("svg",{class:"w-5 h-5 text-red-500",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg"},[e("path",{"fill-rule":"evenodd",d:"M15.707 4.293a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-5-5a1 1 0 011.414-1.414L10 8.586l4.293-4.293a1 1 0 011.414 0zm0 6a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-5-5a1 1 0 111.414-1.414L10 14.586l4.293-4.293a1 1 0 011.414 0z","clip-rule":"evenodd"})],-1),Ge={class:"ml-1"},Qe={class:"flex-1 space-y-4"},Xe={class:"flex justify-between"},Ye={class:"flex"},Ze=e("svg",{class:"w-5 h-5 text-purple-500",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg"},[e("path",{d:"M11 17a1 1 0 001.447.894l4-2A1 1 0 0017 15V9.236a1 1 0 00-1.447-.894l-4 2a1 1 0 00-.553.894V17zM15.211 6.276a1 1 0 000-1.788l-4.764-2.382a1 1 0 00-.894 0L4.789 4.488a1 1 0 000 1.788l4.764 2.382a1 1 0 00.894 0l4.764-2.382zM4.447 8.342A1 1 0 003 9.236V15a1 1 0 00.553.894l4 2A1 1 0 009 17v-5.764a1 1 0 00-.553-.894l-4-2z"})],-1),$e={class:"ml-1"},e0={class:"flex justify-between"},t0={class:"flex"},s0=e("svg",{class:"w-5 h-5 text-yellow-500",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg"},[e("path",{d:"M4.555 5.168A1 1 0 003 6v8a1 1 0 001.555.832L10 11.202V14a1 1 0 001.555.832l6-4a1 1 0 000-1.664l-6-4A1 1 0 0010 6v2.798l-5.445-3.63z"})],-1),l0={class:"ml-1"},a0={class:"flex justify-between"},o0={class:"flex"},n0=e("svg",{class:"w-5 h-5 text-light-blue-500",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg"},[e("path",{"fill-rule":"evenodd",d:"M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z","clip-rule":"evenodd"})],-1),i0={class:"ml-1"},d0={key:1,class:"italic text-gray-500"},c0={class:"flex justify-between"},r0={class:"flex"},_0=e("svg",{class:"w-5 h-5 text-red-500",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg"},[e("path",{"fill-rule":"evenodd",d:"M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z","clip-rule":"evenodd"})],-1),h0={class:"ml-1"},v0={key:0,class:"text-gray-400 italic"},u0=["title"],y0={id:"fourth",class:"flex justify-between pb-4 space-x-8"},f0={class:"flex-1 space-y-4"},w0={class:"flex justify-between"},m0={class:"flex"},x0=e("svg",{class:"w-5 h-5 text-light-blue-500",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg"},[e("path",{d:"M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z"})],-1),p0={class:"ml-1"},g0=["content"],b0={class:"flex-1 space-y-4"},k0={class:"flex justify-between"},z0={class:"flex"},M0=e("svg",{class:"w-5 h-5 text-green-500",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg"},[e("path",{d:"M10 12a2 2 0 100-4 2 2 0 000 4z"}),e("path",{"fill-rule":"evenodd",d:"M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z","clip-rule":"evenodd"})],-1),j0={class:"ml-1"},A0=["content"];function C0(l,h,s,c,w,u){const m=r("app-head"),x=r("PlayerSubMenu"),v=r("icon"),p=r("inertia-link"),g=r("app-layout"),n=L("tippy");return a(),_(g,null,{default:y(()=>[f(m,{title:l.__(":username - Player Details",{username:s.player.username})},null,8,["title"]),e("div",N,[f(x,{player:s.player,"can-show-player-intel":s.canShowPlayerIntel,"can-change-player-skin":s.canChangePlayerSkin},null,8,["player","can-show-player-intel","can-change-player-skin"]),e("div",T,[e("div",H,[e("div",D,[s.player.position?i((a(),o("span",{key:0,title:l.__("Position: :position",{position:s.player.position}),class:"border-2 rounded text-2xl px-2 py-1 border-light-blue-300 bg-light-blue-50 dark:bg-cool-gray-800"},[d(" #"+t(s.player.position),1)],8,R)),[[n]]):i((a(),o("span",{key:1,class:"italic text-sm text-gray-500 dark:text-gray-400",title:l.__("Position: None")},[d(t(l.__("None")),1)],8,I)),[[n]])]),e("span",E,t(l.__("Position")),1)]),e("div",U,[e("div",W,[s.player.rating!=null?i((a(),_(v,{key:0,name:`rating-${s.player.rating}`,title:l.__("Rating: :rating",{rating:s.player.rating}),class:"w-12 h-12 focus:outline-none"},null,8,["name","title"])),[[n]]):i((a(),o("p",{key:1,class:"italic text-sm text-gray-500 dark:text-gray-400",title:l.__("Rating: None")},[d(t(l.__("None")),1)],8,K)),[[n]])]),e("span",q,t(l.__("Rating")),1)]),e("div",F,[e("div",J,[s.player.rank&&s.player.rank.photo_url?i((a(),o("img",{key:0,alt:s.player.rank.name,src:s.player.rank.photo_url,title:l.__("Rank: :rank",{rank:s.player.rank.name}),class:"h-12 focus:outline-none"},null,8,O)),[[n]]):i((a(),o("p",{key:1,class:"italic text-sm text-gray-500 dark:text-gray-400",title:l.__("Rank: None")},[d(t(l.__("None")),1)],8,G)),[[n]])]),e("span",Q,t(l.__("Rank")),1)]),e("div",X,[s.player.country&&s.player.country.name?i((a(),o("img",{key:0,alt:s.player.country.name,src:s.player.country.photo_path,title:l.__("Country: :country",{country:s.player.country.name}),class:"h-12 w-12 -mt-0.5 focus:outline-none"},null,8,Y)),[[n]]):i((a(),o("p",{key:1,class:"italic text-sm text-gray-500 dark:text-gray-400",title:l.__("Country: None")},[d(t(l.__("Unknown")),1)],8,Z)),[[n]]),e("span",$,t(l.__("Country")),1)])]),e("div",e1,[e("div",t1,[e("button",{class:"focus:outline-none absolute top-2 left-2",onClick:h[0]||(h[0]=(...b)=>u.toggle3dPlayerAnimation&&u.toggle3dPlayerAnimation(...b))},[w.playerAnimationEnabled?(a(),_(v,{key:0,class:"w-6 h-6 text-red-300 dark:text-red-500",name:"pause"})):(a(),_(v,{key:1,class:"w-6 h-6 text-green-300 dark:text-green-500",name:"play"}))]),s1]),e("div",l1,[s.player.is_active?S("",!0):(a(),o("div",a1,t(l.__("Player is inactive and will not be considered for rating.")),1)),e("div",o1,[e("div",n1,[e("h1",i1,t(s.player.username),1),e("h2",d1,t(s.player.uuid),1)]),e("div",c1,[e("div",r1,[e("div",_1,[e("div",h1,[e("div",v1,[u1,e("p",y1,t(l.__("Position")),1)]),e("p",f1,t(s.player.position),1)]),e("div",w1,[e("div",m1,[x1,e("p",p1,t(l.__("Score")),1)]),e("p",g1,t(s.player.total_score),1)]),e("div",b1,[e("div",k1,[z1,e("p",M1,t(l.__("Rating")),1)]),e("p",null,[s.player.rating!==null?(a(),o("span",j1,t(s.player.rating),1)):(a(),o("span",A1,t(l.__("None")),1))])])]),e("div",C1,[e("img",{src:s.player.avatar_url,alt:"Avatar",class:"h-30 w-30 rounded"},null,8,V1)]),e("div",B1,[e("div",L1,[e("div",S1,[P1,e("p",N1,t(l.__("Mob Kills")),1)]),e("p",T1,t(s.player.total_mob_kills),1)]),e("div",H1,[e("div",D1,[R1,e("p",I1,t(l.__("Player Kills")),1)]),e("p",E1,t(s.player.total_player_kills),1)]),e("div",U1,[e("div",W1,[K1,e("p",q1,t(l.__("Deaths")),1)]),e("p",F1,t(s.player.total_deaths),1)])])]),e("div",J1,[e("div",O1,[e("div",G1,[e("div",Q1,[X1,e("p",Y1,t(l.__("Rank")),1)]),e("p",null,[s.player.rank?(a(),o("span",Z1,t(s.player.rank.name),1)):(a(),o("span",$1,t(l.__("None")),1))])]),e("div",ee,[e("div",te,[se,e("p",le,t(l.__("Country")),1)]),e("p",null,t(s.player.country.name),1)]),e("div",ae,[e("div",oe,[ne,e("p",ie,t(l.__("Total Playtime")),1)]),e("p",null,t(c.secondsToHMS(s.player.play_time,!0)),1)]),e("div",de,[e("div",ce,[re,e("p",_e,t(l.__("Times Slept")),1)]),e("p",null,t(s.player.total_sleep_in_bed),1)])]),e("div",he,[e("div",ve,[e("div",ue,[ye,e("p",fe,t(l.__("Next Rank")),1)]),e("p",null,[s.player.next_rank?(a(),o("span",we,t(s.player.next_rank),1)):(a(),o("span",me,t(l.__("None")),1))])]),e("div",xe,[e("div",pe,[ge,e("p",be,t(l.__("Sessions")),1)]),e("p",null,t(s.player.total_leave_game),1)]),e("div",ke,[e("div",ze,[Me,e("p",je,t(l.__("Total Afktime")),1)]),e("p",null,t(c.secondsToHMS(s.player.afk_time,!0)),1)]),e("div",Ae,[e("div",Ce,[Ve,e("p",Be,t(l.__("Servers Played")),1)]),e("p",null,t(s.player.servers_count),1)])])]),e("div",Le,[e("div",Se,[e("div",Pe,[e("div",Ne,[Te,e("p",He,t(l.__("Items Mined")),1)]),e("p",null,t(s.player.total_mined),1)]),e("div",De,[e("div",Re,[Ie,e("p",Ee,t(l.__("Items Crafted")),1)]),e("p",null,t(s.player.total_crafted),1)]),e("div",Ue,[e("div",We,[Ke,e("p",qe,t(l.__("Items Picked Up")),1)]),e("p",null,t(s.player.total_picked_up),1)]),e("div",Fe,[e("div",Je,[Oe,e("p",Ge,t(l.__("Items Broken")),1)]),e("p",null,t(s.player.total_broken),1)])]),e("div",Qe,[e("div",Xe,[e("div",Ye,[Ze,e("p",$e,t(l.__("Items Used")),1)]),e("p",null,t(s.player.total_used),1)]),e("div",e0,[e("div",t0,[s0,e("p",l0,t(l.__("Distance Walked")),1)]),e("p",null,t(c.millify(s.player.distance_traveled))+" "+t(l.__("meters")),1)]),e("div",a0,[e("div",o0,[n0,e("p",i0,t(l.__("Claimed By")),1)]),e("p",null,[s.player.owner?(a(),_(p,{key:0,class:"font-bold text-light-blue-400 hover:text-light-blue-600",as:"a",href:l.route("user.public.get",s.player.owner.username)},{default:y(()=>[d(" @"+t(s.player.owner.username),1)]),_:1},8,["href"])):(a(),o("span",d0,t(l.__("None")),1))])]),e("div",c0,[e("div",r0,[_0,e("p",h0,t(l.__("Favorite Server")),1)]),s.player.favorite_server?i((a(),o("p",{key:1,class:"focus:outline-none",title:s.player.favorite_server.hostname},[d(t(s.player.favorite_server.name),1)],8,u0)),[[n]]):(a(),o("p",v0,t(l.__("None")),1))])])]),e("div",y0,[e("div",f0,[e("div",w0,[e("div",m0,[x0,e("p",p0,t(l.__("Joined")),1)]),i((a(),o("p",{class:"ml-1 focus:outline-none",content:c.formatToDayDateString(s.player.first_seen_at)},[d(t(s.player.first_seen_at?c.formatTimeAgoToNow(s.player.first_seen_at):"unknown"),1)],8,g0)),[[n]])])]),e("div",b0,[e("div",k0,[e("div",z0,[M0,e("p",j0,t(l.__("Last Seen")),1)]),i((a(),o("p",{class:"ml-1 focus:outline-none",content:c.formatToDayDateString(s.player.last_seen_at)},[d(t(s.player.last_seen_at?c.formatTimeAgoToNow(s.player.last_seen_at):"unknown"),1)],8,A0)),[[n]])])])])])])])])])]),_:1})}const D0=B(P,[["render",C0]]);export{D0 as default};