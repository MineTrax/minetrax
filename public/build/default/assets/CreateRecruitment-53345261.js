import{T as L,A as M,r as j,x as N,E as T,l as x,o as u,c as h,w as m,b as a,a as s,t as r,i as O,u as o,j as q,v as R,e as D,f as p,d as g,g as P,F as X,H as B,I as H}from"./app-f1704ce4.js";import{_ as c}from"./InputError-99d060cb.js";import{L as V}from"./LoadingButton-37976f47.js";import{X as d}from"./XInput-84f7468c.js";import{X as y}from"./XSelect-071eb764.js";import{X as b}from"./XCheckbox-3936c579.js";import{E as I}from"./easymde-fc3d3c3c.js";import{_ as W}from"./AdminLayout-cf9c4a02.js";import{I as K}from"./Icon-4144b1c7.js";import{_ as Y}from"./DialogModal-9816ba23.js";import{_ as z}from"./SecondaryButton-c6d8a330.js";import{u as G}from"./useFormKit-cc6f0e5f.js";import"./_plugin-vue_export-helper-c27b6911.js";import"./AppLayout-5f3e8958.js";import"./useAuthorizable-18c71ef1.js";import"./use-resolve-button-type-18f071c1.js";import"./open-closed-113ca95c.js";import"./CloudArrowDownIcon-6c974300.js";import"./index-638ebb4e.js";import"./Modal-1a38c55d.js";const J={class:"py-12 px-10 max-w-7xl mx-auto"},Q={class:"flex justify-between mb-8"},Z={class:"font-bold text-3xl text-gray-500 dark:text-gray-300"},ee={class:"mt-10 sm:mt-0"},se={class:"md:grid md:grid-cols-6 md:gap-6"},le={class:"mt-5 md:mt-0 md:col-span-6"},oe=["onSubmit"],te={class:"shadow overflow-hidden sm:rounded-md"},ie={class:"px-4 py-5 bg-white dark:bg-cool-gray-800 sm:p-6"},ae={class:"grid grid-cols-6 gap-4"},re={class:"col-span-6 sm:col-span-6"},ne={class:"col-span-6 sm:col-span-6"},de={class:"col-span-6 sm:col-span-6"},_e={class:"col-span-6 sm:col-span-6"},me={class:"col-span-6 sm:col-span-3"},ue={class:"col-span-6 sm:col-span-3"},pe={class:"col-span-6 sm:col-span-3"},ce={class:"col-span-6 sm:col-span-3"},fe={class:"col-span-6 sm:col-span-6"},be={class:"flex items-center col-span-6 sm:col-span-3"},he={class:"mt-4 flex space-x-4"},ge={class:"flex items-center col-span-6 sm:col-span-3"},ye={class:"mt-4 flex space-x-4"},we={class:"flex items-center col-span-6 sm:col-span-3"},ve={class:"mt-4 flex space-x-4"},xe={class:"flex items-center col-span-6 sm:col-span-3"},Ve={class:"mt-4 flex space-x-4"},ke={class:"col-span-6 sm:col-span-3"},Ue={class:"flex-col col-span-6 space-y-1 sm:col-span-6"},Ae={class:"text-base font-medium text-gray-900 dark:text-gray-300"},Fe={class:"w-full space-y-1"},Se={class:"flex space-x-4"},Ce=s("div",{class:"w-5"},null,-1),Ee={class:"flex-1 block text-sm font-medium text-gray-700 dark:text-gray-400"},$e=s("span",{class:"text-red-500"},"*",-1),Le={class:"flex-1 block text-sm font-medium text-gray-700 dark:text-gray-400"},Me=s("span",{class:"text-red-500"},"*",-1),je={class:"flex-1 block text-sm font-medium text-gray-700 dark:text-gray-400"},Ne={class:"flex-1 block text-sm font-medium text-gray-700 dark:text-gray-400"},Te={class:"flex-1 block text-sm font-medium text-gray-700 dark:text-gray-400"},Oe=s("span",{class:"text-red-500"},"* ",-1),qe=s("span",{class:"text-xs text-gray-500"},"(Eg: Options1,Option2)",-1),Re=["onClick"],De={class:"flex-1"},Pe={class:"flex-1"},Xe={class:"flex-1"},Be={class:"flex-1"},He={class:"flex-1"},Ie={key:1,class:"h-full text-gray-700 text-lg font-semibold dark:text-gray-300 w-full flex items-center justify-center"},We={class:"flex justify-end mt-1"},Ke={class:"px-4 py-3 bg-gray-50 dark:bg-cool-gray-800 sm:px-6 flex justify-between gap-2"},Ye={class:"text-lg font-bold"},fs={__name:"CreateRecruitment",props:{roles:{type:Array,required:!0}},setup(k){const U={draft:"Draft - Application is under development and not visible to users",active:"Active - Application is actively accepting submissions",disabled:"Disabled - Application is disabled for new submissions",archived:"Archived - Application is archived and not visible to users"},w={text:{},textarea:{},select:{hasOptions:!0},multiselect:{hasOptions:!0},radio:{hasOptions:!0},checkbox:{},email:{},number:{},password:{},tel:{},url:{},week:{},month:{},time:{},date:{},"datetime-local":{}},e=L({title:"",slug:"",status:"draft",description:"",max_submission_per_user:null,submission_cooldown_in_seconds:null,is_allow_only_player_linked_users:!1,is_allow_only_verified_users:!1,is_allow_messages_from_users:!0,min_role_weight_to_view_submission:null,min_role_weight_to_vote_on_submission:null,min_role_weight_to_act_on_submission:null,is_notify_staff_on_submission:!0,related_role_id:null,fields:[{type:"number",label:"Years of Experience",name:"experience",placeholder:null,help:null,validation:"required|number",options:null},{type:"textarea",label:"Tell us about yourself",name:"aboutme",placeholder:null,help:"Write about your experience, skills, and why you want to join us.",validation:"required|string",options:null}]});let v=null;const A=()=>{e.description=v.value(),e.fields.map(l=>{l.name=l.label.toLowerCase().replace(/ /g,"_")}),e.post(route("admin.recruitment.store"),{})};M(()=>{v=new I({previewClass:"editor-preview prose max-w-none"})});function F(){e.fields.push({type:"text",label:"",name:"",validation:"required"})}function S(l){e.fields.length!==1&&e.fields.splice(l,1)}const f=j(!1),C=N(()=>G().generateSchemaFromFieldsArray(e.fields));return T(()=>e.title,l=>{e.slug=H.kebabCase(l)}),(l,i)=>{const E=x("app-head"),$=x("inertia-link");return u(),h(W,null,{default:m(()=>[a(E,{title:"Create Application Form"}),s("div",J,[s("div",Q,[s("h1",Z,r(l.__("Create Application Form")),1),a($,{href:l.route("admin.recruitment.index"),class:"inline-flex items-center px-4 py-2 bg-gray-400 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 active:bg-gray-600 focus:outline-none focus:border-gray-500 focus:shadow-outline-gray transition ease-in-out duration-150"},{default:m(()=>[s("span",null,r(l.__("Cancel")),1)]),_:1},8,["href"])]),s("div",ee,[s("div",se,[s("div",le,[s("form",{onSubmit:O(A,["prevent"])},[s("div",te,[s("div",ie,[s("div",ae,[s("div",re,[a(d,{id:"title",modelValue:o(e).title,"onUpdate:modelValue":i[0]||(i[0]=t=>o(e).title=t),label:l.__("Title of this Application"),help:l.__("Eg: Apply to be a Staff Member"),error:o(e).errors.title,type:"text",name:"title","help-error-flex":"flex-row"},null,8,["modelValue","label","help","error"])]),s("div",ne,[a(d,{id:"slug",modelValue:o(e).slug,"onUpdate:modelValue":i[1]||(i[1]=t=>o(e).slug=t),label:l.__("Application Slug for URL"),help:l.__("Only alphabet, number and dashes. Eg: apply-to-be-a-staff-member"),error:o(e).errors.slug,type:"text",name:"slug","help-error-flex":"flex-row"},null,8,["modelValue","label","help","error"])]),s("div",de,[a(y,{id:"status",modelValue:o(e).status,"onUpdate:modelValue":i[2]||(i[2]=t=>o(e).status=t),name:"status",label:l.__("Application Status"),placeholder:l.__("Select a status of application.."),"disable-null":!0,"select-list":U},null,8,["modelValue","label","placeholder"])]),s("div",_e,[q(s("textarea",{id:"description","onUpdate:modelValue":i[3]||(i[3]=t=>o(e).description=t),"aria-label":"description",name:"description",class:"mt-1 focus:ring-light-blue-500 focus:border-light-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"},null,512),[[R,o(e).description]]),a(c,{message:o(e).errors.description,class:"mt-2 text-right"},null,8,["message"])]),s("div",me,[a(d,{id:"max_submission_per_user",modelValue:o(e).max_submission_per_user,"onUpdate:modelValue":i[4]||(i[4]=t=>o(e).max_submission_per_user=t),label:l.__("Max Submission Per User"),help:l.__("How many times a user can reapply after rejection. Leave empty for no limit."),error:o(e).errors.max_submission_per_user,type:"number",name:"max_submission_per_user","help-error-flex":"flex-row"},null,8,["modelValue","label","help","error"])]),s("div",ue,[o(e).max_submission_per_user!=1?(u(),h(d,{key:0,id:"submission_cooldown_in_seconds",modelValue:o(e).submission_cooldown_in_seconds,"onUpdate:modelValue":i[5]||(i[5]=t=>o(e).submission_cooldown_in_seconds=t),label:l.__("Submission Cooldown in Seconds"),help:l.__("After how many seconds user can reapply this application. Leave empty for no cooldown."),error:o(e).errors.submission_cooldown_in_seconds,type:"number",name:"submission_cooldown_in_seconds","help-error-flex":"flex-row"},null,8,["modelValue","label","help","error"])):D("",!0)]),s("div",pe,[a(d,{id:"min_role_weight_to_view_submission",modelValue:o(e).min_role_weight_to_view_submission,"onUpdate:modelValue":i[6]||(i[6]=t=>o(e).min_role_weight_to_view_submission=t),label:l.__("Min Staff Role Weight to View Submission"),help:l.__("Leave empty to allow any staff with [view recruitment_submissions] permission to view submissions."),error:o(e).errors.min_role_weight_to_view_submission,type:"number",name:"min_role_weight_to_view_submission","help-error-flex":"flex-row"},null,8,["modelValue","label","help","error"])]),s("div",ce,[a(d,{id:"min_role_weight_to_vote_on_submission",modelValue:o(e).min_role_weight_to_vote_on_submission,"onUpdate:modelValue":i[7]||(i[7]=t=>o(e).min_role_weight_to_vote_on_submission=t),label:l.__("Min Staff Role Weight to Vote on Submission"),help:l.__("Leave empty to allow any staff with [vote recruitment_submissions] permission to vote on submissions."),error:o(e).errors.min_role_weight_to_vote_on_submission,type:"number",name:"min_role_weight_to_vote_on_submission","help-error-flex":"flex-row"},null,8,["modelValue","label","help","error"])]),s("div",fe,[a(d,{id:"min_role_weight_to_act_on_submission",modelValue:o(e).min_role_weight_to_act_on_submission,"onUpdate:modelValue":i[8]||(i[8]=t=>o(e).min_role_weight_to_act_on_submission=t),label:l.__("Min Staff Role Weight to Act on Submission"),help:l.__("Min staff role weight required to Approve/Reject on submission. Leave empty to allow any staff with [acton recruitment_submissions] permission to act on submissions."),error:o(e).errors.min_role_weight_to_act_on_submission,type:"number",name:"min_role_weight_to_act_on_submission","help-error-flex":"flex-row"},null,8,["modelValue","label","help","error"])]),s("div",be,[s("fieldset",null,[s("div",he,[a(b,{id:"is_allow_messages_from_users",modelValue:o(e).is_allow_messages_from_users,"onUpdate:modelValue":i[9]||(i[9]=t=>o(e).is_allow_messages_from_users=t),label:l.__("Enable Messages Feature"),help:l.__("Enable messages feature for this application. User & Staff will be able to send messages."),name:"is_allow_messages_from_users"},null,8,["modelValue","label","help"])]),a(c,{message:o(e).errors.is_allow_messages_from_users,class:"mt-2"},null,8,["message"])])]),s("div",ge,[s("fieldset",null,[s("div",ye,[a(b,{id:"is_notify_staff_on_submission",modelValue:o(e).is_notify_staff_on_submission,"onUpdate:modelValue":i[10]||(i[10]=t=>o(e).is_notify_staff_on_submission=t),label:l.__("Notify Staff on Event"),help:l.__("Notify staff (with view permission) when application created/withdrawn or message from user."),name:"is_notify_staff_on_submission"},null,8,["modelValue","label","help"])]),a(c,{message:o(e).errors.is_notify_staff_on_submission,class:"mt-2"},null,8,["message"])])]),s("div",we,[s("fieldset",null,[s("div",ve,[a(b,{id:"is_allow_only_player_linked_users",modelValue:o(e).is_allow_only_player_linked_users,"onUpdate:modelValue":i[11]||(i[11]=t=>o(e).is_allow_only_player_linked_users=t),label:l.__("Allow only Player Linked Users"),help:l.__("Allow only users who have linked player to their account to apply."),name:"is_allow_only_player_linked_users"},null,8,["modelValue","label","help"])]),a(c,{message:o(e).errors.is_allow_only_player_linked_users,class:"mt-2"},null,8,["message"])])]),s("div",xe,[s("fieldset",null,[s("div",Ve,[a(b,{id:"is_allow_only_verified_users",modelValue:o(e).is_allow_only_verified_users,"onUpdate:modelValue":i[12]||(i[12]=t=>o(e).is_allow_only_verified_users=t),label:l.__("Allow only Verified Users"),help:l.__("Allow only verified users to apply for this application."),name:"is_allow_only_verified_users"},null,8,["modelValue","label","help"])]),a(c,{message:o(e).errors.is_allow_only_verified_users,class:"mt-2"},null,8,["message"])])]),s("div",ke,[a(y,{id:"related_role_id",modelValue:o(e).related_role_id,"onUpdate:modelValue":i[13]||(i[13]=t=>o(e).related_role_id=t),name:"related_role_id",label:l.__("This Application is Hiring for"),placeholder:l.__("Not Applicable (None)"),"disable-null":!1,"select-list":k.roles,help:l.__("If this application is for hiring of a specific role, select the role here.")},null,8,["modelValue","label","placeholder","select-list","help"])]),s("div",Ue,[s("legend",Ae,r(l.__("Fields")),1),s("div",Fe,[s("div",Se,[Ce,s("label",Ee,[p(r(l.__("Name"))+" ",1),$e]),s("label",Le,[p(r(l.__("Type"))+" ",1),Me]),s("label",je,r(l.__("Validation")),1),s("label",Ne,r(l.__("Help Text")),1),s("label",Te,[p(r(l.__("Options"))+" ",1),Oe,qe])]),(u(!0),g(X,null,P(o(e).fields,(t,n)=>(u(),g("div",{key:n,class:"flex space-x-4"},[s("button",{type:"button",class:"focus:outline-none group",onClick:_=>S(n)},[a(K,{class:"w-5 h-5 text-gray-300 group-hover:text-red-500",name:"trash"})],8,Re),s("div",De,[a(d,{modelValue:t.label,"onUpdate:modelValue":_=>t.label=_,label:l.__("Name Field :index",{index:n+1}),error:o(e).errors[`fields.${n}.label`]||o(e).errors[`fields.${n}.name`],type:"text","help-error-flex":"flex-col",required:!0},null,8,["modelValue","onUpdate:modelValue","label","error"])]),s("div",Pe,[a(y,{modelValue:t.type,"onUpdate:modelValue":_=>t.type=_,label:l.__("Page Type"),error:o(e).errors[`fields.${n}.type`],"help-error-flex":"flex-col","select-list":Object.keys(w),required:!0},null,8,["modelValue","onUpdate:modelValue","label","error","select-list"])]),s("div",Xe,[a(d,{modelValue:t.validation,"onUpdate:modelValue":_=>t.validation=_,label:l.__("Validation Field :index",{index:n+1}),error:o(e).errors[`fields.${n}.validation`],type:"text","help-error-flex":"flex-col"},null,8,["modelValue","onUpdate:modelValue","label","error"])]),s("div",Be,[a(d,{modelValue:t.help,"onUpdate:modelValue":_=>t.help=_,label:l.__("Help Text Field :index",{index:n+1}),error:o(e).errors[`fields.${n}.help`],type:"text","help-error-flex":"flex-col"},null,8,["modelValue","onUpdate:modelValue","label","error"])]),s("div",He,[w[t.type].hasOptions?(u(),h(d,{key:0,modelValue:t.options,"onUpdate:modelValue":_=>t.options=_,label:l.__("Options Field :index",{index:n+1}),error:o(e).errors[`fields.${n}.options`],type:"text","help-error-flex":"flex-col",required:!0},null,8,["modelValue","onUpdate:modelValue","label","error"])):(u(),g("div",Ie," - "))])]))),128)),s("div",We,[s("button",{type:"button",class:"p-1.5 text-xs text-light-blue-500 rounded border border-light-blue-500 focus:outline-none hover:text-light-blue-300 hover:border-light-blue-300 transition ease-in-out duration-150",onClick:F},r(l.__("Add New Field")),1)])])])])]),s("div",Ke,[a(V,{class:"inline-flex justify-center py-2 px-4 border border-gray-200 shadow-sm text-sm font-medium rounded-md text-gray-600 bg-gray-50 hover:bg-white disabled:opacity-50 dark:bg-gray-700 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 dark:hover:border-gray-600",type:"button",onClick:i[14]||(i[14]=t=>f.value=!0)},{default:m(()=>[p(r(l.__("Preview Form")),1)]),_:1}),a(V,{loading:o(e).processing,class:"inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-light-blue-500 hover:bg-light-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 disabled:opacity-50",type:"submit"},{default:m(()=>[p(r(l.__("Create Application Form")),1)]),_:1},8,["loading"])])])],40,oe)])])]),a(Y,{show:f.value,onClose:i[16]||(i[16]=t=>f.value=!1)},{title:m(()=>[s("h3",Ye,r(l.__("Form Preview")),1)]),content:m(()=>[a(o(B),{schema:C.value},null,8,["schema"])]),footer:m(()=>[a(z,{onClick:i[15]||(i[15]=t=>f.value=!1)},{default:m(()=>[p(r(l.__("Close")),1)]),_:1})]),_:1},8,["show"])])]),_:1})}}};export{fs as default};