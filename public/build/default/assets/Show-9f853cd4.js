import{A as F}from"./AppLayout-5f3e8958.js";import $ from"./DeleteUserForm-a17f819a.js";import{J as k}from"./SectionBorder-d89d62c7.js";import y from"./LogoutOtherBrowserSessionsForm-2bfbd5be.js";import v from"./TwoFactorAuthenticationForm-0143db5e.js";import P from"./UpdatePasswordForm-842a03c9.js";import b from"./UpdateProfileInformationForm-ae9cb0b0.js";import j from"./UpdateNotificationPreferencesForm-297b9892.js";import A from"./LinkedSocialAccountsForm-5176a0f5.js";import{_ as B}from"./_plugin-vue_export-helper-c27b6911.js";import{l as t,o as s,c as S,w as i,a as m,t as U,b as o,d as n,e as a,F as x}from"./app-f1704ce4.js";import"./Icon-4144b1c7.js";import"./useAuthorizable-18c71ef1.js";import"./ActionSection-d56e0acb.js";import"./SectionTitle-8e514380.js";import"./DialogModal-9816ba23.js";import"./Modal-1a38c55d.js";import"./DangerButton-eebd22fb.js";import"./Input-3c640f45.js";import"./InputError-99d060cb.js";import"./SecondaryButton-c6d8a330.js";import"./ActionMessage-d75e1dd9.js";import"./Button-0fddd99a.js";import"./ConfirmsPassword-03bb60ea.js";import"./XInput-84f7468c.js";import"./FormSection-c89a1bf5.js";import"./PasswordStrengthMeter-8ea8f10c.js";import"./Label-54d8da87.js";import"./index.es-7cdc35de.js";import"./XCheckbox-3936c579.js";import"./XSelect-071eb764.js";import"./XTextarea-d6659f51.js";import"./LoadingButton-37976f47.js";import"./LoadingSpinner-d377d906.js";import"./XMarkIcon-2c5e91bb.js";const N={components:{UpdateNotificationPreferencesForm:j,AppLayout:F,DeleteUserForm:$,JetSectionBorder:k,LogoutOtherBrowserSessionsForm:y,TwoFactorAuthenticationForm:v,UpdatePasswordForm:P,UpdateProfileInformationForm:b,LinkedSocialAccountsForm:A},props:["sessions"]},C={class:"font-semibold text-xl text-gray-800 leading-tight"},D={class:"max-w-7xl mx-auto py-10 px-2 sm:px-6 lg:px-8"},L={key:0},V={key:1},I={key:2};function J(e,T,p,q,E,M){const c=t("app-head"),_=t("update-profile-information-form"),r=t("jet-section-border"),l=t("update-notification-preferences-form"),f=t("linked-social-accounts-form"),u=t("update-password-form"),d=t("two-factor-authentication-form"),h=t("logout-other-browser-sessions-form"),g=t("delete-user-form"),w=t("app-layout");return s(),S(w,null,{header:i(()=>[m("h2",C,U(e.__("Profile")),1)]),default:i(()=>[o(c,{title:e.__("Your Profile Settings")},null,8,["title"]),m("div",null,[m("div",D,[e.$page.props.jetstream.canUpdateProfileInformation?(s(),n("div",L,[o(_,{user:e.$page.props.auth.user},null,8,["user"]),o(r)])):a("",!0),m("div",null,[o(l,{class:"mt-10 sm:mt-0",user:e.$page.props.auth.user},null,8,["user"]),o(r)]),m("div",null,[o(f,{class:"mt-10 sm:mt-0"}),o(r)]),e.$page.props.jetstream.canUpdatePassword?(s(),n("div",V,[o(u,{class:"mt-10 sm:mt-0"}),o(r)])):a("",!0),e.$page.props.jetstream.canManageTwoFactorAuthentication?(s(),n("div",I,[o(d,{class:"mt-10 sm:mt-0","requires-confirmation":!0}),o(r)])):a("",!0),o(h,{sessions:p.sessions,class:"mt-10 sm:mt-0"},null,8,["sessions"]),e.$page.props.jetstream.hasAccountDeletionFeatures?(s(),n(x,{key:3},[o(r),o(g,{class:"mt-10 sm:mt-0"})],64)):a("",!0)])])]),_:1})}const bo=B(N,[["render",J]]);export{bo as default};