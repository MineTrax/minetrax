import{L as E}from"./LoadingSpinner-CFw8TACB.js";import{X as U}from"./XTextarea-WELTfL_5.js";import{L as H}from"./LoadingButton-CIF9JOGX.js";import{u as W}from"./AppLayout-_QfSG3gX.js";import{u as X}from"./useAuthorizable-CnlAWZWI.js";import{_ as N}from"./UserDisplayname-DiGdz_bY.js";import{I as O}from"./Icon-Bn9n1IU_.js";import{_ as Q}from"./CommonStatusBadge-CgZiGVBu.js";import{o as a,d as i,a as u,r as p,H as G,j as J,D as K,q as P,y as S,t as y,e as m,u as s,b as c,F as V,g as Y,w as h,m as v,f as D,n as b,c as w,k as j}from"./app-BDqFx3nJ.js";function ee(o,k){return a(),i("svg",{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24",fill:"currentColor","aria-hidden":"true","data-slot":"icon"},[u("path",{d:"M3.478 2.404a.75.75 0 0 0-.926.941l2.432 7.905H13.5a.75.75 0 0 1 0 1.5H4.984l-2.432 7.905a.75.75 0 0 0 .926.94 60.519 60.519 0 0 0 18.445-8.986.75.75 0 0 0 0-1.218A60.517 60.517 0 0 0 3.478 2.404Z"})])}function $(o,k){return a(),i("svg",{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24",fill:"currentColor","aria-hidden":"true","data-slot":"icon"},[u("path",{"fill-rule":"evenodd",d:"M12.516 2.17a.75.75 0 0 0-1.032 0 11.209 11.209 0 0 1-7.877 3.08.75.75 0 0 0-.722.515A12.74 12.74 0 0 0 2.25 9.75c0 5.942 4.064 10.933 9.563 12.348a.749.749 0 0 0 .374 0c5.499-1.415 9.563-6.406 9.563-12.348 0-1.39-.223-2.73-.635-3.985a.75.75 0 0 0-.722-.516l-.143.001c-2.996 0-5.717-1.17-7.734-3.08Zm3.094 8.016a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z","clip-rule":"evenodd"})])}const se={class:"flex flex-col p-3 space-y-4"},te={class:"font-extrabold text-gray-800 dark:text-gray-200"},ae={key:0,class:"p-1.5 text-sm text-red-500 bg-red-100 border border-red-500 rounded dark:text-red-400 dark:bg-red-200 dark:bg-opacity-10"},re={key:1,class:"flex items-center justify-center p-4 min-h-64"},ie={id:"messagesContainer",class:"overflow-y-auto min-h-64 max-h-96"},ne={key:0,class:"flex justify-center pt-4 text-sm text-gray-500 dark:text-gray-400"},oe={key:1,class:"flex flex-col mt-3 mr-4 space-y-2"},le={key:0,class:"italic text-sm text-gray-700 dark:text-gray-300"},ue={class:"mr-1"},de=["title"],ce=["src"],me=["title"],ge=["title"],fe=["innerHTML"],pe={id:"send-message",class:"mt-4"},_e={class:"flex justify-end mt-2"},Te={__name:"RecruitmentMessagesBox",props:{submission:{type:Object,required:!0},forAdmin:{type:Boolean,default:!1}},setup(o){const{purifyAndLinkifyText:k,formatTimeAgoToNow:B,formatToDayDateString:L}=W(),{can:R}=X(),l=o,x=p("");let n=p([]),C=p(null),_=p(!0),f=p(!1),A=p(null);const I=p(null),F=l.forAdmin?route("admin.recruitment-submission.message.index",{submission:l.submission.id}):route("recruitment-submission.message.index",{recruitment:l.submission.recruitment.slug,submission:l.submission.id}),Z=l.forAdmin?route("admin.recruitment-submission.message.store",{submission:l.submission.id}):route("recruitment-submission.message.store",{recruitment:l.submission.recruitment.slug,submission:l.submission.id}),M=()=>{j(()=>{const t=document.getElementById("messagesContainer");t.scrollTo({top:t.scrollHeight,behavior:"smooth"})})};G(n,()=>{M()}),J(async()=>{_.value=!0,axios.get(F).then(t=>{n.value=t.data}).finally(()=>{_.value=!1,M()}),C.value=setInterval(()=>q(),5e3)}),K(()=>{clearInterval(C.value)});const T=(t=null)=>{f.value||(f.value=!0,A.value=null,axios.post(Z,{message:x.value,type:t}).then(r=>{r.status===200&&(n.value.push(r.data.data),M())}).catch(r=>{var g,d;A.value=((d=(g=r.response)==null?void 0:g.data)==null?void 0:d.message)||r.message||"Something went wrong."}).finally(()=>{x.value="",f.value=!1,j(()=>{I.value.focus()})}))},q=()=>{const t=n.value.length>0?n.value[n.value.length-1].id:0,r=l.forAdmin?route("admin.recruitment-submission.message.index",{submission:l.submission.id,after:t}):route("recruitment-submission.message.index",{recruitment:l.submission.recruitment.slug,submission:l.submission.id,after:t});axios.get(r).then(g=>{const d=g.data;d.length>0&&(n.value=[...n.value,...d])})};return(t,r)=>{const g=P("InertiaLink"),d=S("tippy"),z=S("confirm");return a(),i("div",se,[u("h3",te,y(t.__("Messages Box")),1),o.forAdmin&&!o.submission.recruitment.is_allow_messages_from_users?(a(),i("span",ae,y(t.__("Messages feature is disabled for this recruitment. Applicant can't send messages.")),1)):m("",!0),s(_)?(a(),i("div",re,[c(E,{loading:s(_)},null,8,["loading"])])):m("",!0),u("div",ie,[!s(_)&&s(n)&&s(n).length===0?(a(),i("div",ne,y(t.__("No messages found.")),1)):m("",!0),!s(_)&&s(n)&&s(n).length>0?(a(),i("div",oe,[(a(!0),i(V,null,Y(s(n),e=>(a(),i("div",{key:e.id,class:"flex space-y-2"},[e.type.value==="recruitment_action"?(a(),i("div",le,[c(g,{as:"span",class:"hover:cursor-pointer inline-block",href:t.route("user.public.get",e.commentator.username)},{default:h(()=>[c(N,{user:e.commentator,"show-username":!1,"show-badges":!1,"text-class":"font-sm"},null,8,["user"])]),_:2},1032,["href"]),u("span",ue,y(t.__(" changed application status to ")),1),c(Q,{status:e.comment},null,8,["status"]),v((a(),i("span",{class:"inline ml-1 text-xs text-gray-500 dark:text-gray-400 focus:outline-none",title:s(L)(e.created_at)},[D(y(s(B)(e.created_at)),1)],8,de)),[[d]])])):(a(),i(V,{key:1},[u("img",{src:e.commentator.profile_photo_url,alt:"My profile",class:b(["w-8 h-8 mt-2 rounded-full",{"order-4":e.type.value==="recruitment_applicant_message"}])},null,10,ce),e.type.value==="recruitment_staff_whisper"?v((a(),i("div",{key:0,class:b(["flex mt-3.5 ml-1",{"order-3":e.type.value==="recruitment_applicant_message"}]),title:t.__("This is a whisper message only visible to staff")},[c(s($),{class:"w-5 h-5 text-yellow-400 dark:text-yellow-300"})],10,me)),[[d]]):m("",!0),u("div",{class:b(["items-start w-full mx-2 space-y-2 text-sm",{"order-2 text-right":e.type.value==="recruitment_applicant_message"}])},[u("div",{class:b(["flex flex-col px-4 py-2 text-gray-700 rounded-2xl dark:bg-opacity-25 dark:text-gray-200",{"bg-orange-100 dark:bg-yellow-300":e.type.value==="recruitment_staff_whisper","bg-gray-200 dark:bg-cool-gray-500":e.type.value==="recruitment_staff_message","bg-gray-100 dark:bg-cool-gray-600":e.type.value==="recruitment_applicant_message"}])},[c(g,{as:"a",class:"hover:cursor-pointer hover:underline",href:t.route("user.public.get",e.commentator.username)},{default:h(()=>[c(N,{user:e.commentator,"show-username":!0,"text-class":"font-sm"},{default:h(()=>[v((a(),i("span",{class:"inline ml-1 text-xs text-gray-500 dark:text-gray-400 focus:outline-none",title:s(L)(e.created_at)},[D(y(s(B)(e.created_at)),1)],8,ge)),[[d]])]),_:2},1032,["user"])]),_:2},1032,["href"]),u("p",{class:"leading-snug break-words whitespace-pre-line md:leading-normal",innerHTML:s(k)(e.comment)},null,8,fe)],2)],2),s(R)("delete recruitment_submission_messages")&&o.forAdmin?v((a(),w(g,{key:1,"preserve-scroll":!0,"preserve-state":!1,as:"button",method:"delete",href:t.route("admin.recruitment-submission.message.delete",[o.submission.id,e.id]),class:b(["focus:outline-none",{"order-1":e.type.value==="recruitment_applicant_message"}])},{default:h(()=>[c(O,{name:"trash",class:"w-4 h-4 text-gray-200 hover:text-red-400 dark:text-gray-500 dark:hover:text-red-500"})]),_:2},1032,["href","class"])),[[z,{message:t.__("Are you sure you want to delete this comment?")}]]):m("",!0)],64))]))),128))])):m("",!0)]),u("div",pe,[c(U,{ref_key:"inputbox",ref:I,modelValue:x.value,"onUpdate:modelValue":r[0]||(r[0]=e=>x.value=e),disabled:!o.submission.i_can_send_message,label:"Write your message here...",class:"w-full",error:s(A)},null,8,["modelValue","disabled","error"]),u("div",_e,[o.forAdmin?v((a(),w(H,{key:0,disabled:!o.submission.i_can_send_message,title:t.__("Send whisper to other staff members. This message will be private and only visible to staff members."),class:"px-4 py-2 mr-2 font-bold text-yellow-500 bg-transparent rounded hover:text-yellow-400 dark:text-white disabled:cursor-not-allowed disabled:opacity-25",loading:s(f),onClick:r[1]||(r[1]=e=>T("recruitment_staff_whisper"))},{default:h(()=>[s(f)?m("",!0):(a(),w(s($),{key:0,class:"w-5 h-5"}))]),_:1},8,["disabled","title","loading"])),[[d]]):m("",!0),c(H,{disabled:!o.submission.i_can_send_message,class:"px-4 py-2 text-white rounded bg-sky-500 hover:bg-sky-600 disabled:cursor-not-allowed disabled:opacity-25",loading:s(f),onClick:r[2]||(r[2]=e=>o.forAdmin?T("recruitment_staff_message"):T())},{default:h(()=>[s(f)?m("",!0):(a(),w(s(ee),{key:0,class:"w-5 h-5"}))]),_:1},8,["disabled","loading"])])])])}}};export{Te as _};