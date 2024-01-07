import{A as w}from"./AppLayout-3179ebfe.js";import F from"./DeleteUserForm-df7e8c85.js";import{J as y}from"./SectionBorder-b6fe1856.js";import $ from"./LogoutOtherBrowserSessionsForm-72c8fa31.js";import k from"./TwoFactorAuthenticationForm-4a5626df.js";import v from"./UpdatePasswordForm-e139bb73.js";import P from"./UpdateProfileInformationForm-ef978d6b.js";import b from"./UpdateNotificationPreferencesForm-272fb21f.js";import{_ as j,l as t,o as s,c as B,w as p,a as n,t as U,b as o,d as a,e as m,F as x}from"./app-f18f52ce.js";import"./useAuthorizable-e8dc1556.js";import"./ActionSection-779af95b.js";import"./SectionTitle-7d46ebdb.js";import"./DialogModal-594caad5.js";import"./Modal-54c15275.js";import"./DangerButton-90e6b9a9.js";import"./Input-29cbeaa0.js";import"./InputError-0315f9ff.js";import"./SecondaryButton-b342fb66.js";import"./ActionMessage-710bafec.js";import"./Button-48935d52.js";import"./XInput-71728fea.js";import"./FormSection-8ac0ec61.js";import"./PasswordStrengthMeter-63949954.js";import"./Label-94c888d1.js";import"./index.es-ca5aed53.js";import"./XCheckbox-20c33ffe.js";import"./XSelect-95884851.js";import"./XTextarea-167a61cb.js";const A={components:{UpdateNotificationPreferencesForm:b,AppLayout:w,DeleteUserForm:F,JetSectionBorder:y,LogoutOtherBrowserSessionsForm:$,TwoFactorAuthenticationForm:k,UpdatePasswordForm:v,UpdateProfileInformationForm:P},props:["sessions"]},S={class:"font-semibold text-xl text-gray-800 leading-tight"},N={class:"max-w-7xl mx-auto py-10 px-2 sm:px-6 lg:px-8"},C={key:0},D={key:1},V={key:2};function I(e,J,i,L,T,q){const c=t("app-head"),_=t("update-profile-information-form"),r=t("jet-section-border"),l=t("update-notification-preferences-form"),f=t("update-password-form"),u=t("two-factor-authentication-form"),d=t("logout-other-browser-sessions-form"),h=t("delete-user-form"),g=t("app-layout");return s(),B(g,null,{header:p(()=>[n("h2",S,U(e.__("Profile")),1)]),default:p(()=>[o(c,{title:e.__("Your Profile Settings")},null,8,["title"]),n("div",null,[n("div",N,[e.$page.props.jetstream.canUpdateProfileInformation?(s(),a("div",C,[o(_,{user:e.$page.props.auth.user},null,8,["user"]),o(r)])):m("",!0),n("div",null,[o(l,{user:e.$page.props.auth.user},null,8,["user"]),o(r)]),e.$page.props.jetstream.canUpdatePassword?(s(),a("div",D,[o(f,{class:"mt-10 sm:mt-0"}),o(r)])):m("",!0),e.$page.props.jetstream.canManageTwoFactorAuthentication?(s(),a("div",V,[o(u,{class:"mt-10 sm:mt-0","requires-confirmation":!0}),o(r)])):m("",!0),o(d,{sessions:i.sessions,class:"mt-10 sm:mt-0"},null,8,["sessions"]),e.$page.props.jetstream.hasAccountDeletionFeatures?(s(),a(x,{key:3},[o(r),o(h,{class:"mt-10 sm:mt-0"})],64)):m("",!0)])])]),_:1})}const ho=j(A,[["render",I]]);export{ho as default};