import{A as w}from"./AppLayout-1a75b1e0.js";import F from"./DeleteUserForm-9eb42b25.js";import{J as y}from"./SectionBorder-43d29163.js";import $ from"./LogoutOtherBrowserSessionsForm-b5b5c0ab.js";import k from"./TwoFactorAuthenticationForm-009849cb.js";import v from"./UpdatePasswordForm-b889b140.js";import P from"./UpdateProfileInformationForm-c854df5d.js";import b from"./UpdateNotificationPreferencesForm-8845a889.js";import{_ as j}from"./_plugin-vue_export-helper-c27b6911.js";import{l as t,o as s,c as B,w as p,a as n,t as U,b as o,d as a,e as m,F as x}from"./app-bf4e71fd.js";import"./useAuthorizable-0466038e.js";import"./ActionSection-298bfb2c.js";import"./SectionTitle-4aa61c3a.js";import"./DialogModal-50e4221a.js";import"./Modal-4025d3eb.js";import"./DangerButton-7a92e259.js";import"./Input-5ef243a4.js";import"./InputError-df845cb5.js";import"./SecondaryButton-567307ee.js";import"./ActionMessage-e62d9824.js";import"./Button-3524ae8f.js";import"./XInput-4ceb38c7.js";import"./FormSection-c59da9af.js";import"./PasswordStrengthMeter-3e04957f.js";import"./Label-655f0ca9.js";import"./index.es-9a9f1e27.js";import"./XCheckbox-de0b36b6.js";import"./XSelect-09f65e03.js";import"./XTextarea-32224c58.js";const A={components:{UpdateNotificationPreferencesForm:b,AppLayout:w,DeleteUserForm:F,JetSectionBorder:y,LogoutOtherBrowserSessionsForm:$,TwoFactorAuthenticationForm:k,UpdatePasswordForm:v,UpdateProfileInformationForm:P},props:["sessions"]},S={class:"font-semibold text-xl text-gray-800 leading-tight"},N={class:"max-w-7xl mx-auto py-10 px-2 sm:px-6 lg:px-8"},C={key:0},D={key:1},V={key:2};function I(e,J,i,L,T,q){const c=t("app-head"),_=t("update-profile-information-form"),r=t("jet-section-border"),l=t("update-notification-preferences-form"),f=t("update-password-form"),u=t("two-factor-authentication-form"),d=t("logout-other-browser-sessions-form"),h=t("delete-user-form"),g=t("app-layout");return s(),B(g,null,{header:p(()=>[n("h2",S,U(e.__("Profile")),1)]),default:p(()=>[o(c,{title:e.__("Your Profile Settings")},null,8,["title"]),n("div",null,[n("div",N,[e.$page.props.jetstream.canUpdateProfileInformation?(s(),a("div",C,[o(_,{user:e.$page.props.auth.user},null,8,["user"]),o(r)])):m("",!0),n("div",null,[o(l,{user:e.$page.props.auth.user},null,8,["user"]),o(r)]),e.$page.props.jetstream.canUpdatePassword?(s(),a("div",D,[o(f,{class:"mt-10 sm:mt-0"}),o(r)])):m("",!0),e.$page.props.jetstream.canManageTwoFactorAuthentication?(s(),a("div",V,[o(u,{class:"mt-10 sm:mt-0","requires-confirmation":!0}),o(r)])):m("",!0),o(d,{sessions:i.sessions,class:"mt-10 sm:mt-0"},null,8,["sessions"]),e.$page.props.jetstream.hasAccountDeletionFeatures?(s(),a(x,{key:3},[o(r),o(h,{class:"mt-10 sm:mt-0"})],64)):m("",!0)])])]),_:1})}const go=j(A,[["render",I]]);export{go as default};