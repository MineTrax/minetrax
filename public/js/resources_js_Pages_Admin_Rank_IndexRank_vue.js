(self["webpackChunk"] = self["webpackChunk"] || []).push([["resources_js_Pages_Admin_Rank_IndexRank_vue"],{

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/ColorThemeToggle.vue?vue&type=script&lang=js&":
/*!***********************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/ColorThemeToggle.vue?vue&type=script&lang=js& ***!
  \***********************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _Components_Icon__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/Components/Icon */ "./resources/js/Components/Icon.vue");
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
  name: 'ColorThemeToggle',
  components: {
    Icon: _Components_Icon__WEBPACK_IMPORTED_MODULE_0__.default
  },
  data: function data() {
    return {
      colorMode: window.colorMode
    };
  },
  methods: {
    toggleTheme: function toggleTheme() {
      if (this.colorMode === 'dark') {
        this.colorMode = 'light';
        window.colorMode = 'light';
        localStorage.theme = 'light';
        document.documentElement.classList.add('light');
        document.documentElement.classList.remove('dark');
      } else {
        this.colorMode = 'dark';
        window.colorMode = 'dark';
        localStorage.theme = 'dark';
        document.documentElement.classList.add('dark');
        document.documentElement.classList.remove('light');
      }
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/Icon.vue?vue&type=script&lang=js&":
/*!***********************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/Icon.vue?vue&type=script&lang=js& ***!
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
    name: String
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/InfiniteScroll.vue?vue&type=script&lang=js&":
/*!*********************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/InfiniteScroll.vue?vue&type=script&lang=js& ***!
  \*********************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var lodash_function__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! lodash/function */ "./node_modules/lodash/function.js");
/* harmony import */ var lodash_function__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(lodash_function__WEBPACK_IMPORTED_MODULE_0__);
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
    loadMore: Function,
    loadingText: {
      type: String,
      "default": 'Loading more...'
    },
    threshold: {
      type: Number,
      "default": 200
    }
  },
  data: function data() {
    return {
      loading: false,
      eventListener: null
    };
  },
  mounted: function mounted() {
    var _this = this;

    window.addEventListener('scroll', this.eventListener = (0,lodash_function__WEBPACK_IMPORTED_MODULE_0__.debounce)(function (e) {
      var pixelFromBotton = document.documentElement.offsetHeight - document.documentElement.scrollTop - window.innerHeight;

      if (pixelFromBotton < _this.threshold && !_this.loading) {
        _this.loading = true;

        _this.loadMore()["finally"](function () {
          return _this.loading = false;
        });
      }
    }, 100));
  },
  destroyed: function destroyed() {
    window.removeEventListener('scroll', this.eventListener);
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/Notification.vue?vue&type=script&lang=js&":
/*!*******************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/Notification.vue?vue&type=script&lang=js& ***!
  \*******************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var date_fns__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! date-fns */ "./node_modules/date-fns/esm/format/index.js");
/* harmony import */ var date_fns__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! date-fns */ "./node_modules/date-fns/esm/formatDistanceToNowStrict/index.js");
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
  name: 'Notification',
  props: ['notification'],
  data: function data() {
    return {
      format: date_fns__WEBPACK_IMPORTED_MODULE_0__.default,
      formatDistanceToNowStrict: date_fns__WEBPACK_IMPORTED_MODULE_1__.default
    };
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/Pagination.vue?vue&type=script&lang=js&":
/*!*****************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/Pagination.vue?vue&type=script&lang=js& ***!
  \*****************************************************************************************************************************************************************************************************************/
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
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  props: {
    links: Array
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/Toast.vue?vue&type=script&lang=js&":
/*!************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/Toast.vue?vue&type=script&lang=js& ***!
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
  props: {
    toast: Object,
    popstate: Boolean
  },
  data: function data() {
    return {
      milliseconds: this.toast && this.toast.milliseconds ? this.toast.milliseconds : 3000,
      show: false,
      timeout: null
    };
  },
  watch: {
    toast: {
      deep: true,
      handler: function handler(o, n) {
        var _this = this;

        this.show = true;

        if (this.timeout) {
          clearTimeout(this.timeout);
        }

        this.timeout = setTimeout(function () {
          _this.show = false;
        }, this.milliseconds);
      }
    }
  },
  mounted: function mounted() {
    var _this2 = this;

    this.show = true;

    if (this.timeout) {
      clearTimeout(this.timeout);
    }

    this.timeout = setTimeout(function () {
      _this2.show = false;
    }, this.milliseconds);
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/ApplicationMark.vue?vue&type=script&lang=js&":
/*!*********************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/ApplicationMark.vue?vue&type=script&lang=js& ***!
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
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  computed: {
    logo: function logo() {
      if (window.colorMode === 'light') return this.$page.props.generalSettings.site_header_logo_path_light;else return this.$page.props.generalSettings.site_header_logo_path_dark;
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/Banner.vue?vue&type=script&lang=js&":
/*!************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/Banner.vue?vue&type=script&lang=js& ***!
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
  data: function data() {
    return {
      show: true
    };
  },
  computed: {
    style: function style() {
      var _this$$page$props$jet;

      return ((_this$$page$props$jet = this.$page.props.jetstream.flash) === null || _this$$page$props$jet === void 0 ? void 0 : _this$$page$props$jet.bannerStyle) || 'success';
    },
    message: function message() {
      var _this$$page$props$jet2;

      return ((_this$$page$props$jet2 = this.$page.props.jetstream.flash) === null || _this$$page$props$jet2 === void 0 ? void 0 : _this$$page$props$jet2.banner) || '';
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/ConfirmationModal.vue?vue&type=script&lang=js&":
/*!***********************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/ConfirmationModal.vue?vue&type=script&lang=js& ***!
  \***********************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _Modal__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Modal */ "./resources/js/Jetstream/Modal.vue");
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
    Modal: _Modal__WEBPACK_IMPORTED_MODULE_0__.default
  },
  props: {
    show: {
      "default": false
    },
    maxWidth: {
      "default": '2xl'
    },
    closeable: {
      "default": true
    }
  },
  methods: {
    close: function close() {
      this.$emit('close');
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/DangerButton.vue?vue&type=script&lang=js&":
/*!******************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/DangerButton.vue?vue&type=script&lang=js& ***!
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
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  props: {
    type: {
      type: String,
      "default": 'button'
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/Dropdown.vue?vue&type=script&lang=js&":
/*!**************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/Dropdown.vue?vue&type=script&lang=js& ***!
  \**************************************************************************************************************************************************************************************************************/
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
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  props: {
    align: {
      "default": 'right'
    },
    width: {
      "default": '48'
    },
    contentClasses: {
      "default": function _default() {
        return ['py-1', 'bg-white'];
      }
    }
  },
  data: function data() {
    return {
      open: false
    };
  },
  computed: {
    widthClass: function widthClass() {
      return {
        '48': 'w-48'
      }[this.width.toString()];
    },
    alignmentClasses: function alignmentClasses() {
      if (this.align === 'left') {
        return 'origin-top-left left-0';
      } else if (this.align === 'right') {
        return 'origin-top-right right-0';
      } else {
        return 'origin-top';
      }
    }
  },
  created: function created() {
    var _this = this;

    var closeOnEscape = function closeOnEscape(e) {
      if (_this.open && e.keyCode === 27) {
        _this.open = false;
      }
    };

    this.$once('hook:destroyed', function () {
      document.removeEventListener('keydown', closeOnEscape);
    });
    document.addEventListener('keydown', closeOnEscape);
  },
  mounted: function mounted() {
    document.addEventListener('click', this.close);
  },
  beforeDestroy: function beforeDestroy() {
    document.removeEventListener('click', this.close);
  },
  methods: {
    close: function close(e) {
      if (!this.$el.contains(e.target)) {
        this.open = false;
      }
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/DropdownLink.vue?vue&type=script&lang=js&":
/*!******************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/DropdownLink.vue?vue&type=script&lang=js& ***!
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
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  props: ['href', 'as', 'btnClass']
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/Modal.vue?vue&type=script&lang=js&":
/*!***********************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/Modal.vue?vue&type=script&lang=js& ***!
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
    show: {
      "default": false
    },
    maxWidth: {
      "default": '2xl'
    },
    closeable: {
      "default": true
    }
  },
  computed: {
    maxWidthClass: function maxWidthClass() {
      return {
        'sm': 'sm:max-w-sm',
        'md': 'sm:max-w-md',
        'lg': 'sm:max-w-lg',
        'xl': 'sm:max-w-xl',
        '2xl': 'sm:max-w-2xl'
      }[this.maxWidth];
    }
  },
  watch: {
    show: {
      immediate: true,
      handler: function handler(show) {
        if (show) {
          document.body.style.overflow = 'hidden';
        } else {
          document.body.style.overflow = null;
        }
      }
    }
  },
  created: function created() {
    var _this = this;

    var closeOnEscape = function closeOnEscape(e) {
      if (e.key === 'Escape' && _this.show) {
        _this.close();
      }
    };

    document.addEventListener('keydown', closeOnEscape);
    this.$once('hook:destroyed', function () {
      document.removeEventListener('keydown', closeOnEscape);
    });
  },
  methods: {
    close: function close() {
      if (this.closeable) {
        this.$emit('close');
      }
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/NavLink.vue?vue&type=script&lang=js&":
/*!*************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/NavLink.vue?vue&type=script&lang=js& ***!
  \*************************************************************************************************************************************************************************************************************/
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
  props: ['href', 'active'],
  computed: {
    classes: function classes() {
      return this.active ? 'inline-flex items-center px-1 pt-1 border-b-2 border-light-blue-400 text-sm leading-5 text-gray-900 dark:text-gray-200 focus:outline-none focus:border-light-blue-700 transition duration-150 ease-in-out' : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm leading-5 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out';
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/ResponsiveNavLink.vue?vue&type=script&lang=js&":
/*!***********************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/ResponsiveNavLink.vue?vue&type=script&lang=js& ***!
  \***********************************************************************************************************************************************************************************************************************/
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
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  props: ['active', 'href', 'as'],
  computed: {
    classes: function classes() {
      return this.active ? 'block pl-3 pr-4 py-2 border-l-4 border-light-blue-400 text-base font-medium text-light-blue-700 bg-light-blue-50 dark:bg-cool-gray-900 focus:outline-none focus:text-light-blue-800 focus:bg-light-blue-100 dark:focus:bg-cool-gray-900 focus:border-light-blue-700 transition duration-150 ease-in-out' : 'block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-cool-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-800 dark:focus:text-gray-200 focus:bg-gray-50 dark:focus:bg-cool-gray-900 focus:border-gray-300 transition duration-150 ease-in-out';
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/SecondaryButton.vue?vue&type=script&lang=js&":
/*!*********************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/SecondaryButton.vue?vue&type=script&lang=js& ***!
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
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  props: {
    loading: {
      type: Boolean,
      "default": false
    },
    type: {
      type: String,
      "default": 'button'
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/SidebarLink.vue?vue&type=script&lang=js&":
/*!*****************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/SidebarLink.vue?vue&type=script&lang=js& ***!
  \*****************************************************************************************************************************************************************************************************************/
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
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  props: ['href', 'active'],
  computed: {
    classes: function classes() {
      return this.active ? 'bg-light-blue-400 -ml-3 cursor-pointer flex items-center px-6 py-2 rounded-r-full text-white w-full transition duration-150 ease-in-out' : 'cursor-pointer -ml-3 flex items-center px-6 py-2 rounded-r-full text-gray-700 dark:text-gray-400 w-full transition duration-150 ease-in-out hover:bg-light-blue-50 dark:hover:bg-cool-gray-700';
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Layouts/AppLayout.vue?vue&type=script&lang=js&":
/*!*************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Layouts/AppLayout.vue?vue&type=script&lang=js& ***!
  \*************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _Jetstream_ApplicationMark__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/Jetstream/ApplicationMark */ "./resources/js/Jetstream/ApplicationMark.vue");
/* harmony import */ var _Jetstream_Banner__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @/Jetstream/Banner */ "./resources/js/Jetstream/Banner.vue");
/* harmony import */ var _Jetstream_Dropdown__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @/Jetstream/Dropdown */ "./resources/js/Jetstream/Dropdown.vue");
/* harmony import */ var _Jetstream_DropdownLink__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @/Jetstream/DropdownLink */ "./resources/js/Jetstream/DropdownLink.vue");
/* harmony import */ var _Jetstream_NavLink__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @/Jetstream/NavLink */ "./resources/js/Jetstream/NavLink.vue");
/* harmony import */ var _Jetstream_SidebarLink__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @/Jetstream/SidebarLink */ "./resources/js/Jetstream/SidebarLink.vue");
/* harmony import */ var _Jetstream_ResponsiveNavLink__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! @/Jetstream/ResponsiveNavLink */ "./resources/js/Jetstream/ResponsiveNavLink.vue");
/* harmony import */ var _Components_Toast__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! @/Components/Toast */ "./resources/js/Components/Toast.vue");
/* harmony import */ var _Components_Icon__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! @/Components/Icon */ "./resources/js/Components/Icon.vue");
/* harmony import */ var _Shared_Search__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! @/Shared/Search */ "./resources/js/Shared/Search.vue");
/* harmony import */ var _Components_AppHead__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! @/Components/AppHead */ "./resources/js/Components/AppHead.vue");
/* harmony import */ var _Components_ColorThemeToggle__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! @/Components/ColorThemeToggle */ "./resources/js/Components/ColorThemeToggle.vue");
/* harmony import */ var _Shared_NotificationDropdown__WEBPACK_IMPORTED_MODULE_12__ = __webpack_require__(/*! @/Shared/NotificationDropdown */ "./resources/js/Shared/NotificationDropdown.vue");
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
    NotificationDropdown: _Shared_NotificationDropdown__WEBPACK_IMPORTED_MODULE_12__.default,
    ColorThemeToggle: _Components_ColorThemeToggle__WEBPACK_IMPORTED_MODULE_11__.default,
    AppHead: _Components_AppHead__WEBPACK_IMPORTED_MODULE_10__.default,
    Search: _Shared_Search__WEBPACK_IMPORTED_MODULE_9__.default,
    Icon: _Components_Icon__WEBPACK_IMPORTED_MODULE_8__.default,
    Toast: _Components_Toast__WEBPACK_IMPORTED_MODULE_7__.default,
    JetApplicationMark: _Jetstream_ApplicationMark__WEBPACK_IMPORTED_MODULE_0__.default,
    JetBanner: _Jetstream_Banner__WEBPACK_IMPORTED_MODULE_1__.default,
    JetDropdown: _Jetstream_Dropdown__WEBPACK_IMPORTED_MODULE_2__.default,
    JetDropdownLink: _Jetstream_DropdownLink__WEBPACK_IMPORTED_MODULE_3__.default,
    JetNavLink: _Jetstream_NavLink__WEBPACK_IMPORTED_MODULE_4__.default,
    JetResponsiveNavLink: _Jetstream_ResponsiveNavLink__WEBPACK_IMPORTED_MODULE_6__.default,
    JetSidebarLink: _Jetstream_SidebarLink__WEBPACK_IMPORTED_MODULE_5__.default
  },
  data: function data() {
    return {
      isAdminSidebarOpen: false,
      showingNavigationDropdown: false,
      canShowAdminSidebar: this.isStaff(this.$page.props.user)
    };
  },
  mounted: function mounted() {
    var _this = this;

    document.addEventListener('keydown', function (e) {
      if (e.keyCode == 27 && _this.isAdminSidebarOpen) _this.isAdminSidebarOpen = false;
    });
  },
  methods: {
    switchToTeam: function switchToTeam(team) {
      this.$inertia.put(route('current-team.update'), {
        'team_id': team.id
      }, {
        preserveState: false
      });
    },
    logout: function logout() {
      this.$inertia.post(route('logout'));
    },
    adminDrawer: function adminDrawer() {
      this.isAdminSidebarOpen = !this.isAdminSidebarOpen;
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Pages/Admin/Rank/IndexRank.vue?vue&type=script&lang=js&":
/*!**********************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Pages/Admin/Rank/IndexRank.vue?vue&type=script&lang=js& ***!
  \**********************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _Layouts_AppLayout__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/Layouts/AppLayout */ "./resources/js/Layouts/AppLayout.vue");
/* harmony import */ var _Jetstream_SectionBorder__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @/Jetstream/SectionBorder */ "./resources/js/Jetstream/SectionBorder.vue");
/* harmony import */ var _Components_Pagination__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @/Components/Pagination */ "./resources/js/Components/Pagination.vue");
/* harmony import */ var date_fns__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! date-fns */ "./node_modules/date-fns/esm/formatDistanceToNowStrict/index.js");
/* harmony import */ var _Jetstream_ConfirmationModal__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @/Jetstream/ConfirmationModal */ "./resources/js/Jetstream/ConfirmationModal.vue");
/* harmony import */ var _Jetstream_SecondaryButton__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @/Jetstream/SecondaryButton */ "./resources/js/Jetstream/SecondaryButton.vue");
/* harmony import */ var _Jetstream_DangerButton__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @/Jetstream/DangerButton */ "./resources/js/Jetstream/DangerButton.vue");
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
    AppLayout: _Layouts_AppLayout__WEBPACK_IMPORTED_MODULE_0__.default,
    JetSectionBorder: _Jetstream_SectionBorder__WEBPACK_IMPORTED_MODULE_1__.default,
    Pagination: _Components_Pagination__WEBPACK_IMPORTED_MODULE_2__.default,
    JetConfirmationModal: _Jetstream_ConfirmationModal__WEBPACK_IMPORTED_MODULE_3__.default,
    JetSecondaryButton: _Jetstream_SecondaryButton__WEBPACK_IMPORTED_MODULE_4__.default,
    JetDangerButton: _Jetstream_DangerButton__WEBPACK_IMPORTED_MODULE_5__.default
  },
  props: {
    ranks: Object
  },
  data: function data() {
    return {
      formatDistanceToNowStrict: date_fns__WEBPACK_IMPORTED_MODULE_6__.default,
      deleteRankForm: this.$inertia.form(),
      rankBeingDeleted: null
    };
  },
  methods: {
    confirmRankDeletion: function confirmRankDeletion(id) {
      this.rankBeingDeleted = id;
    },
    deleteNews: function deleteNews() {
      var _this = this;

      this.deleteRankForm["delete"](route('admin.rank.delete', this.rankBeingDeleted), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: function onSuccess() {
          return _this.rankBeingDeleted = null;
        }
      });
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Shared/NotificationDropdown.vue?vue&type=script&lang=js&":
/*!***********************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Shared/NotificationDropdown.vue?vue&type=script&lang=js& ***!
  \***********************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _Jetstream_Dropdown__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/Jetstream/Dropdown */ "./resources/js/Jetstream/Dropdown.vue");
/* harmony import */ var _Components_Icon__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @/Components/Icon */ "./resources/js/Components/Icon.vue");
/* harmony import */ var _Components_InfiniteScroll__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @/Components/InfiniteScroll */ "./resources/js/Components/InfiniteScroll.vue");
/* harmony import */ var _Components_Notification__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @/Components/Notification */ "./resources/js/Components/Notification.vue");
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
  name: 'NotificationDropdown',
  components: {
    Notification: _Components_Notification__WEBPACK_IMPORTED_MODULE_3__.default,
    InfiniteScroll: _Components_InfiniteScroll__WEBPACK_IMPORTED_MODULE_2__.default,
    Icon: _Components_Icon__WEBPACK_IMPORTED_MODULE_1__.default,
    JetDropdown: _Jetstream_Dropdown__WEBPACK_IMPORTED_MODULE_0__.default
  },
  data: function data() {
    return {
      loading: true,
      error: null,
      notifications: {}
    };
  },
  mounted: function mounted() {
    var _this = this;

    var routeToHit = route('notification.index');
    axios.get(routeToHit).then(function (response) {
      _this.notifications = response.data;
    })["finally"](function (e) {
      _this.loading = false;
    });
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Shared/Search.vue?vue&type=script&lang=js&":
/*!*********************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Shared/Search.vue?vue&type=script&lang=js& ***!
  \*********************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _Components_Icon__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/Components/Icon */ "./resources/js/Components/Icon.vue");
/* harmony import */ var lodash_function__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! lodash/function */ "./node_modules/lodash/function.js");
/* harmony import */ var lodash_function__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(lodash_function__WEBPACK_IMPORTED_MODULE_1__);
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
  name: 'Search',
  components: {
    Icon: _Components_Icon__WEBPACK_IMPORTED_MODULE_0__.default
  },
  data: function data() {
    return {
      showResults: false,
      loading: false,
      searchString: '',
      usersList: [],
      playersList: []
    };
  },
  methods: {
    performSearch: (0,lodash_function__WEBPACK_IMPORTED_MODULE_1__.debounce)(function () {
      var _this = this;

      if (!this.searchString) {
        return;
      }

      this.showResults = true;
      this.loading = true;
      axios.get(route('search', {
        q: this.searchString
      })).then(function (data) {
        _this.usersList = data.data.users;
        _this.playersList = data.data.players;
      })["finally"](function (x) {
        _this.loading = false;
      });
    }, 200)
  },
  // This hide the dropdown if clicked outside of the component
  created: function created() {
    var _this2 = this;

    window.addEventListener('click', function (e) {
      // close dropdown when clicked outside
      if (!_this2.$el.contains(e.target)) {
        _this2.showResults = false;
        _this2.searchString = '';
      }
    });
  }
});

/***/ }),

/***/ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9[0].rules[0].use[1]!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9[0].rules[0].use[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/Toast.vue?vue&type=style&index=0&id=24765128&scoped=true&lang=css&":
/*!********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9[0].rules[0].use[1]!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9[0].rules[0].use[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/Toast.vue?vue&type=style&index=0&id=24765128&scoped=true&lang=css& ***!
  \********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../../node_modules/css-loader/dist/runtime/api.js */ "./node_modules/css-loader/dist/runtime/api.js");
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__);
// Imports

var ___CSS_LOADER_EXPORT___ = _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default()(function(i){return i[1]});
// Module
___CSS_LOADER_EXPORT___.push([module.id, "\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\r\n/* Enter and leave animations can use different */\r\n/* durations and timing functions.              */\n.slide-fade-enter-active[data-v-24765128] {\r\n    transition: all .3s ease;\n}\n.slide-fade-leave-active[data-v-24765128] {\r\n    transition: all .3s cubic-bezier(1.0, 0.5, 0.8, 1.0);\n}\n.slide-fade-enter[data-v-24765128], .slide-fade-leave-to[data-v-24765128]\r\n    /* .slide-fade-leave-active below version 2.1.8 */ {\r\n    transform: translateX(10px);\r\n    opacity: 0;\n}\r\n", ""]);
// Exports
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (___CSS_LOADER_EXPORT___);


/***/ }),

/***/ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9[0].rules[0].use[1]!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9[0].rules[0].use[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/Toast.vue?vue&type=style&index=0&id=24765128&scoped=true&lang=css&":
/*!************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9[0].rules[0].use[1]!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9[0].rules[0].use[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/Toast.vue?vue&type=style&index=0&id=24765128&scoped=true&lang=css& ***!
  \************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! !../../../node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js */ "./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js");
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_0_rules_0_use_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_0_rules_0_use_2_node_modules_vue_loader_lib_index_js_vue_loader_options_Toast_vue_vue_type_style_index_0_id_24765128_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! !!../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9[0].rules[0].use[1]!../../../node_modules/vue-loader/lib/loaders/stylePostLoader.js!../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9[0].rules[0].use[2]!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./Toast.vue?vue&type=style&index=0&id=24765128&scoped=true&lang=css& */ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9[0].rules[0].use[1]!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9[0].rules[0].use[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/Toast.vue?vue&type=style&index=0&id=24765128&scoped=true&lang=css&");

            

var options = {};

options.insert = "head";
options.singleton = false;

var update = _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default()(_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_0_rules_0_use_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_0_rules_0_use_2_node_modules_vue_loader_lib_index_js_vue_loader_options_Toast_vue_vue_type_style_index_0_id_24765128_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_1__.default, options);



/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_0_rules_0_use_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_0_rules_0_use_2_node_modules_vue_loader_lib_index_js_vue_loader_options_Toast_vue_vue_type_style_index_0_id_24765128_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_1__.default.locals || {});

/***/ }),

/***/ "./resources/js/Components/ColorThemeToggle.vue":
/*!******************************************************!*\
  !*** ./resources/js/Components/ColorThemeToggle.vue ***!
  \******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _ColorThemeToggle_vue_vue_type_template_id_5f445c65___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ColorThemeToggle.vue?vue&type=template&id=5f445c65& */ "./resources/js/Components/ColorThemeToggle.vue?vue&type=template&id=5f445c65&");
/* harmony import */ var _ColorThemeToggle_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./ColorThemeToggle.vue?vue&type=script&lang=js& */ "./resources/js/Components/ColorThemeToggle.vue?vue&type=script&lang=js&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */
;
var component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__.default)(
  _ColorThemeToggle_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__.default,
  _ColorThemeToggle_vue_vue_type_template_id_5f445c65___WEBPACK_IMPORTED_MODULE_0__.render,
  _ColorThemeToggle_vue_vue_type_template_id_5f445c65___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/Components/ColorThemeToggle.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "./resources/js/Components/Icon.vue":
/*!******************************************!*\
  !*** ./resources/js/Components/Icon.vue ***!
  \******************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _Icon_vue_vue_type_template_id_7b50d278___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Icon.vue?vue&type=template&id=7b50d278& */ "./resources/js/Components/Icon.vue?vue&type=template&id=7b50d278&");
/* harmony import */ var _Icon_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Icon.vue?vue&type=script&lang=js& */ "./resources/js/Components/Icon.vue?vue&type=script&lang=js&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */
;
var component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__.default)(
  _Icon_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__.default,
  _Icon_vue_vue_type_template_id_7b50d278___WEBPACK_IMPORTED_MODULE_0__.render,
  _Icon_vue_vue_type_template_id_7b50d278___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/Components/Icon.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "./resources/js/Components/InfiniteScroll.vue":
/*!****************************************************!*\
  !*** ./resources/js/Components/InfiniteScroll.vue ***!
  \****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _InfiniteScroll_vue_vue_type_template_id_d1c55ee8___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./InfiniteScroll.vue?vue&type=template&id=d1c55ee8& */ "./resources/js/Components/InfiniteScroll.vue?vue&type=template&id=d1c55ee8&");
/* harmony import */ var _InfiniteScroll_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./InfiniteScroll.vue?vue&type=script&lang=js& */ "./resources/js/Components/InfiniteScroll.vue?vue&type=script&lang=js&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */
;
var component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__.default)(
  _InfiniteScroll_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__.default,
  _InfiniteScroll_vue_vue_type_template_id_d1c55ee8___WEBPACK_IMPORTED_MODULE_0__.render,
  _InfiniteScroll_vue_vue_type_template_id_d1c55ee8___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/Components/InfiniteScroll.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "./resources/js/Components/Notification.vue":
/*!**************************************************!*\
  !*** ./resources/js/Components/Notification.vue ***!
  \**************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _Notification_vue_vue_type_template_id_be911194___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Notification.vue?vue&type=template&id=be911194& */ "./resources/js/Components/Notification.vue?vue&type=template&id=be911194&");
/* harmony import */ var _Notification_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Notification.vue?vue&type=script&lang=js& */ "./resources/js/Components/Notification.vue?vue&type=script&lang=js&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */
;
var component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__.default)(
  _Notification_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__.default,
  _Notification_vue_vue_type_template_id_be911194___WEBPACK_IMPORTED_MODULE_0__.render,
  _Notification_vue_vue_type_template_id_be911194___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/Components/Notification.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "./resources/js/Components/Pagination.vue":
/*!************************************************!*\
  !*** ./resources/js/Components/Pagination.vue ***!
  \************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _Pagination_vue_vue_type_template_id_0e1fe725___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Pagination.vue?vue&type=template&id=0e1fe725& */ "./resources/js/Components/Pagination.vue?vue&type=template&id=0e1fe725&");
/* harmony import */ var _Pagination_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Pagination.vue?vue&type=script&lang=js& */ "./resources/js/Components/Pagination.vue?vue&type=script&lang=js&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */
;
var component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__.default)(
  _Pagination_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__.default,
  _Pagination_vue_vue_type_template_id_0e1fe725___WEBPACK_IMPORTED_MODULE_0__.render,
  _Pagination_vue_vue_type_template_id_0e1fe725___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/Components/Pagination.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "./resources/js/Components/Toast.vue":
/*!*******************************************!*\
  !*** ./resources/js/Components/Toast.vue ***!
  \*******************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _Toast_vue_vue_type_template_id_24765128_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Toast.vue?vue&type=template&id=24765128&scoped=true& */ "./resources/js/Components/Toast.vue?vue&type=template&id=24765128&scoped=true&");
/* harmony import */ var _Toast_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Toast.vue?vue&type=script&lang=js& */ "./resources/js/Components/Toast.vue?vue&type=script&lang=js&");
/* harmony import */ var _Toast_vue_vue_type_style_index_0_id_24765128_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./Toast.vue?vue&type=style&index=0&id=24765128&scoped=true&lang=css& */ "./resources/js/Components/Toast.vue?vue&type=style&index=0&id=24765128&scoped=true&lang=css&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! !../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");



;


/* normalize component */

var component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_3__.default)(
  _Toast_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__.default,
  _Toast_vue_vue_type_template_id_24765128_scoped_true___WEBPACK_IMPORTED_MODULE_0__.render,
  _Toast_vue_vue_type_template_id_24765128_scoped_true___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  "24765128",
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/Components/Toast.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "./resources/js/Jetstream/ApplicationMark.vue":
/*!****************************************************!*\
  !*** ./resources/js/Jetstream/ApplicationMark.vue ***!
  \****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _ApplicationMark_vue_vue_type_template_id_6ed2e539___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ApplicationMark.vue?vue&type=template&id=6ed2e539& */ "./resources/js/Jetstream/ApplicationMark.vue?vue&type=template&id=6ed2e539&");
/* harmony import */ var _ApplicationMark_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./ApplicationMark.vue?vue&type=script&lang=js& */ "./resources/js/Jetstream/ApplicationMark.vue?vue&type=script&lang=js&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */
;
var component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__.default)(
  _ApplicationMark_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__.default,
  _ApplicationMark_vue_vue_type_template_id_6ed2e539___WEBPACK_IMPORTED_MODULE_0__.render,
  _ApplicationMark_vue_vue_type_template_id_6ed2e539___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/Jetstream/ApplicationMark.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "./resources/js/Jetstream/Banner.vue":
/*!*******************************************!*\
  !*** ./resources/js/Jetstream/Banner.vue ***!
  \*******************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _Banner_vue_vue_type_template_id_55462a60___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Banner.vue?vue&type=template&id=55462a60& */ "./resources/js/Jetstream/Banner.vue?vue&type=template&id=55462a60&");
/* harmony import */ var _Banner_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Banner.vue?vue&type=script&lang=js& */ "./resources/js/Jetstream/Banner.vue?vue&type=script&lang=js&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */
;
var component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__.default)(
  _Banner_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__.default,
  _Banner_vue_vue_type_template_id_55462a60___WEBPACK_IMPORTED_MODULE_0__.render,
  _Banner_vue_vue_type_template_id_55462a60___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/Jetstream/Banner.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "./resources/js/Jetstream/ConfirmationModal.vue":
/*!******************************************************!*\
  !*** ./resources/js/Jetstream/ConfirmationModal.vue ***!
  \******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _ConfirmationModal_vue_vue_type_template_id_3478b418___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ConfirmationModal.vue?vue&type=template&id=3478b418& */ "./resources/js/Jetstream/ConfirmationModal.vue?vue&type=template&id=3478b418&");
/* harmony import */ var _ConfirmationModal_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./ConfirmationModal.vue?vue&type=script&lang=js& */ "./resources/js/Jetstream/ConfirmationModal.vue?vue&type=script&lang=js&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */
;
var component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__.default)(
  _ConfirmationModal_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__.default,
  _ConfirmationModal_vue_vue_type_template_id_3478b418___WEBPACK_IMPORTED_MODULE_0__.render,
  _ConfirmationModal_vue_vue_type_template_id_3478b418___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/Jetstream/ConfirmationModal.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "./resources/js/Jetstream/DangerButton.vue":
/*!*************************************************!*\
  !*** ./resources/js/Jetstream/DangerButton.vue ***!
  \*************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _DangerButton_vue_vue_type_template_id_cdf2462e___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./DangerButton.vue?vue&type=template&id=cdf2462e& */ "./resources/js/Jetstream/DangerButton.vue?vue&type=template&id=cdf2462e&");
/* harmony import */ var _DangerButton_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./DangerButton.vue?vue&type=script&lang=js& */ "./resources/js/Jetstream/DangerButton.vue?vue&type=script&lang=js&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */
;
var component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__.default)(
  _DangerButton_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__.default,
  _DangerButton_vue_vue_type_template_id_cdf2462e___WEBPACK_IMPORTED_MODULE_0__.render,
  _DangerButton_vue_vue_type_template_id_cdf2462e___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/Jetstream/DangerButton.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "./resources/js/Jetstream/Dropdown.vue":
/*!*********************************************!*\
  !*** ./resources/js/Jetstream/Dropdown.vue ***!
  \*********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _Dropdown_vue_vue_type_template_id_bd908476___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Dropdown.vue?vue&type=template&id=bd908476& */ "./resources/js/Jetstream/Dropdown.vue?vue&type=template&id=bd908476&");
/* harmony import */ var _Dropdown_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Dropdown.vue?vue&type=script&lang=js& */ "./resources/js/Jetstream/Dropdown.vue?vue&type=script&lang=js&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */
;
var component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__.default)(
  _Dropdown_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__.default,
  _Dropdown_vue_vue_type_template_id_bd908476___WEBPACK_IMPORTED_MODULE_0__.render,
  _Dropdown_vue_vue_type_template_id_bd908476___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/Jetstream/Dropdown.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "./resources/js/Jetstream/DropdownLink.vue":
/*!*************************************************!*\
  !*** ./resources/js/Jetstream/DropdownLink.vue ***!
  \*************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _DropdownLink_vue_vue_type_template_id_1114e65f___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./DropdownLink.vue?vue&type=template&id=1114e65f& */ "./resources/js/Jetstream/DropdownLink.vue?vue&type=template&id=1114e65f&");
/* harmony import */ var _DropdownLink_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./DropdownLink.vue?vue&type=script&lang=js& */ "./resources/js/Jetstream/DropdownLink.vue?vue&type=script&lang=js&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */
;
var component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__.default)(
  _DropdownLink_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__.default,
  _DropdownLink_vue_vue_type_template_id_1114e65f___WEBPACK_IMPORTED_MODULE_0__.render,
  _DropdownLink_vue_vue_type_template_id_1114e65f___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/Jetstream/DropdownLink.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "./resources/js/Jetstream/Modal.vue":
/*!******************************************!*\
  !*** ./resources/js/Jetstream/Modal.vue ***!
  \******************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _Modal_vue_vue_type_template_id_64f7dca9___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Modal.vue?vue&type=template&id=64f7dca9& */ "./resources/js/Jetstream/Modal.vue?vue&type=template&id=64f7dca9&");
/* harmony import */ var _Modal_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Modal.vue?vue&type=script&lang=js& */ "./resources/js/Jetstream/Modal.vue?vue&type=script&lang=js&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */
;
var component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__.default)(
  _Modal_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__.default,
  _Modal_vue_vue_type_template_id_64f7dca9___WEBPACK_IMPORTED_MODULE_0__.render,
  _Modal_vue_vue_type_template_id_64f7dca9___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/Jetstream/Modal.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "./resources/js/Jetstream/NavLink.vue":
/*!********************************************!*\
  !*** ./resources/js/Jetstream/NavLink.vue ***!
  \********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _NavLink_vue_vue_type_template_id_1719168e___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./NavLink.vue?vue&type=template&id=1719168e& */ "./resources/js/Jetstream/NavLink.vue?vue&type=template&id=1719168e&");
/* harmony import */ var _NavLink_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./NavLink.vue?vue&type=script&lang=js& */ "./resources/js/Jetstream/NavLink.vue?vue&type=script&lang=js&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */
;
var component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__.default)(
  _NavLink_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__.default,
  _NavLink_vue_vue_type_template_id_1719168e___WEBPACK_IMPORTED_MODULE_0__.render,
  _NavLink_vue_vue_type_template_id_1719168e___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/Jetstream/NavLink.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "./resources/js/Jetstream/ResponsiveNavLink.vue":
/*!******************************************************!*\
  !*** ./resources/js/Jetstream/ResponsiveNavLink.vue ***!
  \******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _ResponsiveNavLink_vue_vue_type_template_id_c1e95d36___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ResponsiveNavLink.vue?vue&type=template&id=c1e95d36& */ "./resources/js/Jetstream/ResponsiveNavLink.vue?vue&type=template&id=c1e95d36&");
/* harmony import */ var _ResponsiveNavLink_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./ResponsiveNavLink.vue?vue&type=script&lang=js& */ "./resources/js/Jetstream/ResponsiveNavLink.vue?vue&type=script&lang=js&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */
;
var component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__.default)(
  _ResponsiveNavLink_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__.default,
  _ResponsiveNavLink_vue_vue_type_template_id_c1e95d36___WEBPACK_IMPORTED_MODULE_0__.render,
  _ResponsiveNavLink_vue_vue_type_template_id_c1e95d36___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/Jetstream/ResponsiveNavLink.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "./resources/js/Jetstream/SecondaryButton.vue":
/*!****************************************************!*\
  !*** ./resources/js/Jetstream/SecondaryButton.vue ***!
  \****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _SecondaryButton_vue_vue_type_template_id_8dd9837c___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./SecondaryButton.vue?vue&type=template&id=8dd9837c& */ "./resources/js/Jetstream/SecondaryButton.vue?vue&type=template&id=8dd9837c&");
/* harmony import */ var _SecondaryButton_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./SecondaryButton.vue?vue&type=script&lang=js& */ "./resources/js/Jetstream/SecondaryButton.vue?vue&type=script&lang=js&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */
;
var component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__.default)(
  _SecondaryButton_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__.default,
  _SecondaryButton_vue_vue_type_template_id_8dd9837c___WEBPACK_IMPORTED_MODULE_0__.render,
  _SecondaryButton_vue_vue_type_template_id_8dd9837c___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/Jetstream/SecondaryButton.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "./resources/js/Jetstream/SectionBorder.vue":
/*!**************************************************!*\
  !*** ./resources/js/Jetstream/SectionBorder.vue ***!
  \**************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _SectionBorder_vue_vue_type_template_id_2661c926___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./SectionBorder.vue?vue&type=template&id=2661c926& */ "./resources/js/Jetstream/SectionBorder.vue?vue&type=template&id=2661c926&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! !../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");

var script = {}


/* normalize component */
;
var component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_1__.default)(
  script,
  _SectionBorder_vue_vue_type_template_id_2661c926___WEBPACK_IMPORTED_MODULE_0__.render,
  _SectionBorder_vue_vue_type_template_id_2661c926___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/Jetstream/SectionBorder.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "./resources/js/Jetstream/SidebarLink.vue":
/*!************************************************!*\
  !*** ./resources/js/Jetstream/SidebarLink.vue ***!
  \************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _SidebarLink_vue_vue_type_template_id_7f9d081c___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./SidebarLink.vue?vue&type=template&id=7f9d081c& */ "./resources/js/Jetstream/SidebarLink.vue?vue&type=template&id=7f9d081c&");
/* harmony import */ var _SidebarLink_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./SidebarLink.vue?vue&type=script&lang=js& */ "./resources/js/Jetstream/SidebarLink.vue?vue&type=script&lang=js&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */
;
var component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__.default)(
  _SidebarLink_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__.default,
  _SidebarLink_vue_vue_type_template_id_7f9d081c___WEBPACK_IMPORTED_MODULE_0__.render,
  _SidebarLink_vue_vue_type_template_id_7f9d081c___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/Jetstream/SidebarLink.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "./resources/js/Layouts/AppLayout.vue":
/*!********************************************!*\
  !*** ./resources/js/Layouts/AppLayout.vue ***!
  \********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _AppLayout_vue_vue_type_template_id_5663af57___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./AppLayout.vue?vue&type=template&id=5663af57& */ "./resources/js/Layouts/AppLayout.vue?vue&type=template&id=5663af57&");
/* harmony import */ var _AppLayout_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./AppLayout.vue?vue&type=script&lang=js& */ "./resources/js/Layouts/AppLayout.vue?vue&type=script&lang=js&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */
;
var component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__.default)(
  _AppLayout_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__.default,
  _AppLayout_vue_vue_type_template_id_5663af57___WEBPACK_IMPORTED_MODULE_0__.render,
  _AppLayout_vue_vue_type_template_id_5663af57___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/Layouts/AppLayout.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "./resources/js/Pages/Admin/Rank/IndexRank.vue":
/*!*****************************************************!*\
  !*** ./resources/js/Pages/Admin/Rank/IndexRank.vue ***!
  \*****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _IndexRank_vue_vue_type_template_id_b72e4738___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./IndexRank.vue?vue&type=template&id=b72e4738& */ "./resources/js/Pages/Admin/Rank/IndexRank.vue?vue&type=template&id=b72e4738&");
/* harmony import */ var _IndexRank_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./IndexRank.vue?vue&type=script&lang=js& */ "./resources/js/Pages/Admin/Rank/IndexRank.vue?vue&type=script&lang=js&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */
;
var component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__.default)(
  _IndexRank_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__.default,
  _IndexRank_vue_vue_type_template_id_b72e4738___WEBPACK_IMPORTED_MODULE_0__.render,
  _IndexRank_vue_vue_type_template_id_b72e4738___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/Pages/Admin/Rank/IndexRank.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "./resources/js/Shared/NotificationDropdown.vue":
/*!******************************************************!*\
  !*** ./resources/js/Shared/NotificationDropdown.vue ***!
  \******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _NotificationDropdown_vue_vue_type_template_id_3eac6b78___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./NotificationDropdown.vue?vue&type=template&id=3eac6b78& */ "./resources/js/Shared/NotificationDropdown.vue?vue&type=template&id=3eac6b78&");
/* harmony import */ var _NotificationDropdown_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./NotificationDropdown.vue?vue&type=script&lang=js& */ "./resources/js/Shared/NotificationDropdown.vue?vue&type=script&lang=js&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */
;
var component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__.default)(
  _NotificationDropdown_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__.default,
  _NotificationDropdown_vue_vue_type_template_id_3eac6b78___WEBPACK_IMPORTED_MODULE_0__.render,
  _NotificationDropdown_vue_vue_type_template_id_3eac6b78___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/Shared/NotificationDropdown.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "./resources/js/Shared/Search.vue":
/*!****************************************!*\
  !*** ./resources/js/Shared/Search.vue ***!
  \****************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _Search_vue_vue_type_template_id_2d3cfc04_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Search.vue?vue&type=template&id=2d3cfc04&scoped=true& */ "./resources/js/Shared/Search.vue?vue&type=template&id=2d3cfc04&scoped=true&");
/* harmony import */ var _Search_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Search.vue?vue&type=script&lang=js& */ "./resources/js/Shared/Search.vue?vue&type=script&lang=js&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */
;
var component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__.default)(
  _Search_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__.default,
  _Search_vue_vue_type_template_id_2d3cfc04_scoped_true___WEBPACK_IMPORTED_MODULE_0__.render,
  _Search_vue_vue_type_template_id_2d3cfc04_scoped_true___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  "2d3cfc04",
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/Shared/Search.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "./resources/js/Components/ColorThemeToggle.vue?vue&type=script&lang=js&":
/*!*******************************************************************************!*\
  !*** ./resources/js/Components/ColorThemeToggle.vue?vue&type=script&lang=js& ***!
  \*******************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_ColorThemeToggle_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./ColorThemeToggle.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/ColorThemeToggle.vue?vue&type=script&lang=js&");
 /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_ColorThemeToggle_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__.default); 

/***/ }),

/***/ "./resources/js/Components/Icon.vue?vue&type=script&lang=js&":
/*!*******************************************************************!*\
  !*** ./resources/js/Components/Icon.vue?vue&type=script&lang=js& ***!
  \*******************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Icon_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./Icon.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/Icon.vue?vue&type=script&lang=js&");
 /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Icon_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__.default); 

/***/ }),

/***/ "./resources/js/Components/InfiniteScroll.vue?vue&type=script&lang=js&":
/*!*****************************************************************************!*\
  !*** ./resources/js/Components/InfiniteScroll.vue?vue&type=script&lang=js& ***!
  \*****************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_InfiniteScroll_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./InfiniteScroll.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/InfiniteScroll.vue?vue&type=script&lang=js&");
 /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_InfiniteScroll_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__.default); 

/***/ }),

/***/ "./resources/js/Components/Notification.vue?vue&type=script&lang=js&":
/*!***************************************************************************!*\
  !*** ./resources/js/Components/Notification.vue?vue&type=script&lang=js& ***!
  \***************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Notification_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./Notification.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/Notification.vue?vue&type=script&lang=js&");
 /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Notification_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__.default); 

/***/ }),

/***/ "./resources/js/Components/Pagination.vue?vue&type=script&lang=js&":
/*!*************************************************************************!*\
  !*** ./resources/js/Components/Pagination.vue?vue&type=script&lang=js& ***!
  \*************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Pagination_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./Pagination.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/Pagination.vue?vue&type=script&lang=js&");
 /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Pagination_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__.default); 

/***/ }),

/***/ "./resources/js/Components/Toast.vue?vue&type=script&lang=js&":
/*!********************************************************************!*\
  !*** ./resources/js/Components/Toast.vue?vue&type=script&lang=js& ***!
  \********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Toast_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./Toast.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/Toast.vue?vue&type=script&lang=js&");
 /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Toast_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__.default); 

/***/ }),

/***/ "./resources/js/Jetstream/ApplicationMark.vue?vue&type=script&lang=js&":
/*!*****************************************************************************!*\
  !*** ./resources/js/Jetstream/ApplicationMark.vue?vue&type=script&lang=js& ***!
  \*****************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_ApplicationMark_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./ApplicationMark.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/ApplicationMark.vue?vue&type=script&lang=js&");
 /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_ApplicationMark_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__.default); 

/***/ }),

/***/ "./resources/js/Jetstream/Banner.vue?vue&type=script&lang=js&":
/*!********************************************************************!*\
  !*** ./resources/js/Jetstream/Banner.vue?vue&type=script&lang=js& ***!
  \********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Banner_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./Banner.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/Banner.vue?vue&type=script&lang=js&");
 /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Banner_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__.default); 

/***/ }),

/***/ "./resources/js/Jetstream/ConfirmationModal.vue?vue&type=script&lang=js&":
/*!*******************************************************************************!*\
  !*** ./resources/js/Jetstream/ConfirmationModal.vue?vue&type=script&lang=js& ***!
  \*******************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_ConfirmationModal_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./ConfirmationModal.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/ConfirmationModal.vue?vue&type=script&lang=js&");
 /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_ConfirmationModal_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__.default); 

/***/ }),

/***/ "./resources/js/Jetstream/DangerButton.vue?vue&type=script&lang=js&":
/*!**************************************************************************!*\
  !*** ./resources/js/Jetstream/DangerButton.vue?vue&type=script&lang=js& ***!
  \**************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_DangerButton_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./DangerButton.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/DangerButton.vue?vue&type=script&lang=js&");
 /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_DangerButton_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__.default); 

/***/ }),

/***/ "./resources/js/Jetstream/Dropdown.vue?vue&type=script&lang=js&":
/*!**********************************************************************!*\
  !*** ./resources/js/Jetstream/Dropdown.vue?vue&type=script&lang=js& ***!
  \**********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Dropdown_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./Dropdown.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/Dropdown.vue?vue&type=script&lang=js&");
 /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Dropdown_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__.default); 

/***/ }),

/***/ "./resources/js/Jetstream/DropdownLink.vue?vue&type=script&lang=js&":
/*!**************************************************************************!*\
  !*** ./resources/js/Jetstream/DropdownLink.vue?vue&type=script&lang=js& ***!
  \**************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_DropdownLink_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./DropdownLink.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/DropdownLink.vue?vue&type=script&lang=js&");
 /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_DropdownLink_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__.default); 

/***/ }),

/***/ "./resources/js/Jetstream/Modal.vue?vue&type=script&lang=js&":
/*!*******************************************************************!*\
  !*** ./resources/js/Jetstream/Modal.vue?vue&type=script&lang=js& ***!
  \*******************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Modal_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./Modal.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/Modal.vue?vue&type=script&lang=js&");
 /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Modal_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__.default); 

/***/ }),

/***/ "./resources/js/Jetstream/NavLink.vue?vue&type=script&lang=js&":
/*!*********************************************************************!*\
  !*** ./resources/js/Jetstream/NavLink.vue?vue&type=script&lang=js& ***!
  \*********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_NavLink_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./NavLink.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/NavLink.vue?vue&type=script&lang=js&");
 /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_NavLink_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__.default); 

/***/ }),

/***/ "./resources/js/Jetstream/ResponsiveNavLink.vue?vue&type=script&lang=js&":
/*!*******************************************************************************!*\
  !*** ./resources/js/Jetstream/ResponsiveNavLink.vue?vue&type=script&lang=js& ***!
  \*******************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_ResponsiveNavLink_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./ResponsiveNavLink.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/ResponsiveNavLink.vue?vue&type=script&lang=js&");
 /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_ResponsiveNavLink_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__.default); 

/***/ }),

/***/ "./resources/js/Jetstream/SecondaryButton.vue?vue&type=script&lang=js&":
/*!*****************************************************************************!*\
  !*** ./resources/js/Jetstream/SecondaryButton.vue?vue&type=script&lang=js& ***!
  \*****************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_SecondaryButton_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./SecondaryButton.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/SecondaryButton.vue?vue&type=script&lang=js&");
 /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_SecondaryButton_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__.default); 

/***/ }),

/***/ "./resources/js/Jetstream/SidebarLink.vue?vue&type=script&lang=js&":
/*!*************************************************************************!*\
  !*** ./resources/js/Jetstream/SidebarLink.vue?vue&type=script&lang=js& ***!
  \*************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_SidebarLink_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./SidebarLink.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/SidebarLink.vue?vue&type=script&lang=js&");
 /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_SidebarLink_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__.default); 

/***/ }),

/***/ "./resources/js/Layouts/AppLayout.vue?vue&type=script&lang=js&":
/*!*********************************************************************!*\
  !*** ./resources/js/Layouts/AppLayout.vue?vue&type=script&lang=js& ***!
  \*********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_AppLayout_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./AppLayout.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Layouts/AppLayout.vue?vue&type=script&lang=js&");
 /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_AppLayout_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__.default); 

/***/ }),

/***/ "./resources/js/Pages/Admin/Rank/IndexRank.vue?vue&type=script&lang=js&":
/*!******************************************************************************!*\
  !*** ./resources/js/Pages/Admin/Rank/IndexRank.vue?vue&type=script&lang=js& ***!
  \******************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_IndexRank_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!../../../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./IndexRank.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Pages/Admin/Rank/IndexRank.vue?vue&type=script&lang=js&");
 /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_IndexRank_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__.default); 

/***/ }),

/***/ "./resources/js/Shared/NotificationDropdown.vue?vue&type=script&lang=js&":
/*!*******************************************************************************!*\
  !*** ./resources/js/Shared/NotificationDropdown.vue?vue&type=script&lang=js& ***!
  \*******************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_NotificationDropdown_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./NotificationDropdown.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Shared/NotificationDropdown.vue?vue&type=script&lang=js&");
 /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_NotificationDropdown_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__.default); 

/***/ }),

/***/ "./resources/js/Shared/Search.vue?vue&type=script&lang=js&":
/*!*****************************************************************!*\
  !*** ./resources/js/Shared/Search.vue?vue&type=script&lang=js& ***!
  \*****************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Search_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./Search.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Shared/Search.vue?vue&type=script&lang=js&");
 /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Search_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__.default); 

/***/ }),

/***/ "./resources/js/Components/Toast.vue?vue&type=style&index=0&id=24765128&scoped=true&lang=css&":
/*!****************************************************************************************************!*\
  !*** ./resources/js/Components/Toast.vue?vue&type=style&index=0&id=24765128&scoped=true&lang=css& ***!
  \****************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_style_loader_dist_cjs_js_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_0_rules_0_use_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_0_rules_0_use_2_node_modules_vue_loader_lib_index_js_vue_loader_options_Toast_vue_vue_type_style_index_0_id_24765128_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/style-loader/dist/cjs.js!../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9[0].rules[0].use[1]!../../../node_modules/vue-loader/lib/loaders/stylePostLoader.js!../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9[0].rules[0].use[2]!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./Toast.vue?vue&type=style&index=0&id=24765128&scoped=true&lang=css& */ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9[0].rules[0].use[1]!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9[0].rules[0].use[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/Toast.vue?vue&type=style&index=0&id=24765128&scoped=true&lang=css&");


/***/ }),

/***/ "./resources/js/Components/ColorThemeToggle.vue?vue&type=template&id=5f445c65&":
/*!*************************************************************************************!*\
  !*** ./resources/js/Components/ColorThemeToggle.vue?vue&type=template&id=5f445c65& ***!
  \*************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_ColorThemeToggle_vue_vue_type_template_id_5f445c65___WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   "staticRenderFns": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_ColorThemeToggle_vue_vue_type_template_id_5f445c65___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_ColorThemeToggle_vue_vue_type_template_id_5f445c65___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./ColorThemeToggle.vue?vue&type=template&id=5f445c65& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/ColorThemeToggle.vue?vue&type=template&id=5f445c65&");


/***/ }),

/***/ "./resources/js/Components/Icon.vue?vue&type=template&id=7b50d278&":
/*!*************************************************************************!*\
  !*** ./resources/js/Components/Icon.vue?vue&type=template&id=7b50d278& ***!
  \*************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Icon_vue_vue_type_template_id_7b50d278___WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   "staticRenderFns": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Icon_vue_vue_type_template_id_7b50d278___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Icon_vue_vue_type_template_id_7b50d278___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./Icon.vue?vue&type=template&id=7b50d278& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/Icon.vue?vue&type=template&id=7b50d278&");


/***/ }),

/***/ "./resources/js/Components/InfiniteScroll.vue?vue&type=template&id=d1c55ee8&":
/*!***********************************************************************************!*\
  !*** ./resources/js/Components/InfiniteScroll.vue?vue&type=template&id=d1c55ee8& ***!
  \***********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_InfiniteScroll_vue_vue_type_template_id_d1c55ee8___WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   "staticRenderFns": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_InfiniteScroll_vue_vue_type_template_id_d1c55ee8___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_InfiniteScroll_vue_vue_type_template_id_d1c55ee8___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./InfiniteScroll.vue?vue&type=template&id=d1c55ee8& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/InfiniteScroll.vue?vue&type=template&id=d1c55ee8&");


/***/ }),

/***/ "./resources/js/Components/Notification.vue?vue&type=template&id=be911194&":
/*!*********************************************************************************!*\
  !*** ./resources/js/Components/Notification.vue?vue&type=template&id=be911194& ***!
  \*********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Notification_vue_vue_type_template_id_be911194___WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   "staticRenderFns": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Notification_vue_vue_type_template_id_be911194___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Notification_vue_vue_type_template_id_be911194___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./Notification.vue?vue&type=template&id=be911194& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/Notification.vue?vue&type=template&id=be911194&");


/***/ }),

/***/ "./resources/js/Components/Pagination.vue?vue&type=template&id=0e1fe725&":
/*!*******************************************************************************!*\
  !*** ./resources/js/Components/Pagination.vue?vue&type=template&id=0e1fe725& ***!
  \*******************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Pagination_vue_vue_type_template_id_0e1fe725___WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   "staticRenderFns": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Pagination_vue_vue_type_template_id_0e1fe725___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Pagination_vue_vue_type_template_id_0e1fe725___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./Pagination.vue?vue&type=template&id=0e1fe725& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/Pagination.vue?vue&type=template&id=0e1fe725&");


/***/ }),

/***/ "./resources/js/Components/Toast.vue?vue&type=template&id=24765128&scoped=true&":
/*!**************************************************************************************!*\
  !*** ./resources/js/Components/Toast.vue?vue&type=template&id=24765128&scoped=true& ***!
  \**************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Toast_vue_vue_type_template_id_24765128_scoped_true___WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   "staticRenderFns": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Toast_vue_vue_type_template_id_24765128_scoped_true___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Toast_vue_vue_type_template_id_24765128_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./Toast.vue?vue&type=template&id=24765128&scoped=true& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/Toast.vue?vue&type=template&id=24765128&scoped=true&");


/***/ }),

/***/ "./resources/js/Jetstream/ApplicationMark.vue?vue&type=template&id=6ed2e539&":
/*!***********************************************************************************!*\
  !*** ./resources/js/Jetstream/ApplicationMark.vue?vue&type=template&id=6ed2e539& ***!
  \***********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_ApplicationMark_vue_vue_type_template_id_6ed2e539___WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   "staticRenderFns": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_ApplicationMark_vue_vue_type_template_id_6ed2e539___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_ApplicationMark_vue_vue_type_template_id_6ed2e539___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./ApplicationMark.vue?vue&type=template&id=6ed2e539& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/ApplicationMark.vue?vue&type=template&id=6ed2e539&");


/***/ }),

/***/ "./resources/js/Jetstream/Banner.vue?vue&type=template&id=55462a60&":
/*!**************************************************************************!*\
  !*** ./resources/js/Jetstream/Banner.vue?vue&type=template&id=55462a60& ***!
  \**************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Banner_vue_vue_type_template_id_55462a60___WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   "staticRenderFns": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Banner_vue_vue_type_template_id_55462a60___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Banner_vue_vue_type_template_id_55462a60___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./Banner.vue?vue&type=template&id=55462a60& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/Banner.vue?vue&type=template&id=55462a60&");


/***/ }),

/***/ "./resources/js/Jetstream/ConfirmationModal.vue?vue&type=template&id=3478b418&":
/*!*************************************************************************************!*\
  !*** ./resources/js/Jetstream/ConfirmationModal.vue?vue&type=template&id=3478b418& ***!
  \*************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_ConfirmationModal_vue_vue_type_template_id_3478b418___WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   "staticRenderFns": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_ConfirmationModal_vue_vue_type_template_id_3478b418___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_ConfirmationModal_vue_vue_type_template_id_3478b418___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./ConfirmationModal.vue?vue&type=template&id=3478b418& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/ConfirmationModal.vue?vue&type=template&id=3478b418&");


/***/ }),

/***/ "./resources/js/Jetstream/DangerButton.vue?vue&type=template&id=cdf2462e&":
/*!********************************************************************************!*\
  !*** ./resources/js/Jetstream/DangerButton.vue?vue&type=template&id=cdf2462e& ***!
  \********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_DangerButton_vue_vue_type_template_id_cdf2462e___WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   "staticRenderFns": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_DangerButton_vue_vue_type_template_id_cdf2462e___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_DangerButton_vue_vue_type_template_id_cdf2462e___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./DangerButton.vue?vue&type=template&id=cdf2462e& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/DangerButton.vue?vue&type=template&id=cdf2462e&");


/***/ }),

/***/ "./resources/js/Jetstream/Dropdown.vue?vue&type=template&id=bd908476&":
/*!****************************************************************************!*\
  !*** ./resources/js/Jetstream/Dropdown.vue?vue&type=template&id=bd908476& ***!
  \****************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Dropdown_vue_vue_type_template_id_bd908476___WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   "staticRenderFns": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Dropdown_vue_vue_type_template_id_bd908476___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Dropdown_vue_vue_type_template_id_bd908476___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./Dropdown.vue?vue&type=template&id=bd908476& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/Dropdown.vue?vue&type=template&id=bd908476&");


/***/ }),

/***/ "./resources/js/Jetstream/DropdownLink.vue?vue&type=template&id=1114e65f&":
/*!********************************************************************************!*\
  !*** ./resources/js/Jetstream/DropdownLink.vue?vue&type=template&id=1114e65f& ***!
  \********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_DropdownLink_vue_vue_type_template_id_1114e65f___WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   "staticRenderFns": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_DropdownLink_vue_vue_type_template_id_1114e65f___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_DropdownLink_vue_vue_type_template_id_1114e65f___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./DropdownLink.vue?vue&type=template&id=1114e65f& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/DropdownLink.vue?vue&type=template&id=1114e65f&");


/***/ }),

/***/ "./resources/js/Jetstream/Modal.vue?vue&type=template&id=64f7dca9&":
/*!*************************************************************************!*\
  !*** ./resources/js/Jetstream/Modal.vue?vue&type=template&id=64f7dca9& ***!
  \*************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Modal_vue_vue_type_template_id_64f7dca9___WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   "staticRenderFns": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Modal_vue_vue_type_template_id_64f7dca9___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Modal_vue_vue_type_template_id_64f7dca9___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./Modal.vue?vue&type=template&id=64f7dca9& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/Modal.vue?vue&type=template&id=64f7dca9&");


/***/ }),

/***/ "./resources/js/Jetstream/NavLink.vue?vue&type=template&id=1719168e&":
/*!***************************************************************************!*\
  !*** ./resources/js/Jetstream/NavLink.vue?vue&type=template&id=1719168e& ***!
  \***************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_NavLink_vue_vue_type_template_id_1719168e___WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   "staticRenderFns": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_NavLink_vue_vue_type_template_id_1719168e___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_NavLink_vue_vue_type_template_id_1719168e___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./NavLink.vue?vue&type=template&id=1719168e& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/NavLink.vue?vue&type=template&id=1719168e&");


/***/ }),

/***/ "./resources/js/Jetstream/ResponsiveNavLink.vue?vue&type=template&id=c1e95d36&":
/*!*************************************************************************************!*\
  !*** ./resources/js/Jetstream/ResponsiveNavLink.vue?vue&type=template&id=c1e95d36& ***!
  \*************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_ResponsiveNavLink_vue_vue_type_template_id_c1e95d36___WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   "staticRenderFns": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_ResponsiveNavLink_vue_vue_type_template_id_c1e95d36___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_ResponsiveNavLink_vue_vue_type_template_id_c1e95d36___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./ResponsiveNavLink.vue?vue&type=template&id=c1e95d36& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/ResponsiveNavLink.vue?vue&type=template&id=c1e95d36&");


/***/ }),

/***/ "./resources/js/Jetstream/SecondaryButton.vue?vue&type=template&id=8dd9837c&":
/*!***********************************************************************************!*\
  !*** ./resources/js/Jetstream/SecondaryButton.vue?vue&type=template&id=8dd9837c& ***!
  \***********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_SecondaryButton_vue_vue_type_template_id_8dd9837c___WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   "staticRenderFns": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_SecondaryButton_vue_vue_type_template_id_8dd9837c___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_SecondaryButton_vue_vue_type_template_id_8dd9837c___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./SecondaryButton.vue?vue&type=template&id=8dd9837c& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/SecondaryButton.vue?vue&type=template&id=8dd9837c&");


/***/ }),

/***/ "./resources/js/Jetstream/SectionBorder.vue?vue&type=template&id=2661c926&":
/*!*********************************************************************************!*\
  !*** ./resources/js/Jetstream/SectionBorder.vue?vue&type=template&id=2661c926& ***!
  \*********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_SectionBorder_vue_vue_type_template_id_2661c926___WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   "staticRenderFns": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_SectionBorder_vue_vue_type_template_id_2661c926___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_SectionBorder_vue_vue_type_template_id_2661c926___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./SectionBorder.vue?vue&type=template&id=2661c926& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/SectionBorder.vue?vue&type=template&id=2661c926&");


/***/ }),

/***/ "./resources/js/Jetstream/SidebarLink.vue?vue&type=template&id=7f9d081c&":
/*!*******************************************************************************!*\
  !*** ./resources/js/Jetstream/SidebarLink.vue?vue&type=template&id=7f9d081c& ***!
  \*******************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_SidebarLink_vue_vue_type_template_id_7f9d081c___WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   "staticRenderFns": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_SidebarLink_vue_vue_type_template_id_7f9d081c___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_SidebarLink_vue_vue_type_template_id_7f9d081c___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./SidebarLink.vue?vue&type=template&id=7f9d081c& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/SidebarLink.vue?vue&type=template&id=7f9d081c&");


/***/ }),

/***/ "./resources/js/Layouts/AppLayout.vue?vue&type=template&id=5663af57&":
/*!***************************************************************************!*\
  !*** ./resources/js/Layouts/AppLayout.vue?vue&type=template&id=5663af57& ***!
  \***************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_AppLayout_vue_vue_type_template_id_5663af57___WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   "staticRenderFns": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_AppLayout_vue_vue_type_template_id_5663af57___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_AppLayout_vue_vue_type_template_id_5663af57___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./AppLayout.vue?vue&type=template&id=5663af57& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Layouts/AppLayout.vue?vue&type=template&id=5663af57&");


/***/ }),

/***/ "./resources/js/Pages/Admin/Rank/IndexRank.vue?vue&type=template&id=b72e4738&":
/*!************************************************************************************!*\
  !*** ./resources/js/Pages/Admin/Rank/IndexRank.vue?vue&type=template&id=b72e4738& ***!
  \************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_IndexRank_vue_vue_type_template_id_b72e4738___WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   "staticRenderFns": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_IndexRank_vue_vue_type_template_id_b72e4738___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_IndexRank_vue_vue_type_template_id_b72e4738___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./IndexRank.vue?vue&type=template&id=b72e4738& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Pages/Admin/Rank/IndexRank.vue?vue&type=template&id=b72e4738&");


/***/ }),

/***/ "./resources/js/Shared/NotificationDropdown.vue?vue&type=template&id=3eac6b78&":
/*!*************************************************************************************!*\
  !*** ./resources/js/Shared/NotificationDropdown.vue?vue&type=template&id=3eac6b78& ***!
  \*************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_NotificationDropdown_vue_vue_type_template_id_3eac6b78___WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   "staticRenderFns": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_NotificationDropdown_vue_vue_type_template_id_3eac6b78___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_NotificationDropdown_vue_vue_type_template_id_3eac6b78___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./NotificationDropdown.vue?vue&type=template&id=3eac6b78& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Shared/NotificationDropdown.vue?vue&type=template&id=3eac6b78&");


/***/ }),

/***/ "./resources/js/Shared/Search.vue?vue&type=template&id=2d3cfc04&scoped=true&":
/*!***********************************************************************************!*\
  !*** ./resources/js/Shared/Search.vue?vue&type=template&id=2d3cfc04&scoped=true& ***!
  \***********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Search_vue_vue_type_template_id_2d3cfc04_scoped_true___WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   "staticRenderFns": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Search_vue_vue_type_template_id_2d3cfc04_scoped_true___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Search_vue_vue_type_template_id_2d3cfc04_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./Search.vue?vue&type=template&id=2d3cfc04&scoped=true& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Shared/Search.vue?vue&type=template&id=2d3cfc04&scoped=true&");


/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/ColorThemeToggle.vue?vue&type=template&id=5f445c65&":
/*!****************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/ColorThemeToggle.vue?vue&type=template&id=5f445c65& ***!
  \****************************************************************************************************************************************************************************************************************************/
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
  return _c("div", [
    _c(
      "button",
      { on: { click: _vm.toggleTheme } },
      [
        _vm.colorMode === "dark"
          ? _c("icon", {
              directives: [{ name: "tippy", rawName: "v-tippy" }],
              staticClass: "w-6 h-6 text-gray-400 focus:outline-none",
              attrs: { title: "Use Light Theme", name: "moon-full" }
            })
          : _c("icon", {
              directives: [{ name: "tippy", rawName: "v-tippy" }],
              staticClass: "w-6 h-6 text-gray-400 focus:outline-none",
              attrs: { title: "Use Dark Theme", name: "moon-outline" }
            })
      ],
      1
    )
  ])
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/Icon.vue?vue&type=template&id=7b50d278&":
/*!****************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/Icon.vue?vue&type=template&id=7b50d278& ***!
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
  return _vm.name === "close"
    ? _c(
        "svg",
        {
          staticClass: "w-6 h-6 text-gray-300",
          attrs: {
            xmlns: "http://www.w3.org/2000/svg",
            fill: "none",
            viewBox: "0 0 24 24",
            stroke: "currentColor"
          }
        },
        [
          _c("path", {
            attrs: {
              "stroke-linecap": "round",
              "stroke-linejoin": "round",
              "stroke-width": "2",
              d: "M6 18L18 6M6 6l12 12"
            }
          })
        ]
      )
    : _vm.name === "server"
    ? _c(
        "svg",
        {
          staticClass: "w-5 w-5",
          attrs: {
            xmlns: "http://www.w3.org/2000/svg",
            fill: "none",
            viewBox: "0 0 24 24",
            stroke: "currentColor"
          }
        },
        [
          _c("path", {
            attrs: {
              "stroke-linecap": "round",
              "stroke-linejoin": "round",
              "stroke-width": "2",
              d:
                "M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"
            }
          })
        ]
      )
    : _vm.name === "degree-hat"
    ? _c(
        "svg",
        {
          staticClass: "w-5 h-5",
          attrs: {
            xmlns: "http://www.w3.org/2000/svg",
            fill: "none",
            viewBox: "0 0 24 24",
            stroke: "currentColor"
          }
        },
        [
          _c("path", { attrs: { fill: "#fff", d: "M12 14l9-5-9-5-9 5 9 5z" } }),
          _vm._v(" "),
          _c("path", {
            attrs: {
              fill: "#fff",
              d:
                "M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"
            }
          }),
          _vm._v(" "),
          _c("path", {
            attrs: {
              "stroke-linecap": "round",
              "stroke-linejoin": "round",
              "stroke-width": "2",
              d:
                "M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"
            }
          })
        ]
      )
    : _vm.name === "cross-circle"
    ? _c(
        "svg",
        {
          staticClass: "w-5 h-5",
          attrs: {
            xmlns: "http://www.w3.org/2000/svg",
            fill: "none",
            viewBox: "0 0 24 24",
            stroke: "currentColor"
          }
        },
        [
          _c("path", {
            attrs: {
              "stroke-linecap": "round",
              "stroke-linejoin": "round",
              "stroke-width": "2",
              d:
                "M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
            }
          })
        ]
      )
    : _vm.name === "check-circle"
    ? _c(
        "svg",
        {
          staticClass: "w-5 h-5",
          attrs: {
            xmlns: "http://www.w3.org/2000/svg",
            fill: "none",
            viewBox: "0 0 24 24",
            stroke: "currentColor"
          }
        },
        [
          _c("path", {
            attrs: {
              "stroke-linecap": "round",
              "stroke-linejoin": "round",
              "stroke-width": "2",
              d: "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
            }
          })
        ]
      )
    : _vm.name === "newspaper"
    ? _c(
        "svg",
        {
          staticClass: "w-5 h-5",
          attrs: {
            xmlns: "http://www.w3.org/2000/svg",
            fill: "none",
            viewBox: "0 0 24 24",
            stroke: "currentColor"
          }
        },
        [
          _c("path", {
            attrs: {
              "stroke-linecap": "round",
              "stroke-linejoin": "round",
              "stroke-width": "2",
              d:
                "M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"
            }
          })
        ]
      )
    : _vm.name === "shield-check"
    ? _c(
        "svg",
        {
          staticClass: "w-5 h-5",
          attrs: {
            xmlns: "http://www.w3.org/2000/svg",
            fill: "none",
            viewBox: "0 0 24 24",
            stroke: "currentColor"
          }
        },
        [
          _c("path", {
            attrs: {
              "stroke-linecap": "round",
              "stroke-linejoin": "round",
              "stroke-width": "2",
              d:
                "M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
            }
          })
        ]
      )
    : _vm.name === "shield-check-fill"
    ? _c(
        "svg",
        {
          attrs: {
            xmlns: "http://www.w3.org/2000/svg",
            viewBox: "0 0 20 20",
            fill: "currentColor"
          }
        },
        [
          _c("path", {
            attrs: {
              "fill-rule": "evenodd",
              d:
                "M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z",
              "clip-rule": "evenodd"
            }
          })
        ]
      )
    : _vm.name === "paper-clip"
    ? _c(
        "svg",
        {
          staticClass: "w-5 h-5",
          attrs: {
            fill: "none",
            stroke: "currentColor",
            viewBox: "0 0 24 24",
            xmlns: "http://www.w3.org/2000/svg"
          }
        },
        [
          _c("path", {
            attrs: {
              "stroke-linecap": "round",
              "stroke-linejoin": "round",
              "stroke-width": "2",
              d:
                "M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"
            }
          })
        ]
      )
    : _vm.name === "cog"
    ? _c(
        "svg",
        {
          staticClass: "w-5 h-5",
          attrs: {
            xmlns: "http://www.w3.org/2000/svg",
            fill: "none",
            viewBox: "0 0 24 24",
            stroke: "currentColor"
          }
        },
        [
          _c("path", {
            attrs: {
              "stroke-linecap": "round",
              "stroke-linejoin": "round",
              "stroke-width": "2",
              d:
                "M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
            }
          }),
          _vm._v(" "),
          _c("path", {
            attrs: {
              "stroke-linecap": "round",
              "stroke-linejoin": "round",
              "stroke-width": "2",
              d: "M15 12a3 3 0 11-6 0 3 3 0 016 0z"
            }
          })
        ]
      )
    : _vm.name === "trash"
    ? _c(
        "svg",
        {
          attrs: {
            xmlns: "http://www.w3.org/2000/svg",
            fill: "none",
            viewBox: "0 0 24 24",
            stroke: "currentColor"
          }
        },
        [
          _c("path", {
            attrs: {
              "stroke-linecap": "round",
              "stroke-linejoin": "round",
              "stroke-width": "2",
              d:
                "M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
            }
          })
        ]
      )
    : _vm.name === "heart-fill"
    ? _c(
        "svg",
        {
          attrs: {
            fill: "currentColor",
            viewBox: "0 0 20 20",
            xmlns: "http://www.w3.org/2000/svg"
          }
        },
        [
          _c("path", {
            attrs: {
              "fill-rule": "evenodd",
              d:
                "M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z",
              "clip-rule": "evenodd"
            }
          })
        ]
      )
    : _vm.name === "heart-hollow"
    ? _c(
        "svg",
        {
          attrs: {
            xmlns: "http://www.w3.org/2000/svg",
            fill: "none",
            viewBox: "0 0 24 24",
            stroke: "currentColor"
          }
        },
        [
          _c("path", {
            attrs: {
              "stroke-linecap": "round",
              "stroke-linejoin": "round",
              "stroke-width": "2",
              d:
                "M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
            }
          })
        ]
      )
    : _vm.name === "rating-0"
    ? _c("svg", { attrs: { viewBox: "0 0 24 24", fill: "none" } }, [
        _c("circle", {
          attrs: { cx: "12", cy: "12", r: "12", fill: "#1F1F22" }
        }),
        _vm._v(" "),
        _c("path", {
          attrs: {
            "fill-rule": "evenodd",
            "clip-rule": "evenodd",
            d:
              "M16.685 17.467a7.2 7.2 0 1 0-9.371 0l-1.563 1.822A9.58 9.58 0 0 1 2.4 12 9.6 9.6 0 0 1 12 2.4a9.6 9.6 0 0 1 9.6 9.6 9.58 9.58 0 0 1-3.352 7.29l-1.563-1.823z",
            fill: "#CDCDCD",
            "fill-opacity": ".1"
          }
        })
      ])
    : _vm.name === "rating-1"
    ? _c("svg", { attrs: { viewBox: "0 0 24 24", fill: "none" } }, [
        _c("circle", {
          attrs: { cx: "12", cy: "12", r: "12", fill: "#1F1F22" }
        }),
        _vm._v(" "),
        _c("path", {
          attrs: {
            "fill-rule": "evenodd",
            "clip-rule": "evenodd",
            d:
              "M16.685 17.467a7.2 7.2 0 10-9.371 0l-1.563 1.822A9.58 9.58 0 012.4 12 9.6 9.6 0 0112 2.4a9.6 9.6 0 019.6 9.6 9.58 9.58 0 01-3.352 7.29l-1.563-1.823z",
            fill: "#CDCDCD",
            "fill-opacity": ".1"
          }
        }),
        _vm._v(" "),
        _c("path", {
          attrs: {
            "fill-rule": "evenodd",
            "clip-rule": "evenodd",
            d:
              "M5.894 15.816L3.858 17.09a9.656 9.656 0 001.894 2.2l1.562-1.822a7.206 7.206 0 01-1.42-1.65z",
            fill: "#EEE"
          }
        }),
        _vm._v(" "),
        _c("path", {
          attrs: {
            d:
              "M11.765 10.233l-1.487.824v-1.034L12 8.948h.991V14.4h-1.226v-4.167z",
            fill: "#EEE"
          }
        })
      ])
    : _vm.name === "rating-2"
    ? _c("svg", { attrs: { viewBox: "0 0 24 24", fill: "none" } }, [
        _c("circle", {
          attrs: { cx: "12", cy: "12", r: "12", fill: "#1F1F22" }
        }),
        _vm._v(" "),
        _c("path", {
          attrs: {
            "fill-rule": "evenodd",
            "clip-rule": "evenodd",
            d:
              "M16.685 17.467a7.2 7.2 0 10-9.371 0l-1.563 1.822A9.58 9.58 0 012.4 12 9.6 9.6 0 0112 2.4a9.6 9.6 0 019.6 9.6 9.58 9.58 0 01-3.352 7.29l-1.563-1.823z",
            fill: "#CDCDCD",
            "fill-opacity": ".1"
          }
        }),
        _vm._v(" "),
        _c("path", {
          attrs: {
            "fill-rule": "evenodd",
            "clip-rule": "evenodd",
            d:
              "M5.257 14.53l-2.249.842a9.613 9.613 0 002.743 3.917l1.563-1.822a7.206 7.206 0 01-2.057-2.938z",
            fill: "#1CE400"
          }
        }),
        _vm._v(" "),
        _c("path", {
          attrs: {
            d:
              "M10.05 13.157c0-.303.084-.566.252-.79a1.6 1.6 0 01.655-.512 8.17 8.17 0 01.748-.286 2.78 2.78 0 00.663-.302c.157-.107.235-.233.235-.378v-.698c0-.173-.07-.288-.21-.344-.15-.062-.386-.092-.705-.092-.387 0-.896.07-1.529.21V9.04a8.523 8.523 0 011.756-.177c.66 0 1.15.101 1.47.303.324.201.487.537.487 1.008v.756c0 .285-.087.534-.26.747a1.567 1.567 0 01-.656.47c-.252.107-.51.202-.773.286a2.65 2.65 0 00-.68.336c-.162.123-.244.27-.244.437v.277h2.621v.916h-3.83v-1.243z",
            fill: "#1CE400"
          }
        })
      ])
    : _vm.name === "rating-3"
    ? _c("svg", { attrs: { viewBox: "0 0 24 24", fill: "none" } }, [
        _c("circle", {
          attrs: { cx: "12", cy: "12", r: "12", fill: "#1F1F22" }
        }),
        _vm._v(" "),
        _c("path", {
          attrs: {
            "fill-rule": "evenodd",
            "clip-rule": "evenodd",
            d:
              "M16.686 17.467a7.2 7.2 0 10-9.371 0l-1.563 1.822A9.58 9.58 0 012.4 12 9.6 9.6 0 0112 2.4a9.6 9.6 0 019.6 9.6 9.58 9.58 0 01-3.352 7.29l-1.562-1.823z",
            fill: "#CDCDCD",
            "fill-opacity": ".1"
          }
        }),
        _vm._v(" "),
        _c("path", {
          attrs: {
            "fill-rule": "evenodd",
            "clip-rule": "evenodd",
            d:
              "M2.4 12a9.58 9.58 0 003.352 7.29l1.562-1.823A7.184 7.184 0 014.801 12H2.4z",
            fill: "#1CE400"
          }
        }),
        _vm._v(" "),
        _c("path", {
          attrs: {
            d:
              "M11.79 14.484c-.47 0-1.08-.042-1.831-.126v-.975l.269.05c.106.023.165.037.176.043l.286.05c.067.011.21.028.428.05.168.017.339.026.513.026.324 0 .548-.04.672-.118.128-.078.193-.227.193-.445v-.63c0-.263-.283-.395-.849-.395h-.99v-.84h.99c.437 0 .656-.16.656-.479v-.529a.453.453 0 00-.068-.269c-.044-.067-.126-.114-.243-.142a2.239 2.239 0 00-.504-.042c-.32 0-.812.033-1.479.1V8.94c.762-.05 1.3-.076 1.613-.076.683 0 1.176.079 1.479.235.308.157.462.434.462.832v.899a.62.62 0 01-.152.42.703.703 0 01-.37.227c.494.173.74.445.74.814v.89c0 .466-.16.799-.479 1-.319.202-.823.303-1.512.303z",
            fill: "#1CE400"
          }
        })
      ])
    : _vm.name === "rating-4"
    ? _c("svg", { attrs: { viewBox: "0 0 24 24", fill: "none" } }, [
        _c("circle", {
          attrs: { cx: "12", cy: "12", r: "12", fill: "#1F1F22" }
        }),
        _vm._v(" "),
        _c("path", {
          attrs: {
            "fill-rule": "evenodd",
            "clip-rule": "evenodd",
            d:
              "M16.686 17.467a7.2 7.2 0 10-9.371 0l-1.563 1.822A9.58 9.58 0 012.4 12 9.6 9.6 0 0112 2.4a9.6 9.6 0 019.6 9.6 9.58 9.58 0 01-3.352 7.29l-1.562-1.823z",
            fill: "#CDCDCD",
            "fill-opacity": ".1"
          }
        }),
        _vm._v(" "),
        _c("path", {
          attrs: {
            "fill-rule": "evenodd",
            "clip-rule": "evenodd",
            d:
              "M6.91 6.91L5.211 5.211A9.57 9.57 0 002.4 12a9.58 9.58 0 003.352 7.289l1.562-1.822A7.184 7.184 0 014.801 12c0-1.988.805-3.788 2.108-5.09z",
            fill: "#FFC800"
          }
        }),
        _vm._v(" "),
        _c("path", {
          attrs: {
            d:
              "M12.303 13.3h-2.52v-.967l2.243-3.385h1.386v3.47H14v.881h-.588v1.1h-1.109v-1.1zm0-.883v-2.31l-1.47 2.31h1.47z",
            fill: "#FFC800"
          }
        })
      ])
    : _vm.name === "rating-5"
    ? _c("svg", { attrs: { viewBox: "0 0 24 24", fill: "none" } }, [
        _c("circle", {
          attrs: { cx: "12", cy: "12", r: "12", fill: "#1F1F22" }
        }),
        _vm._v(" "),
        _c("path", {
          attrs: {
            "fill-rule": "evenodd",
            "clip-rule": "evenodd",
            d:
              "M16.686 17.467a7.2 7.2 0 10-9.371 0l-1.563 1.822A9.58 9.58 0 012.4 12 9.6 9.6 0 0112 2.4a9.6 9.6 0 019.6 9.6 9.58 9.58 0 01-3.352 7.29l-1.562-1.823z",
            fill: "#CDCDCD",
            "fill-opacity": ".1"
          }
        }),
        _vm._v(" "),
        _c("path", {
          attrs: {
            "fill-rule": "evenodd",
            "clip-rule": "evenodd",
            d:
              "M12 2.4A9.6 9.6 0 002.4 12a9.58 9.58 0 003.352 7.29l1.562-1.823A7.2 7.2 0 0112 4.8V2.4z",
            fill: "#FFC800"
          }
        }),
        _vm._v(" "),
        _c("path", {
          attrs: {
            d:
              "M11.815 14.484c-.386 0-.966-.031-1.739-.093v-1.016c.695.129 1.218.193 1.571.193.308 0 .532-.033.672-.1a.357.357 0 00.21-.337v-.814c0-.152-.05-.258-.151-.32-.101-.067-.266-.1-.496-.1h-1.68V8.948h3.444v.941H11.43v1.109h.856c.325 0 .642.061.95.185a.909.909 0 01.554.865v1.142c0 .219-.042.415-.126.588-.084.168-.19.297-.32.387a1.315 1.315 0 01-.453.201c-.185.05-.364.084-.537.101a10.05 10.05 0 01-.538.017z",
            fill: "#FFC800"
          }
        })
      ])
    : _vm.name === "rating-6"
    ? _c("svg", { attrs: { viewBox: "0 0 24 24", fill: "none" } }, [
        _c("circle", {
          attrs: { cx: "12", cy: "12", r: "12", fill: "#1F1F22" }
        }),
        _vm._v(" "),
        _c("path", {
          attrs: {
            "fill-rule": "evenodd",
            "clip-rule": "evenodd",
            d:
              "M16.686 17.467a7.2 7.2 0 10-9.371 0l-1.563 1.822A9.58 9.58 0 012.4 12 9.6 9.6 0 0112 2.4a9.6 9.6 0 019.6 9.6 9.58 9.58 0 01-3.352 7.29l-1.562-1.823z",
            fill: "#CDCDCD",
            "fill-opacity": ".1"
          }
        }),
        _vm._v(" "),
        _c("path", {
          attrs: {
            "fill-rule": "evenodd",
            "clip-rule": "evenodd",
            d:
              "M15.816 5.895a7.2 7.2 0 00-8.502 11.572l-1.562 1.822A9.58 9.58 0 012.4 12 9.6 9.6 0 0112 2.4c1.87 0 3.613.535 5.089 1.458l-1.273 2.037z",
            fill: "#FFC800"
          }
        }),
        _vm._v(" "),
        _c("path", {
          attrs: {
            d:
              "M11.992 14.484a5.99 5.99 0 01-.613-.025 2.48 2.48 0 01-.496-.11 1.24 1.24 0 01-.453-.243 1.184 1.184 0 01-.286-.437 1.89 1.89 0 01-.118-.689v-2.537c0-.268.045-.506.135-.714.095-.212.215-.375.361-.487.123-.095.288-.173.496-.235a2.71 2.71 0 01.604-.126c.213-.011.406-.017.58-.017.24 0 .745.028 1.512.084v.9c-.756-.09-1.296-.135-1.621-.135-.269 0-.46.014-.571.042-.112.028-.188.084-.227.168-.034.078-.05.22-.05.428v.647h.898c.303 0 .521.005.655.017.135.005.286.03.454.075.18.045.31.11.395.193.09.079.168.2.235.362.062.168.092.366.092.596v.74c0 .257-.039.484-.117.68-.079.19-.18.338-.303.445-.112.1-.26.182-.445.243a2.04 2.04 0 01-.537.11 5.58 5.58 0 01-.58.025zm.017-.815c.246 0 .417-.014.512-.042.101-.028.165-.081.193-.16a1.51 1.51 0 00.042-.428v-.79c0-.134-.016-.23-.05-.285-.034-.062-.104-.104-.21-.126a2.557 2.557 0 00-.496-.034h-.756v1.243c0 .19.014.328.042.412.034.084.101.14.202.168.106.028.28.042.52.042z",
            fill: "#FFC800"
          }
        })
      ])
    : _vm.name === "rating-7"
    ? _c("svg", { attrs: { viewBox: "0 0 24 24", fill: "none" } }, [
        _c("circle", {
          attrs: { cx: "12", cy: "12", r: "12", fill: "#1F1F22" }
        }),
        _vm._v(" "),
        _c("path", {
          attrs: {
            "fill-rule": "evenodd",
            "clip-rule": "evenodd",
            d:
              "M16.686 17.467a7.2 7.2 0 10-9.371 0l-1.563 1.822A9.58 9.58 0 012.4 12 9.6 9.6 0 0112 2.4a9.6 9.6 0 019.6 9.6 9.58 9.58 0 01-3.352 7.29l-1.562-1.823z",
            fill: "#CDCDCD",
            "fill-opacity": ".1"
          }
        }),
        _vm._v(" "),
        _c("path", {
          attrs: {
            "fill-rule": "evenodd",
            "clip-rule": "evenodd",
            d:
              "M17.934 7.92a7.2 7.2 0 10-10.62 9.546L5.752 19.29A9.58 9.58 0 012.4 12a9.6 9.6 0 0117.512-5.44l-1.978 1.36z",
            fill: "#FFC800"
          }
        }),
        _vm._v(" "),
        _c("path", {
          attrs: {
            d: "M12.546 9.906H9.9v-.958h4v.84L11.807 14.4h-1.36l2.1-4.494z",
            fill: "#FFC800"
          }
        })
      ])
    : _vm.name === "rating-8"
    ? _c("svg", { attrs: { viewBox: "0 0 24 24", fill: "none" } }, [
        _c("circle", {
          attrs: { cx: "12", cy: "12", r: "12", fill: "#1F1F22" }
        }),
        _vm._v(" "),
        _c("path", {
          attrs: {
            "fill-rule": "evenodd",
            "clip-rule": "evenodd",
            d:
              "M16.686 17.467a7.2 7.2 0 10-9.371 0l-1.563 1.822A9.58 9.58 0 012.4 12 9.6 9.6 0 0112 2.4a9.6 9.6 0 019.6 9.6 9.58 9.58 0 01-3.352 7.29l-1.562-1.823z",
            fill: "#CDCDCD",
            "fill-opacity": ".1"
          }
        }),
        _vm._v(" "),
        _c("path", {
          attrs: {
            "fill-rule": "evenodd",
            "clip-rule": "evenodd",
            d:
              "M19.2 12h2.4A9.6 9.6 0 0012 2.4 9.6 9.6 0 002.4 12a9.58 9.58 0 003.352 7.29l1.562-1.823A7.2 7.2 0 1119.2 12z",
            fill: "#FF6309"
          }
        }),
        _vm._v(" "),
        _c("path", {
          attrs: {
            d:
              "M12 14.484c-.723 0-1.252-.09-1.588-.269-.33-.18-.496-.49-.496-.932v-.941c0-.18.09-.347.269-.504.179-.157.392-.263.638-.32v-.033a.879.879 0 01-.504-.235.614.614 0 01-.218-.462v-.781c0-.392.143-.68.428-.866.291-.184.781-.277 1.47-.277s1.176.093 1.462.277c.291.185.437.474.437.866v.78a.614.614 0 01-.219.463.879.879 0 01-.504.235v.034c.247.056.46.162.639.319s.268.325.268.504v.94c0 .454-.17.768-.512.941-.342.174-.865.26-1.57.26zm0-3.293c.246 0 .416-.034.512-.1.1-.074.15-.188.15-.345v-.63c0-.163-.05-.277-.15-.345-.096-.072-.266-.109-.513-.109-.246 0-.42.037-.52.11-.096.067-.143.181-.143.344v.63a.41.41 0 00.142.336c.09.073.264.11.521.11zm0 2.495c.24 0 .414-.014.52-.042.112-.028.185-.076.218-.143.04-.067.06-.174.06-.32v-.738c0-.163-.048-.283-.144-.362-.095-.072-.31-.109-.646-.109-.32 0-.535.037-.647.11-.107.067-.16.187-.16.36v.74c0 .145.017.252.05.32.04.066.113.114.219.142.112.028.288.042.53.042z",
            fill: "#FF6309"
          }
        })
      ])
    : _vm.name === "rating-9"
    ? _c("svg", { attrs: { viewBox: "0 0 24 24", fill: "none" } }, [
        _c("circle", {
          attrs: { cx: "12", cy: "12", r: "12", fill: "#1F1F22" }
        }),
        _vm._v(" "),
        _c("path", {
          attrs: {
            "fill-rule": "evenodd",
            "clip-rule": "evenodd",
            d:
              "M16.686 17.467a7.2 7.2 0 10-9.371 0l-1.563 1.822A9.58 9.58 0 012.4 12 9.6 9.6 0 0112 2.4a9.6 9.6 0 019.6 9.6 9.58 9.58 0 01-3.352 7.29l-1.562-1.823z",
            fill: "#CDCDCD",
            "fill-opacity": ".1"
          }
        }),
        _vm._v(" "),
        _c("path", {
          attrs: {
            "fill-rule": "evenodd",
            "clip-rule": "evenodd",
            d:
              "M18.517 15.066a7.2 7.2 0 10-11.202 2.4L5.751 19.29A9.58 9.58 0 012.4 12 9.6 9.6 0 0112 2.4a9.6 9.6 0 019.6 9.6 9.563 9.563 0 01-.91 4.089l-2.173-1.023z",
            fill: "#FF6309"
          }
        }),
        _vm._v(" "),
        _c("path", {
          attrs: {
            d:
              "M11.84 14.484c-.48 0-.999-.028-1.553-.084v-.874c.717.079 1.229.118 1.537.118.286 0 .493-.02.622-.059.128-.04.212-.112.252-.218.044-.107.067-.275.067-.504v-.513h-.907c-.303 0-.521-.003-.656-.008a2.63 2.63 0 01-.453-.084.898.898 0 01-.395-.193 1.052 1.052 0 01-.235-.37 1.706 1.706 0 01-.093-.588v-.74c0-.257.04-.48.118-.671.078-.196.18-.35.302-.462.112-.095.258-.174.437-.235.185-.062.367-.101.546-.118.213-.011.406-.017.58-.017.263 0 .47.009.621.025.157.012.322.045.496.101a1.129 1.129 0 01.74.689 1.9 1.9 0 01.117.689v2.537c0 .565-.171.971-.513 1.218-.336.24-.879.36-1.63.36zm.925-2.949V10.26c0-.19-.017-.322-.05-.395-.029-.073-.093-.12-.194-.143a2.73 2.73 0 00-.529-.034 2.11 2.11 0 00-.504.042.26.26 0 00-.193.152c-.034.072-.05.198-.05.378v.831c0 .135.016.233.05.294.033.056.1.095.201.118a2.7 2.7 0 00.504.033h.765z",
            fill: "#FF6309"
          }
        })
      ])
    : _vm.name === "rating-10"
    ? _c("svg", { attrs: { viewBox: "0 0 24 24", fill: "none" } }, [
        _c("circle", {
          attrs: { cx: "12", cy: "12", r: "12", fill: "#1F1F22" }
        }),
        _vm._v(" "),
        _c("path", {
          attrs: {
            "fill-rule": "evenodd",
            "clip-rule": "evenodd",
            d:
              "M16.686 17.467a7.2 7.2 0 10-9.371 0l-1.563 1.822A9.58 9.58 0 012.4 12 9.6 9.6 0 0112 2.4a9.6 9.6 0 019.6 9.6 9.58 9.58 0 01-3.352 7.29l-1.562-1.823z",
            fill: "#CDCDCD",
            "fill-opacity": ".1"
          }
        }),
        _vm._v(" "),
        _c("path", {
          attrs: {
            "fill-rule": "evenodd",
            "clip-rule": "evenodd",
            d:
              "M16.686 17.467a7.2 7.2 0 10-9.371 0l-1.563 1.822A9.58 9.58 0 012.4 12 9.6 9.6 0 0112 2.4a9.6 9.6 0 019.6 9.6 9.58 9.58 0 01-3.352 7.29l-1.562-1.823z",
            fill: "#FE1F00"
          }
        }),
        _vm._v(" "),
        _c("path", {
          attrs: {
            d:
              "M9.233 10.233l-1.487.824v-1.034l1.722-1.075h.991V14.4H9.233v-4.167zm4.595 4.251c-.246 0-.448-.009-.604-.025a3.295 3.295 0 01-.513-.101 1.237 1.237 0 01-.462-.235 1.202 1.202 0 01-.294-.454 1.7 1.7 0 01-.126-.689v-2.612c0-.258.04-.485.118-.68a1.23 1.23 0 01.302-.463c.107-.095.252-.17.437-.226a2.45 2.45 0 01.554-.118c.213-.011.41-.017.588-.017.252 0 .454.009.605.025a2.4 2.4 0 01.504.101c.202.062.361.143.479.244.118.1.218.246.302.437.084.19.126.422.126.697v2.612c0 .258-.042.485-.126.68a1.15 1.15 0 01-.302.454 1.32 1.32 0 01-.462.235c-.19.062-.372.098-.546.11a5.58 5.58 0 01-.58.025zm.017-.79c.235 0 .403-.014.504-.042a.306.306 0 00.202-.176c.033-.084.05-.221.05-.412v-2.78c0-.19-.017-.328-.05-.412a.282.282 0 00-.202-.168c-.1-.033-.269-.05-.504-.05-.24 0-.414.017-.52.05a.282.282 0 00-.202.168c-.034.084-.05.221-.05.412v2.78c0 .19.016.328.05.412.033.084.1.143.201.176.107.028.28.042.521.042z",
            fill: "#FE1F00"
          }
        })
      ])
    : _vm.name === "comment"
    ? _c(
        "svg",
        {
          attrs: {
            xmlns: "http://www.w3.org/2000/svg",
            fill: "none",
            viewBox: "0 0 24 24",
            stroke: "currentColor"
          }
        },
        [
          _c("path", {
            attrs: {
              "stroke-linecap": "round",
              "stroke-linejoin": "round",
              "stroke-width": "2",
              d:
                "M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"
            }
          })
        ]
      )
    : _vm.name === "verified-check-fill"
    ? _c(
        "svg",
        {
          attrs: {
            xmlns: "http://www.w3.org/2000/svg",
            viewBox: "0 0 20 20",
            fill: "currentColor"
          }
        },
        [
          _c("path", {
            attrs: {
              "fill-rule": "evenodd",
              d:
                "M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z",
              "clip-rule": "evenodd"
            }
          })
        ]
      )
    : _vm.name === "chart-pie"
    ? _c(
        "svg",
        {
          attrs: {
            xmlns: "http://www.w3.org/2000/svg",
            fill: "none",
            viewBox: "0 0 24 24",
            stroke: "currentColor"
          }
        },
        [
          _c("path", {
            attrs: {
              "stroke-linecap": "round",
              "stroke-linejoin": "round",
              "stroke-width": "2",
              d: "M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"
            }
          }),
          _vm._v(" "),
          _c("path", {
            attrs: {
              "stroke-linecap": "round",
              "stroke-linejoin": "round",
              "stroke-width": "2",
              d: "M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"
            }
          })
        ]
      )
    : _vm.name === "collection"
    ? _c(
        "svg",
        {
          attrs: {
            xmlns: "http://www.w3.org/2000/svg",
            fill: "none",
            viewBox: "0 0 24 24",
            stroke: "currentColor"
          }
        },
        [
          _c("path", {
            attrs: {
              "stroke-linecap": "round",
              "stroke-linejoin": "round",
              "stroke-width": "2",
              d:
                "M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
            }
          })
        ]
      )
    : _vm.name === "users"
    ? _c(
        "svg",
        {
          attrs: {
            xmlns: "http://www.w3.org/2000/svg",
            fill: "none",
            viewBox: "0 0 24 24",
            stroke: "currentColor"
          }
        },
        [
          _c("path", {
            attrs: {
              "stroke-linecap": "round",
              "stroke-linejoin": "round",
              "stroke-width": "2",
              d:
                "M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
            }
          })
        ]
      )
    : _vm.name === "ban"
    ? _c(
        "svg",
        {
          attrs: {
            xmlns: "http://www.w3.org/2000/svg",
            fill: "none",
            viewBox: "0 0 24 24",
            stroke: "currentColor"
          }
        },
        [
          _c("path", {
            attrs: {
              "stroke-linecap": "round",
              "stroke-linejoin": "round",
              "stroke-width": "2",
              d:
                "M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"
            }
          })
        ]
      )
    : _vm.name === "volume-off-fill"
    ? _c(
        "svg",
        {
          attrs: {
            xmlns: "http://www.w3.org/2000/svg",
            viewBox: "0 0 20 20",
            fill: "currentColor"
          }
        },
        [
          _c("path", {
            attrs: {
              "fill-rule": "evenodd",
              d:
                "M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.707.707L4.586 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.586l3.707-3.707a1 1 0 011.09-.217zM12.293 7.293a1 1 0 011.414 0L15 8.586l1.293-1.293a1 1 0 111.414 1.414L16.414 10l1.293 1.293a1 1 0 01-1.414 1.414L15 11.414l-1.293 1.293a1 1 0 01-1.414-1.414L13.586 10l-1.293-1.293a1 1 0 010-1.414z",
              "clip-rule": "evenodd"
            }
          })
        ]
      )
    : _vm.name === "photograph"
    ? _c(
        "svg",
        {
          attrs: {
            fill: "none",
            stroke: "currentColor",
            viewBox: "0 0 24 24",
            xmlns: "http://www.w3.org/2000/svg"
          }
        },
        [
          _c("path", {
            attrs: {
              "stroke-linecap": "round",
              "stroke-linejoin": "round",
              "stroke-width": "2",
              d:
                "M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
            }
          })
        ]
      )
    : _vm.name === "twitter"
    ? _c("svg", { attrs: { viewBox: "0 0 512 512" } }, [
        _c("path", {
          attrs: {
            d:
              "M512 97.248c-19.04 8.352-39.328 13.888-60.48 16.576 21.76-12.992 38.368-33.408 46.176-58.016-20.288 12.096-42.688 20.64-66.56 25.408C411.872 60.704 384.416 48 354.464 48c-58.112 0-104.896 47.168-104.896 104.992 0 8.32.704 16.32 2.432 23.936-87.264-4.256-164.48-46.08-216.352-109.792-9.056 15.712-14.368 33.696-14.368 53.056 0 36.352 18.72 68.576 46.624 87.232-16.864-.32-33.408-5.216-47.424-12.928v1.152c0 51.008 36.384 93.376 84.096 103.136-8.544 2.336-17.856 3.456-27.52 3.456-6.72 0-13.504-.384-19.872-1.792 13.6 41.568 52.192 72.128 98.08 73.12-35.712 27.936-81.056 44.768-130.144 44.768-8.608 0-16.864-.384-25.12-1.44C46.496 446.88 101.6 464 161.024 464c193.152 0 298.752-160 298.752-298.688 0-4.64-.16-9.12-.384-13.568 20.832-14.784 38.336-33.248 52.608-54.496z"
          }
        })
      ])
    : _vm.name === "github"
    ? _c(
        "svg",
        {
          attrs: { viewBox: "0 0 24 24", xmlns: "http://www.w3.org/2000/svg" }
        },
        [
          _c("path", {
            attrs: {
              d:
                "M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"
            }
          })
        ]
      )
    : _vm.name === "facebook"
    ? _c(
        "svg",
        {
          attrs: { xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 24 24" }
        },
        [
          _c("path", {
            attrs: {
              d:
                "M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"
            }
          })
        ]
      )
    : _vm.name === "google"
    ? _c(
        "svg",
        {
          attrs: { xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 24 24" }
        },
        [
          _c("path", {
            attrs: {
              d:
                "M12.545 10.239v3.821h5.445c-.712 2.315-2.647 3.972-5.445 3.972a6.033 6.033 0 110-12.064c1.498 0 2.866.549 3.921 1.453l2.814-2.814A9.969 9.969 0 0012.545 2C7.021 2 2.543 6.477 2.543 12s4.478 10 10.002 10c8.396 0 10.249-7.85 9.426-11.748l-9.426-.013z"
            }
          })
        ]
      )
    : _vm.name === "spin-loader"
    ? _c(
        "svg",
        {
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
    : _vm.name === "pause"
    ? _c(
        "svg",
        {
          attrs: {
            fill: "none",
            stroke: "currentColor",
            viewBox: "0 0 24 24",
            xmlns: "http://www.w3.org/2000/svg"
          }
        },
        [
          _c("path", {
            attrs: {
              "stroke-linecap": "round",
              "stroke-linejoin": "round",
              "stroke-width": "2",
              d: "M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"
            }
          })
        ]
      )
    : _vm.name === "play"
    ? _c(
        "svg",
        {
          attrs: {
            fill: "none",
            stroke: "currentColor",
            viewBox: "0 0 24 24",
            xmlns: "http://www.w3.org/2000/svg"
          }
        },
        [
          _c("path", {
            attrs: {
              "stroke-linecap": "round",
              "stroke-linejoin": "round",
              "stroke-width": "2",
              d:
                "M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"
            }
          }),
          _vm._v(" "),
          _c("path", {
            attrs: {
              "stroke-linecap": "round",
              "stroke-linejoin": "round",
              "stroke-width": "2",
              d: "M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            }
          })
        ]
      )
    : _vm.name === "finger-print"
    ? _c(
        "svg",
        {
          attrs: {
            fill: "currentColor",
            viewBox: "0 0 20 20",
            xmlns: "http://www.w3.org/2000/svg"
          }
        },
        [
          _c("path", {
            attrs: {
              "fill-rule": "evenodd",
              d:
                "M6.625 2.655A9 9 0 0119 11a1 1 0 11-2 0 7 7 0 00-9.625-6.492 1 1 0 11-.75-1.853zM4.662 4.959A1 1 0 014.75 6.37 6.97 6.97 0 003 11a1 1 0 11-2 0 8.97 8.97 0 012.25-5.953 1 1 0 011.412-.088z",
              "clip-rule": "evenodd"
            }
          }),
          _vm._v(" "),
          _c("path", {
            attrs: {
              "fill-rule": "evenodd",
              d:
                "M5 11a5 5 0 1110 0 1 1 0 11-2 0 3 3 0 10-6 0c0 1.677-.345 3.276-.968 4.729a1 1 0 11-1.838-.789A9.964 9.964 0 005 11zm8.921 2.012a1 1 0 01.831 1.145 19.86 19.86 0 01-.545 2.436 1 1 0 11-1.92-.558c.207-.713.371-1.445.49-2.192a1 1 0 011.144-.83z",
              "clip-rule": "evenodd"
            }
          }),
          _vm._v(" "),
          _c("path", {
            attrs: {
              "fill-rule": "evenodd",
              d:
                "M10 10a1 1 0 011 1c0 2.236-.46 4.368-1.29 6.304a1 1 0 01-1.838-.789A13.952 13.952 0 009 11a1 1 0 011-1z",
              "clip-rule": "evenodd"
            }
          })
        ]
      )
    : _vm.name === "discord"
    ? _c(
        "svg",
        {
          attrs: { viewBox: "0 0 24 24", xmlns: "http://www.w3.org/2000/svg" }
        },
        [
          _c("path", {
            attrs: {
              d:
                "M20.317 4.3698a19.7913 19.7913 0 00-4.8851-1.5152.0741.0741 0 00-.0785.0371c-.211.3753-.4447.8648-.6083 1.2495-1.8447-.2762-3.68-.2762-5.4868 0-.1636-.3933-.4058-.8742-.6177-1.2495a.077.077 0 00-.0785-.037 19.7363 19.7363 0 00-4.8852 1.515.0699.0699 0 00-.0321.0277C.5334 9.0458-.319 13.5799.0992 18.0578a.0824.0824 0 00.0312.0561c2.0528 1.5076 4.0413 2.4228 5.9929 3.0294a.0777.0777 0 00.0842-.0276c.4616-.6304.8731-1.2952 1.226-1.9942a.076.076 0 00-.0416-.1057c-.6528-.2476-1.2743-.5495-1.8722-.8923a.077.077 0 01-.0076-.1277c.1258-.0943.2517-.1923.3718-.2914a.0743.0743 0 01.0776-.0105c3.9278 1.7933 8.18 1.7933 12.0614 0a.0739.0739 0 01.0785.0095c.1202.099.246.1981.3728.2924a.077.077 0 01-.0066.1276 12.2986 12.2986 0 01-1.873.8914.0766.0766 0 00-.0407.1067c.3604.698.7719 1.3628 1.225 1.9932a.076.076 0 00.0842.0286c1.961-.6067 3.9495-1.5219 6.0023-3.0294a.077.077 0 00.0313-.0552c.5004-5.177-.8382-9.6739-3.5485-13.6604a.061.061 0 00-.0312-.0286zM8.02 15.3312c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9555-2.4189 2.157-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.9555 2.4189-2.1569 2.4189zm7.9748 0c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9554-2.4189 2.1569-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.946 2.4189-2.1568 2.4189Z"
            }
          })
        ]
      )
    : _vm.name === "moon-full"
    ? _c(
        "svg",
        {
          attrs: {
            fill: "currentColor",
            viewBox: "0 0 20 20",
            xmlns: "http://www.w3.org/2000/svg"
          }
        },
        [
          _c("path", {
            attrs: {
              d:
                "M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"
            }
          })
        ]
      )
    : _vm.name === "moon-outline"
    ? _c(
        "svg",
        {
          attrs: {
            fill: "none",
            stroke: "currentColor",
            viewBox: "0 0 24 24",
            xmlns: "http://www.w3.org/2000/svg"
          }
        },
        [
          _c("path", {
            attrs: {
              "stroke-linecap": "round",
              "stroke-linejoin": "round",
              "stroke-width": "2",
              d:
                "M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
            }
          })
        ]
      )
    : _vm.name === "bell"
    ? _c(
        "svg",
        {
          attrs: {
            fill: "currentColor",
            viewBox: "0 0 20 20",
            xmlns: "http://www.w3.org/2000/svg"
          }
        },
        [
          _c("path", {
            attrs: {
              d:
                "M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"
            }
          })
        ]
      )
    : _vm.name === "skull-bones-outline"
    ? _c(
        "svg",
        {
          attrs: {
            fill: "currentColor",
            xmlns: "http://www.w3.org/2000/svg",
            viewBox: "0 0 1280 1234"
          }
        },
        [
          _c("path", {
            attrs: {
              d:
                "M615.5.6c-2.7.2-12 .8-20.5 1.4-91.2 6.4-175.9 29.7-233.7 64.2-31.9 19.1-67.8 53.3-91.5 87.3-29.4 42.2-47.8 89.9-55 142.5-1.6 12.4-1.7 54.3 0 68 6.2 50.8 17.5 90.9 36.6 130.5 12.7 26.2 25.6 46.3 40.9 63.7l7.7 8.8-3.6 18.7c-3.4 17.8-3.6 19.9-3.6 39.3-.1 29.2 4 52.6 13.1 74.6 2.1 5.2 3.6 9.4 3.3 9.4-1.3 0-50-24.3-70.5-35.2-28.4-15.1-28.9-15.6-41.9-41.3-13.4-26.6-19.5-35.1-33.8-46.7-9.5-7.7-24.8-15.7-35-18.3-4.5-1.2-9.3-1.6-15.6-1.3-8.1.3-10 .8-16.5 4-12.9 6.4-23.8 19-29.7 34.3-5.2 13.9-7.7 28.9-9.5 57.9-.4 5.3-.8 6.3-3.8 8.9-23 20.1-32.4 29.9-40.6 42.5C2.3 729-1.4 742.2.8 755c3.9 23 22.6 37.9 56.5 45.2 15.1 3.2 38.3 3.2 56.7-.2 9.9-1.8 17.1-2.4 27-2.5h13.5l38 17.9c53.8 25.3 105.2 50 154.8 74.1l42.8 20.8-.3 6-.3 5.9-13 7c-73.3 39.1-139.7 69.6-158.6 72.8-9 1.5-15.6.9-30.4-3-23.9-6.2-40.1-6.2-63.8-.1-16.2 4.2-20 6.2-27.6 13.9-15.8 16.1-20.8 37-13.7 57.4 4.2 11.9 9.9 19 33.1 41l21.3 20.3 1.1 7c.6 3.8 2.2 17.4 3.5 30 2.1 20.6 2.7 23.8 5.5 30.5 6.2 14.9 17.9 27 30.9 32.1 7.7 3 24.1 3.3 32.2.5 15.8-5.4 24.4-11.7 39.3-29.2 9.5-11 14.6-18.3 22.9-32.1 5.8-9.8 12.9-21 15.6-24.9l5.1-7.1 72.9-38.6 73-38.7 15.8 15.8c26.8 26.5 44 36.8 79.7 47.6 27.9 8.5 49.8 12.1 83.4 13.6 36.3 1.7 73.9-.6 98.8-6.1 21.4-4.8 46.2-13.3 63-21.7 17.7-8.8 38.3-24.6 54.1-41.5l7.9-8.5L914 1098c68.1 35.5 72.8 38.1 77.4 43.1 6.9 7.7 11.8 15.5 19.1 30.7 7.9 16.3 13.1 23.8 23.2 33.2 24.1 22.3 43.4 31.3 60.8 28.3 10.6-1.8 18.8-6.3 27.1-14.7 14.1-14.4 20.6-34.7 23.5-73.9l1.2-15.9 12.1-10.1c15.8-13.1 31-28.5 36.9-37.2 16.1-23.8 11.8-51.2-10.5-68-8.2-6.1-15.1-9.5-27.1-13.4-23.4-7.5-42.6-8-67.2-1.7-16.7 4.3-24 4.6-33.5 1.3-3.6-1.3-42.4-18.9-86.3-39.2l-79.8-37 .3-7.5.3-7.4 115-54.2c63.3-29.8 117.3-55 120-55.9 9.6-3.3 18.5-3.1 33 .6 12.4 3.2 13.8 3.4 30.5 3.3 27.5 0 43.5-3 58.4-10.6 10.6-5.5 21.1-15.8 25.5-25.2 5.6-11.7 7.6-26.4 4.7-35.9-2.7-9-22.2-33.2-42.9-53.4l-11.5-11.2-.7-9.8c-1.6-23.3-8.6-52.7-16.1-67.4-5.2-10.5-17.9-20.9-29.2-24.3-23.1-6.7-46 2.3-72.1 28.4-10 10-14.6 17.3-23.6 37.6-3.5 7.9-7.9 16.7-9.9 19.6-7.8 11.7-9.7 13-55.8 35.6-23.6 11.6-42.8 20.9-42.8 20.7 0-.1 1.1-2.7 2.4-5.6 10.1-22.9 15.4-54.7 14.3-84.9-.5-12.3-1.5-19.8-4.6-35.9l-4-20.4 6.8-7.3c32-33.6 56.8-79 69.2-126.4 13.1-49.9 13-111.9-.1-165-15.5-62.8-49.4-119.5-97.4-162.9-29.5-26.6-49.1-39.3-81.3-52.5C832 26.2 759.7 8.4 704 2.5 690.3 1.1 627.4-.3 615.5.6zM702 50.5c61 7 132.7 26.2 175.6 47 46.8 22.7 95.8 76.5 118.9 130.5 11.1 25.7 18.1 52.4 21.6 81.5 2.1 16.8 1.8 60-.4 75-5.8 39.1-20.4 77.1-41.1 106.9l-4.1 5.9-.7-4.4c-1.2-7.7-.8-32.9.7-45.2 1.1-9.9 2.6-15.3 8.4-32.5 11.7-34.1 12.9-39.7 15.2-68.5 2.2-29-.1-47.7-7.6-62.7-3.3-6.5-12.7-19-14.3-19-.5 0-8.2 7.7-17.1 17.1l-16.4 17 2.1 2.2c5.8 6.1 7.3 19.2 5.3 44.4-1.7 20-3.1 26.2-11.2 49.7-8.9 25.4-10.6 32.6-12.4 51.1-1.9 19-1.9 35.9 0 55 2.4 25 3.4 31 12.3 75.5l5.7 28.5V625c-.1 26-3.2 42.2-11.5 59.5-8.7 18.1-18.1 24-62.5 39.4-26.9 9.4-41.2 16.9-64.6 33.8-24.8 17.9-39.8 45.7-41.6 77l-.6 10.3-3.6 1c-7.6 2.2-28.1 5.9-40.6 7.4-23.6 2.8-53.3 3.8-96 3.3-47.4-.6-64-2.1-89.6-7.8l-9.7-2.2-.6-4.5c-.3-2.5-.6-6.6-.6-9.1-.1-10-5.7-29.3-11.8-40.1-8.5-15.1-18-26-31.2-35.9-23-17.3-31.6-21.6-62.4-31.3-45.7-14.3-57.6-22.8-66.6-47.6-6.3-17.3-9.7-41.9-8.6-60.7.4-5.5 3.1-22.4 6.1-37.5 10.3-52.5 12.4-67.5 13.2-96.1 1.1-38.6-1.1-52.3-13.8-88-7-19.9-8.8-27.8-10.5-48.1-2.2-25.9-1-37.9 4.5-44l2.9-3.2-16.7-17.3-16.6-17.4-5.2 5.6C290 287 284.7 308.9 287 342.9c2.3 33.5 3.9 41.4 15 72.1 7.8 21.7 9.1 28.2 9.7 50.5.3 11 .3 24-.2 28.9l-.7 8.9-3.4-5.4c-6.2-9.8-20.2-39.2-24.9-52.4-20.1-55.8-26.3-113.3-17.3-160.5 4.7-25 11-43.6 23.3-68.5 25.8-52.6 65.4-93.6 114.5-118.4C455.7 71.4 524.9 54.7 607 49c18.7-1.3 78.9-.3 95 1.5zm462.8 560.7c1.6 2 6 16.8 8.3 28 1.3 6.7 2.2 15.7 2.5 25.8.4 10.4 1 16.9 2.1 19.9 1.2 3.5 5.9 8.7 22.8 25.5 23.4 23.3 31.5 32.8 29.8 35.5-2.9 4.6-12.4 7.3-31.3 8.8-12.6.9-15.2.7-30.5-2.9-21.8-5.2-37.2-5-55.9.9-5.7 1.8-163.5 75.3-220.8 102.9-2.1.9-4 1.6-4.2 1.3-.6-.6 2.7-16.8 5.9-28.4 3-11 10.8-33.3 12.2-34.5.4-.5 41.5-20.8 91.3-45.2l90.5-44.3 7.8-7.5c10.6-10 18.8-21.3 24.9-34 17.3-36 14.9-32.1 25-41 7.9-6.8 15.6-12 17.9-12 .4 0 1.1.6 1.7 1.2zm-1040.7 6c3.9 2 8.9 5.7 13 9.8 5.9 5.9 7.7 8.8 15.9 25.1 5 10.1 11.2 21.5 13.8 25.3 5.7 8.6 17.2 20.3 24.3 24.9 13.1 8.3 65.2 34.8 116.9 59.5 57.4 27.4 67.4 32.3 68.2 33.5 3.4 5.4 17.8 56.3 17.8 63 0 .3-17.7-8-39.2-18.5-47.3-22.9-110.9-53.2-153.8-73.3-28.1-13.2-32.4-14.9-40.5-16.2-12.2-2-33.4-1.2-53 2.2-15.9 2.7-26.2 3.1-36.2 1.5-7.7-1.3-18.2-4.5-20.8-6.4-1.7-1.2-1.8-1.7-.7-3.8 4-7.3 13.1-17.4 27.7-30.3 16.5-14.7 22-20.6 24.4-26 .7-1.7 1.9-11.7 2.7-23.5 1.6-23.7 1.9-25.9 4.5-36 2.2-8 5.3-14 7.3-14 .7 0 4.2 1.4 7.7 3.2zm334.9 187c9.3 11.5 13.2 21 14.6 35.5l.7 7.3h4.5c3.6 0 4.2.2 3.3 1.4-.9 1-.4 1.8 2.5 3.7l3.5 2.4-.1 38.9c0 21.5-.3 39.2-.6 39.5-.3.3-1.6-.6-2.8-2.2-1.8-2.3-2.7-6.2-4.9-22-3.1-21.6-7.2-42.3-11.8-59.6-2.5-9.2-13.3-43.8-15.6-49.8-1.2-3.2 2.1-.7 6.7 4.9zm362 12.9c-10.2 30.7-16 55.8-20.9 90.2-1.9 13.3-3.6 21.8-4.6 23.4-.9 1.3-1.8 2.3-2 2.1-.2-.2-.5-18-.6-39.7l-.2-39.4 4.2-2.5c2.6-1.5 4-3 3.7-3.8-.4-1.1.6-1.4 4.4-1.4h5v-6.3c0-7.9 2.9-19.1 6.5-25.4 3-5.1 8.6-12.6 9-12.1.2.2-1.9 6.9-4.5 14.9zm24 7.4c-2.7 9.6-5.8 28.2-6.6 39-.5 6.5-.2 11.2 1 17 5.3 27.1 4.1 60.1-3.5 90.5-9.2 37.1-43.2 79-78.4 96.8-8.7 4.3-30.3 12-43.2 15.3-17 4.3-27.6 5.8-51.7 7.1-24 1.3-57.4.1-76.1-2.7-20.6-3.1-49.4-11.5-64-18.6-27.7-13.5-60.2-50.4-72.7-82.5-7.5-19.4-11.2-42.4-11.2-69.9 0-16.4.4-22.2 2.3-32.5 2.4-13.5 2.3-21.2-.5-37.9-.8-4.6-1.3-8.6-1.1-8.8.6-.5 6.1 19.8 9.7 35.2 2.7 12 5.3 27.5 8.8 52.5 1.3 9 2.1 12.4 3.1 12.2.8-.2 1 .4.6 1.7-1.2 3.8 23.4 26.1 40.3 36.4 26.8 16.3 62.2 26.2 106.2 29.6 54.8 4.4 93.2.7 132.9-12.6 35.1-11.7 64.4-31.1 75.8-50.1 2.5-4.2 2.5-4.3.7-5.7-1.8-1.4-1.8-1.5.5-1.2 2.4.2 2.5-.1 3.8-9.8 5.2-40.2 9.9-62.3 19.9-94.3 4.7-14.9 6.7-19 3.4-6.7zm-71.3 68.9c-.5 1.1-13.6 8.6-15.1 8.6-.3 0-.6-8.1-.6-18 0-13.6.3-18.2 1.3-18.5.6-.2 3.9-1.2 7.2-2.2l6-1.8.8 15.4c.5 8.4.6 15.9.4 16.5zM522 884.2c0 9.8-.3 17.8-.6 17.8s-3.7-1.6-7.5-3.6l-6.9-3.6v-11.2c0-6.1.3-13.4.6-16.2l.6-5.1 6.9 2.1 6.9 2v17.8zm217 6.2v20.4l-7.8 2.1c-12.4 3.3-11.2 5.3-11.2-19.2v-21.4l7.3-1c3.9-.5 8.2-1 9.5-1.1l2.2-.2v20.4zm-186.5-18.3l7.5 1.1V884c0 5.9-.3 15.4-.6 21.2l-.7 10.6-7.1-2c-11.6-3.3-10.6-1.1-10.6-23.3 0-22.4-1.3-20.3 11.5-18.4zM701 896.4v22.4l-2.7.6c-1.6.3-4.6.7-6.8.8l-4 .3-.3-22.7-.2-22.7 5.2-.4c2.9-.2 6.1-.4 7.1-.5 1.6-.2 1.7 1.5 1.7 22.2zm-107.4-20.1c.3.3.2 10.6-.2 22.9-.7 22.1-.7 22.5-2.8 22.2-1.2-.2-4.2-.6-6.8-.9l-4.8-.6v-45.2l7 .5c3.9.3 7.3.8 7.6 1.1zm38.2 23.4l.2 23.3h-19v-47.1l9.3.3 9.2.3.3 23.2zm36.2-.8v22.8l-5.2.7c-2.9.3-6.8.6-8.5.6H651v-47h17v22.9zm104.3 52.7c-2.2 1.6-12.8 7.4-13.7 7.4-.3 0-.6-7.9-.6-17.5V924l7.8-3.9 7.7-3.9.3 17c.2 15.3.1 17.2-1.5 18.4zm-257-31.1l6.7 2.9v17.8c0 9.8-.2 17.8-.5 17.8-.7 0-10.8-6.2-12.7-7.8-1.6-1.3-1.8-3.5-1.8-17.9 0-9.1.4-16.3.8-16.1.4.1 3.8 1.6 7.5 3.3zm37 13.2l6.7 1.7v18.8c0 10.3-.3 18.8-.7 18.8-.5 0-4.5-1.2-9-2.6l-8.3-2.6v-37l2.3.6c1.2.3 5.2 1.3 9 2.3zM739 949.5v18.4l-8.1 2.6c-4.4 1.4-8.4 2.5-9 2.5-.5 0-.9-7.6-.9-18.9v-18.9l7.8-2c4.2-1.1 8.3-2 9-2.1.9-.1 1.2 4.2 1.2 18.4zm-37 9v19.3l-5 1.1c-9.8 2.1-9 3.8-9-18.9v-19.9l3.3-.5c5.1-.9 9.1-1.2 10-.8.4.2.7 9 .7 19.7zm-113.7-18l4.7.6v38.1l-6.7-.7c-3.8-.4-7.1-.9-7.5-1.2-.5-.2-.8-9-.8-19.4v-19.1l2.8.6c1.5.2 4.8.8 7.5 1.1zm80 14.7c.4 7.9.7 17.1.7 20.4 0 6.1 0 6.2-3.1 6.8-1.7.3-5.8.6-9 .6H651v-40.9l7.3-.4c3.9-.1 7.7-.4 8.3-.5.7-.1 1.3 4.6 1.7 14zm-36.5 7.3l.2 19.5h-7.7c-13.5 0-12.4 1.8-12.3-20 .1-10.4.4-19 .7-19.3.2-.3 4.6-.3 9.6-.1l9.2.4.3 19.5zm-232.7 22.4c3.1 11.6 7.2 22.7 11.3 30.9l2.4 4.7-74.5 39.5c-55.6 29.4-75.4 40.4-78.1 43.2-6.1 6.4-14.5 18.2-23.2 32.7-13.3 22.1-17.4 28-25.4 37.1-7 7.8-13.5 13-16.2 13-2.5 0-4.5-5.4-5.5-14.3-3.2-30.5-4.1-38.3-6-48.2-1.1-6.1-2.9-12.8-4.1-15.1-1.3-2.7-10.2-11.9-25.6-26.5-12.9-12.3-24.3-23.7-25.3-25.4-4-6.4-1.5-9.5 9.7-12.1 12.4-2.9 24.4-2.4 38.8 1.4 21.5 5.7 34.9 6.4 53.5 2.6 11.6-2.3 39.7-13.6 77.1-30.9 16.1-7.4 77.4-38.4 84.1-42.4 1.8-1.1 3.5-1.9 3.6-1.7.2.2 1.7 5.4 3.4 11.5zm560.7 23.2c40.5 18.8 75.8 34.9 78.4 36 10.2 4 18.8 5.4 32.8 5.3 12.2 0 15-.4 29-3.8 14-3.5 16.4-3.8 25-3.3 6.6.3 12 1.3 17.5 3.2 8.1 2.7 14.5 6 14.5 7.5 0 1.8-15 16.7-27.4 27.3-16.7 14.2-24.2 21.3-26.5 25.1-2.5 4.1-3.7 11.2-5.7 34.1-2.2 26.8-5.6 42.3-9.6 44.9-2.1 1.3-9.2-3.4-20-13.4-7.4-6.8-8.1-7.9-15.8-23.5-9.5-19.3-16.6-29.6-29-42.1-7.7-7.7-10.9-10.1-20.3-15.1-7.8-4.2-132.1-68.7-134.4-69.7-.1-.1 1.6-3.7 3.6-8.1 4.5-9.3 9.5-24.1 11.2-32.6.6-3.2 1.6-5.9 2.1-5.9s34.1 15.3 74.6 34.1z"
            }
          }),
          _c("path", {
            attrs: {
              d:
                "M530.5 459.6c-.5.2-17.6 2.4-37.8 4.9-41.3 5.1-64.8 8.8-72.2 11.1-23.9 7.7-40.9 27.3-44.6 51.4-1.7 11.2-.2 18.6 7.5 37.7 21 51.5 20.2 50 35 64.9 13.4 13.3 26.3 20.3 44.6 24 15.6 3.1 30.1.2 43.5-8.7 8.3-5.6 12.1-9.5 28-29.4 24.3-30.3 45.8-66 49.1-81.4 4.2-19.8-.4-42.9-11.3-56.9-7.1-9.1-14.3-14.2-23.8-16.7-5.4-1.4-15.5-1.9-18-.9zM733.3 460.5c-8.4 2.3-13.4 5.3-20 12.1-7 7.2-12.3 17-14.9 27.9-2.1 8.8-2.3 25-.5 33.5 4.5 20.5 41.6 76.8 67.5 102.6 17.6 17.4 36.7 22.1 61.3 14.9 14.6-4.2 25.3-10.5 36.3-21.4 10.8-10.7 15.9-18.3 21.1-31 2.6-6.4 7.6-18.7 11.3-27.4 9-21.8 10.6-27.3 10.6-37.1 0-20.3-10-38.8-27.1-50.2-11.6-7.7-18.6-9.9-45-13.8-27.7-4.2-79.7-10.7-88.9-11.2-3.8-.2-8.7.2-11.7 1.1zM620 622.9c-21.2 17.4-35.6 37.4-45.5 62.9-5.8 15-7.8 29-7.9 53.7-.1 19.1.1 21.7 2.2 29 10.1 34.9 26.8 33.6 59.5-4.7 4-4.7 4.6-5.9 3.7-7.5-.8-1.3-.9-20.9-.3-70.1.4-37.6.4-69 0-69.8-.4-.8-1.1-1.4-1.5-1.3-.4 0-5 3.5-10.2 7.8zM649.4 617.2c-.5.8-.6 31.5-.3 70.3.4 43.6.2 69.5-.4 70.2-2 2.7 24.6 29.3 33.8 33.9 6.7 3.2 12.6 3.5 16.3.7 7.2-5.3 13-17.9 15.2-33.2 2-13.7.7-39.3-2.9-57.6-3.7-19.1-20.2-49-35.7-65.1-5.7-5.8-23.2-20.4-24.5-20.4-.4 0-1 .6-1.5 1.2z"
            }
          })
        ]
      )
    : _vm.name === "swords-cross"
    ? _c(
        "svg",
        {
          attrs: {
            fill: "currentColor",
            xmlns: "http://www.w3.org/2000/svg",
            viewBox: "0 0 307.164 307.164"
          }
        },
        [
          _c("path", {
            attrs: {
              d:
                "M193.307 126.847A467.822 467.822 0 00296.811 8.601a2.381 2.381 0 00-3.458-3.15c-9.58 7.185-24.574 17.651-39.701 25.109-19.557 9.641-40.571 13.577-51.19 15.055a15.619 15.619 0 00-10.929 6.941c-5.225 8.016-15.351 23.039-28.405 39.984 6.044 7.515 12.568 15.213 19.406 22.654 3.755 4.085 7.343 7.965 10.773 11.653zM115.393 147.168c-17.296 18.396-29.524 30.808-36.563 37.816l-3.183-3.183c-3.906-3.904-10.236-3.904-14.143 0-3.905 3.905-3.905 10.237 0 14.143l1.405 1.405a12.473 12.473 0 00-10.071 3.598c-3.232 3.232-4.311 7.791-3.263 11.921-4.131-1.048-8.69.031-11.922 3.262-3.232 3.232-4.311 7.792-3.263 11.922-4.13-1.047-8.69.031-11.921 3.262-2.991 2.991-4.14 7.119-3.466 10.992l-1.932-1.932c-3.906-3.904-10.236-3.904-14.143 0-3.905 3.905-3.905 10.237 0 14.143l42.193 42.192a10.005 10.005 0 005.977 2.868l23.146 2.55c.372.041.741.061 1.107.061 5.031 0 9.363-3.789 9.927-8.906.605-5.489-3.354-10.43-8.845-11.034l-19.653-2.165-14.243-14.243c.712.124 1.432.195 2.153.195 3.199 0 6.398-1.221 8.839-3.661 3.232-3.232 4.311-7.791 3.263-11.921 1.011.257 2.046.399 3.083.399 3.199 0 6.398-1.221 8.839-3.661 3.232-3.232 4.311-7.791 3.263-11.922 1.011.256 2.045.398 3.082.398 3.199 0 6.398-1.221 8.839-3.661a12.473 12.473 0 003.599-10.071l2.814 2.814 2.166 19.653c.563 5.118 4.895 8.906 9.927 8.906.366 0 .735-.02 1.107-.061 5.49-.605 9.45-5.545 8.845-11.034l-2.55-23.145a10.008 10.008 0 00-2.868-5.977l-5.84-5.84 41.007-41.007a482.113 482.113 0 01-26.712-19.076z"
            }
          }),
          _c("path", {
            attrs: {
              d:
                "M304.235 240.375c-3.906-3.904-10.236-3.904-14.143 0l-1.932 1.932c.674-3.873-.475-8.001-3.466-10.992-3.232-3.232-7.79-4.31-11.921-3.262 1.048-4.131-.03-8.691-3.262-11.922-3.232-3.232-7.79-4.31-11.92-3.263 1.047-4.13-.031-8.689-3.263-11.921a12.46 12.46 0 00-3.943-2.657 12.519 12.519 0 00-6.13-.941l1.406-1.406c3.905-3.905 3.905-10.237 0-14.143-3.906-3.904-10.236-3.904-14.143 0l-3.183 3.183c-9.534-9.492-28.572-28.879-56.844-59.64-25.939-28.223-47.365-59.759-55.859-72.788a15.617 15.617 0 00-10.929-6.942c-10.619-1.478-31.633-5.414-51.19-15.055-15.128-7.456-30.122-17.923-39.702-25.107a2.377 2.377 0 00-3.032.145 2.381 2.381 0 00-.426 3.006A467.811 467.811 0 00154.2 156.256l2.486 1.615 49.381 49.381-5.84 5.84a10.005 10.005 0 00-2.868 5.977l-.068.62-2.481 22.526c-.606 5.489 3.354 10.43 8.845 11.034.372.041.741.061 1.107.061 5.031 0 9.363-3.788 9.927-8.906l1.29-11.707 4.632-4.632a12.453 12.453 0 002.656 3.942 12.463 12.463 0 008.839 3.661c1.037 0 2.072-.142 3.083-.399-1.048 4.131.03 8.69 3.262 11.922a12.46 12.46 0 008.839 3.661c1.037 0 2.071-.142 3.082-.398-1.048 4.13.031 8.689 3.263 11.921a12.463 12.463 0 008.839 3.661c.721 0 1.441-.071 2.154-.195l-14.243 14.243-19.653 2.165c-5.49.604-9.45 5.545-8.845 11.034.563 5.118 4.895 8.906 9.927 8.906.366 0 .735-.021 1.107-.061l23.146-2.55a10.008 10.008 0 005.977-2.868l42.192-42.192c3.904-3.906 3.904-10.238-.001-14.143z"
            }
          })
        ]
      )
    : _vm.name === "line-chart"
    ? _c(
        "svg",
        {
          staticClass: "bi bi-graph-up",
          attrs: {
            xmlns: "http://www.w3.org/2000/svg",
            fill: "currentColor",
            viewBox: "0 0 16 16"
          }
        },
        [
          _c("path", {
            attrs: {
              "fill-rule": "evenodd",
              d:
                "M0 0h1v15h15v1H0V0Zm14.817 3.113a.5.5 0 0 1 .07.704l-4.5 5.5a.5.5 0 0 1-.74.037L7.06 6.767l-3.656 5.027a.5.5 0 0 1-.808-.588l4-5.5a.5.5 0 0 1 .758-.06l2.609 2.61 4.15-5.073a.5.5 0 0 1 .704-.07Z"
            }
          })
        ]
      )
    : _vm.name === "cpu"
    ? _c(
        "svg",
        {
          staticClass: "bi bi-cpu",
          attrs: {
            xmlns: "http://www.w3.org/2000/svg",
            fill: "currentColor",
            viewBox: "0 0 16 16"
          }
        },
        [
          _c("path", {
            attrs: {
              d:
                "M5 0a.5.5 0 0 1 .5.5V2h1V.5a.5.5 0 0 1 1 0V2h1V.5a.5.5 0 0 1 1 0V2h1V.5a.5.5 0 0 1 1 0V2A2.5 2.5 0 0 1 14 4.5h1.5a.5.5 0 0 1 0 1H14v1h1.5a.5.5 0 0 1 0 1H14v1h1.5a.5.5 0 0 1 0 1H14v1h1.5a.5.5 0 0 1 0 1H14a2.5 2.5 0 0 1-2.5 2.5v1.5a.5.5 0 0 1-1 0V14h-1v1.5a.5.5 0 0 1-1 0V14h-1v1.5a.5.5 0 0 1-1 0V14h-1v1.5a.5.5 0 0 1-1 0V14A2.5 2.5 0 0 1 2 11.5H.5a.5.5 0 0 1 0-1H2v-1H.5a.5.5 0 0 1 0-1H2v-1H.5a.5.5 0 0 1 0-1H2v-1H.5a.5.5 0 0 1 0-1H2A2.5 2.5 0 0 1 4.5 2V.5A.5.5 0 0 1 5 0zm-.5 3A1.5 1.5 0 0 0 3 4.5v7A1.5 1.5 0 0 0 4.5 13h7a1.5 1.5 0 0 0 1.5-1.5v-7A1.5 1.5 0 0 0 11.5 3h-7zM5 6.5A1.5 1.5 0 0 1 6.5 5h3A1.5 1.5 0 0 1 11 6.5v3A1.5 1.5 0 0 1 9.5 11h-3A1.5 1.5 0 0 1 5 9.5v-3zM6.5 6a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3z"
            }
          })
        ]
      )
    : _vm.name === "ram"
    ? _c(
        "svg",
        {
          staticClass: "bi bi-memory",
          attrs: {
            xmlns: "http://www.w3.org/2000/svg",
            fill: "currentColor",
            viewBox: "0 0 16 16"
          }
        },
        [
          _c("path", {
            attrs: {
              d:
                "M1 3a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h4.586a1 1 0 0 0 .707-.293l.353-.353a.5.5 0 0 1 .708 0l.353.353a1 1 0 0 0 .707.293H15a1 1 0 0 0 1-1V4a1 1 0 0 0-1-1H1Zm.5 1h3a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-4a.5.5 0 0 1 .5-.5Zm5 0h3a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-4a.5.5 0 0 1 .5-.5Zm4.5.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-4ZM2 10v2H1v-2h1Zm2 0v2H3v-2h1Zm2 0v2H5v-2h1Zm3 0v2H8v-2h1Zm2 0v2h-1v-2h1Zm2 0v2h-1v-2h1Zm2 0v2h-1v-2h1Z"
            }
          })
        ]
      )
    : _vm.name === "numbers"
    ? _c(
        "svg",
        {
          staticClass: "bi bi-123",
          attrs: {
            xmlns: "http://www.w3.org/2000/svg",
            fill: "currentColor",
            viewBox: "0 0 16 16"
          }
        },
        [
          _c("path", {
            attrs: {
              d:
                "M2.873 11.297V4.142H1.699L0 5.379v1.137l1.64-1.18h.06v5.961h1.174Zm3.213-5.09v-.063c0-.618.44-1.169 1.196-1.169.676 0 1.174.44 1.174 1.106 0 .624-.42 1.101-.807 1.526L4.99 10.553v.744h4.78v-.99H6.643v-.069L8.41 8.252c.65-.724 1.237-1.332 1.237-2.27C9.646 4.849 8.723 4 7.308 4c-1.573 0-2.36 1.064-2.36 2.15v.057h1.138Zm6.559 1.883h.786c.823 0 1.374.481 1.379 1.179.01.707-.55 1.216-1.421 1.21-.77-.005-1.326-.419-1.379-.953h-1.095c.042 1.053.938 1.918 2.464 1.918 1.478 0 2.642-.839 2.62-2.144-.02-1.143-.922-1.651-1.551-1.714v-.063c.535-.09 1.347-.66 1.326-1.678-.026-1.053-.933-1.855-2.359-1.845-1.5.005-2.317.88-2.348 1.898h1.116c.032-.498.498-.944 1.206-.944.703 0 1.206.435 1.206 1.07.005.64-.504 1.106-1.2 1.106h-.75v.96Z"
            }
          })
        ]
      )
    : _vm.name === "calculator"
    ? _c(
        "svg",
        {
          staticClass: "bi bi-calculator",
          attrs: {
            xmlns: "http://www.w3.org/2000/svg",
            fill: "currentColor",
            viewBox: "0 0 16 16"
          }
        },
        [
          _c("path", {
            attrs: {
              d:
                "M12 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h8zM4 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H4z"
            }
          }),
          _vm._v(" "),
          _c("path", {
            attrs: {
              d:
                "M4 2.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5v-2zm0 4a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm0 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm0 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm3-6a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm0 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm0 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm3-6a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm0 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-4z"
            }
          })
        ]
      )
    : _vm.name === "toggle-off"
    ? _c(
        "svg",
        {
          staticClass: "bi bi-toggle2-off",
          attrs: {
            xmlns: "http://www.w3.org/2000/svg",
            fill: "currentColor",
            viewBox: "0 0 16 16"
          }
        },
        [
          _c("path", {
            attrs: {
              d:
                "M9 11c.628-.836 1-1.874 1-3a4.978 4.978 0 0 0-1-3h4a3 3 0 1 1 0 6H9z"
            }
          }),
          _vm._v(" "),
          _c("path", {
            attrs: {
              d:
                "M5 12a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm0 1A5 5 0 1 0 5 3a5 5 0 0 0 0 10z"
            }
          })
        ]
      )
    : _vm.name === "toggle-on"
    ? _c(
        "svg",
        {
          staticClass: "bi bi-toggle2-on",
          attrs: {
            xmlns: "http://www.w3.org/2000/svg",
            fill: "currentColor",
            viewBox: "0 0 16 16"
          }
        },
        [
          _c("path", {
            attrs: {
              d:
                "M7 5H3a3 3 0 0 0 0 6h4a4.995 4.995 0 0 1-.584-1H3a2 2 0 1 1 0-4h3.416c.156-.357.352-.692.584-1z"
            }
          }),
          _vm._v(" "),
          _c("path", { attrs: { d: "M16 8A5 5 0 1 1 6 8a5 5 0 0 1 10 0z" } })
        ]
      )
    : _vm.name === "grid"
    ? _c(
        "svg",
        {
          staticClass: "bi bi-grid",
          attrs: {
            xmlns: "http://www.w3.org/2000/svg",
            fill: "currentColor",
            viewBox: "0 0 16 16"
          }
        },
        [
          _c("path", {
            attrs: {
              d:
                "M1 2.5A1.5 1.5 0 0 1 2.5 1h3A1.5 1.5 0 0 1 7 2.5v3A1.5 1.5 0 0 1 5.5 7h-3A1.5 1.5 0 0 1 1 5.5v-3zM2.5 2a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 1h3A1.5 1.5 0 0 1 15 2.5v3A1.5 1.5 0 0 1 13.5 7h-3A1.5 1.5 0 0 1 9 5.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zM1 10.5A1.5 1.5 0 0 1 2.5 9h3A1.5 1.5 0 0 1 7 10.5v3A1.5 1.5 0 0 1 5.5 15h-3A1.5 1.5 0 0 1 1 13.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 9h3a1.5 1.5 0 0 1 1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-3A1.5 1.5 0 0 1 9 13.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3z"
            }
          })
        ]
      )
    : _vm.name === "joystick"
    ? _c(
        "svg",
        {
          staticClass: "bi bi-joystick",
          attrs: {
            xmlns: "http://www.w3.org/2000/svg",
            fill: "currentColor",
            viewBox: "0 0 16 16"
          }
        },
        [
          _c("path", {
            attrs: {
              d:
                "M10 2a2 2 0 0 1-1.5 1.937v5.087c.863.083 1.5.377 1.5.726 0 .414-.895.75-2 .75s-2-.336-2-.75c0-.35.637-.643 1.5-.726V3.937A2 2 0 1 1 10 2z"
            }
          }),
          _vm._v(" "),
          _c("path", {
            attrs: {
              d:
                "M0 9.665v1.717a1 1 0 0 0 .553.894l6.553 3.277a2 2 0 0 0 1.788 0l6.553-3.277a1 1 0 0 0 .553-.894V9.665c0-.1-.06-.19-.152-.23L9.5 6.715v.993l5.227 2.178a.125.125 0 0 1 .001.23l-5.94 2.546a2 2 0 0 1-1.576 0l-5.94-2.546a.125.125 0 0 1 .001-.23L6.5 7.708l-.013-.988L.152 9.435a.25.25 0 0 0-.152.23z"
            }
          })
        ]
      )
    : _vm._e()
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/InfiniteScroll.vue?vue&type=template&id=d1c55ee8&":
/*!**************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/InfiniteScroll.vue?vue&type=template&id=d1c55ee8& ***!
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
  return _c(
    "fragment",
    [
      _vm._t("default"),
      _vm._v(" "),
      _vm.loading
        ? _vm._t("loading", function() {
            return [
              _c(
                "div",
                {
                  staticClass:
                    "p-5 text-center text-gray-600 dark:text-gray-300 text-sm"
                },
                [_vm._v("\n      " + _vm._s(_vm.loadingText) + "\n    ")]
              )
            ]
          })
        : _vm._e()
    ],
    2
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/Notification.vue?vue&type=template&id=be911194&":
/*!************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/Notification.vue?vue&type=template&id=be911194& ***!
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
    {
      class: {
        "bg-light-blue-50 rounded dark:bg-cool-gray-600 dark:bg-opacity-25":
          _vm.notification.read_at == null
      }
    },
    [
      _vm.notification.type === "App\\Notifications\\PostCommentedByUser"
        ? _c(
            "inertia-link",
            {
              staticClass: "flex cursor-pointer",
              attrs: {
                as: "div",
                href: _vm.route("post.show", _vm.notification.data.post_id)
              }
            },
            [
              _c("img", {
                staticClass: "w-10 h-10 rounded-full m-1",
                attrs: {
                  src: _vm.notification.data.causer.profile_photo_url,
                  alt: "Profile Picture"
                }
              }),
              _vm._v(" "),
              _c("div", { staticClass: "m-1" }, [
                _c("p", [
                  _c("b", [_vm._v(_vm._s(_vm.notification.data.causer.name))]),
                  _vm._v(
                    "(@" +
                      _vm._s(_vm.notification.data.causer.username) +
                      ") commented on your post."
                  )
                ]),
                _vm._v(" "),
                _c("p", { staticClass: "text-xs" }, [
                  _vm._v(
                    "\n        " +
                      _vm._s(
                        _vm.formatDistanceToNowStrict(
                          new Date(_vm.notification.created_at),
                          { addSuffix: true }
                        )
                      ) +
                      "\n      "
                  )
                ])
              ])
            ]
          )
        : _vm._e(),
      _vm._v(" "),
      _vm.notification.type === "App\\Notifications\\PostLikedByUser"
        ? _c(
            "inertia-link",
            {
              staticClass: "flex cursor-pointer",
              attrs: {
                as: "div",
                href: _vm.route("post.show", _vm.notification.data.post_id)
              }
            },
            [
              _c("img", {
                staticClass: "w-10 h-10 rounded-full m-1",
                attrs: {
                  src: _vm.notification.data.causer.profile_photo_url,
                  alt: "Profile Picture"
                }
              }),
              _vm._v(" "),
              _c("div", { staticClass: "m-1" }, [
                _c("p", [
                  _c("b", [_vm._v(_vm._s(_vm.notification.data.causer.name))]),
                  _vm._v(
                    "(@" +
                      _vm._s(_vm.notification.data.causer.username) +
                      ") liked your post."
                  )
                ]),
                _vm._v(" "),
                _c("p", { staticClass: "text-xs" }, [
                  _vm._v(
                    "\n        " +
                      _vm._s(
                        _vm.formatDistanceToNowStrict(
                          new Date(_vm.notification.created_at),
                          { addSuffix: true }
                        )
                      ) +
                      "\n      "
                  )
                ])
              ])
            ]
          )
        : _vm._e(),
      _vm._v(" "),
      _vm.notification.type === "App\\Notifications\\UserYouAreMuted"
        ? _c(
            "inertia-link",
            {
              staticClass: "flex cursor-pointer",
              attrs: { as: "div", href: _vm.route("profile.show") }
            },
            [
              _c("img", {
                staticClass: "w-10 h-10 rounded-full m-1",
                attrs: {
                  src: _vm.notification.data.causer.profile_photo_url,
                  alt: "Profile Picture"
                }
              }),
              _vm._v(" "),
              _c("div", { staticClass: "m-1" }, [
                _c("p", [
                  _c("b", [_vm._v(_vm._s(_vm.notification.data.causer.name))]),
                  _vm._v(
                    "(@" +
                      _vm._s(_vm.notification.data.causer.username) +
                      ") muted you."
                  )
                ]),
                _vm._v(" "),
                _c("p", { staticClass: "text-xs" }, [
                  _vm._v(
                    "\n        " +
                      _vm._s(
                        _vm.formatDistanceToNowStrict(
                          new Date(_vm.notification.created_at),
                          { addSuffix: true }
                        )
                      ) +
                      "\n      "
                  )
                ])
              ])
            ]
          )
        : _vm._e(),
      _vm._v(" "),
      _vm.notification.type === "App\\Notifications\\UserYouAreBanned"
        ? _c(
            "inertia-link",
            {
              staticClass: "flex cursor-pointer",
              attrs: { as: "div", href: _vm.route("profile.show") }
            },
            [
              _c("img", {
                staticClass: "w-10 h-10 rounded-full m-1",
                attrs: {
                  src: _vm.notification.data.causer.profile_photo_url,
                  alt: "Profile Picture"
                }
              }),
              _vm._v(" "),
              _c("div", { staticClass: "m-1" }, [
                _c("p", [
                  _c("b", [_vm._v(_vm._s(_vm.notification.data.causer.name))]),
                  _vm._v(
                    "(@" +
                      _vm._s(_vm.notification.data.causer.username) +
                      ") banned you."
                  )
                ]),
                _vm._v(" "),
                _c("p", { staticClass: "text-xs" }, [
                  _vm._v(
                    "\n        " +
                      _vm._s(
                        _vm.formatDistanceToNowStrict(
                          new Date(_vm.notification.created_at),
                          { addSuffix: true }
                        )
                      ) +
                      "\n      "
                  )
                ])
              ])
            ]
          )
        : _vm._e()
    ],
    1
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/Pagination.vue?vue&type=template&id=0e1fe725&":
/*!**********************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/Pagination.vue?vue&type=template&id=0e1fe725& ***!
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
    { staticClass: "mt-6 -mb-1 flex flex-wrap" },
    [
      _vm._l(_vm.links, function(link, key) {
        return [
          link.url === null
            ? _c("div", {
                key: key,
                staticClass:
                  "mr-1 mb-1 px-4 py-3 text-sm border rounded text-gray-400",
                class: { "ml-auto": link.label === "Next &raquo;" },
                domProps: { innerHTML: _vm._s(link.label) }
              })
            : _c("inertia-link", {
                key: key,
                staticClass:
                  "mr-1 mb-1 px-4 py-3 text-sm border rounded hover:bg-white focus:border-indigo-500 focus:text-indigo-500",
                class: {
                  "bg-white shadow": link.active,
                  "bg-gray-50": !link.active,
                  "ml-auto": link.label === "Next &raquo;"
                },
                attrs: { href: link.url },
                domProps: { innerHTML: _vm._s(link.label) }
              })
        ]
      })
    ],
    2
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/Toast.vue?vue&type=template&id=24765128&scoped=true&":
/*!*****************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Components/Toast.vue?vue&type=template&id=24765128&scoped=true& ***!
  \*****************************************************************************************************************************************************************************************************************************/
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
  return _c("transition", { attrs: { name: "slide-fade" } }, [
    _vm.toast && _vm.show && !_vm.popstate
      ? _c(
          "div",
          {
            staticClass:
              "flex fixed w-full max-w-sm mt-16 mr-4 bg-white right-0 top-0 p-4 rounded-lg shadow z-50"
          },
          [
            _c("div", { staticClass: "mr-3" }, [
              _vm.toast.type === "success"
                ? _c(
                    "svg",
                    {
                      staticClass: "w-6 h-6 text-green-500",
                      attrs: {
                        xmlns: "http://www.w3.org/2000/svg",
                        fill: "none",
                        viewBox: "0 0 24 24",
                        stroke: "currentColor"
                      }
                    },
                    [
                      _c("path", {
                        attrs: {
                          "stroke-linecap": "round",
                          "stroke-linejoin": "round",
                          "stroke-width": "2",
                          d: "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                        }
                      })
                    ]
                  )
                : _vm.toast.type === "danger"
                ? _c(
                    "svg",
                    {
                      staticClass: "w-6 h-6 text-red-500",
                      attrs: {
                        xmlns: "http://www.w3.org/2000/svg",
                        fill: "none",
                        viewBox: "0 0 24 24",
                        stroke: "currentColor"
                      }
                    },
                    [
                      _c("path", {
                        attrs: {
                          "stroke-linecap": "round",
                          "stroke-linejoin": "round",
                          "stroke-width": "2",
                          d:
                            "M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
                        }
                      })
                    ]
                  )
                : _vm.toast.type === "warning"
                ? _c(
                    "svg",
                    {
                      staticClass: "w-6 h-6 text-yellow-500",
                      attrs: {
                        xmlns: "http://www.w3.org/2000/svg",
                        fill: "none",
                        viewBox: "0 0 24 24",
                        stroke: "currentColor"
                      }
                    },
                    [
                      _c("path", {
                        attrs: {
                          "stroke-linecap": "round",
                          "stroke-linejoin": "round",
                          "stroke-width": "2",
                          d:
                            "M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                        }
                      })
                    ]
                  )
                : _c(
                    "svg",
                    {
                      staticClass: "w-6 h-6 text-blue-500",
                      attrs: {
                        xmlns: "http://www.w3.org/2000/svg",
                        fill: "none",
                        viewBox: "0 0 24 24",
                        stroke: "currentColor"
                      }
                    },
                    [
                      _c("path", {
                        attrs: {
                          "stroke-linecap": "round",
                          "stroke-linejoin": "round",
                          "stroke-width": "2",
                          d: "M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                        }
                      })
                    ]
                  )
            ]),
            _vm._v(" "),
            _c("div", { staticClass: "flex-1" }, [
              _c("b", { staticClass: "text-sm" }, [
                _vm._v(_vm._s(_vm.toast.title))
              ]),
              _vm._v(" "),
              _vm.toast.body
                ? _c("p", { staticClass: "text-sm text-gray-600" }, [
                    _vm._v("\n        " + _vm._s(_vm.toast.body) + "\n      ")
                  ])
                : _vm._e()
            ]),
            _vm._v(" "),
            _c("div", { staticClass: "ml-2" }, [
              _c(
                "button",
                {
                  staticClass: "focus:outline-none",
                  on: {
                    click: function($event) {
                      _vm.show = !_vm.show
                    }
                  }
                },
                [
                  _c(
                    "svg",
                    {
                      staticClass: "w-5 h-5 text-gray-500 hover:text-gray-700 ",
                      attrs: {
                        xmlns: "http://www.w3.org/2000/svg",
                        fill: "none",
                        viewBox: "0 0 24 24",
                        stroke: "currentColor"
                      }
                    },
                    [
                      _c("path", {
                        attrs: {
                          "stroke-linecap": "round",
                          "stroke-linejoin": "round",
                          "stroke-width": "2",
                          d: "M6 18L18 6M6 6l12 12"
                        }
                      })
                    ]
                  )
                ]
              )
            ])
          ]
        )
      : _vm._e()
  ])
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/ApplicationMark.vue?vue&type=template&id=6ed2e539&":
/*!**************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/ApplicationMark.vue?vue&type=template&id=6ed2e539& ***!
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
  return _c("img", {
    staticClass: "logo",
    attrs: { src: _vm.logo, alt: "Site Header Logo" }
  })
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/Banner.vue?vue&type=template&id=55462a60&":
/*!*****************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/Banner.vue?vue&type=template&id=55462a60& ***!
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
  return _c("div", [
    _vm.show && _vm.message
      ? _c(
          "div",
          {
            class: {
              "bg-indigo-500": _vm.style == "success",
              "bg-red-700": _vm.style == "danger"
            }
          },
          [
            _c(
              "div",
              {
                staticClass: "max-w-screen-xl mx-auto py-2 px-3 sm:px-6 lg:px-8"
              },
              [
                _c(
                  "div",
                  {
                    staticClass: "flex items-center justify-between flex-wrap"
                  },
                  [
                    _c(
                      "div",
                      { staticClass: "w-0 flex-1 flex items-center min-w-0" },
                      [
                        _c(
                          "span",
                          {
                            staticClass: "flex p-2 rounded-lg",
                            class: {
                              "bg-indigo-600": _vm.style == "success",
                              "bg-red-600": _vm.style == "danger"
                            }
                          },
                          [
                            _vm.style == "success"
                              ? _c(
                                  "svg",
                                  {
                                    staticClass: "h-5 w-5 text-white",
                                    attrs: {
                                      xmlns: "http://www.w3.org/2000/svg",
                                      fill: "none",
                                      viewBox: "0 0 24 24",
                                      stroke: "currentColor"
                                    }
                                  },
                                  [
                                    _c("path", {
                                      attrs: {
                                        "stroke-linecap": "round",
                                        "stroke-linejoin": "round",
                                        "stroke-width": "2",
                                        d:
                                          "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                      }
                                    })
                                  ]
                                )
                              : _vm._e(),
                            _vm._v(" "),
                            _vm.style == "danger"
                              ? _c(
                                  "svg",
                                  {
                                    staticClass: "h-5 w-5 text-white",
                                    attrs: {
                                      xmlns: "http://www.w3.org/2000/svg",
                                      fill: "none",
                                      viewBox: "0 0 24 24",
                                      stroke: "currentColor"
                                    }
                                  },
                                  [
                                    _c("path", {
                                      attrs: {
                                        "stroke-linecap": "round",
                                        "stroke-linejoin": "round",
                                        "stroke-width": "2",
                                        d:
                                          "M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                                      }
                                    })
                                  ]
                                )
                              : _vm._e()
                          ]
                        ),
                        _vm._v(" "),
                        _c(
                          "p",
                          {
                            staticClass:
                              "ml-3 font-medium text-sm text-white truncate"
                          },
                          [
                            _vm._v(
                              "\n            " +
                                _vm._s(_vm.message) +
                                "\n          "
                            )
                          ]
                        )
                      ]
                    ),
                    _vm._v(" "),
                    _c("div", { staticClass: "flex-shrink-0 sm:ml-3" }, [
                      _c(
                        "button",
                        {
                          staticClass:
                            "-mr-1 flex p-2 rounded-md focus:outline-none sm:-mr-2 transition ease-in-out duration-150",
                          class: {
                            "hover:bg-indigo-600 focus:bg-indigo-600":
                              _vm.style == "success",
                            "hover:bg-red-600 focus:bg-red-600":
                              _vm.style == "danger"
                          },
                          attrs: { type: "button", "aria-label": "Dismiss" },
                          on: {
                            click: function($event) {
                              $event.preventDefault()
                              _vm.show = false
                            }
                          }
                        },
                        [
                          _c(
                            "svg",
                            {
                              staticClass: "h-5 w-5 text-white",
                              attrs: {
                                xmlns: "http://www.w3.org/2000/svg",
                                fill: "none",
                                viewBox: "0 0 24 24",
                                stroke: "currentColor"
                              }
                            },
                            [
                              _c("path", {
                                attrs: {
                                  "stroke-linecap": "round",
                                  "stroke-linejoin": "round",
                                  "stroke-width": "2",
                                  d: "M6 18L18 6M6 6l12 12"
                                }
                              })
                            ]
                          )
                        ]
                      )
                    ])
                  ]
                )
              ]
            )
          ]
        )
      : _vm._e()
  ])
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/ConfirmationModal.vue?vue&type=template&id=3478b418&":
/*!****************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/ConfirmationModal.vue?vue&type=template&id=3478b418& ***!
  \****************************************************************************************************************************************************************************************************************************/
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
    "modal",
    {
      attrs: {
        show: _vm.show,
        "max-width": _vm.maxWidth,
        closeable: _vm.closeable
      },
      on: { close: _vm.close }
    },
    [
      _c(
        "div",
        {
          staticClass:
            "bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-t-8 border-red-600"
        },
        [
          _c("div", { staticClass: "sm:flex sm:items-start" }, [
            _c(
              "div",
              {
                staticClass:
                  "mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10"
              },
              [
                _c(
                  "svg",
                  {
                    staticClass: "h-6 w-6 text-red-600",
                    attrs: {
                      stroke: "currentColor",
                      fill: "none",
                      viewBox: "0 0 24 24"
                    }
                  },
                  [
                    _c("path", {
                      attrs: {
                        "stroke-linecap": "round",
                        "stroke-linejoin": "round",
                        "stroke-width": "2",
                        d:
                          "M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                      }
                    })
                  ]
                )
              ]
            ),
            _vm._v(" "),
            _c(
              "div",
              { staticClass: "mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left" },
              [
                _c(
                  "h3",
                  { staticClass: "text-lg font-bold text-red-500" },
                  [_vm._t("title")],
                  2
                ),
                _vm._v(" "),
                _c("div", { staticClass: "mt-2" }, [_vm._t("content")], 2)
              ]
            )
          ])
        ]
      ),
      _vm._v(" "),
      _c("div", { staticClass: "px-6 py-4 text-right" }, [_vm._t("footer")], 2)
    ]
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/DangerButton.vue?vue&type=template&id=cdf2462e&":
/*!***********************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/DangerButton.vue?vue&type=template&id=cdf2462e& ***!
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
  return _c(
    "button",
    {
      staticClass:
        "inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:outline-none focus:border-red-700 focus:shadow-outline-red active:bg-red-600 transition ease-in-out duration-150",
      attrs: { type: _vm.type }
    },
    [_vm._t("default")],
    2
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/Dropdown.vue?vue&type=template&id=bd908476&":
/*!*******************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/Dropdown.vue?vue&type=template&id=bd908476& ***!
  \*******************************************************************************************************************************************************************************************************************/
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
    { staticClass: "relative" },
    [
      _c(
        "div",
        {
          on: {
            click: function($event) {
              _vm.open = !_vm.open
            }
          }
        },
        [_vm._t("trigger")],
        2
      ),
      _vm._v(" "),
      _c(
        "transition",
        {
          attrs: {
            "enter-active-class": "transition ease-out duration-200",
            "enter-class": "transform opacity-0 scale-95",
            "enter-to-class": "transform opacity-100 scale-100",
            "leave-active-class": "transition ease-in duration-75",
            "leave-class": "transform opacity-100 scale-100",
            "leave-to-class": "transform opacity-0 scale-95"
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
                  value: _vm.open,
                  expression: "open"
                }
              ],
              staticClass: "absolute z-50 mt-2 rounded-md shadow-lg",
              class: [_vm.widthClass, _vm.alignmentClasses],
              staticStyle: { display: "none" }
            },
            [
              _c(
                "div",
                {
                  staticClass:
                    "rounded-md ring-1 ring-black ring-opacity-5 dark:bg-gray-800",
                  class: _vm.contentClasses
                },
                [_vm._t("content")],
                2
              )
            ]
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

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/DropdownLink.vue?vue&type=template&id=1114e65f&":
/*!***********************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/DropdownLink.vue?vue&type=template&id=1114e65f& ***!
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
  return _c(
    "div",
    [
      _vm.as == "button"
        ? _c(
            "button",
            {
              staticClass:
                "block w-full px-4 py-2 text-sm leading-5 text-gray-700 dark:text-gray-400 text-left hover:bg-cool-gray-100 dark:hover:bg-cool-gray-900 focus:outline-none focus:bg-cool-gray-100 dark:focus:bg-cool-gray-900 transition duration-150 ease-in-out",
              class: _vm.btnClass,
              attrs: { type: "submit" }
            },
            [_vm._t("default")],
            2
          )
        : _c(
            "inertia-link",
            {
              staticClass:
                "block px-4 py-2 text-sm leading-5 text-gray-700 dark:text-gray-400 hover:bg-cool-gray-100 dark:hover:bg-cool-gray-900 focus:outline-none focus:bg-cool-gray-100 dark:focus:bg-cool-gray-900 transition duration-150 ease-in-out",
              class: _vm.btnClass,
              attrs: { href: _vm.href }
            },
            [_vm._t("default")],
            2
          )
    ],
    1
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/Modal.vue?vue&type=template&id=64f7dca9&":
/*!****************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/Modal.vue?vue&type=template&id=64f7dca9& ***!
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
    "portal",
    { attrs: { to: "modal" } },
    [
      _c("transition", { attrs: { "leave-active-class": "duration-200" } }, [
        _c(
          "div",
          {
            directives: [
              {
                name: "show",
                rawName: "v-show",
                value: _vm.show,
                expression: "show"
              }
            ],
            staticClass: "fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0"
          },
          [
            _c(
              "transition",
              {
                attrs: {
                  "enter-active-class": "ease-out duration-300",
                  "enter-class": "opacity-0",
                  "enter-to-class": "opacity-100",
                  "leave-active-class": "ease-in duration-200",
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
                        value: _vm.show,
                        expression: "show"
                      }
                    ],
                    staticClass: "fixed inset-0 transform transition-all",
                    on: { click: _vm.close }
                  },
                  [
                    _c("div", {
                      staticClass: "absolute inset-0 bg-gray-500 opacity-75"
                    })
                  ]
                )
              ]
            ),
            _vm._v(" "),
            _c(
              "transition",
              {
                attrs: {
                  "enter-active-class": "ease-out duration-300",
                  "enter-class":
                    "opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95",
                  "enter-to-class": "opacity-100 translate-y-0 sm:scale-100",
                  "leave-active-class": "ease-in duration-200",
                  "leave-class": "opacity-100 translate-y-0 sm:scale-100",
                  "leave-to-class":
                    "opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
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
                        value: _vm.show,
                        expression: "show"
                      }
                    ],
                    staticClass:
                      "border-t-8 border-gray-800 mb-6 bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full sm:mx-auto",
                    class: _vm.maxWidthClass
                  },
                  [_vm._t("default")],
                  2
                )
              ]
            )
          ],
          1
        )
      ])
    ],
    1
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/NavLink.vue?vue&type=template&id=1719168e&":
/*!******************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/NavLink.vue?vue&type=template&id=1719168e& ***!
  \******************************************************************************************************************************************************************************************************************/
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
    "inertia-link",
    { class: _vm.classes, attrs: { href: _vm.href } },
    [_vm._t("default")],
    2
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/ResponsiveNavLink.vue?vue&type=template&id=c1e95d36&":
/*!****************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/ResponsiveNavLink.vue?vue&type=template&id=c1e95d36& ***!
  \****************************************************************************************************************************************************************************************************************************/
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
      _vm.as == "button"
        ? _c(
            "button",
            { staticClass: "w-full text-left", class: _vm.classes },
            [_vm._t("default")],
            2
          )
        : _c(
            "inertia-link",
            { class: _vm.classes, attrs: { href: _vm.href } },
            [_vm._t("default")],
            2
          )
    ],
    1
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/SecondaryButton.vue?vue&type=template&id=8dd9837c&":
/*!**************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/SecondaryButton.vue?vue&type=template&id=8dd9837c& ***!
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
  return _c(
    "button",
    {
      staticClass:
        "inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:text-gray-800 active:bg-gray-50 transition ease-in-out duration-150 dark:bg-cool-gray-700 dark:text-gray-200 dark:border-gray-800 dark:hover:text-white dark:hover:bg-cool-gray-600",
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

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/SectionBorder.vue?vue&type=template&id=2661c926&":
/*!************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/SectionBorder.vue?vue&type=template&id=2661c926& ***!
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
  return _vm._m(0)
}
var staticRenderFns = [
  function() {
    var _vm = this
    var _h = _vm.$createElement
    var _c = _vm._self._c || _h
    return _c("div", { staticClass: "hidden sm:block" }, [
      _c("div", { staticClass: "py-8" }, [
        _c("div", {
          staticClass: "border-t border-gray-200 dark:border-cool-gray-700"
        })
      ])
    ])
  }
]
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/SidebarLink.vue?vue&type=template&id=7f9d081c&":
/*!**********************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Jetstream/SidebarLink.vue?vue&type=template&id=7f9d081c& ***!
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
    { staticClass: "flex items-center p-3" },
    [
      _c("inertia-link", { class: _vm.classes, attrs: { href: _vm.href } }, [
        _c("span", { staticClass: "mr-2" }, [_vm._t("icon")], 2),
        _vm._v(" "),
        _c("span", [_vm._t("default")], 2)
      ])
    ],
    1
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Layouts/AppLayout.vue?vue&type=template&id=5663af57&":
/*!******************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Layouts/AppLayout.vue?vue&type=template&id=5663af57& ***!
  \******************************************************************************************************************************************************************************************************************/
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
      _c("jet-banner"),
      _vm._v(" "),
      _c("toast", {
        attrs: {
          toast: _vm.$page.props.toast,
          popstate: _vm.$page.props.popstate
        }
      }),
      _vm._v(" "),
      _vm.$page.props.isImpersonating
        ? _c(
            "inertia-link",
            {
              directives: [{ name: "tippy", rawName: "v-tippy" }],
              staticClass:
                "flex fixed bottom-4 right-4 rounded-full bg-red-500 text-white p-2 hover:bg-red-700",
              attrs: {
                title: "Leave Impersonation",
                as: "a",
                href: _vm.route("admin.impersonate.leave")
              }
            },
            [_c("icon", { staticClass: "h-8 w-8", attrs: { name: "ban" } })],
            1
          )
        : _vm._e(),
      _vm._v(" "),
      _c(
        "div",
        {
          staticClass: "min-h-screen",
          class: { "border-4 border-red-500": _vm.$page.props.isImpersonating }
        },
        [
          _c("nav", { staticClass: "bg-white dark:bg-cool-gray-800 shadow" }, [
            _c(
              "div",
              { staticClass: "max-w-11xl mx-auto px-4 md:px-6 lg:px-8" },
              [
                _c(
                  "div",
                  { staticClass: "flex justify-between h-14 font-semibold" },
                  [
                    _c("div", { staticClass: "flex" }, [
                      _c(
                        "div",
                        { staticClass: "flex-shrink-0 flex items-center" },
                        [
                          _c(
                            "inertia-link",
                            { attrs: { href: _vm.route("home") } },
                            [
                              _c("jet-application-mark", {
                                staticClass: "block h-9 w-auto"
                              })
                            ],
                            1
                          ),
                          _vm._v(" "),
                          _vm.canShowAdminSidebar
                            ? _c(
                                "button",
                                {
                                  directives: [
                                    { name: "tippy", rawName: "v-tippy" }
                                  ],
                                  staticClass: "ml-2 focus:outline-none",
                                  attrs: {
                                    content: "Administration Section",
                                    "aria-label": "Open Menu"
                                  },
                                  on: { click: _vm.adminDrawer }
                                },
                                [
                                  _c(
                                    "svg",
                                    {
                                      staticClass:
                                        "w-6 h-6 text-gray-300 dark:text-gray-600",
                                      attrs: {
                                        xmlns: "http://www.w3.org/2000/svg",
                                        fill: "none",
                                        viewBox: "0 0 24 24",
                                        stroke: "currentColor"
                                      }
                                    },
                                    [
                                      _c("path", {
                                        attrs: {
                                          "stroke-linecap": "round",
                                          "stroke-linejoin": "round",
                                          "stroke-width": "2",
                                          d: "M4 6h16M4 12h16M4 18h16"
                                        }
                                      })
                                    ]
                                  )
                                ]
                              )
                            : _vm._e()
                        ],
                        1
                      ),
                      _vm._v(" "),
                      _c(
                        "div",
                        {
                          staticClass:
                            "hidden space-x-8 md:-my-px md:ml-10 md:flex"
                        },
                        [
                          _c(
                            "jet-nav-link",
                            {
                              attrs: {
                                href: _vm.route("home"),
                                active: _vm.route().current("home")
                              }
                            },
                            [_vm._v("\n                Home\n              ")]
                          ),
                          _vm._v(" "),
                          _c(
                            "jet-nav-link",
                            {
                              attrs: {
                                href: _vm.route("player.index"),
                                active: _vm.route().current("player.index")
                              }
                            },
                            [
                              _vm._v(
                                "\n                Statistics\n              "
                              )
                            ]
                          ),
                          _vm._v(" "),
                          _c(
                            "jet-nav-link",
                            {
                              attrs: {
                                href: _vm.route("poll.index"),
                                active: _vm.route().current("poll.index")
                              }
                            },
                            [_vm._v("\n                Polls\n              ")]
                          ),
                          _vm._v(" "),
                          _c(
                            "div",
                            {
                              staticClass:
                                "inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm leading-5 text-gray-500 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out"
                            },
                            [
                              _c("jet-dropdown", {
                                attrs: { align: "right", width: "48" },
                                scopedSlots: _vm._u([
                                  {
                                    key: "trigger",
                                    fn: function() {
                                      return [
                                        _c(
                                          "span",
                                          {
                                            staticClass:
                                              "inline-flex rounded-md"
                                          },
                                          [
                                            _c(
                                              "button",
                                              {
                                                staticClass:
                                                  "inline-flex font-semibold items-center py-2 border border-transparent text-sm leading-4 rounded-md text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 focus:outline-none transition ease-in-out duration-150",
                                                attrs: { type: "button" }
                                              },
                                              [
                                                _vm._v(
                                                  "\n                        Others\n                        "
                                                ),
                                                _c(
                                                  "svg",
                                                  {
                                                    staticClass:
                                                      "ml-2 -mr-0.5 h-4 w-4",
                                                    attrs: {
                                                      xmlns:
                                                        "http://www.w3.org/2000/svg",
                                                      viewBox: "0 0 20 20",
                                                      fill: "currentColor"
                                                    }
                                                  },
                                                  [
                                                    _c("path", {
                                                      attrs: {
                                                        "fill-rule": "evenodd",
                                                        d:
                                                          "M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z",
                                                        "clip-rule": "evenodd"
                                                      }
                                                    })
                                                  ]
                                                )
                                              ]
                                            )
                                          ]
                                        )
                                      ]
                                    },
                                    proxy: true
                                  },
                                  {
                                    key: "content",
                                    fn: function() {
                                      return [
                                        _c(
                                          "jet-dropdown-link",
                                          {
                                            staticClass: "text-sm",
                                            attrs: {
                                              href: _vm.route("news.index")
                                            }
                                          },
                                          [
                                            _vm._v(
                                              "\n                      News\n                    "
                                            )
                                          ]
                                        ),
                                        _vm._v(" "),
                                        _c(
                                          "jet-dropdown-link",
                                          {
                                            staticClass: "text-sm",
                                            attrs: {
                                              href: _vm.route("staff.index")
                                            }
                                          },
                                          [
                                            _vm._v(
                                              "\n                      Staff Members\n                    "
                                            )
                                          ]
                                        ),
                                        _vm._v(" "),
                                        _vm._l(
                                          _vm.$page.props.customPageList,
                                          function(customPage) {
                                            return _vm.$page.props
                                              .customPageList
                                              ? _c(
                                                  "jet-dropdown-link",
                                                  {
                                                    key: customPage.id,
                                                    attrs: {
                                                      href: _vm.route(
                                                        "custom-page.show",
                                                        customPage.path
                                                      )
                                                    }
                                                  },
                                                  [
                                                    _vm._v(
                                                      "\n                      " +
                                                        _vm._s(
                                                          customPage.title
                                                        ) +
                                                        "\n                    "
                                                    )
                                                  ]
                                                )
                                              : _vm._e()
                                          }
                                        ),
                                        _vm._v(" "),
                                        _c(
                                          "jet-dropdown-link",
                                          {
                                            staticClass: "text-sm",
                                            attrs: {
                                              href: _vm.route("features.list")
                                            }
                                          },
                                          [
                                            _vm._v(
                                              "\n                      Features\n                    "
                                            )
                                          ]
                                        )
                                      ]
                                    },
                                    proxy: true
                                  }
                                ])
                              })
                            ],
                            1
                          )
                        ],
                        1
                      )
                    ]),
                    _vm._v(" "),
                    _c(
                      "div",
                      { staticClass: "hidden md:flex md:items-center md:ml-6" },
                      [
                        _vm.$page.props.user ? _c("search") : _vm._e(),
                        _vm._v(" "),
                        _vm.$page.props.user
                          ? _c("notification-dropdown")
                          : _vm._e(),
                        _vm._v(" "),
                        _c(
                          "div",
                          { staticClass: "ml-3 relative" },
                          [
                            _vm.$page.props.jetstream.hasTeamFeatures
                              ? _c("jet-dropdown", {
                                  attrs: { align: "right", width: "60" },
                                  scopedSlots: _vm._u(
                                    [
                                      {
                                        key: "trigger",
                                        fn: function() {
                                          return [
                                            _c(
                                              "span",
                                              {
                                                staticClass:
                                                  "inline-flex rounded-md"
                                              },
                                              [
                                                _c(
                                                  "button",
                                                  {
                                                    staticClass:
                                                      "inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:bg-gray-50 hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150",
                                                    attrs: { type: "button" }
                                                  },
                                                  [
                                                    _vm._v(
                                                      "\n                      " +
                                                        _vm._s(
                                                          _vm.$page.props.user
                                                            .current_team.name
                                                        ) +
                                                        "\n\n                      "
                                                    ),
                                                    _c(
                                                      "svg",
                                                      {
                                                        staticClass:
                                                          "ml-2 -mr-0.5 h-4 w-4",
                                                        attrs: {
                                                          xmlns:
                                                            "http://www.w3.org/2000/svg",
                                                          viewBox: "0 0 20 20",
                                                          fill: "currentColor"
                                                        }
                                                      },
                                                      [
                                                        _c("path", {
                                                          attrs: {
                                                            "fill-rule":
                                                              "evenodd",
                                                            d:
                                                              "M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z",
                                                            "clip-rule":
                                                              "evenodd"
                                                          }
                                                        })
                                                      ]
                                                    )
                                                  ]
                                                )
                                              ]
                                            )
                                          ]
                                        },
                                        proxy: true
                                      },
                                      {
                                        key: "content",
                                        fn: function() {
                                          return [
                                            _c(
                                              "div",
                                              { staticClass: "w-60" },
                                              [
                                                _vm.$page.props.jetstream
                                                  .hasTeamFeatures
                                                  ? [
                                                      _c(
                                                        "div",
                                                        {
                                                          staticClass:
                                                            "block px-4 py-2 text-xs text-gray-400"
                                                        },
                                                        [
                                                          _vm._v(
                                                            "\n                        Manage Team\n                      "
                                                          )
                                                        ]
                                                      ),
                                                      _vm._v(" "),
                                                      _c(
                                                        "jet-dropdown-link",
                                                        {
                                                          attrs: {
                                                            href: _vm.route(
                                                              "teams.show",
                                                              _vm.$page.props
                                                                .user
                                                                .current_team
                                                            )
                                                          }
                                                        },
                                                        [
                                                          _vm._v(
                                                            "\n                        Team Settings\n                      "
                                                          )
                                                        ]
                                                      ),
                                                      _vm._v(" "),
                                                      _vm.$page.props.jetstream
                                                        .canCreateTeams
                                                        ? _c(
                                                            "jet-dropdown-link",
                                                            {
                                                              attrs: {
                                                                href: _vm.route(
                                                                  "teams.create"
                                                                )
                                                              }
                                                            },
                                                            [
                                                              _vm._v(
                                                                "\n                        Create New Team\n                      "
                                                              )
                                                            ]
                                                          )
                                                        : _vm._e(),
                                                      _vm._v(" "),
                                                      _c("div", {
                                                        staticClass:
                                                          "border-t border-gray-100"
                                                      }),
                                                      _vm._v(" "),
                                                      _c(
                                                        "div",
                                                        {
                                                          staticClass:
                                                            "block px-4 py-2 text-xs text-gray-400"
                                                        },
                                                        [
                                                          _vm._v(
                                                            "\n                        Switch Teams\n                      "
                                                          )
                                                        ]
                                                      ),
                                                      _vm._v(" "),
                                                      _vm._l(
                                                        _vm.$page.props.user
                                                          .all_teams,
                                                        function(team) {
                                                          return [
                                                            _c(
                                                              "form",
                                                              {
                                                                key: team.id,
                                                                on: {
                                                                  submit: function(
                                                                    $event
                                                                  ) {
                                                                    $event.preventDefault()
                                                                    return _vm.switchToTeam(
                                                                      team
                                                                    )
                                                                  }
                                                                }
                                                              },
                                                              [
                                                                _c(
                                                                  "jet-dropdown-link",
                                                                  {
                                                                    attrs: {
                                                                      as:
                                                                        "button"
                                                                    }
                                                                  },
                                                                  [
                                                                    _c(
                                                                      "div",
                                                                      {
                                                                        staticClass:
                                                                          "flex items-center"
                                                                      },
                                                                      [
                                                                        team.id ==
                                                                        _vm
                                                                          .$page
                                                                          .props
                                                                          .user
                                                                          .current_team_id
                                                                          ? _c(
                                                                              "svg",
                                                                              {
                                                                                staticClass:
                                                                                  "mr-2 h-5 w-5 text-green-400",
                                                                                attrs: {
                                                                                  fill:
                                                                                    "none",
                                                                                  "stroke-linecap":
                                                                                    "round",
                                                                                  "stroke-linejoin":
                                                                                    "round",
                                                                                  "stroke-width":
                                                                                    "2",
                                                                                  stroke:
                                                                                    "currentColor",
                                                                                  viewBox:
                                                                                    "0 0 24 24"
                                                                                }
                                                                              },
                                                                              [
                                                                                _c(
                                                                                  "path",
                                                                                  {
                                                                                    attrs: {
                                                                                      d:
                                                                                        "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                                                                    }
                                                                                  }
                                                                                )
                                                                              ]
                                                                            )
                                                                          : _vm._e(),
                                                                        _vm._v(
                                                                          " "
                                                                        ),
                                                                        _c(
                                                                          "div",
                                                                          [
                                                                            _vm._v(
                                                                              _vm._s(
                                                                                team.name
                                                                              )
                                                                            )
                                                                          ]
                                                                        )
                                                                      ]
                                                                    )
                                                                  ]
                                                                )
                                                              ],
                                                              1
                                                            )
                                                          ]
                                                        }
                                                      )
                                                    ]
                                                  : _vm._e()
                                              ],
                                              2
                                            )
                                          ]
                                        },
                                        proxy: true
                                      }
                                    ],
                                    null,
                                    false,
                                    978515698
                                  )
                                })
                              : _vm._e()
                          ],
                          1
                        ),
                        _vm._v(" "),
                        _vm.$page.props.user
                          ? _c(
                              "div",
                              {
                                staticClass:
                                  "ml-3 relative font-semibold dark:text-gray-400"
                              },
                              [
                                _c("jet-dropdown", {
                                  attrs: { align: "right", width: "48" },
                                  scopedSlots: _vm._u(
                                    [
                                      {
                                        key: "trigger",
                                        fn: function() {
                                          return [
                                            _vm.$page.props.jetstream
                                              .managesProfilePhotos
                                              ? _c(
                                                  "button",
                                                  {
                                                    staticClass:
                                                      "font-semibold flex items-center text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 dark:focus:border-cool-gray-700 transition duration-150 ease-in-out"
                                                  },
                                                  [
                                                    _vm._v(
                                                      "\n                    " +
                                                        _vm._s(
                                                          _vm.$page.props.user
                                                            .name
                                                        ) +
                                                        "\n                    "
                                                    ),
                                                    _c("img", {
                                                      staticClass:
                                                        "h-8 w-8 ml-0.5 rounded-full object-cover",
                                                      attrs: {
                                                        src:
                                                          _vm.$page.props.user
                                                            .profile_photo_url,
                                                        alt:
                                                          _vm.$page.props.user
                                                            .name
                                                      }
                                                    })
                                                  ]
                                                )
                                              : _c(
                                                  "span",
                                                  {
                                                    staticClass:
                                                      "inline-flex rounded-md"
                                                  },
                                                  [
                                                    _c(
                                                      "button",
                                                      {
                                                        staticClass:
                                                          "font-semibold inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150",
                                                        attrs: {
                                                          type: "button"
                                                        }
                                                      },
                                                      [
                                                        _vm._v(
                                                          "\n                      " +
                                                            _vm._s(
                                                              _vm.$page.props
                                                                .user.name
                                                            ) +
                                                            "\n\n                      "
                                                        ),
                                                        _c(
                                                          "svg",
                                                          {
                                                            staticClass:
                                                              "ml-2 -mr-0.5 h-4 w-4",
                                                            attrs: {
                                                              xmlns:
                                                                "http://www.w3.org/2000/svg",
                                                              viewBox:
                                                                "0 0 20 20",
                                                              fill:
                                                                "currentColor"
                                                            }
                                                          },
                                                          [
                                                            _c("path", {
                                                              attrs: {
                                                                "fill-rule":
                                                                  "evenodd",
                                                                d:
                                                                  "M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z",
                                                                "clip-rule":
                                                                  "evenodd"
                                                              }
                                                            })
                                                          ]
                                                        )
                                                      ]
                                                    )
                                                  ]
                                                )
                                          ]
                                        },
                                        proxy: true
                                      },
                                      {
                                        key: "content",
                                        fn: function() {
                                          return [
                                            _c(
                                              "div",
                                              {
                                                staticClass:
                                                  "block px-4 py-2 text-xs text-gray-400"
                                              },
                                              [
                                                _vm._v(
                                                  "\n                    Manage Account\n                  "
                                                )
                                              ]
                                            ),
                                            _vm._v(" "),
                                            _c(
                                              "jet-dropdown-link",
                                              {
                                                staticClass: "text-sm",
                                                attrs: {
                                                  href: _vm.route(
                                                    "user.public.get",
                                                    _vm.$page.props.user
                                                      .username
                                                  )
                                                }
                                              },
                                              [
                                                _vm._v(
                                                  "\n                    Public Profile\n                  "
                                                )
                                              ]
                                            ),
                                            _vm._v(" "),
                                            _c(
                                              "jet-dropdown-link",
                                              {
                                                attrs: {
                                                  href: _vm.route(
                                                    "profile.show"
                                                  )
                                                }
                                              },
                                              [
                                                _vm._v(
                                                  "\n                    Edit Profile\n                  "
                                                )
                                              ]
                                            ),
                                            _vm._v(" "),
                                            _c(
                                              "jet-dropdown-link",
                                              {
                                                attrs: {
                                                  href: _vm.route(
                                                    "linked-player.list"
                                                  )
                                                }
                                              },
                                              [
                                                _vm._v(
                                                  "\n                    Linked Players\n                  "
                                                )
                                              ]
                                            ),
                                            _vm._v(" "),
                                            _vm.$page.props.jetstream
                                              .hasApiFeatures
                                              ? _c(
                                                  "jet-dropdown-link",
                                                  {
                                                    attrs: {
                                                      href: _vm.route(
                                                        "api-tokens.index"
                                                      )
                                                    }
                                                  },
                                                  [
                                                    _vm._v(
                                                      "\n                    API Tokens\n                  "
                                                    )
                                                  ]
                                                )
                                              : _vm._e(),
                                            _vm._v(" "),
                                            _c("div", {
                                              staticClass:
                                                "border-t border-gray-100 dark:border-cool-gray-700"
                                            }),
                                            _vm._v(" "),
                                            _c(
                                              "form",
                                              {
                                                on: {
                                                  submit: function($event) {
                                                    $event.preventDefault()
                                                    return _vm.logout.apply(
                                                      null,
                                                      arguments
                                                    )
                                                  }
                                                }
                                              },
                                              [
                                                _c(
                                                  "jet-dropdown-link",
                                                  {
                                                    attrs: {
                                                      as: "button",
                                                      "btn-class":
                                                        "font-semibold"
                                                    }
                                                  },
                                                  [
                                                    _vm._v(
                                                      "\n                      Logout\n                    "
                                                    )
                                                  ]
                                                )
                                              ],
                                              1
                                            )
                                          ]
                                        },
                                        proxy: true
                                      }
                                    ],
                                    null,
                                    false,
                                    70818752
                                  )
                                })
                              ],
                              1
                            )
                          : _vm._e(),
                        _vm._v(" "),
                        _vm.$page.props.user
                          ? _c("color-theme-toggle", {
                              staticClass:
                                "hidden space-x-8 md:ml-8 md:flex justify-center items-center"
                            })
                          : _vm._e()
                      ],
                      1
                    ),
                    _vm._v(" "),
                    !_vm.$page.props.user
                      ? _c(
                          "div",
                          { staticClass: "flex" },
                          [
                            !_vm.$page.props.user
                              ? _c("search", {
                                  staticClass: "mt-2 hidden md:block"
                                })
                              : _vm._e(),
                            _vm._v(" "),
                            _c(
                              "div",
                              {
                                staticClass:
                                  "hidden space-x-8 md:-my-px md:ml-8 md:flex"
                              },
                              [
                                _c(
                                  "jet-nav-link",
                                  {
                                    attrs: {
                                      href: _vm.route("login"),
                                      active: _vm.route().current("login")
                                    }
                                  },
                                  [
                                    _vm._v(
                                      "\n                Login\n              "
                                    )
                                  ]
                                )
                              ],
                              1
                            ),
                            _vm._v(" "),
                            _c(
                              "div",
                              {
                                staticClass:
                                  "hidden space-x-8 md:-my-px md:ml-8 md:flex"
                              },
                              [
                                _c(
                                  "jet-nav-link",
                                  {
                                    attrs: {
                                      href: _vm.route("register"),
                                      active: _vm.route().current("register")
                                    }
                                  },
                                  [
                                    _vm._v(
                                      "\n                Register\n              "
                                    )
                                  ]
                                )
                              ],
                              1
                            ),
                            _vm._v(" "),
                            _c("color-theme-toggle", {
                              staticClass:
                                "hidden md:flex space-x-8 md:ml-8 justify-center items-center"
                            })
                          ],
                          1
                        )
                      : _vm._e(),
                    _vm._v(" "),
                    _c(
                      "div",
                      { staticClass: "-mr-2 flex items-center md:hidden" },
                      [
                        _c("color-theme-toggle", {
                          staticClass:
                            "space-x-8 md:ml-8 flex md:hidden justify-center items-center"
                        }),
                        _vm._v(" "),
                        _c(
                          "button",
                          {
                            staticClass:
                              "inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-cool-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-cool-gray-900 focus:text-gray-500 dark:focus:text-gray-200 transition duration-150 ease-in-out",
                            on: {
                              click: function($event) {
                                _vm.showingNavigationDropdown = !_vm.showingNavigationDropdown
                              }
                            }
                          },
                          [
                            _c(
                              "svg",
                              {
                                staticClass: "h-6 w-6",
                                attrs: {
                                  stroke: "currentColor",
                                  fill: "none",
                                  viewBox: "0 0 24 24"
                                }
                              },
                              [
                                _c("path", {
                                  class: {
                                    hidden: _vm.showingNavigationDropdown,
                                    "inline-flex": !_vm.showingNavigationDropdown
                                  },
                                  attrs: {
                                    "stroke-linecap": "round",
                                    "stroke-linejoin": "round",
                                    "stroke-width": "2",
                                    d: "M4 6h16M4 12h16M4 18h16"
                                  }
                                }),
                                _vm._v(" "),
                                _c("path", {
                                  class: {
                                    hidden: !_vm.showingNavigationDropdown,
                                    "inline-flex": _vm.showingNavigationDropdown
                                  },
                                  attrs: {
                                    "stroke-linecap": "round",
                                    "stroke-linejoin": "round",
                                    "stroke-width": "2",
                                    d: "M6 18L18 6M6 6l12 12"
                                  }
                                })
                              ]
                            )
                          ]
                        )
                      ],
                      1
                    )
                  ]
                )
              ]
            ),
            _vm._v(" "),
            _c(
              "div",
              {
                staticClass: "md:hidden",
                class: {
                  block: _vm.showingNavigationDropdown,
                  hidden: !_vm.showingNavigationDropdown
                }
              },
              [
                _c(
                  "div",
                  { staticClass: "pt-2 pb-3 space-y-1" },
                  [
                    _c(
                      "jet-responsive-nav-link",
                      {
                        attrs: {
                          href: _vm.route("home"),
                          active: _vm.route().current("home")
                        }
                      },
                      [_vm._v("\n            Home\n          ")]
                    ),
                    _vm._v(" "),
                    _c(
                      "jet-responsive-nav-link",
                      {
                        attrs: {
                          href: _vm.route("player.index"),
                          active: _vm.route().current("player.index")
                        }
                      },
                      [_vm._v("\n            Statistics\n          ")]
                    ),
                    _vm._v(" "),
                    _c(
                      "jet-responsive-nav-link",
                      {
                        attrs: {
                          href: _vm.route("poll.index"),
                          active: _vm.route().current("poll.index")
                        }
                      },
                      [_vm._v("\n            Polls\n          ")]
                    ),
                    _vm._v(" "),
                    _c(
                      "jet-responsive-nav-link",
                      {
                        attrs: {
                          href: _vm.route("news.index"),
                          active: _vm.route().current("news.index")
                        }
                      },
                      [_vm._v("\n            News\n          ")]
                    ),
                    _vm._v(" "),
                    _c(
                      "jet-responsive-nav-link",
                      {
                        attrs: {
                          href: _vm.route("staff.index"),
                          active: _vm.route().current("staff.index")
                        }
                      },
                      [_vm._v("\n            Staff Members\n          ")]
                    ),
                    _vm._v(" "),
                    _vm._l(_vm.$page.props.customPageList, function(
                      customPage
                    ) {
                      return _vm.$page.props.customPageList
                        ? _c(
                            "jet-responsive-nav-link",
                            {
                              key: customPage.id,
                              attrs: {
                                href: _vm.route(
                                  "custom-page.show",
                                  customPage.path
                                )
                              }
                            },
                            [
                              _vm._v(
                                "\n            " +
                                  _vm._s(customPage.title) +
                                  "\n          "
                              )
                            ]
                          )
                        : _vm._e()
                    }),
                    _vm._v(" "),
                    _c(
                      "jet-responsive-nav-link",
                      {
                        attrs: {
                          href: _vm.route("features.list"),
                          active: _vm.route().current("features.list")
                        }
                      },
                      [_vm._v("\n            Features\n          ")]
                    ),
                    _vm._v(" "),
                    !_vm.$page.props.user
                      ? [
                          _c(
                            "jet-responsive-nav-link",
                            {
                              attrs: {
                                href: _vm.route("login"),
                                active: _vm.route().current("login")
                              }
                            },
                            [_vm._v("\n              Login\n            ")]
                          ),
                          _vm._v(" "),
                          _c(
                            "jet-responsive-nav-link",
                            {
                              attrs: {
                                href: _vm.route("register"),
                                active: _vm.route().current("register")
                              }
                            },
                            [_vm._v("\n              Register\n            ")]
                          )
                        ]
                      : _vm._e()
                  ],
                  2
                ),
                _vm._v(" "),
                _vm.$page.props.user
                  ? _c(
                      "div",
                      {
                        staticClass:
                          "pt-4 pb-1 border-t border-gray-200 dark:border-gray-700"
                      },
                      [
                        _c("div", { staticClass: "flex items-center px-4" }, [
                          _vm.$page.props.jetstream.managesProfilePhotos
                            ? _c("div", { staticClass: "flex-shrink-0 mr-3" }, [
                                _c("img", {
                                  staticClass:
                                    "h-10 w-10 rounded-full object-cover",
                                  attrs: {
                                    src: _vm.$page.props.user.profile_photo_url,
                                    alt: _vm.$page.props.user.name
                                  }
                                })
                              ])
                            : _vm._e(),
                          _vm._v(" "),
                          _c("div", [
                            _c(
                              "div",
                              {
                                staticClass:
                                  "font-medium text-base text-gray-800 dark:text-gray-300"
                              },
                              [
                                _vm._v(
                                  "\n                " +
                                    _vm._s(_vm.$page.props.user.name) +
                                    "\n              "
                                )
                              ]
                            ),
                            _vm._v(" "),
                            _c(
                              "div",
                              {
                                staticClass: "font-medium text-sm text-gray-500"
                              },
                              [
                                _vm._v(
                                  "\n                " +
                                    _vm._s(_vm.$page.props.user.email) +
                                    "\n              "
                                )
                              ]
                            )
                          ])
                        ]),
                        _vm._v(" "),
                        _c(
                          "div",
                          { staticClass: "mt-3 space-y-1" },
                          [
                            _c(
                              "jet-responsive-nav-link",
                              {
                                attrs: {
                                  href: _vm.route(
                                    "user.public.get",
                                    _vm.$page.props.user.username
                                  ),
                                  active: _vm.route().current("user.public.get")
                                }
                              },
                              [
                                _vm._v(
                                  "\n              Public Profile\n            "
                                )
                              ]
                            ),
                            _vm._v(" "),
                            _c(
                              "jet-responsive-nav-link",
                              {
                                attrs: {
                                  href: _vm.route("profile.show"),
                                  active: _vm.route().current("profile.show")
                                }
                              },
                              [
                                _vm._v(
                                  "\n              Edit Profile\n            "
                                )
                              ]
                            ),
                            _vm._v(" "),
                            _c(
                              "jet-responsive-nav-link",
                              {
                                attrs: {
                                  href: _vm.route("linked-player.list"),
                                  active: _vm
                                    .route()
                                    .current("linked-player.list")
                                }
                              },
                              [
                                _vm._v(
                                  "\n              Linked Players\n            "
                                )
                              ]
                            ),
                            _vm._v(" "),
                            _c(
                              "jet-responsive-nav-link",
                              {
                                attrs: {
                                  href: _vm.route("notification.index"),
                                  active: _vm
                                    .route()
                                    .current("notification.index")
                                }
                              },
                              [
                                _vm._v(
                                  "\n              Notifications\n            "
                                )
                              ]
                            ),
                            _vm._v(" "),
                            _vm.$page.props.jetstream.hasApiFeatures
                              ? _c(
                                  "jet-responsive-nav-link",
                                  {
                                    attrs: {
                                      href: _vm.route("api-tokens.index"),
                                      active: _vm
                                        .route()
                                        .current("api-tokens.index")
                                    }
                                  },
                                  [
                                    _vm._v(
                                      "\n              API Tokens\n            "
                                    )
                                  ]
                                )
                              : _vm._e(),
                            _vm._v(" "),
                            _c(
                              "form",
                              {
                                attrs: { method: "POST" },
                                on: {
                                  submit: function($event) {
                                    $event.preventDefault()
                                    return _vm.logout.apply(null, arguments)
                                  }
                                }
                              },
                              [
                                _c(
                                  "jet-responsive-nav-link",
                                  { attrs: { as: "button" } },
                                  [
                                    _vm._v(
                                      "\n                Logout\n              "
                                    )
                                  ]
                                )
                              ],
                              1
                            )
                          ],
                          1
                        )
                      ]
                    )
                  : _vm._e(),
                _vm._v(" "),
                _c(
                  "div",
                  { staticClass: "flex just-center pb-2" },
                  [_c("search", { staticClass: "inline-block md:hidden" })],
                  1
                )
              ]
            )
          ]),
          _vm._v(" "),
          _vm.canShowAdminSidebar
            ? _c(
                "aside",
                {
                  staticClass:
                    "transform top-0 left-0 w-72 bg-white dark:bg-cool-gray-800 fixed h-full overflow-auto ease-in-out transition-all duration-300 z-30 shadow",
                  class: _vm.isAdminSidebarOpen
                    ? "translate-x-0"
                    : "-translate-x-full"
                },
                [
                  _c(
                    "span",
                    {
                      staticClass:
                        "flex w-full items-center p-4 px-7 border-b border-gray-200 dark:border-gray-700 h-14"
                    },
                    [
                      _c(
                        "inertia-link",
                        { attrs: { href: _vm.route("home") } },
                        [
                          _c("jet-application-mark", {
                            staticClass: "block h-9 w-auto"
                          })
                        ],
                        1
                      ),
                      _vm._v(" "),
                      _c(
                        "button",
                        {
                          staticClass: "ml-2 focus:outline-none",
                          attrs: { "aria-label": "Open Menu" },
                          on: { click: _vm.adminDrawer }
                        },
                        [_c("icon", { attrs: { name: "close" } })],
                        1
                      )
                    ],
                    1
                  ),
                  _vm._v(" "),
                  _vm.canWild("servers")
                    ? _c(
                        "jet-sidebar-link",
                        {
                          attrs: {
                            href: _vm.route("admin.server.index"),
                            active: _vm.route().current("admin.server.index")
                          }
                        },
                        [
                          _c(
                            "template",
                            { slot: "icon" },
                            [_c("icon", { attrs: { name: "server" } })],
                            1
                          ),
                          _vm._v("\n        Servers\n      ")
                        ],
                        2
                      )
                    : _vm._e(),
                  _vm._v(" "),
                  _vm.canWild("users")
                    ? _c(
                        "jet-sidebar-link",
                        {
                          attrs: {
                            href: _vm.route("admin.user.index"),
                            active: _vm.route().current("admin.user.index")
                          }
                        },
                        [
                          _c(
                            "template",
                            { slot: "icon" },
                            [
                              _c("icon", {
                                staticClass: "w-5 h-5",
                                attrs: { name: "users" }
                              })
                            ],
                            1
                          ),
                          _vm._v("\n        Users\n      ")
                        ],
                        2
                      )
                    : _vm._e(),
                  _vm._v(" "),
                  _vm.canWild("roles")
                    ? _c(
                        "jet-sidebar-link",
                        {
                          attrs: {
                            href: _vm.route("admin.role.index"),
                            active: _vm.route().current("admin.role.index")
                          }
                        },
                        [
                          _c(
                            "template",
                            { slot: "icon" },
                            [_c("icon", { attrs: { name: "shield-check" } })],
                            1
                          ),
                          _vm._v("\n        User Roles\n      ")
                        ],
                        2
                      )
                    : _vm._e(),
                  _vm._v(" "),
                  _vm.canWild("ranks")
                    ? _c(
                        "jet-sidebar-link",
                        {
                          attrs: {
                            href: _vm.route("admin.rank.index"),
                            active: _vm.route().current("admin.rank.index")
                          }
                        },
                        [
                          _c(
                            "template",
                            { slot: "icon" },
                            [_c("icon", { attrs: { name: "degree-hat" } })],
                            1
                          ),
                          _vm._v("\n        Player Ranks\n      ")
                        ],
                        2
                      )
                    : _vm._e(),
                  _vm._v(" "),
                  _vm.canWild("news")
                    ? _c(
                        "jet-sidebar-link",
                        {
                          attrs: {
                            href: _vm.route("admin.news.index"),
                            active: _vm.route().current("admin.news.index")
                          }
                        },
                        [
                          _c(
                            "template",
                            { slot: "icon" },
                            [_c("icon", { attrs: { name: "newspaper" } })],
                            1
                          ),
                          _vm._v("\n        News\n      ")
                        ],
                        2
                      )
                    : _vm._e(),
                  _vm._v(" "),
                  _vm.canWild("polls")
                    ? _c(
                        "jet-sidebar-link",
                        {
                          attrs: {
                            href: _vm.route("admin.poll.index"),
                            active: _vm.route().current("admin.poll.index")
                          }
                        },
                        [
                          _c(
                            "template",
                            { slot: "icon" },
                            [
                              _c("icon", {
                                staticClass: "h-5 w-5",
                                attrs: { name: "chart-pie" }
                              })
                            ],
                            1
                          ),
                          _vm._v("\n        Polls\n      ")
                        ],
                        2
                      )
                    : _vm._e(),
                  _vm._v(" "),
                  _vm.canWild("custom_pages")
                    ? _c(
                        "jet-sidebar-link",
                        {
                          attrs: {
                            href: _vm.route("admin.custom-page.index"),
                            active: _vm
                              .route()
                              .current("admin.custom-page.index")
                          }
                        },
                        [
                          _c(
                            "template",
                            { slot: "icon" },
                            [
                              _c("icon", {
                                staticClass: "h-5 w-5",
                                attrs: { name: "collection" }
                              })
                            ],
                            1
                          ),
                          _vm._v("\n        Custom Pages\n      ")
                        ],
                        2
                      )
                    : _vm._e(),
                  _vm._v(" "),
                  _vm.canWild("sessions")
                    ? _c(
                        "jet-sidebar-link",
                        {
                          attrs: {
                            href: _vm.route("admin.session.index"),
                            active: _vm.route().current("admin.session.index")
                          }
                        },
                        [
                          _c(
                            "template",
                            { slot: "icon" },
                            [
                              _c("icon", {
                                staticClass: "h-5 w-5",
                                attrs: { name: "finger-print" }
                              })
                            ],
                            1
                          ),
                          _vm._v("\n        User Sessions\n      ")
                        ],
                        2
                      )
                    : _vm._e(),
                  _vm._v(" "),
                  _vm.canWild("settings")
                    ? _c(
                        "jet-sidebar-link",
                        {
                          attrs: {
                            href: _vm.route("admin.setting.general.show"),
                            active: _vm
                              .route()
                              .current("admin.setting.general.show")
                          }
                        },
                        [
                          _c(
                            "template",
                            { slot: "icon" },
                            [_c("icon", { attrs: { name: "cog" } })],
                            1
                          ),
                          _vm._v("\n        Settings\n      ")
                        ],
                        2
                      )
                    : _vm._e()
                ],
                1
              )
            : _vm._e(),
          _vm._v(" "),
          _c("main", [_vm._t("default")], 2),
          _vm._v(" "),
          _c(
            "footer",
            { staticClass: "flex flex-col p-5 items-center justify-center" },
            [
              _c(
                "div",
                { staticClass: "text-sm text-gray-800 dark:text-gray-400" },
                [
                  _vm._v(
                    "\n        © " +
                      _vm._s(_vm.$page.props.generalSettings.site_name) +
                      " " +
                      _vm._s(new Date().getFullYear()) +
                      "\n      "
                  )
                ]
              )
            ]
          ),
          _vm._v(" "),
          _c("portal-target", { attrs: { name: "modal", multiple: "" } })
        ],
        1
      )
    ],
    1
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Pages/Admin/Rank/IndexRank.vue?vue&type=template&id=b72e4738&":
/*!***************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Pages/Admin/Rank/IndexRank.vue?vue&type=template&id=b72e4738& ***!
  \***************************************************************************************************************************************************************************************************************************/
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
    "app-layout",
    [
      _c("app-head", { attrs: { title: "Player Ranks Administration" } }),
      _vm._v(" "),
      _c(
        "div",
        { staticClass: "py-12 px-10 max-w-7xl mx-auto" },
        [
          _c("div", { staticClass: "flex justify-between mb-8" }, [
            _c(
              "h1",
              {
                staticClass:
                  "font-bold text-3xl text-gray-500 dark:text-gray-300"
              },
              [_vm._v("\n        Player Ranks\n      ")]
            ),
            _vm._v(" "),
            _c(
              "div",
              { staticClass: "flex" },
              [
                _vm.can("update ranks")
                  ? _c(
                      "inertia-link",
                      {
                        staticClass:
                          "mr-2 inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:shadow-outline-red transition ease-in-out duration-150",
                        attrs: {
                          method: "post",
                          as: "button",
                          href: _vm.route("admin.rank.reset")
                        }
                      },
                      [_vm._v("\n          Reset to Default Ranks\n        ")]
                    )
                  : _vm._e(),
                _vm._v(" "),
                _vm.can("create ranks")
                  ? _c(
                      "inertia-link",
                      {
                        staticClass:
                          "inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:shadow-outline-gray transition ease-in-out duration-150",
                        attrs: { href: _vm.route("admin.rank.create") }
                      },
                      [
                        _c("span", [_vm._v("Add New")]),
                        _vm._v(" "),
                        _c("span", { staticClass: "hidden md:inline" }, [
                          _vm._v(" Rank")
                        ])
                      ]
                    )
                  : _vm._e()
              ],
              1
            )
          ]),
          _vm._v(" "),
          _c("div", { staticClass: "flex flex-col" }, [
            _c(
              "div",
              { staticClass: "-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8" },
              [
                _c(
                  "div",
                  {
                    staticClass:
                      "py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8"
                  },
                  [
                    _c(
                      "div",
                      {
                        staticClass:
                          "shadow overflow-hidden border-b border-gray-200 dark:border-none sm:rounded-lg"
                      },
                      [
                        _c(
                          "table",
                          {
                            staticClass:
                              "min-w-full divide-y divide-gray-200 dark:divide-gray-700"
                          },
                          [
                            _c(
                              "thead",
                              {
                                staticClass:
                                  "bg-gray-50 dark:bg-cool-gray-800 text-gray-500 dark:text-gray-300"
                              },
                              [
                                _c("tr", [
                                  _c(
                                    "th",
                                    {
                                      staticClass:
                                        "px-3 py-3 text-left text-xs font-medium uppercase tracking-wider",
                                      attrs: { scope: "col" }
                                    },
                                    [
                                      _vm._v(
                                        "\n                    #\n                  "
                                      )
                                    ]
                                  ),
                                  _vm._v(" "),
                                  _c(
                                    "th",
                                    {
                                      staticClass:
                                        "px-6 py-3 text-left text-xs font-medium uppercase tracking-wider",
                                      attrs: { scope: "col" }
                                    },
                                    [
                                      _vm._v(
                                        "\n                    Name\n                  "
                                      )
                                    ]
                                  ),
                                  _vm._v(" "),
                                  _c(
                                    "th",
                                    {
                                      staticClass:
                                        "px-6 py-3 text-left text-xs font-medium uppercase tracking-wider",
                                      attrs: { scope: "col" }
                                    },
                                    [
                                      _vm._v(
                                        "\n                    PlayTime Needed\n                  "
                                      )
                                    ]
                                  ),
                                  _vm._v(" "),
                                  _c(
                                    "th",
                                    {
                                      staticClass:
                                        "px-6 py-3 text-left text-xs font-medium uppercase tracking-wider",
                                      attrs: { scope: "col" }
                                    },
                                    [
                                      _vm._v(
                                        "\n                    Score Needed\n                  "
                                      )
                                    ]
                                  ),
                                  _vm._v(" "),
                                  _c(
                                    "th",
                                    {
                                      staticClass:
                                        "px-6 py-3 text-left text-xs font-medium uppercase tracking-wider",
                                      attrs: { scope: "col" }
                                    },
                                    [
                                      _vm._v(
                                        "\n                    Player Count\n                  "
                                      )
                                    ]
                                  ),
                                  _vm._v(" "),
                                  _c(
                                    "th",
                                    {
                                      staticClass:
                                        "px-6 py-3 text-left text-xs font-medium uppercase tracking-wider",
                                      attrs: { scope: "col" }
                                    },
                                    [
                                      _vm._v(
                                        "\n                    Created\n                  "
                                      )
                                    ]
                                  ),
                                  _vm._v(" "),
                                  _c(
                                    "th",
                                    {
                                      staticClass: "relative px-6 py-3",
                                      attrs: { scope: "col" }
                                    },
                                    [
                                      _c("span", { staticClass: "sr-only" }, [
                                        _vm._v("Edit")
                                      ])
                                    ]
                                  )
                                ])
                              ]
                            ),
                            _vm._v(" "),
                            _c(
                              "tbody",
                              {
                                staticClass:
                                  "bg-white dark:bg-cool-gray-800 divide-y divide-gray-200 dark:divide-gray-700"
                              },
                              [
                                _vm._l(_vm.ranks.data, function(rank, index) {
                                  return _c("tr", { key: index }, [
                                    _c(
                                      "td",
                                      {
                                        staticClass:
                                          "px-3 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"
                                      },
                                      [
                                        _vm._v(
                                          "\n                    " +
                                            _vm._s(index + _vm.ranks.from) +
                                            "\n                  "
                                        )
                                      ]
                                    ),
                                    _vm._v(" "),
                                    _c(
                                      "td",
                                      {
                                        staticClass:
                                          "px-6 py-4 whitespace-nowrap"
                                      },
                                      [
                                        _c(
                                          "div",
                                          { staticClass: "flex items-center" },
                                          [
                                            _c(
                                              "div",
                                              {
                                                staticClass:
                                                  "flex-shrink-0 h-10 w-10"
                                              },
                                              [
                                                _c("img", {
                                                  staticClass: "h-10 w-10",
                                                  attrs: {
                                                    src: rank.photo_url,
                                                    alt: ""
                                                  }
                                                })
                                              ]
                                            ),
                                            _vm._v(" "),
                                            _c("div", { staticClass: "ml-4" }, [
                                              _c(
                                                "div",
                                                {
                                                  staticClass:
                                                    "text-sm font-medium text-gray-900 dark:text-gray-300"
                                                },
                                                [
                                                  _vm._v(
                                                    "\n                          " +
                                                      _vm._s(rank.name) +
                                                      "\n                        "
                                                  )
                                                ]
                                              ),
                                              _vm._v(" "),
                                              _c(
                                                "div",
                                                {
                                                  staticClass:
                                                    "text-sm text-gray-500 dark:text-gray-400"
                                                },
                                                [
                                                  _vm._v(
                                                    "\n                          " +
                                                      _vm._s(rank.shortname) +
                                                      "\n                        "
                                                  )
                                                ]
                                              )
                                            ])
                                          ]
                                        )
                                      ]
                                    ),
                                    _vm._v(" "),
                                    _c(
                                      "td",
                                      {
                                        staticClass:
                                          "px-6 py-4 whitespace-nowrap"
                                      },
                                      [
                                        _c(
                                          "div",
                                          {
                                            staticClass:
                                              "text-sm text-gray-900 dark:text-gray-300"
                                          },
                                          [
                                            _vm._v(
                                              "\n                      " +
                                                _vm._s(
                                                  rank.total_play_one_minute_needed
                                                ) +
                                                "\n                    "
                                            )
                                          ]
                                        )
                                      ]
                                    ),
                                    _vm._v(" "),
                                    _c(
                                      "td",
                                      {
                                        staticClass:
                                          "px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"
                                      },
                                      [
                                        _vm._v(
                                          "\n                    " +
                                            _vm._s(rank.total_score_needed) +
                                            "\n                  "
                                        )
                                      ]
                                    ),
                                    _vm._v(" "),
                                    _c(
                                      "td",
                                      {
                                        staticClass:
                                          "px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"
                                      },
                                      [
                                        _vm._v(
                                          "\n                    " +
                                            _vm._s(rank.players_count) +
                                            "\n                  "
                                        )
                                      ]
                                    ),
                                    _vm._v(" "),
                                    _c(
                                      "td",
                                      {
                                        staticClass:
                                          "px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"
                                      },
                                      [
                                        _vm._v(
                                          "\n                    " +
                                            _vm._s(
                                              _vm.formatDistanceToNowStrict(
                                                new Date(rank.created_at),
                                                { addSuffix: true }
                                              )
                                            ) +
                                            "\n                  "
                                        )
                                      ]
                                    ),
                                    _vm._v(" "),
                                    _c(
                                      "td",
                                      {
                                        staticClass:
                                          "px-6 py-4 whitespace-nowrap text-right text-sm font-medium dark:text-gray-400"
                                      },
                                      [
                                        _c(
                                          "a",
                                          {
                                            staticClass:
                                              "text-blue-600 hover:text-blue-900",
                                            attrs: { href: "#" }
                                          },
                                          [_vm._v("View")]
                                        ),
                                        _vm._v(
                                          "\n                    /\n                    "
                                        ),
                                        _vm.can("update ranks")
                                          ? _c(
                                              "inertia-link",
                                              {
                                                staticClass:
                                                  "text-yellow-600 hover:text-yellow-900",
                                                attrs: {
                                                  as: "a",
                                                  href: _vm.route(
                                                    "admin.rank.edit",
                                                    rank.id
                                                  )
                                                }
                                              },
                                              [
                                                _vm._v(
                                                  "\n                      Edit\n                    "
                                                )
                                              ]
                                            )
                                          : _vm._e(),
                                        _vm._v(
                                          "\n                    /\n                    "
                                        ),
                                        _vm.can("delete ranks")
                                          ? _c(
                                              "button",
                                              {
                                                staticClass:
                                                  "text-red-600 hover:text-red-900 focus:outline-none",
                                                on: {
                                                  click: function($event) {
                                                    return _vm.confirmRankDeletion(
                                                      rank.id
                                                    )
                                                  }
                                                }
                                              },
                                              [
                                                _vm._v(
                                                  "\n                      Delete\n                    "
                                                )
                                              ]
                                            )
                                          : _vm._e()
                                      ],
                                      1
                                    )
                                  ])
                                }),
                                _vm._v(" "),
                                _vm.ranks.data.length === 0
                                  ? _c("tr", [
                                      _c(
                                        "td",
                                        {
                                          staticClass:
                                            "border-t dark:border-gray-700 px-6 py-4 text-center",
                                          attrs: { colspan: "7" }
                                        },
                                        [
                                          _vm._v(
                                            "\n                    No ranks found.\n                  "
                                          )
                                        ]
                                      )
                                    ])
                                  : _vm._e()
                              ],
                              2
                            )
                          ]
                        )
                      ]
                    )
                  ]
                )
              ]
            )
          ]),
          _vm._v(" "),
          _c("pagination", { attrs: { links: _vm.ranks.links } })
        ],
        1
      ),
      _vm._v(" "),
      _c("jet-confirmation-modal", {
        attrs: { show: _vm.rankBeingDeleted },
        on: {
          close: function($event) {
            _vm.rankBeingDeleted = null
          }
        },
        scopedSlots: _vm._u([
          {
            key: "title",
            fn: function() {
              return [_vm._v("\n      Delete Rank\n    ")]
            },
            proxy: true
          },
          {
            key: "content",
            fn: function() {
              return [
                _vm._v(
                  "\n      Are you sure you would like to delete this Rank?\n    "
                )
              ]
            },
            proxy: true
          },
          {
            key: "footer",
            fn: function() {
              return [
                _c(
                  "jet-secondary-button",
                  {
                    nativeOn: {
                      click: function($event) {
                        _vm.rankBeingDeleted = null
                      }
                    }
                  },
                  [_vm._v("\n        Nevermind\n      ")]
                ),
                _vm._v(" "),
                _c(
                  "jet-danger-button",
                  {
                    staticClass: "ml-2",
                    class: { "opacity-25": _vm.deleteRankForm.processing },
                    attrs: { disabled: _vm.deleteRankForm.processing },
                    nativeOn: {
                      click: function($event) {
                        return _vm.deleteNews.apply(null, arguments)
                      }
                    }
                  },
                  [_vm._v("\n        Delete Rank\n      ")]
                )
              ]
            },
            proxy: true
          }
        ])
      })
    ],
    1
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Shared/NotificationDropdown.vue?vue&type=template&id=3eac6b78&":
/*!****************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Shared/NotificationDropdown.vue?vue&type=template&id=3eac6b78& ***!
  \****************************************************************************************************************************************************************************************************************************/
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
  return _c("jet-dropdown", {
    staticClass: "ml-4",
    attrs: { width: "full" },
    scopedSlots: _vm._u([
      {
        key: "trigger",
        fn: function() {
          return [
            _c("icon", {
              staticClass: "w-5 h-5 text-gray-700 dark:text-gray-200",
              attrs: { name: "bell" }
            })
          ]
        },
        proxy: true
      },
      {
        key: "content",
        fn: function() {
          return [
            _c("div", { staticClass: "container p-3 pb-2 w-80" }, [
              _c(
                "div",
                { staticClass: "flex justify-between" },
                [
                  _c("div", { staticClass: "block text-xs text-gray-400" }, [
                    _vm._v("\n          Notifications\n        ")
                  ]),
                  _vm._v(" "),
                  _c(
                    "inertia-link",
                    {
                      staticClass:
                        "block text-xs text-light-blue-400 hover:underline",
                      attrs: {
                        as: "button",
                        href: _vm.route("notification.mark-as-read"),
                        method: "post",
                        "preserve-state": false
                      }
                    },
                    [_vm._v("\n          Mark all as read\n        ")]
                  )
                ],
                1
              ),
              _vm._v(" "),
              _vm.loading
                ? _c("div", { staticClass: "flex p-4 justify-center" }, [
                    _c(
                      "svg",
                      {
                        staticClass:
                          "animate-spin -ml-1 mr-3 h-5 w-5 text-light-blue-600",
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
                  ])
                : _vm._e(),
              _vm._v(" "),
              _vm.error
                ? _c(
                    "div",
                    {
                      staticClass:
                        "bg-red-50 border border-red-400 mt-2 p-1 rounded text-center text-red-400"
                    },
                    [_vm._v("\n        " + _vm._s(_vm.error) + "\n      ")]
                  )
                : _vm._e(),
              _vm._v(" "),
              _c(
                "div",
                {
                  staticClass:
                    "flex flex-col text-sm text-gray-700 dark:text-gray-300 mt-2 h-96 overflow-y-auto space-y-1"
                },
                [
                  _vm._l(_vm.notifications.data, function(notification) {
                    return _c("notification", {
                      key: notification.id,
                      attrs: { notification: notification }
                    })
                  }),
                  _vm._v(" "),
                  !_vm.loading && _vm.notifications.data.length <= 0
                    ? _c(
                        "div",
                        {
                          key: 999999999,
                          staticClass:
                            "flex items-center justify-center italic text-gray-500 dark:text-gray-400 p-4"
                        },
                        [
                          _vm._v(
                            "\n          No notifications to show.\n        "
                          )
                        ]
                      )
                    : _vm._e()
                ],
                2
              ),
              _vm._v(" "),
              _c(
                "div",
                {
                  staticClass:
                    "flex justify-center text-light-blue-400 text-xs mt-2 hover:underline cursor-pointer"
                },
                [
                  _c(
                    "inertia-link",
                    {
                      attrs: { as: "a", href: _vm.route("notification.index") }
                    },
                    [_vm._v("\n          View All\n        ")]
                  )
                ],
                1
              )
            ])
          ]
        },
        proxy: true
      }
    ])
  })
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Shared/Search.vue?vue&type=template&id=2d3cfc04&scoped=true&":
/*!**************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/Shared/Search.vue?vue&type=template&id=2d3cfc04&scoped=true& ***!
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
  return _c(
    "div",
    { staticClass: "relative mx-auto text-gray-600 dark:text-gray-400" },
    [
      _c(
        "form",
        {
          on: {
            submit: function($event) {
              $event.preventDefault()
              return _vm.performSearch.apply(null, arguments)
            }
          }
        },
        [
          _c("input", {
            directives: [
              {
                name: "model",
                rawName: "v-model",
                value: _vm.searchString,
                expression: "searchString"
              }
            ],
            staticClass:
              "border-none bg-gray-200 dark:bg-cool-gray-900 h-10 px-5 pr-10 focus:w-80 rounded-full text-sm focus:outline-none focus:ring-0",
            class: { "w-80": _vm.showResults },
            attrs: {
              "aria-label": "search",
              type: "search",
              name: "search",
              placeholder: "Search..",
              autocomplete: "off"
            },
            domProps: { value: _vm.searchString },
            on: {
              input: [
                function($event) {
                  if ($event.target.composing) {
                    return
                  }
                  _vm.searchString = $event.target.value
                },
                _vm.performSearch
              ]
            }
          }),
          _vm._v(" "),
          _c(
            "button",
            {
              staticClass: "absolute right-0 top-0 mt-3 mr-4",
              attrs: { type: "submit" }
            },
            [
              _c(
                "svg",
                {
                  staticClass:
                    "text-gray-400 dark:text-gray-600 h-4 w-4 fill-current",
                  staticStyle: { "enable-background": "new 0 0 56.966 56.966" },
                  attrs: {
                    id: "Capa_1",
                    xmlns: "http://www.w3.org/2000/svg",
                    "xmlns:xlink": "http://www.w3.org/1999/xlink",
                    version: "1.1",
                    x: "0px",
                    y: "0px",
                    viewBox: "0 0 56.966 56.966",
                    "xml:space": "preserve"
                  }
                },
                [
                  _c("path", {
                    attrs: {
                      d:
                        "M55.146,51.887L41.588,37.786c3.486-4.144,5.396-9.358,5.396-14.786c0-12.682-10.318-23-23-23s-23,10.318-23,23  s10.318,23,23,23c4.761,0,9.298-1.436,13.177-4.162l13.661,14.208c0.571,0.593,1.339,0.92,2.162,0.92  c0.779,0,1.518-0.297,2.079-0.837C56.255,54.982,56.293,53.08,55.146,51.887z M23.984,6c9.374,0,17,7.626,17,17s-7.626,17-17,17  s-17-7.626-17-17S14.61,6,23.984,6z"
                    }
                  })
                ]
              )
            ]
          )
        ]
      ),
      _vm._v(" "),
      _vm.showResults && _vm.searchString
        ? _c(
            "div",
            {
              staticClass:
                "absolute bg-white dark:bg-cool-gray-800 px-3 py-1 w-full rounded-md shadow-lg z-50",
              attrs: { id: "results" }
            },
            [
              _vm.loading
                ? _c(
                    "div",
                    {
                      staticClass: "text-center p-2",
                      attrs: { id: "loading" }
                    },
                    [_vm._v("\n      Loading...\n    ")]
                  )
                : _vm._e(),
              _vm._v(" "),
              !_vm.loading
                ? _c("div", { attrs: { id: "users" } }, [
                    _c(
                      "span",
                      {
                        staticClass:
                          "text-xs text-gray-400 dark:text-gray-300 font-extrabold"
                      },
                      [_vm._v("USERS")]
                    ),
                    _vm._v(" "),
                    _c(
                      "div",
                      { staticClass: "flex flex-col" },
                      _vm._l(_vm.usersList, function(user) {
                        return _c(
                          "inertia-link",
                          {
                            key: user.username,
                            staticClass:
                              "flex px-2 py-1 justify-between hover:bg-light-blue-100 dark:hover:bg-cool-gray-900 rounded cursor-pointer",
                            attrs: {
                              id: "user",
                              as: "div",
                              href: _vm.route("user.public.get", user.username)
                            }
                          },
                          [
                            _c("div", { staticClass: "flex" }, [
                              _c("img", {
                                staticClass: "mr-3 w-10 h-10 rounded-full",
                                attrs: {
                                  src: user.profile_photo_url,
                                  alt: "Image"
                                }
                              }),
                              _vm._v(" "),
                              _c("div", { staticClass: "text-sm" }, [
                                _c(
                                  "p",
                                  {
                                    staticClass:
                                      "text-gray-700 dark:text-gray-300 font-bold"
                                  },
                                  [
                                    _vm._v(
                                      "\n                " +
                                        _vm._s(user.title) +
                                        "\n              "
                                    )
                                  ]
                                ),
                                _vm._v(" "),
                                _c(
                                  "p",
                                  {
                                    staticClass:
                                      "text-gray-500 dark:text-gray-500"
                                  },
                                  [
                                    _vm._v(
                                      "\n                @" +
                                        _vm._s(user.username) +
                                        "\n              "
                                    )
                                  ]
                                )
                              ])
                            ]),
                            _vm._v(" "),
                            _c("div", { staticClass: "flex" }, [
                              _c("img", {
                                directives: [
                                  { name: "tippy", rawName: "v-tippy" }
                                ],
                                staticClass:
                                  "h-8 w-8 -mt-0.5 focus:outline-none",
                                attrs: {
                                  title: user.country.name,
                                  src: user.country.photo_path,
                                  alt: ""
                                }
                              })
                            ])
                          ]
                        )
                      }),
                      1
                    ),
                    _vm._v(" "),
                    !_vm.usersList || _vm.usersList.length <= 0
                      ? _c(
                          "div",
                          {
                            staticClass: "italic",
                            attrs: { id: "emptyusers" }
                          },
                          [_vm._v("\n        No users found.\n      ")]
                        )
                      : _vm._e()
                  ])
                : _vm._e(),
              _vm._v(" "),
              !_vm.loading
                ? _c(
                    "div",
                    { staticClass: "mt-5 pb-4", attrs: { id: "players" } },
                    [
                      _c(
                        "span",
                        {
                          staticClass:
                            "text-xs text-gray-400 dark:text-gray-300 font-extrabold"
                        },
                        [_vm._v("PLAYERS")]
                      ),
                      _vm._v(" "),
                      _c(
                        "div",
                        { staticClass: "flex flex-col" },
                        _vm._l(_vm.playersList, function(player) {
                          return _c(
                            "inertia-link",
                            {
                              key: player.uuid,
                              staticClass:
                                "flex justify-between px-2 py-1 hover:bg-light-blue-100 dark:hover:bg-cool-gray-900 rounded cursor-pointer",
                              attrs: {
                                id: "player",
                                as: "div",
                                href: _vm.route("player.show", player.uuid)
                              }
                            },
                            [
                              _c("div", { staticClass: "flex items-center" }, [
                                _c("img", {
                                  staticClass: "mr-3 w-8 h-8",
                                  attrs: {
                                    src: player.avatar_url,
                                    alt: "Avatar"
                                  }
                                }),
                                _vm._v(" "),
                                _c("div", { staticClass: "text-sm" }, [
                                  _c(
                                    "p",
                                    {
                                      staticClass:
                                        "text-gray-700 dark:text-gray-300 font-bold"
                                    },
                                    [
                                      _vm._v(
                                        "\n                " +
                                          _vm._s(player.title) +
                                          "\n              "
                                      )
                                    ]
                                  )
                                ])
                              ]),
                              _vm._v(" "),
                              _c(
                                "div",
                                { staticClass: "flex space-x-2" },
                                [
                                  _c("icon", {
                                    directives: [
                                      {
                                        name: "show",
                                        rawName: "v-show",
                                        value: player.rating != null,
                                        expression: "player.rating != null"
                                      },
                                      { name: "tippy", rawName: "v-tippy" }
                                    ],
                                    staticClass: "w-8 h-8 focus:outline-none",
                                    attrs: {
                                      name: "rating-" + player.rating,
                                      content: player.rating
                                    }
                                  }),
                                  _vm._v(" "),
                                  _c("img", {
                                    directives: [
                                      {
                                        name: "show",
                                        rawName: "v-show",
                                        value: player.rank.photo_path,
                                        expression: "player.rank.photo_path"
                                      },
                                      { name: "tippy", rawName: "v-tippy" }
                                    ],
                                    staticClass: "h-8 w-8 focus:outline-none",
                                    attrs: {
                                      src: player.rank.photo_path,
                                      alt: player.rank.name,
                                      title: player.rank.name
                                    }
                                  }),
                                  _vm._v(" "),
                                  _c("img", {
                                    directives: [
                                      { name: "tippy", rawName: "v-tippy" }
                                    ],
                                    staticClass:
                                      "h-8 w-8 -mt-0.5 focus:outline-none",
                                    attrs: {
                                      title: player.country.name,
                                      src: player.country.photo_path,
                                      alt: ""
                                    }
                                  })
                                ],
                                1
                              )
                            ]
                          )
                        }),
                        1
                      ),
                      _vm._v(" "),
                      !_vm.playersList || _vm.playersList.length <= 0
                        ? _c(
                            "div",
                            {
                              staticClass: "italic",
                              attrs: { id: "emptyplayers" }
                            },
                            [_vm._v("\n        No players found.\n      ")]
                          )
                        : _vm._e()
                    ]
                  )
                : _vm._e()
            ]
          )
        : _vm._e()
    ]
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ })

}]);