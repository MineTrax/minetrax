import{A as w}from"./AppLayout-bc98d524.js";import F from"./DeleteUserForm-c3fefc2e.js";import{J as y}from"./SectionBorder-d5862df1.js";import $ from"./LogoutOtherBrowserSessionsForm-39d98e2b.js";import k from"./TwoFactorAuthenticationForm-b485ac7a.js";import v from"./UpdatePasswordForm-45f156ab.js";import P from"./UpdateProfileInformationForm-bf4cbdd8.js";import b from"./UpdateNotificationPreferencesForm-cf6be47c.js";import{_ as j,l as t,o as s,c as B,w as p,a as n,t as U,b as o,d as a,e as m,F as x}from"./app-219916e6.js";import"./useAuthorizable-f6fcaee4.js";import"./ActionSection-eab9a47c.js";import"./SectionTitle-1d5fda19.js";import"./DialogModal-35292332.js";import"./Modal-3a5a6150.js";import"./DangerButton-29c30c6a.js";import"./Input-fb913dd0.js";import"./InputError-a0b79d1a.js";import"./SecondaryButton-6f634be2.js";import"./ActionMessage-e64adfe2.js";import"./Button-aea4d8b2.js";import"./XInput-c2282f3e.js";import"./FormSection-99eda654.js";import"./PasswordStrengthMeter-5f847698.js";import"./Label-59485fcf.js";import"./index.es-83f30aea.js";import"./XCheckbox-f2443cad.js";import"./XSelect-2a9a783b.js";import"./XTextarea-1f6b1812.js";const A={components:{UpdateNotificationPreferencesForm:b,AppLayout:w,DeleteUserForm:F,JetSectionBorder:y,LogoutOtherBrowserSessionsForm:$,TwoFactorAuthenticationForm:k,UpdatePasswordForm:v,UpdateProfileInformationForm:P},props:["sessions"]},S={class:"font-semibold text-xl text-gray-800 leading-tight"},N={class:"max-w-7xl mx-auto py-10 px-2 sm:px-6 lg:px-8"},C={key:0},D={key:1},V={key:2};function I(e,J,i,L,T,q){const c=t("app-head"),_=t("update-profile-information-form"),r=t("jet-section-border"),l=t("update-notification-preferences-form"),f=t("update-password-form"),u=t("two-factor-authentication-form"),d=t("logout-other-browser-sessions-form"),h=t("delete-user-form"),g=t("app-layout");return s(),B(g,null,{header:p(()=>[n("h2",S,U(e.__("Profile")),1)]),default:p(()=>[o(c,{title:e.__("Your Profile Settings")},null,8,["title"]),n("div",null,[n("div",N,[e.$page.props.jetstream.canUpdateProfileInformation?(s(),a("div",C,[o(_,{user:e.$page.props.auth.user},null,8,["user"]),o(r)])):m("",!0),n("div",null,[o(l,{user:e.$page.props.auth.user},null,8,["user"]),o(r)]),e.$page.props.jetstream.canUpdatePassword?(s(),a("div",D,[o(f,{class:"mt-10 sm:mt-0"}),o(r)])):m("",!0),e.$page.props.jetstream.canManageTwoFactorAuthentication?(s(),a("div",V,[o(u,{class:"mt-10 sm:mt-0","requires-confirmation":!0}),o(r)])):m("",!0),o(d,{sessions:i.sessions,class:"mt-10 sm:mt-0"},null,8,["sessions"]),e.$page.props.jetstream.hasAccountDeletionFeatures?(s(),a(x,{key:3},[o(r),o(h,{class:"mt-10 sm:mt-0"})],64)):m("",!0)])])]),_:1})}const ho=j(A,[["render",I]]);export{ho as default};