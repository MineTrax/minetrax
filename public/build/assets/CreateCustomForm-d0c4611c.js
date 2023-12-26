import{r as x,T as w,s as V,l as d,o as k,c as C,w as m,b as a,a as e,t as n,i as F,u as t,j as U,v as S,f as u}from"./app-ab7f0bba.js";import{_ as r}from"./InputError-7c6e144b.js";import{L as D}from"./LoadingButton-e3fccb22.js";import{X as c}from"./XInput-b0a341fb.js";import{X as E}from"./XSelect-fdfe12d8.js";import{X as _}from"./XCheckbox-7799a05e.js";import{E as O}from"./easymde-70d7ea2f.js";import{_ as X}from"./AdminLayout-ea4e1499.js";import"./AppLayout-8715e6d2.js";import"./useAuthorizable-1f35cec3.js";import"./CloudArrowDownIcon-809b19cc.js";import"./index-34764b0e.js";const M={class:"py-12 px-10 max-w-7xl mx-auto"},N={class:"flex justify-between mb-8"},T={class:"font-bold text-3xl text-gray-500 dark:text-gray-300"},j={class:"mt-10 sm:mt-0"},A={class:"md:grid md:grid-cols-6 md:gap-6"},B={class:"mt-5 md:mt-0 md:col-span-4"},K=["onSubmit"],L={class:"shadow overflow-hidden sm:rounded-md"},P={class:"px-4 py-5 bg-white dark:bg-cool-gray-800 sm:p-6"},$={class:"grid grid-cols-6 gap-4"},q={class:"col-span-6 sm:col-span-6"},z={class:"col-span-6 sm:col-span-6"},I={class:"col-span-6 sm:col-span-6"},Z={class:"col-span-6 sm:col-span-6"},G={class:"flex items-center col-span-6 sm:col-span-6"},H={class:"text-base font-medium text-gray-900 dark:text-gray-300"},J={class:"mt-4 flex space-x-4"},Q={class:"px-4 py-3 bg-gray-50 dark:bg-cool-gray-800 sm:px-6 flex justify-end"},R={class:"md:col-span-2"},W={class:"px-4 sm:px-0"},Y={class:"text-lg font-medium leading-6 text-gray-900 dark:text-gray-400"},ee={class:"mt-1 text-sm text-gray-600 dark:text-gray-500"},se=e("br",null,null,-1),ce={__name:"CreateCustomForm",setup(oe){const p=x("draft"),g={draft:"Draft - Form is under development and not visible to users",active:"Active - Form is actively accepting submissions",disabled:"Disabled - Form is disabled for new submissions",archived:"Archived - Form is archived and not visible to users"},s=w({title:"",slug:"",status:"draft",description:"",is_only_auth:!1,is_only_staff:!1,is_notify_staff_on_submission:!0,fields:[]});let f=null;const b=()=>{s.description=f.value(),s.post(route("admin.custom-form.store"),{})};return V(()=>{f=new O({previewClass:"editor-preview prose max-w-none"})}),(o,l)=>{const h=d("app-head"),y=d("inertia-link"),v=d("FormKit");return k(),C(X,null,{default:m(()=>[a(h,{title:"Create Custom Form"}),e("div",M,[e("div",N,[e("h1",T,n(o.__("Create Custom Form")),1),a(y,{href:o.route("admin.custom-form.index"),class:"inline-flex items-center px-4 py-2 bg-gray-400 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 active:bg-gray-600 focus:outline-none focus:border-gray-500 focus:shadow-outline-gray transition ease-in-out duration-150"},{default:m(()=>[e("span",null,n(o.__("Cancel")),1)]),_:1},8,["href"])]),e("div",j,[e("div",A,[e("div",B,[e("form",{onSubmit:F(b,["prevent"])},[e("div",L,[e("div",P,[e("div",$,[e("div",q,[a(v,{label:"Username",type:"text",help:"Pick a new username",validation:"required|matches:/^@[a-zA-Z]+$/|length:5",value:"@FormKit"}),a(c,{id:"title",modelValue:t(s).title,"onUpdate:modelValue":l[0]||(l[0]=i=>t(s).title=i),label:o.__("Title of Custom Form"),help:o.__("Eg: Contact Us"),error:t(s).errors.title,type:"text",name:"title","help-error-flex":"flex-row"},null,8,["modelValue","label","help","error"])]),e("div",z,[a(c,{id:"slug",modelValue:t(s).slug,"onUpdate:modelValue":l[1]||(l[1]=i=>t(s).slug=i),label:o.__("Form Slug"),help:o.__("Only alphabet, number and dashes. Eg: contact-us"),error:t(s).errors.slug,type:"text",name:"slug","help-error-flex":"flex-row"},null,8,["modelValue","label","help","error"])]),e("div",I,[a(E,{id:"status",modelValue:p.value,"onUpdate:modelValue":l[2]||(l[2]=i=>p.value=i),name:"status",label:o.__("Form Status"),placeholder:o.__("Select a status of form.."),"disable-null":!0,"select-list":g},null,8,["modelValue","label","placeholder"])]),e("div",Z,[U(e("textarea",{id:"description","onUpdate:modelValue":l[3]||(l[3]=i=>t(s).description=i),"aria-label":"description",name:"description",class:"mt-1 focus:ring-light-blue-500 focus:border-light-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"},null,512),[[S,t(s).description]]),a(r,{message:t(s).errors.description,class:"mt-2 text-right"},null,8,["message"])]),e("div",G,[e("fieldset",null,[e("legend",H,n(o.__("Options")),1),e("div",J,[a(_,{id:"is_only_auth",modelValue:t(s).is_only_auth,"onUpdate:modelValue":l[4]||(l[4]=i=>t(s).is_only_auth=i),label:o.__("Auth Users Only"),help:o.__("Only authenticated users should be able to fill this form."),name:"is_only_auth"},null,8,["modelValue","label","help"]),a(_,{id:"is_only_staff",modelValue:t(s).is_only_staff,"onUpdate:modelValue":l[5]||(l[5]=i=>t(s).is_only_staff=i),label:o.__("Staff Only"),help:o.__("Only staff members can view & fill this form."),name:"is_only_staff"},null,8,["modelValue","label","help"]),a(_,{id:"is_notify_staff_on_submission",modelValue:t(s).is_notify_staff_on_submission,"onUpdate:modelValue":l[6]||(l[6]=i=>t(s).is_notify_staff_on_submission=i),label:o.__("Notify Staff on Submission"),help:o.__("Notify staff members (with view submission permission) when new submission is made for this form."),name:"is_notify_staff_on_submission"},null,8,["modelValue","label","help"])]),a(r,{message:t(s).errors.is_in_navbar,class:"mt-2"},null,8,["message"]),a(r,{message:t(s).errors.is_visible,class:"mt-2"},null,8,["message"]),a(r,{message:t(s).errors.is_sidebar_visible,class:"mt-2"},null,8,["message"])])])])]),e("div",Q,[a(D,{loading:t(s).processing,class:"inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-light-blue-500 hover:bg-light-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 disabled:opacity-50",type:"submit"},{default:m(()=>[u(n(o.__("Create Custom Form")),1)]),_:1},8,["loading"])])])],40,K)]),e("div",R,[e("div",W,[e("h3",Y,n(o.__("Form Preview")),1),e("p",ee,[u(n(o.__("Using custom pages you can create a page based on markdown to show information like privacy, rules etc."))+" ",1),se,u(" "+n(o.__("Using custom pages you can also redirect to some external links.")),1)])])])])])])]),_:1})}}};export{ce as default};