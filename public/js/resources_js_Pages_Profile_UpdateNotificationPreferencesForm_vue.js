(self["webpackChunk"] = self["webpackChunk"] || []).push([["resources_js_Pages_Profile_UpdateNotificationPreferencesForm_vue"],{

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/Form/XCheckbox.vue?vue&type=script&lang=js&":
/*!*********************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/Form/XCheckbox.vue?vue&type=script&lang=js& ***!
  \*********************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  model: {
    prop: 'checked',
    event: 'change'
  },
  props: {
    checked: {
      type: [Array, Boolean, Number],
      "default": null
    },
    value: [Number, String, Array, Object, Boolean, Date],
    name: String,
    help: String,
    label: String,
    type: String,
    id: String,
    error: String,
    required: {
      type: Boolean,
      "default": false
    }
  },
  computed: {
    proxyChecked: {
      get: function get() {
        return this.checked;
      },
      set: function set(val) {
        this.$emit('change', val);
      }
    },
    checkboxSizeClass: function checkboxSizeClass() {
      if (this.help) {
        return 'p-3';
      }

      return 'p-2';
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/Form/XInput.vue?vue&type=script&lang=js&":
/*!******************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/Form/XInput.vue?vue&type=script&lang=js& ***!
  \******************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  props: {
    value: [Number, String, Array, Object, Boolean, Date],
    name: String,
    help: String,
    label: String,
    type: String,
    id: String,
    error: String,
    autocomplete: {
      type: String,
      "default": 'off'
    },
    autofocus: {
      type: [String, Boolean],
      "default": false
    },
    required: {
      type: [String, Boolean],
      "default": false
    },
    disabled: {
      type: [String, Boolean],
      "default": false
    },
    helpErrorFlex: {
      type: String,
      "default": 'flex-col'
    },
    inputClass: {
      type: String,
      "default": ''
    }
  },
  data: function data() {
    return {
      hasFocus: false
    };
  },
  computed: {
    borderColor: function borderColor() {
      if (this.error) {
        return 'border-red-400 dark:border-red-600';
      } else {
        return 'border-gray-300 dark:border-gray-900';
      }
    },
    textColor: function textColor() {
      if (this.hasFocus) {
        return 'text-light-blue-400';
      }

      if (this.error) {
        return 'text-red-400 dark:text-red-600';
      } else {
        return 'text-gray-500 dark:text-gray-400';
      }
    }
  },
  methods: {
    focus: function focus() {
      this.$refs.input.focus();
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/ActionMessage.vue?vue&type=script&lang=js&":
/*!*******************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/ActionMessage.vue?vue&type=script&lang=js& ***!
  \*******************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  props: ['on']
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/Button.vue?vue&type=script&lang=js&":
/*!************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/Button.vue?vue&type=script&lang=js& ***!
  \************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  props: {
    loading: {
      type: Boolean,
      "default": false
    },
    type: {
      type: String,
      "default": 'submit'
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/FormSection.vue?vue&type=script&lang=js&":
/*!*****************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/FormSection.vue?vue&type=script&lang=js& ***!
  \*****************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _SectionTitle__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./SectionTitle */ "./resources/js/Jetstream/SectionTitle.vue");
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  components: {
    JetSectionTitle: _SectionTitle__WEBPACK_IMPORTED_MODULE_0__.default
  },
  computed: {
    hasActions: function hasActions() {
      return !!this.$slots.actions;
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/Input.vue?vue&type=script&lang=js&":
/*!***********************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/Input.vue?vue&type=script&lang=js& ***!
  \***********************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
//
//
//
//
//
//
//
//
//
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  props: ['value'],
  methods: {
    focus: function focus() {
      this.$refs.input.focus();
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/InputError.vue?vue&type=script&lang=js&":
/*!****************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/InputError.vue?vue&type=script&lang=js& ***!
  \****************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
//
//
//
//
//
//
//
//
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  props: ['message']
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/Label.vue?vue&type=script&lang=js&":
/*!***********************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/Label.vue?vue&type=script&lang=js& ***!
  \***********************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
//
//
//
//
//
//
//
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  props: ['value']
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Pages/Profile/UpdateNotificationPreferencesForm.vue?vue&type=script&lang=js&":
/*!*******************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Pages/Profile/UpdateNotificationPreferencesForm.vue?vue&type=script&lang=js& ***!
  \*******************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _Jetstream_ActionMessage__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/Jetstream/ActionMessage */ "./resources/js/Jetstream/ActionMessage.vue");
/* harmony import */ var _Jetstream_Button__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @/Jetstream/Button */ "./resources/js/Jetstream/Button.vue");
/* harmony import */ var _Jetstream_FormSection__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @/Jetstream/FormSection */ "./resources/js/Jetstream/FormSection.vue");
/* harmony import */ var _Jetstream_Input__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @/Jetstream/Input */ "./resources/js/Jetstream/Input.vue");
/* harmony import */ var _Jetstream_InputError__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @/Jetstream/InputError */ "./resources/js/Jetstream/InputError.vue");
/* harmony import */ var _Jetstream_Label__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @/Jetstream/Label */ "./resources/js/Jetstream/Label.vue");
/* harmony import */ var vue_password_strength_meter__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! vue-password-strength-meter */ "./node_modules/vue-password-strength-meter/dist/vue-password-strength-meter.min.js");
/* harmony import */ var vue_password_strength_meter__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(vue_password_strength_meter__WEBPACK_IMPORTED_MODULE_6__);
/* harmony import */ var _Components_Form_XInput__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! @/Components/Form/XInput */ "./resources/js/Components/Form/XInput.vue");
/* harmony import */ var _Components_Form_XCheckbox__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! @/Components/Form/XCheckbox */ "./resources/js/Components/Form/XCheckbox.vue");
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//









/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  components: {
    XCheckbox: _Components_Form_XCheckbox__WEBPACK_IMPORTED_MODULE_8__.default,
    XInput: _Components_Form_XInput__WEBPACK_IMPORTED_MODULE_7__.default,
    JetActionMessage: _Jetstream_ActionMessage__WEBPACK_IMPORTED_MODULE_0__.default,
    JetButton: _Jetstream_Button__WEBPACK_IMPORTED_MODULE_1__.default,
    JetFormSection: _Jetstream_FormSection__WEBPACK_IMPORTED_MODULE_2__.default,
    JetInput: _Jetstream_Input__WEBPACK_IMPORTED_MODULE_3__.default,
    JetInputError: _Jetstream_InputError__WEBPACK_IMPORTED_MODULE_4__.default,
    JetLabel: _Jetstream_Label__WEBPACK_IMPORTED_MODULE_5__.default,
    Password: (vue_password_strength_meter__WEBPACK_IMPORTED_MODULE_6___default())
  },
  props: ['user'],
  data: function data() {
    var _this$user$settings, _this$user$settings$n, _this$user$settings2, _this$user$settings2$, _this$user$settings3, _this$user$settings3$, _this$user$settings4, _this$user$settings4$;

    return {
      form: this.$inertia.form({
        like_on_post__email: (_this$user$settings = this.user.settings) === null || _this$user$settings === void 0 ? void 0 : (_this$user$settings$n = _this$user$settings.notifications) === null || _this$user$settings$n === void 0 ? void 0 : _this$user$settings$n.like_on_post.includes('mail'),
        comment_on_post__email: (_this$user$settings2 = this.user.settings) === null || _this$user$settings2 === void 0 ? void 0 : (_this$user$settings2$ = _this$user$settings2.notifications) === null || _this$user$settings2$ === void 0 ? void 0 : _this$user$settings2$.comment_on_post.includes('mail'),
        you_are_muted__email: (_this$user$settings3 = this.user.settings) === null || _this$user$settings3 === void 0 ? void 0 : (_this$user$settings3$ = _this$user$settings3.notifications) === null || _this$user$settings3$ === void 0 ? void 0 : _this$user$settings3$.you_are_muted.includes('mail'),
        you_are_banned__email: (_this$user$settings4 = this.user.settings) === null || _this$user$settings4 === void 0 ? void 0 : (_this$user$settings4$ = _this$user$settings4.notifications) === null || _this$user$settings4$ === void 0 ? void 0 : _this$user$settings4$.you_are_banned.includes('mail')
      })
    };
  },
  methods: {
    updateNotificationPreference: function updateNotificationPreference() {
      this.form.put(route('auth.put-notification-preferences'), {
        errorBag: 'updateNotificationPreference',
        preserveScroll: true
      });
    }
  }
});

/***/ }),

/***/ "./resources/js/Components/Form/XCheckbox.vue":
/*!****************************************************!*\
  !*** ./resources/js/Components/Form/XCheckbox.vue ***!
  \****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _XCheckbox_vue_vue_type_template_id_19d357bb___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./XCheckbox.vue?vue&type=template&id=19d357bb& */ "./resources/js/Components/Form/XCheckbox.vue?vue&type=template&id=19d357bb&");
/* harmony import */ var _XCheckbox_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./XCheckbox.vue?vue&type=script&lang=js& */ "./resources/js/Components/Form/XCheckbox.vue?vue&type=script&lang=js&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */
;
var component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__.default)(
  _XCheckbox_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__.default,
  _XCheckbox_vue_vue_type_template_id_19d357bb___WEBPACK_IMPORTED_MODULE_0__.render,
  _XCheckbox_vue_vue_type_template_id_19d357bb___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/Components/Form/XCheckbox.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "./resources/js/Components/Form/XInput.vue":
/*!*************************************************!*\
  !*** ./resources/js/Components/Form/XInput.vue ***!
  \*************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _XInput_vue_vue_type_template_id_156fde82___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./XInput.vue?vue&type=template&id=156fde82& */ "./resources/js/Components/Form/XInput.vue?vue&type=template&id=156fde82&");
/* harmony import */ var _XInput_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./XInput.vue?vue&type=script&lang=js& */ "./resources/js/Components/Form/XInput.vue?vue&type=script&lang=js&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */
;
var component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__.default)(
  _XInput_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__.default,
  _XInput_vue_vue_type_template_id_156fde82___WEBPACK_IMPORTED_MODULE_0__.render,
  _XInput_vue_vue_type_template_id_156fde82___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/Components/Form/XInput.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "./resources/js/Jetstream/ActionMessage.vue":
/*!**************************************************!*\
  !*** ./resources/js/Jetstream/ActionMessage.vue ***!
  \**************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _ActionMessage_vue_vue_type_template_id_bcb26626___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ActionMessage.vue?vue&type=template&id=bcb26626& */ "./resources/js/Jetstream/ActionMessage.vue?vue&type=template&id=bcb26626&");
/* harmony import */ var _ActionMessage_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./ActionMessage.vue?vue&type=script&lang=js& */ "./resources/js/Jetstream/ActionMessage.vue?vue&type=script&lang=js&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */
;
var component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__.default)(
  _ActionMessage_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__.default,
  _ActionMessage_vue_vue_type_template_id_bcb26626___WEBPACK_IMPORTED_MODULE_0__.render,
  _ActionMessage_vue_vue_type_template_id_bcb26626___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/Jetstream/ActionMessage.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "./resources/js/Jetstream/Button.vue":
/*!*******************************************!*\
  !*** ./resources/js/Jetstream/Button.vue ***!
  \*******************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _Button_vue_vue_type_template_id_afb94134___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Button.vue?vue&type=template&id=afb94134& */ "./resources/js/Jetstream/Button.vue?vue&type=template&id=afb94134&");
/* harmony import */ var _Button_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Button.vue?vue&type=script&lang=js& */ "./resources/js/Jetstream/Button.vue?vue&type=script&lang=js&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */
;
var component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__.default)(
  _Button_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__.default,
  _Button_vue_vue_type_template_id_afb94134___WEBPACK_IMPORTED_MODULE_0__.render,
  _Button_vue_vue_type_template_id_afb94134___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/Jetstream/Button.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "./resources/js/Jetstream/FormSection.vue":
/*!************************************************!*\
  !*** ./resources/js/Jetstream/FormSection.vue ***!
  \************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _FormSection_vue_vue_type_template_id_68c2427d___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./FormSection.vue?vue&type=template&id=68c2427d& */ "./resources/js/Jetstream/FormSection.vue?vue&type=template&id=68c2427d&");
/* harmony import */ var _FormSection_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./FormSection.vue?vue&type=script&lang=js& */ "./resources/js/Jetstream/FormSection.vue?vue&type=script&lang=js&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */
;
var component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__.default)(
  _FormSection_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__.default,
  _FormSection_vue_vue_type_template_id_68c2427d___WEBPACK_IMPORTED_MODULE_0__.render,
  _FormSection_vue_vue_type_template_id_68c2427d___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/Jetstream/FormSection.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "./resources/js/Jetstream/Input.vue":
/*!******************************************!*\
  !*** ./resources/js/Jetstream/Input.vue ***!
  \******************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _Input_vue_vue_type_template_id_49616346___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Input.vue?vue&type=template&id=49616346& */ "./resources/js/Jetstream/Input.vue?vue&type=template&id=49616346&");
/* harmony import */ var _Input_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Input.vue?vue&type=script&lang=js& */ "./resources/js/Jetstream/Input.vue?vue&type=script&lang=js&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */
;
var component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__.default)(
  _Input_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__.default,
  _Input_vue_vue_type_template_id_49616346___WEBPACK_IMPORTED_MODULE_0__.render,
  _Input_vue_vue_type_template_id_49616346___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/Jetstream/Input.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "./resources/js/Jetstream/InputError.vue":
/*!***********************************************!*\
  !*** ./resources/js/Jetstream/InputError.vue ***!
  \***********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _InputError_vue_vue_type_template_id_045826dc___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./InputError.vue?vue&type=template&id=045826dc& */ "./resources/js/Jetstream/InputError.vue?vue&type=template&id=045826dc&");
/* harmony import */ var _InputError_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./InputError.vue?vue&type=script&lang=js& */ "./resources/js/Jetstream/InputError.vue?vue&type=script&lang=js&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */
;
var component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__.default)(
  _InputError_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__.default,
  _InputError_vue_vue_type_template_id_045826dc___WEBPACK_IMPORTED_MODULE_0__.render,
  _InputError_vue_vue_type_template_id_045826dc___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/Jetstream/InputError.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "./resources/js/Jetstream/Label.vue":
/*!******************************************!*\
  !*** ./resources/js/Jetstream/Label.vue ***!
  \******************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _Label_vue_vue_type_template_id_812c3520___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Label.vue?vue&type=template&id=812c3520& */ "./resources/js/Jetstream/Label.vue?vue&type=template&id=812c3520&");
/* harmony import */ var _Label_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Label.vue?vue&type=script&lang=js& */ "./resources/js/Jetstream/Label.vue?vue&type=script&lang=js&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */
;
var component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__.default)(
  _Label_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__.default,
  _Label_vue_vue_type_template_id_812c3520___WEBPACK_IMPORTED_MODULE_0__.render,
  _Label_vue_vue_type_template_id_812c3520___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/Jetstream/Label.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "./resources/js/Jetstream/SectionTitle.vue":
/*!*************************************************!*\
  !*** ./resources/js/Jetstream/SectionTitle.vue ***!
  \*************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _SectionTitle_vue_vue_type_template_id_63bb8527___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./SectionTitle.vue?vue&type=template&id=63bb8527& */ "./resources/js/Jetstream/SectionTitle.vue?vue&type=template&id=63bb8527&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! !../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");

var script = {}


/* normalize component */
;
var component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_1__.default)(
  script,
  _SectionTitle_vue_vue_type_template_id_63bb8527___WEBPACK_IMPORTED_MODULE_0__.render,
  _SectionTitle_vue_vue_type_template_id_63bb8527___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/Jetstream/SectionTitle.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "./resources/js/Pages/Profile/UpdateNotificationPreferencesForm.vue":
/*!**************************************************************************!*\
  !*** ./resources/js/Pages/Profile/UpdateNotificationPreferencesForm.vue ***!
  \**************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _UpdateNotificationPreferencesForm_vue_vue_type_template_id_6f9784ee___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./UpdateNotificationPreferencesForm.vue?vue&type=template&id=6f9784ee& */ "./resources/js/Pages/Profile/UpdateNotificationPreferencesForm.vue?vue&type=template&id=6f9784ee&");
/* harmony import */ var _UpdateNotificationPreferencesForm_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./UpdateNotificationPreferencesForm.vue?vue&type=script&lang=js& */ "./resources/js/Pages/Profile/UpdateNotificationPreferencesForm.vue?vue&type=script&lang=js&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */
;
var component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__.default)(
  _UpdateNotificationPreferencesForm_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__.default,
  _UpdateNotificationPreferencesForm_vue_vue_type_template_id_6f9784ee___WEBPACK_IMPORTED_MODULE_0__.render,
  _UpdateNotificationPreferencesForm_vue_vue_type_template_id_6f9784ee___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/Pages/Profile/UpdateNotificationPreferencesForm.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "./resources/js/Components/Form/XCheckbox.vue?vue&type=script&lang=js&":
/*!*****************************************************************************!*\
  !*** ./resources/js/Components/Form/XCheckbox.vue?vue&type=script&lang=js& ***!
  \*****************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_XCheckbox_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!../../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./XCheckbox.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/Form/XCheckbox.vue?vue&type=script&lang=js&");
 /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_XCheckbox_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__.default); 

/***/ }),

/***/ "./resources/js/Components/Form/XInput.vue?vue&type=script&lang=js&":
/*!**************************************************************************!*\
  !*** ./resources/js/Components/Form/XInput.vue?vue&type=script&lang=js& ***!
  \**************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_XInput_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!../../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./XInput.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/Form/XInput.vue?vue&type=script&lang=js&");
 /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_XInput_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__.default); 

/***/ }),

/***/ "./resources/js/Jetstream/ActionMessage.vue?vue&type=script&lang=js&":
/*!***************************************************************************!*\
  !*** ./resources/js/Jetstream/ActionMessage.vue?vue&type=script&lang=js& ***!
  \***************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_ActionMessage_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./ActionMessage.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/ActionMessage.vue?vue&type=script&lang=js&");
 /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_ActionMessage_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__.default); 

/***/ }),

/***/ "./resources/js/Jetstream/Button.vue?vue&type=script&lang=js&":
/*!********************************************************************!*\
  !*** ./resources/js/Jetstream/Button.vue?vue&type=script&lang=js& ***!
  \********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Button_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./Button.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/Button.vue?vue&type=script&lang=js&");
 /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Button_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__.default); 

/***/ }),

/***/ "./resources/js/Jetstream/FormSection.vue?vue&type=script&lang=js&":
/*!*************************************************************************!*\
  !*** ./resources/js/Jetstream/FormSection.vue?vue&type=script&lang=js& ***!
  \*************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_FormSection_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./FormSection.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/FormSection.vue?vue&type=script&lang=js&");
 /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_FormSection_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__.default); 

/***/ }),

/***/ "./resources/js/Jetstream/Input.vue?vue&type=script&lang=js&":
/*!*******************************************************************!*\
  !*** ./resources/js/Jetstream/Input.vue?vue&type=script&lang=js& ***!
  \*******************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Input_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./Input.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/Input.vue?vue&type=script&lang=js&");
 /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Input_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__.default); 

/***/ }),

/***/ "./resources/js/Jetstream/InputError.vue?vue&type=script&lang=js&":
/*!************************************************************************!*\
  !*** ./resources/js/Jetstream/InputError.vue?vue&type=script&lang=js& ***!
  \************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_InputError_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./InputError.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/InputError.vue?vue&type=script&lang=js&");
 /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_InputError_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__.default); 

/***/ }),

/***/ "./resources/js/Jetstream/Label.vue?vue&type=script&lang=js&":
/*!*******************************************************************!*\
  !*** ./resources/js/Jetstream/Label.vue?vue&type=script&lang=js& ***!
  \*******************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Label_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./Label.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/Label.vue?vue&type=script&lang=js&");
 /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Label_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__.default); 

/***/ }),

/***/ "./resources/js/Pages/Profile/UpdateNotificationPreferencesForm.vue?vue&type=script&lang=js&":
/*!***************************************************************************************************!*\
  !*** ./resources/js/Pages/Profile/UpdateNotificationPreferencesForm.vue?vue&type=script&lang=js& ***!
  \***************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_UpdateNotificationPreferencesForm_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!../../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./UpdateNotificationPreferencesForm.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Pages/Profile/UpdateNotificationPreferencesForm.vue?vue&type=script&lang=js&");
 /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_UpdateNotificationPreferencesForm_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__.default); 

/***/ }),

/***/ "./resources/js/Components/Form/XCheckbox.vue?vue&type=template&id=19d357bb&":
/*!***********************************************************************************!*\
  !*** ./resources/js/Components/Form/XCheckbox.vue?vue&type=template&id=19d357bb& ***!
  \***********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_XCheckbox_vue_vue_type_template_id_19d357bb___WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   "staticRenderFns": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_XCheckbox_vue_vue_type_template_id_19d357bb___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_XCheckbox_vue_vue_type_template_id_19d357bb___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./XCheckbox.vue?vue&type=template&id=19d357bb& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/Form/XCheckbox.vue?vue&type=template&id=19d357bb&");


/***/ }),

/***/ "./resources/js/Components/Form/XInput.vue?vue&type=template&id=156fde82&":
/*!********************************************************************************!*\
  !*** ./resources/js/Components/Form/XInput.vue?vue&type=template&id=156fde82& ***!
  \********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_XInput_vue_vue_type_template_id_156fde82___WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   "staticRenderFns": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_XInput_vue_vue_type_template_id_156fde82___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_XInput_vue_vue_type_template_id_156fde82___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./XInput.vue?vue&type=template&id=156fde82& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/Form/XInput.vue?vue&type=template&id=156fde82&");


/***/ }),

/***/ "./resources/js/Jetstream/ActionMessage.vue?vue&type=template&id=bcb26626&":
/*!*********************************************************************************!*\
  !*** ./resources/js/Jetstream/ActionMessage.vue?vue&type=template&id=bcb26626& ***!
  \*********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_ActionMessage_vue_vue_type_template_id_bcb26626___WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   "staticRenderFns": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_ActionMessage_vue_vue_type_template_id_bcb26626___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_ActionMessage_vue_vue_type_template_id_bcb26626___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./ActionMessage.vue?vue&type=template&id=bcb26626& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/ActionMessage.vue?vue&type=template&id=bcb26626&");


/***/ }),

/***/ "./resources/js/Jetstream/Button.vue?vue&type=template&id=afb94134&":
/*!**************************************************************************!*\
  !*** ./resources/js/Jetstream/Button.vue?vue&type=template&id=afb94134& ***!
  \**************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Button_vue_vue_type_template_id_afb94134___WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   "staticRenderFns": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Button_vue_vue_type_template_id_afb94134___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Button_vue_vue_type_template_id_afb94134___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./Button.vue?vue&type=template&id=afb94134& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/Button.vue?vue&type=template&id=afb94134&");


/***/ }),

/***/ "./resources/js/Jetstream/FormSection.vue?vue&type=template&id=68c2427d&":
/*!*******************************************************************************!*\
  !*** ./resources/js/Jetstream/FormSection.vue?vue&type=template&id=68c2427d& ***!
  \*******************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_FormSection_vue_vue_type_template_id_68c2427d___WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   "staticRenderFns": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_FormSection_vue_vue_type_template_id_68c2427d___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_FormSection_vue_vue_type_template_id_68c2427d___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./FormSection.vue?vue&type=template&id=68c2427d& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/FormSection.vue?vue&type=template&id=68c2427d&");


/***/ }),

/***/ "./resources/js/Jetstream/Input.vue?vue&type=template&id=49616346&":
/*!*************************************************************************!*\
  !*** ./resources/js/Jetstream/Input.vue?vue&type=template&id=49616346& ***!
  \*************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Input_vue_vue_type_template_id_49616346___WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   "staticRenderFns": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Input_vue_vue_type_template_id_49616346___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Input_vue_vue_type_template_id_49616346___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./Input.vue?vue&type=template&id=49616346& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/Input.vue?vue&type=template&id=49616346&");


/***/ }),

/***/ "./resources/js/Jetstream/InputError.vue?vue&type=template&id=045826dc&":
/*!******************************************************************************!*\
  !*** ./resources/js/Jetstream/InputError.vue?vue&type=template&id=045826dc& ***!
  \******************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_InputError_vue_vue_type_template_id_045826dc___WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   "staticRenderFns": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_InputError_vue_vue_type_template_id_045826dc___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_InputError_vue_vue_type_template_id_045826dc___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./InputError.vue?vue&type=template&id=045826dc& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/InputError.vue?vue&type=template&id=045826dc&");


/***/ }),

/***/ "./resources/js/Jetstream/Label.vue?vue&type=template&id=812c3520&":
/*!*************************************************************************!*\
  !*** ./resources/js/Jetstream/Label.vue?vue&type=template&id=812c3520& ***!
  \*************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Label_vue_vue_type_template_id_812c3520___WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   "staticRenderFns": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Label_vue_vue_type_template_id_812c3520___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Label_vue_vue_type_template_id_812c3520___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./Label.vue?vue&type=template&id=812c3520& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/Label.vue?vue&type=template&id=812c3520&");


/***/ }),

/***/ "./resources/js/Jetstream/SectionTitle.vue?vue&type=template&id=63bb8527&":
/*!********************************************************************************!*\
  !*** ./resources/js/Jetstream/SectionTitle.vue?vue&type=template&id=63bb8527& ***!
  \********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_SectionTitle_vue_vue_type_template_id_63bb8527___WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   "staticRenderFns": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_SectionTitle_vue_vue_type_template_id_63bb8527___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_SectionTitle_vue_vue_type_template_id_63bb8527___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./SectionTitle.vue?vue&type=template&id=63bb8527& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/SectionTitle.vue?vue&type=template&id=63bb8527&");


/***/ }),

/***/ "./resources/js/Pages/Profile/UpdateNotificationPreferencesForm.vue?vue&type=template&id=6f9784ee&":
/*!*********************************************************************************************************!*\
  !*** ./resources/js/Pages/Profile/UpdateNotificationPreferencesForm.vue?vue&type=template&id=6f9784ee& ***!
  \*********************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_UpdateNotificationPreferencesForm_vue_vue_type_template_id_6f9784ee___WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   "staticRenderFns": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_UpdateNotificationPreferencesForm_vue_vue_type_template_id_6f9784ee___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_UpdateNotificationPreferencesForm_vue_vue_type_template_id_6f9784ee___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./UpdateNotificationPreferencesForm.vue?vue&type=template&id=6f9784ee& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Pages/Profile/UpdateNotificationPreferencesForm.vue?vue&type=template&id=6f9784ee&");


/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/Form/XCheckbox.vue?vue&type=template&id=19d357bb&":
/*!**************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/Form/XCheckbox.vue?vue&type=template&id=19d357bb& ***!
  \**************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* binding */ render),
/* harmony export */   "staticRenderFns": () => (/* binding */ staticRenderFns)
/* harmony export */ });
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("div", { staticClass: "flex items-start" }, [
    _c("input", {
      directives: [
        {
          name: "model",
          rawName: "v-model",
          value: _vm.proxyChecked,
          expression: "proxyChecked"
        }
      ],
      ref: "input",
      staticClass:
        "rounded border-gray-300 dark:bg-cool-gray-900 dark:border-gray-900 text-light-blue-500 shadow-sm focus:border-light-blue-300 focus:ring focus:ring-light-blue-200 focus:ring-opacity-50",
      class: _vm.checkboxSizeClass,
      attrs: {
        id: _vm.id,
        type: "checkbox",
        name: _vm.name,
        required: _vm.required
      },
      domProps: {
        value: _vm.value,
        checked: Array.isArray(_vm.proxyChecked)
          ? _vm._i(_vm.proxyChecked, _vm.value) > -1
          : _vm.proxyChecked
      },
      on: {
        input: function($event) {
          return _vm.$emit("input", $event.target.value)
        },
        change: function($event) {
          var $$a = _vm.proxyChecked,
            $$el = $event.target,
            $$c = $$el.checked ? true : false
          if (Array.isArray($$a)) {
            var $$v = _vm.value,
              $$i = _vm._i($$a, $$v)
            if ($$el.checked) {
              $$i < 0 && (_vm.proxyChecked = $$a.concat([$$v]))
            } else {
              $$i > -1 &&
                (_vm.proxyChecked = $$a
                  .slice(0, $$i)
                  .concat($$a.slice($$i + 1)))
            }
          } else {
            _vm.proxyChecked = $$c
          }
        }
      }
    }),
    _vm._v(" "),
    _c(
      "label",
      {
        staticClass:
          "font-medium text-sm flex flex-col text-gray-600 dark:text-gray-400 ml-2 leading-tight",
        attrs: { for: _vm.id }
      },
      [
        _vm.label ? _c("span", [_vm._v(_vm._s(_vm.label))]) : _vm._e(),
        _vm._v(" "),
        _vm.help
          ? _c("span", { staticClass: "text-xs text-gray-400" }, [
              _vm._v(_vm._s(_vm.help))
            ])
          : _vm._e(),
        _vm._v(" "),
        _vm.error
          ? _c("span", { staticClass: "text-xs text-red-500" }, [
              _vm._v(_vm._s(_vm.error))
            ])
          : _vm._e()
      ]
    )
  ])
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/Form/XInput.vue?vue&type=template&id=156fde82&":
/*!***********************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/Form/XInput.vue?vue&type=template&id=156fde82& ***!
  \***********************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* binding */ render),
/* harmony export */   "staticRenderFns": () => (/* binding */ staticRenderFns)
/* harmony export */ });
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("div", { staticClass: "floating-input relative w-full" }, [
    _c("input", {
      ref: "input",
      staticClass:
        "dark:bg-cool-gray-900 dark:text-gray-300 border focus:outline-none rounded-md w-full p-3 h-14 disabled:opacity-50 focus:border-light-blue-300 focus:ring text-sm focus:ring-light-blue-200 focus:ring-opacity-50",
      class: _vm.borderColor + " " + _vm.inputClass,
      attrs: {
        id: _vm.id,
        type: _vm.type,
        name: _vm.name,
        placeholder: _vm.label,
        autocomplete: _vm.autocomplete,
        autofocus: _vm.autofocus,
        required: _vm.required,
        disabled: _vm.disabled
      },
      domProps: { value: _vm.value },
      on: {
        input: function($event) {
          return _vm.$emit("input", $event.target.value)
        },
        focus: function($event) {
          _vm.hasFocus = true
        },
        blur: function($event) {
          _vm.hasFocus = false
        }
      }
    }),
    _vm._v(" "),
    _c(
      "label",
      {
        staticClass:
          "absolute top-0 left-0 px-3 py-5 text-sm h-full pointer-events-none transform origin-left transition-all duration-100 ease-in-out ",
        class: _vm.textColor,
        attrs: { for: _vm.id }
      },
      [_vm._v(_vm._s(_vm.label))]
    ),
    _vm._v(" "),
    _c(
      "div",
      {
        staticClass: "info mt-1 flex",
        class: _vm.help
          ? "justify-between " + _vm.helpErrorFlex
          : "justify-end " + _vm.helpErrorFlex
      },
      [
        _c(
          "p",
          {
            directives: [
              {
                name: "show",
                rawName: "v-show",
                value: _vm.help,
                expression: "help"
              }
            ],
            staticClass: "text-xs text-gray-500"
          },
          [_vm._v("\n      " + _vm._s(_vm.help) + "\n    ")]
        ),
        _vm._v(" "),
        _c(
          "p",
          {
            directives: [
              {
                name: "show",
                rawName: "v-show",
                value: _vm.error,
                expression: "error"
              }
            ],
            staticClass: "text-xs text-red-500"
          },
          [_vm._v("\n      " + _vm._s(_vm.error) + "\n    ")]
        )
      ]
    )
  ])
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/ActionMessage.vue?vue&type=template&id=bcb26626&":
/*!************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/ActionMessage.vue?vue&type=template&id=bcb26626& ***!
  \************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* binding */ render),
/* harmony export */   "staticRenderFns": () => (/* binding */ staticRenderFns)
/* harmony export */ });
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "div",
    [
      _c(
        "transition",
        {
          attrs: {
            "leave-active-class": "transition ease-in duration-1000",
            "leave-class": "opacity-100",
            "leave-to-class": "opacity-0"
          }
        },
        [
          _c(
            "div",
            {
              directives: [
                {
                  name: "show",
                  rawName: "v-show",
                  value: _vm.on,
                  expression: "on"
                }
              ],
              staticClass: "text-sm text-gray-600 dark:text-gray-200"
            },
            [_vm._t("default")],
            2
          )
        ]
      )
    ],
    1
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/Button.vue?vue&type=template&id=afb94134&":
/*!*****************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/Button.vue?vue&type=template&id=afb94134& ***!
  \*****************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* binding */ render),
/* harmony export */   "staticRenderFns": () => (/* binding */ staticRenderFns)
/* harmony export */ });
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "button",
    {
      staticClass:
        "inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:shadow-outline-gray transition ease-in-out duration-150 dark:bg-cool-gray-700 dark:hover:bg-cool-gray-600",
      attrs: { type: _vm.type }
    },
    [
      _vm.loading
        ? _c(
            "svg",
            {
              staticClass: "animate-spin -ml-1 mr-3 h-4 w-4 text-white",
              attrs: {
                xmlns: "http://www.w3.org/2000/svg",
                fill: "none",
                viewBox: "0 0 24 24"
              }
            },
            [
              _c("circle", {
                staticClass: "opacity-25",
                attrs: {
                  cx: "12",
                  cy: "12",
                  r: "10",
                  stroke: "currentColor",
                  "stroke-width": "4"
                }
              }),
              _vm._v(" "),
              _c("path", {
                staticClass: "opacity-75",
                attrs: {
                  fill: "currentColor",
                  d:
                    "M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                }
              })
            ]
          )
        : _vm._e(),
      _vm._v(" "),
      _vm._t("default")
    ],
    2
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/FormSection.vue?vue&type=template&id=68c2427d&":
/*!**********************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/FormSection.vue?vue&type=template&id=68c2427d& ***!
  \**********************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* binding */ render),
/* harmony export */   "staticRenderFns": () => (/* binding */ staticRenderFns)
/* harmony export */ });
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "div",
    { staticClass: "md:grid md:grid-cols-3 md:gap-6" },
    [
      _c("jet-section-title", {
        scopedSlots: _vm._u(
          [
            {
              key: "title",
              fn: function() {
                return [_vm._t("title")]
              },
              proxy: true
            },
            {
              key: "description",
              fn: function() {
                return [_vm._t("description")]
              },
              proxy: true
            }
          ],
          null,
          true
        )
      }),
      _vm._v(" "),
      _c("div", { staticClass: "mt-5 md:mt-0 md:col-span-2" }, [
        _c(
          "form",
          {
            on: {
              submit: function($event) {
                $event.preventDefault()
                return _vm.$emit("submitted")
              }
            }
          },
          [
            _c("div", { staticClass: "shadow overflow-hidden sm:rounded-md" }, [
              _c(
                "div",
                {
                  staticClass: "px-4 py-5 bg-white dark:bg-cool-gray-800 sm:p-6"
                },
                [
                  _c(
                    "div",
                    { staticClass: "grid grid-cols-6 gap-6" },
                    [_vm._t("form")],
                    2
                  )
                ]
              ),
              _vm._v(" "),
              _vm.hasActions
                ? _c(
                    "div",
                    {
                      staticClass:
                        "flex items-center justify-end px-4 py-3 bg-gray-50 dark:bg-cool-gray-800 text-right sm:px-6"
                    },
                    [_vm._t("actions")],
                    2
                  )
                : _vm._e()
            ])
          ]
        )
      ])
    ],
    1
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/Input.vue?vue&type=template&id=49616346&":
/*!****************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/Input.vue?vue&type=template&id=49616346& ***!
  \****************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* binding */ render),
/* harmony export */   "staticRenderFns": () => (/* binding */ staticRenderFns)
/* harmony export */ });
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("input", {
    ref: "input",
    staticClass:
      "border-gray-300 focus:border-light-blue-300 focus:ring focus:ring-light-blue-200 focus:ring-opacity-50 rounded-md shadow-sm",
    domProps: { value: _vm.value },
    on: {
      input: function($event) {
        return _vm.$emit("input", $event.target.value)
      }
    }
  })
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/InputError.vue?vue&type=template&id=045826dc&":
/*!*********************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/InputError.vue?vue&type=template&id=045826dc& ***!
  \*********************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* binding */ render),
/* harmony export */   "staticRenderFns": () => (/* binding */ staticRenderFns)
/* harmony export */ });
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "div",
    {
      directives: [
        {
          name: "show",
          rawName: "v-show",
          value: _vm.message,
          expression: "message"
        }
      ]
    },
    [
      _c("p", { staticClass: "text-xs text-red-500" }, [
        _vm._v("\n    " + _vm._s(_vm.message) + "\n  ")
      ])
    ]
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/Label.vue?vue&type=template&id=812c3520&":
/*!****************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/Label.vue?vue&type=template&id=812c3520& ***!
  \****************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* binding */ render),
/* harmony export */   "staticRenderFns": () => (/* binding */ staticRenderFns)
/* harmony export */ });
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "label",
    {
      staticClass: "block font-medium text-sm text-gray-700 dark:text-gray-400"
    },
    [
      _vm.value
        ? _c("span", [_vm._v(_vm._s(_vm.value))])
        : _c("span", [_vm._t("default")], 2)
    ]
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/SectionTitle.vue?vue&type=template&id=63bb8527&":
/*!***********************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/SectionTitle.vue?vue&type=template&id=63bb8527& ***!
  \***********************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* binding */ render),
/* harmony export */   "staticRenderFns": () => (/* binding */ staticRenderFns)
/* harmony export */ });
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("div", { staticClass: "md:col-span-1" }, [
    _c("div", { staticClass: "px-4 sm:px-0" }, [
      _c(
        "h3",
        { staticClass: "text-lg font-medium text-gray-900 dark:text-gray-200" },
        [_vm._t("title")],
        2
      ),
      _vm._v(" "),
      _c(
        "p",
        { staticClass: "mt-1 text-sm text-gray-600 dark:text-gray-500" },
        [_vm._t("description")],
        2
      )
    ])
  ])
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Pages/Profile/UpdateNotificationPreferencesForm.vue?vue&type=template&id=6f9784ee&":
/*!************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Pages/Profile/UpdateNotificationPreferencesForm.vue?vue&type=template&id=6f9784ee& ***!
  \************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* binding */ render),
/* harmony export */   "staticRenderFns": () => (/* binding */ staticRenderFns)
/* harmony export */ });
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("jet-form-section", {
    on: { submitted: _vm.updateNotificationPreference },
    scopedSlots: _vm._u([
      {
        key: "title",
        fn: function() {
          return [_vm._v("\n    Notification Preferences\n  ")]
        },
        proxy: true
      },
      {
        key: "description",
        fn: function() {
          return [
            _vm._v(
              "\n    Check what you want to receive notifications for.\n  "
            )
          ]
        },
        proxy: true
      },
      {
        key: "form",
        fn: function() {
          return [
            _c("div", { staticClass: "col-span-6 sm:col-span-6" }, [
              _c("table", { staticClass: "table-auto w-full" }, [
                _c("thead", [
                  _c("tr", [
                    _c(
                      "th",
                      {
                        staticClass:
                          "text-lg text-left font-semibold text-gray-600 dark:text-gray-300",
                        attrs: { scope: "col" }
                      },
                      [_vm._v("\n              Notify me when\n            ")]
                    ),
                    _vm._v(" "),
                    _c(
                      "th",
                      {
                        staticClass:
                          "font-semibold text-gray-600 dark:text-gray-300"
                      },
                      [_vm._v("\n              via Email\n            ")]
                    )
                  ])
                ]),
                _vm._v(" "),
                _c(
                  "tbody",
                  { staticClass: "text-gray-700 dark:text-gray-400" },
                  [
                    _c("tr", { staticClass: "mt-5" }, [
                      _c("td", [_vm._v("Someone commented on my Post")]),
                      _vm._v(" "),
                      _c(
                        "td",
                        [
                          _c("x-checkbox", {
                            staticClass: "justify-center",
                            attrs: { name: "check1" },
                            model: {
                              value: _vm.form.comment_on_post__email,
                              callback: function($$v) {
                                _vm.$set(
                                  _vm.form,
                                  "comment_on_post__email",
                                  $$v
                                )
                              },
                              expression: "form.comment_on_post__email"
                            }
                          })
                        ],
                        1
                      )
                    ]),
                    _vm._v(" "),
                    _c("tr", [
                      _c("td", [_vm._v("Someone liked my Post")]),
                      _vm._v(" "),
                      _c(
                        "td",
                        [
                          _c("x-checkbox", {
                            staticClass: "justify-center",
                            attrs: { name: "check2" },
                            model: {
                              value: _vm.form.like_on_post__email,
                              callback: function($$v) {
                                _vm.$set(_vm.form, "like_on_post__email", $$v)
                              },
                              expression: "form.like_on_post__email"
                            }
                          })
                        ],
                        1
                      )
                    ]),
                    _vm._v(" "),
                    _c("tr", [
                      _c("td", [_vm._v("I am muted by Staff")]),
                      _vm._v(" "),
                      _c(
                        "td",
                        [
                          _c("x-checkbox", {
                            staticClass: "justify-center",
                            attrs: { name: "check3" },
                            model: {
                              value: _vm.form.you_are_muted__email,
                              callback: function($$v) {
                                _vm.$set(_vm.form, "you_are_muted__email", $$v)
                              },
                              expression: "form.you_are_muted__email"
                            }
                          })
                        ],
                        1
                      )
                    ]),
                    _vm._v(" "),
                    _c("tr", [
                      _c("td", [_vm._v("I am banned by Staff")]),
                      _vm._v(" "),
                      _c(
                        "td",
                        { staticClass: "text-center" },
                        [
                          _c("x-checkbox", {
                            staticClass: "justify-center",
                            attrs: { name: "check4" },
                            model: {
                              value: _vm.form.you_are_banned__email,
                              callback: function($$v) {
                                _vm.$set(_vm.form, "you_are_banned__email", $$v)
                              },
                              expression: "form.you_are_banned__email"
                            }
                          })
                        ],
                        1
                      )
                    ])
                  ]
                )
              ])
            ])
          ]
        },
        proxy: true
      },
      {
        key: "actions",
        fn: function() {
          return [
            _c(
              "jet-action-message",
              {
                staticClass: "mr-3",
                attrs: { on: _vm.form.recentlySuccessful }
              },
              [_vm._v("\n      Saved.\n    ")]
            ),
            _vm._v(" "),
            _c(
              "jet-button",
              {
                class: { "opacity-25": _vm.form.processing },
                attrs: {
                  disabled: _vm.form.processing,
                  loading: _vm.form.processing
                }
              },
              [_vm._v("\n      Save\n    ")]
            )
          ]
        },
        proxy: true
      }
    ])
  })
}
var staticRenderFns = []
render._withStripped = true



/***/ })

}]);