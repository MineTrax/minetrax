import{J as b}from"./AuthenticationCard-BrzoS2pm.js";import{L as h}from"./LoadingButton-CIF9JOGX.js";import{A as w}from"./AppLayout-_QfSG3gX.js";import{X as y}from"./XInput-BBvCiCBA.js";import{C as x,q as t,c as k,w as i,o as d,b as r,a,t as l,d as v,e as V,f as B,l as C}from"./app-BDqFx3nJ.js";import{_ as N}from"./_plugin-vue_export-helper-DlAUqK2U.js";import"./Icon-Bn9n1IU_.js";import"./useAuthorizable-CnlAWZWI.js";const j={components:{XInput:y,AppLayout:w,LoadingButton:h,JetAuthenticationCard:b},props:{status:String},data(){return{form:x({email:""})}},methods:{submit(){this.form.post(this.route("password.email"))}}},A={class:"mb-4 text-sm text-gray-600 dark:text-gray-400"},L={key:0,class:"mb-4 font-medium text-sm text-green-600 dark:text-green-400"},E={class:"flex items-center justify-end mt-4"};function F(e,o,m,J,s,u){const p=t("app-head"),c=t("x-input"),f=t("loading-button"),_=t("jet-authentication-card"),g=t("app-layout");return d(),k(g,null,{default:i(()=>[r(p,{title:e.__("Forgot Password")},null,8,["title"]),r(_,null,{default:i(()=>[a("div",A,l(e.__("Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.")),1),m.status?(d(),v("div",L,l(m.status),1)):V("",!0),a("form",{onSubmit:o[1]||(o[1]=C((...n)=>u.submit&&u.submit(...n),["prevent"]))},[a("div",null,[r(c,{id:"email",modelValue:s.form.email,"onUpdate:modelValue":o[0]||(o[0]=n=>s.form.email=n),label:e.__("Email Address"),error:s.form.errors.email,autofocus:!0,required:!0,type:"email",name:"email"},null,8,["modelValue","label","error"])]),a("div",E,[r(f,{loading:s.form.processing,class:"ml-4 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-light-blue-500 hover:bg-light-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 disabled:opacity-50"},{default:i(()=>[B(l(e.__("Email Password Reset Link")),1)]),_:1},8,["loading"])])],32)]),_:1})]),_:1})}const T=N(j,[["render",F]]);export{T as default};