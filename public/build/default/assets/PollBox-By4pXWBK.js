import{u as y}from"./AppLayout-_QfSG3gX.js";import{o,d as n,a as i,F as _,g as p,n as u,l as f,t as r,e as c,x as m,q as x,b,aE as k}from"./app-BDqFx3nJ.js";import{_ as v}from"./_plugin-vue_export-helper-DlAUqK2U.js";const w={name:"Poll",props:{question:{type:String,required:!1},answers:{type:Array,required:!1},showResults:{type:Boolean,default:!1},showTotalVotes:{type:Boolean,default:!0},finalResults:{type:Boolean,default:!1},multiple:{type:Boolean,default:!1},submitButtonText:{type:String,default:"Submit"},customId:{type:Number,default:0},isComingSoon:{type:Boolean,default:!1},started_at:{type:String,default:null},closed_at:{type:String,default:null}},setup(){const{formatTimeAgoToNow:t,formatToDayDateString:s}=y();return{formatTimeAgoToNow:t,formatToDayDateString:s}},data(){return{visibleResults:JSON.parse(this.showResults)}},computed:{totalVotes(){let t=0;return this.answers.filter(s=>{!isNaN(s.votes)&&s.votes>0&&(t+=parseInt(s.votes))}),t},totalVotesFormatted(){return this.totalVotes.toString().replace(/\B(?=(\d{3})+(?!\d))/g,",")},mostVotes(){let t=0;return this.answers.filter(s=>{!isNaN(s.votes)&&s.votes>0&&s.votes>=t&&(t=s.votes)}),t},calcAnswers(){return this.totalVotes===0?this.answers.map(t=>(t.percent="0%",t)):this.answers.filter(t=>(!isNaN(t.votes)&&t.votes>0?t.percent=Math.round(parseInt(t.votes)/this.totalVotes*100)+"%":t.percent="0%",t))},totalSelections(){return this.calcAnswers.filter(t=>t.selected).length}},methods:{handleMultiple(){let t=[];this.calcAnswers.filter(e=>{e.selected&&(e.votes++,t.push({value:e.value,votes:e.votes}))}),this.visibleResults=!0;let s={arSelected:t,totalVotes:this.totalVotes};this.customId&&(s.customId=this.customId),this.$emit("addvote",s)},handleVote(t){if(this.isComingSoon)return;if(this.multiple){t.selected===void 0&&console.log("Please add 'selected: false' on the answer object"),t.selected=!t.selected;return}this.$page.props.auth.user&&(t.votes++,t.selected=!0,this.visibleResults=!0);let s={value:t.value,votes:t.votes,totalVotes:this.totalVotes};this.customId&&(s.customId=this.customId),this.$emit("addvote",s)}}},V={class:"vue-poll"},S=["innerHTML"],C={class:"ans-cnt"},T=["onClick"],N=["innerHTML"],R=["textContent"],B=["innerHTML"],L=["textContent"],M=["innerHTML"],A={key:0,class:"text-gray-400 text-xs italic"},P={class:"flex justify-between items-baseline"},H=["textContent"],I={key:1,class:"text-gray-400 text-xs italic"},q=["textContent"];function j(t,s,e,h,d,a){return o(),n("div",V,[i("h3",{class:"qst dark:text-gray-300",innerHTML:e.question},null,8,S),i("div",C,[(o(!0),n(_,null,p(a.calcAnswers,(l,g)=>(o(),n("div",{key:g,class:u({ans:!0,[l.custom_class]:l.custom_class})},[e.finalResults?(o(),n(_,{key:1},[i("div",{class:u({"ans-voted final dark:text-gray-200":!0,selected:l.selected})},[l.percent?(o(),n("span",{key:0,class:"percent",textContent:r(l.percent)},null,8,L)):c("",!0),i("span",{class:"txt",innerHTML:l.text},null,8,M)],2),i("span",{class:u({"bg bg-cool-gray-200 dark:bg-cool-gray-700":!0,"bg-light-blue-300 dark:bg-light-blue-500":a.mostVotes==l.votes}),style:m({width:l.percent})},null,6)],64)):(o(),n(_,{key:0},[d.visibleResults?(o(),n("div",{key:1,class:u({"ans-voted dark:text-gray-200":!0,selected:l.selected})},[l.percent?(o(),n("span",{key:0,class:"percent",textContent:r(l.percent)},null,8,R)):c("",!0),i("span",{class:"txt",innerHTML:l.text},null,8,B)],2)):(o(),n("div",{key:0,class:u(["hover:bg-light-blue-100 dark:hover:bg-cool-gray-900",{"ans-no-vote noselect":!0,active:l.selected}]),onClick:f(Q=>a.handleVote(l),["prevent"])},[i("span",{class:"txt",innerHTML:l.text},null,8,N)],10,T)),i("span",{class:"bg bg-cool-gray-200 dark:bg-cool-gray-700",style:m({width:d.visibleResults?l.percent:"0%"})},null,4)],64))],2))),128))]),e.isComingSoon?(o(),n("div",A,r(t.__("Poll starting"))+" "+r(h.formatTimeAgoToNow(e.started_at)),1)):c("",!0),i("div",P,[e.showTotalVotes&&(d.visibleResults||e.finalResults)?(o(),n("div",{key:0,class:"votes",textContent:r(a.totalVotesFormatted+" votes")},null,8,H)):c("",!0),!e.isComingSoon&&e.closed_at&&!e.finalResults?(o(),n("div",I,r(t.__("Poll closing"))+" "+r(h.formatTimeAgoToNow(e.closed_at)),1)):c("",!0)]),!e.finalResults&&!d.visibleResults&&e.multiple&&a.totalSelections>0?(o(),n("a",{key:1,href:"#",class:"submit",onClick:s[0]||(s[0]=f((...l)=>a.handleMultiple&&a.handleMultiple(...l),["prevent"])),textContent:r(e.submitButtonText)},null,8,q)):c("",!0)])}const F=v(w,[["render",j]]),D={components:{Poll:F},props:{poll:Object,isListing:{type:Boolean,default:!1}},data(){return{options:this.poll}},methods:{addVote(t){this.poll.isComingSoon||this.$inertia.post(route("poll.vote",[this.poll.id,t.value]),null,{preserveState:!0,preserveScroll:!0})}}},z={key:0},E={class:"p-3 sm:px-5 bg-white dark:bg-cool-gray-800 rounded shadow"},O={key:0,class:"font-extrabold text-gray-800 dark:text-gray-200"},J={key:1,class:"font-extrabold text-gray-800 dark:text-gray-200"},G={class:"mt-3 text-gray-500 dark:text-gray-300"};function K(t,s,e,h,d,a){const l=x("poll");return e.poll?(o(),n("div",z,[i("div",E,[e.isListing?c("",!0):(o(),n("h3",O,r(t.__("Latest Poll")),1)),e.isListing?(o(),n("h3",J,r(t.__("Poll"))+" "+r(e.poll.id),1)):c("",!0),i("div",G,[b(l,k(d.options,{onAddvote:a.addVote}),null,16,["onAddvote"])])])])):c("",!0)}const Y=v(D,[["render",K]]);export{Y as P};