import{_ as b}from"./ActionMessage.38b5b129.js";import{_ as j}from"./ActionSection.ce410472.js";import{_ as B}from"./Button.66a65189.js";import{_ as x}from"./DialogModal.e7834bfe.js";import{_ as S}from"./Input.d5f5f0bf.js";import{_ as C}from"./InputError.5ca08716.js";import{_ as L}from"./SecondaryButton.3115ee58.js";import{_ as M,z as l,o as i,c as V,w as t,f as r,t as o,a as s,d as c,F as J,h as O,e as N,b as _,U as z,n as F}from"./app.7bd3ac4b.js";import"./SectionTitle.f910e3a6.js";import"./Modal.5b176690.js";const I={components:{JetActionMessage:b,JetActionSection:j,JetButton:B,JetDialogModal:x,JetInput:S,JetInputError:C,JetSecondaryButton:L},props:["sessions"],data(){return{confirmingLogout:!1,form:this.$inertia.form({password:""})}},methods:{confirmLogout(){this.confirmingLogout=!0,setTimeout(()=>this.$refs.password.focus(),250)},logoutOtherBrowserSessions(){this.form.delete(route("other-browser-sessions.destroy"),{preserveScroll:!0,onSuccess:()=>this.closeModal(),onError:()=>this.$refs.password.focus(),onFinish:()=>this.form.reset()})},closeModal(){this.confirmingLogout=!1,this.form.reset()}}},D={class:"max-w-xl text-sm text-gray-600 dark:text-gray-400"},E={key:0,class:"mt-5 space-y-6"},K={key:0,fill:"none","stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",viewBox:"0 0 24 24",stroke:"currentColor",class:"w-8 h-8 text-gray-500"},T=s("path",{d:"M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"},null,-1),A=[T],H={key:1,xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24","stroke-width":"2",stroke:"currentColor",fill:"none","stroke-linecap":"round","stroke-linejoin":"round",class:"w-8 h-8 text-gray-500"},P=s("path",{d:"M0 0h24v24H0z",stroke:"none"},null,-1),U=s("rect",{x:"7",y:"4",width:"10",height:"16",rx:"1"},null,-1),q=s("path",{d:"M11 5h2M12 17v.01"},null,-1),G=[P,U,q],Q={class:"ml-3"},R={class:"text-sm text-gray-600 dark:text-gray-400"},W={class:"text-xs text-gray-500"},X={key:0,class:"text-green-500 font-semibold"},Y={key:1},Z={class:"flex items-center mt-5"},$={class:"mt-4"};function oo(e,m,u,eo,a,d){const h=l("jet-button"),f=l("jet-action-message"),p=l("jet-input"),g=l("jet-input-error"),w=l("jet-secondary-button"),y=l("jet-dialog-modal"),v=l("jet-action-section");return i(),V(v,null,{title:t(()=>[r(o(e.__("Browser Sessions")),1)]),description:t(()=>[r(o(e.__("Manage and logout your active sessions on other browsers and devices.")),1)]),content:t(()=>[s("div",D,o(e.__("If necessary, you may logout of all of your other browser sessions across all of your devices. Some of your recent sessions are listed below; however, this list may not be exhaustive. If you feel your account has been compromised, you should also update your password.")),1),u.sessions.length>0?(i(),c("div",E,[(i(!0),c(J,null,O(u.sessions,(n,k)=>(i(),c("div",{key:k,class:"flex items-center"},[s("div",null,[n.agent.is_desktop?(i(),c("svg",K,A)):(i(),c("svg",H,G))]),s("div",Q,[s("div",R,o(n.agent.platform)+" - "+o(n.agent.browser),1),s("div",null,[s("div",W,[r(o(n.ip_address)+", ",1),n.is_current_device?(i(),c("span",X,o(e.__("This device")),1)):(i(),c("span",Y,o(e.__("Last active"))+"\xA0"+o(n.last_active),1))])])])]))),128))])):N("",!0),s("div",Z,[_(h,{onClick:d.confirmLogout},{default:t(()=>[r(o(e.__("Logout Other Browser Sessions")),1)]),_:1},8,["onClick"]),_(f,{on:a.form.recentlySuccessful,class:"ml-3"},{default:t(()=>[r(o(e.__("Done"))+". ",1)]),_:1},8,["on"])]),_(y,{show:a.confirmingLogout,onClose:d.closeModal},{title:t(()=>[r(o(e.__("Logout Other Browser Sessions")),1)]),content:t(()=>[r(o(e.__("Please enter your password to confirm you would like to logout of your other browser sessions across all of your devices."))+" ",1),s("div",$,[_(p,{ref:"password",modelValue:a.form.password,"onUpdate:modelValue":m[0]||(m[0]=n=>a.form.password=n),type:"password",class:"mt-1 block w-3/4",placeholder:e.__("Password"),onKeyup:z(d.logoutOtherBrowserSessions,["enter","native"])},null,8,["modelValue","placeholder","onKeyup"]),_(g,{message:a.form.errors.password,class:"mt-2"},null,8,["message"])])]),footer:t(()=>[_(w,{onClick:d.closeModal},{default:t(()=>[r(o(e.__("Nevermind")),1)]),_:1},8,["onClick"]),_(h,{class:F(["ml-2",{"opacity-25":a.form.processing}]),disabled:a.form.processing,onClick:d.logoutOtherBrowserSessions},{default:t(()=>[r(o(e.__("Logout Other Browser Sessions")),1)]),_:1},8,["class","disabled","onClick"])]),_:1},8,["show","onClose"])]),_:1})}const uo=M(I,[["render",oo]]);export{uo as default};