import{L as w}from"./LoadingButton-48824f65.js";import{X as x}from"./XInput-5de78b04.js";import{X as V}from"./XSelect-fa8c7abc.js";import{X as k}from"./XCheckbox-da8a8990.js";import{T as S,l as i,o as f,c as v,w as b,a as s,t as u,b as t,i as q,f as j}from"./app-10f1fbb6.js";import{_ as E}from"./AdminLayout-56ec59aa.js";import{_ as A}from"./_plugin-vue_export-helper-c27b6911.js";import"./AppLayout-e973448b.js";import"./Icon-07002ff2.js";import"./useAuthorizable-a9d4f38f.js";import"./use-resolve-button-type-5343a0d4.js";import"./open-closed-98ce9e0e.js";import"./CloudArrowDownIcon-01098e33.js";import"./index-886e100a.js";const C={components:{AdminLayout:E,XSelect:V,LoadingButton:w,XInput:x,XCheckbox:k},props:{server:{type:[Object],required:!1},versionsArray:Array},data(){var r,o,a,c,e,m,d,p,n,_;return{isCreateOperation:!this.server,form:S({name:(r=this.server)==null?void 0:r.name,ip_address:(o=this.server)==null?void 0:o.ip_address,join_port:(a=this.server)==null?void 0:a.join_port,query_port:(c=this.server)==null?void 0:c.query_port,webquery_port:(e=this.server)==null?void 0:e.webquery_port,minecraft_version:(m=this.server)==null?void 0:m.minecraft_version,hostname:(d=this.server)==null?void 0:d.hostname,is_server_intel_enabled:((p=this.server)==null?void 0:p.is_server_intel_enabled)??!0,settings:{is_skin_change_via_web_allowed:((_=(n=this.server)==null?void 0:n.settings)==null?void 0:_.is_skin_change_via_web_allowed)??!0}})}},methods:{postForm(){this.isCreateOperation?this.form.post(route("admin.server-bungee.store"),{preserveScroll:!0}):this.form.put(route("admin.server.update.bungee",this.server.id),{preserveScroll:!0})}}},B={class:"py-12 px-10 max-w-5xl mx-auto"},U={class:"flex justify-between mb-8"},O={class:"font-bold text-3xl text-gray-500 dark:text-gray-300"},X={class:"mt-10 sm:mt-0"},L={class:""},I={class:"mt-5 md:mt-0 md:col-span-2"},N={class:"shadow overflow-hidden sm:rounded-md"},P={class:"px-4 py-5 bg-white dark:bg-cool-gray-800 sm:p-6"},T={class:"grid grid-cols-6 gap-6"},F={class:"col-span-6 sm:col-span-3"},M={class:"col-span-6 sm:col-span-3"},W={class:"col-span-6 sm:col-span-2"},Q={class:"col-span-6 sm:col-span-2"},R={class:"col-span-6 sm:col-span-2"},D={class:"col-span-6 sm:col-span-6"},H={class:"grid grid-cols-2 gap-6"},J={class:"text-xs text-gray-400 flex items-center"},z={class:"col-span-6 sm:col-span-3"},G={class:"flex items-center col-span-6 sm:col-span-6"},K={class:"flex items-center col-span-6 sm:col-span-6"},Y={class:"px-4 py-3 bg-gray-50 dark:bg-cool-gray-800 sm:px-6 flex justify-end"};function Z(r,o,a,c,e,m){const d=i("app-head"),p=i("inertia-link"),n=i("x-input"),_=i("x-select"),h=i("x-checkbox"),g=i("loading-button"),y=i("AdminLayout");return f(),v(y,null,{default:b(()=>[e.isCreateOperation?(f(),v(d,{key:0,title:r.__("Add Bungee Server")},null,8,["title"])):(f(),v(d,{key:1,title:r.__("Edit Bungee Server: :name",{name:a.server.name})},null,8,["title"])),s("div",B,[s("div",U,[s("h1",O,u(e.isCreateOperation?r.__("Add Bungee/Velocity Server"):r.__("Edit Bungee/Velocity Server: :name",{name:a.server.name})),1),t(p,{href:r.route("admin.server.index"),class:"inline-flex items-center px-4 py-2 bg-gray-400 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 active:bg-gray-600 focus:outline-none focus:border-gray-500 focus:shadow-outline-gray transition ease-in-out duration-150"},{default:b(()=>[s("span",null,u(r.__("Cancel")),1)]),_:1},8,["href"])]),s("div",X,[s("div",L,[s("div",I,[s("form",{onSubmit:o[9]||(o[9]=q((...l)=>m.postForm&&m.postForm(...l),["prevent"]))},[s("div",N,[s("div",P,[s("div",T,[s("div",F,[t(n,{id:"name",modelValue:e.form.name,"onUpdate:modelValue":o[0]||(o[0]=l=>e.form.name=l),label:r.__("Server Name"),error:e.form.errors.name,autocomplete:"name",type:"text",name:"name",help:r.__("Eg: My Bungee Server"),"help-error-flex":"flex-col"},null,8,["modelValue","label","error","help"])]),s("div",M,[t(n,{id:"hostname",modelValue:e.form.hostname,"onUpdate:modelValue":o[1]||(o[1]=l=>e.form.hostname=l),label:r.__("Hostname"),error:e.form.errors.hostname,autocomplete:"hostname",type:"text",name:"hostname",help:r.__("Eg: play-my-bungee-server.com"),"help-error-flex":"flex-col"},null,8,["modelValue","label","error","help"])]),s("div",W,[t(n,{id:"ip_address",modelValue:e.form.ip_address,"onUpdate:modelValue":o[2]||(o[2]=l=>e.form.ip_address=l),label:r.__("IP Address"),error:e.form.errors.ip_address,autocomplete:"ip_address",type:"text",name:"ip_address",help:r.__("Eg: 78.46.130.197"),"help-error-flex":"flex-col"},null,8,["modelValue","label","error","help"])]),s("div",Q,[t(n,{id:"join_port",modelValue:e.form.join_port,"onUpdate:modelValue":o[3]||(o[3]=l=>e.form.join_port=l),label:r.__("Join Port"),error:e.form.errors.join_port,autocomplete:"join_port",type:"text",name:"join_port",help:r.__("Eg: 25565"),"help-error-flex":"flex-col"},null,8,["modelValue","label","error","help"])]),s("div",R,[t(n,{id:"query_port",modelValue:e.form.query_port,"onUpdate:modelValue":o[4]||(o[4]=l=>e.form.query_port=l),label:r.__("Query Port"),error:e.form.errors.query_port,autocomplete:"query_port",type:"text",name:"query_port",help:r.__("Eg: 25575"),"help-error-flex":"flex-col"},null,8,["modelValue","label","error","help"])]),s("div",D,[s("div",H,[t(n,{id:"webquery_port",modelValue:e.form.webquery_port,"onUpdate:modelValue":o[5]||(o[5]=l=>e.form.webquery_port=l),label:r.__("Webquery Port"),error:e.form.errors.webquery_port,type:"text",name:"webquery_port","help-error-flex":"flex-col"},null,8,["modelValue","label","error"]),s("div",J,u(r.__("WebQuery port is a new port which MineTrax plugin will open for secure connection between server and web. Enter a port value which is available and can be open. Eg: 25569")),1)])]),s("div",z,[t(_,{id:"minecraft_version",modelValue:e.form.minecraft_version,"onUpdate:modelValue":o[6]||(o[6]=l=>e.form.minecraft_version=l),name:"minecraft_version",error:e.form.errors.minecraft_version,label:r.__("Server Version"),"select-list":a.versionsArray,placeholder:r.__("Select version.."),"disable-null":!0},null,8,["modelValue","error","label","select-list","placeholder"])]),s("div",G,[t(h,{id:"is_server_intel_enabled",modelValue:e.form.is_server_intel_enabled,"onUpdate:modelValue":o[7]||(o[7]=l=>e.form.is_server_intel_enabled=l),label:r.__("Enable Server Intel / Analytics"),help:r.__("If enabled, server analytics data (performance metric, join activity etc) will be captured for this server via plugin."),name:"is_server_intel_enabled"},null,8,["modelValue","label","help"])]),s("div",K,[t(h,{id:"is_skin_change_via_web_allowed",modelValue:e.form.settings.is_skin_change_via_web_allowed,"onUpdate:modelValue":o[8]||(o[8]=l=>e.form.settings.is_skin_change_via_web_allowed=l),label:r.__("Enable Skin Change via Web (SkinsRestorer)"),help:r.__("Allow user to change their linked players skin via web for this server. This will require SkinsRestorer plugin to be installed on the server."),name:"is_skin_change_via_web_allowed"},null,8,["modelValue","label","help"])])])]),s("div",Y,[t(g,{loading:e.form.processing,class:"inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-light-blue-500 hover:bg-light-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 disabled:opacity-50",type:"submit"},{default:b(()=>[j(u(e.isCreateOperation?r.__("Add Server"):r.__("Edit Server")),1)]),_:1},8,["loading"])])])],32)])])])])]),_:1})}const ue=A(C,[["render",Z]]);export{ue as default};
