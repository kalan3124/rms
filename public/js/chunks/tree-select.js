(window["webpackJsonp"] = window["webpackJsonp"] || []).push([["tree-select"],{

/***/ "./resources/js/components/CrudPage/Input/TreeSelect.jsx":
/*!***************************************************************!*\
  !*** ./resources/js/components/CrudPage/Input/TreeSelect.jsx ***!
  \***************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var react_treebeard__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! react-treebeard */ "./node_modules/react-treebeard/index.js");
/* harmony import */ var react_treebeard__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(react_treebeard__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _agent__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../agent */ "./resources/js/agent.js");
/* harmony import */ var _constants_treeSelectTheme__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../constants/treeSelectTheme */ "./resources/js/constants/treeSelectTheme.js");
/* harmony import */ var _TreeSelectContainer__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./TreeSelectContainer */ "./resources/js/components/CrudPage/Input/TreeSelectContainer.jsx");
/* harmony import */ var _material_ui_core_TextField__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @material-ui/core/TextField */ "./node_modules/@material-ui/core/TextField/index.js");
/* harmony import */ var _material_ui_core_TextField__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_material_ui_core_TextField__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var _material_ui_core_Paper__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! @material-ui/core/Paper */ "./node_modules/@material-ui/core/Paper/index.js");
/* harmony import */ var _material_ui_core_Paper__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(_material_ui_core_Paper__WEBPACK_IMPORTED_MODULE_6__);
/* harmony import */ var _material_ui_core_Fade__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! @material-ui/core/Fade */ "./node_modules/@material-ui/core/Fade/index.js");
/* harmony import */ var _material_ui_core_Fade__WEBPACK_IMPORTED_MODULE_7___default = /*#__PURE__*/__webpack_require__.n(_material_ui_core_Fade__WEBPACK_IMPORTED_MODULE_7__);
/* harmony import */ var _material_ui_core_styles_withStyles__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! @material-ui/core/styles/withStyles */ "./node_modules/@material-ui/core/styles/withStyles.js");
/* harmony import */ var _material_ui_core_styles_withStyles__WEBPACK_IMPORTED_MODULE_8___default = /*#__PURE__*/__webpack_require__.n(_material_ui_core_styles_withStyles__WEBPACK_IMPORTED_MODULE_8__);
/* harmony import */ var _material_ui_core_ClickAwayListener__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! @material-ui/core/ClickAwayListener */ "./node_modules/@material-ui/core/ClickAwayListener/index.js");
/* harmony import */ var _material_ui_core_ClickAwayListener__WEBPACK_IMPORTED_MODULE_9___default = /*#__PURE__*/__webpack_require__.n(_material_ui_core_ClickAwayListener__WEBPACK_IMPORTED_MODULE_9__);
/* harmony import */ var _material_ui_core_Checkbox__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! @material-ui/core/Checkbox */ "./node_modules/@material-ui/core/Checkbox/index.js");
/* harmony import */ var _material_ui_core_Checkbox__WEBPACK_IMPORTED_MODULE_10___default = /*#__PURE__*/__webpack_require__.n(_material_ui_core_Checkbox__WEBPACK_IMPORTED_MODULE_10__);
/* harmony import */ var _material_ui_core_FormControlLabel__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! @material-ui/core/FormControlLabel */ "./node_modules/@material-ui/core/FormControlLabel/index.js");
/* harmony import */ var _material_ui_core_FormControlLabel__WEBPACK_IMPORTED_MODULE_11___default = /*#__PURE__*/__webpack_require__.n(_material_ui_core_FormControlLabel__WEBPACK_IMPORTED_MODULE_11__);
/* harmony import */ var _material_ui_core_Popper__WEBPACK_IMPORTED_MODULE_12__ = __webpack_require__(/*! @material-ui/core/Popper */ "./node_modules/@material-ui/core/Popper/index.js");
/* harmony import */ var _material_ui_core_Popper__WEBPACK_IMPORTED_MODULE_12___default = /*#__PURE__*/__webpack_require__.n(_material_ui_core_Popper__WEBPACK_IMPORTED_MODULE_12__);
/* harmony import */ var _material_ui_icons_ExpandMore__WEBPACK_IMPORTED_MODULE_13__ = __webpack_require__(/*! @material-ui/icons/ExpandMore */ "./node_modules/@material-ui/icons/ExpandMore.js");
/* harmony import */ var _material_ui_icons_ExpandMore__WEBPACK_IMPORTED_MODULE_13___default = /*#__PURE__*/__webpack_require__.n(_material_ui_icons_ExpandMore__WEBPACK_IMPORTED_MODULE_13__);
/* harmony import */ var _material_ui_icons_ExpandLess__WEBPACK_IMPORTED_MODULE_14__ = __webpack_require__(/*! @material-ui/icons/ExpandLess */ "./node_modules/@material-ui/icons/ExpandLess.js");
/* harmony import */ var _material_ui_icons_ExpandLess__WEBPACK_IMPORTED_MODULE_14___default = /*#__PURE__*/__webpack_require__.n(_material_ui_icons_ExpandLess__WEBPACK_IMPORTED_MODULE_14__);
/* harmony import */ var lodash_set__WEBPACK_IMPORTED_MODULE_15__ = __webpack_require__(/*! lodash.set */ "./node_modules/lodash.set/index.js");
/* harmony import */ var lodash_set__WEBPACK_IMPORTED_MODULE_15___default = /*#__PURE__*/__webpack_require__.n(lodash_set__WEBPACK_IMPORTED_MODULE_15__);
/* harmony import */ var lodash_get__WEBPACK_IMPORTED_MODULE_16__ = __webpack_require__(/*! lodash.get */ "./node_modules/lodash.get/index.js");
/* harmony import */ var lodash_get__WEBPACK_IMPORTED_MODULE_16___default = /*#__PURE__*/__webpack_require__.n(lodash_get__WEBPACK_IMPORTED_MODULE_16__);
function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }

function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _nonIterableSpread(); }

function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance"); }

function _iterableToArray(iter) { if (Symbol.iterator in Object(iter) || Object.prototype.toString.call(iter) === "[object Arguments]") return Array.from(iter); }

function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = new Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; var ownKeys = Object.keys(source); if (typeof Object.getOwnPropertySymbols === 'function') { ownKeys = ownKeys.concat(Object.getOwnPropertySymbols(source).filter(function (sym) { return Object.getOwnPropertyDescriptor(source, sym).enumerable; })); } ownKeys.forEach(function (key) { _defineProperty(target, key, source[key]); }); } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }



















react_treebeard__WEBPACK_IMPORTED_MODULE_1__["decorators"].Loading = function (props) {
  return react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    style: props.style
  }, "loading...");
};

react_treebeard__WEBPACK_IMPORTED_MODULE_1__["decorators"].MoreToggle = function (props) {
  return react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", null, react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_material_ui_icons_ExpandMore__WEBPACK_IMPORTED_MODULE_13___default.a, null));
};

react_treebeard__WEBPACK_IMPORTED_MODULE_1__["decorators"].LessToggle = function (props) {
  return react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", null, react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_material_ui_icons_ExpandLess__WEBPACK_IMPORTED_MODULE_14___default.a, null));
};

react_treebeard__WEBPACK_IMPORTED_MODULE_1__["decorators"].Container = _TreeSelectContainer__WEBPACK_IMPORTED_MODULE_4__["default"];

var styles = function styles(theme) {
  return {
    popper: {
      zIndex: 1600
    },
    paper: {
      borderRadius: theme.spacing.unit,
      minWidth: theme.spacing.unit * 28,
      maxWidth: theme.spacing.unit * 70,
      maxHeight: theme.spacing.unit * 40,
      overflow: 'auto',
      boxShadow: '1px 1px 4px #404040'
    }
  };
};

var TreeSelect =
/*#__PURE__*/
function (_Component) {
  _inherits(TreeSelect, _Component);

  function TreeSelect(props) {
    var _this;

    _classCallCheck(this, TreeSelect);

    _this = _possibleConstructorReturn(this, _getPrototypeOf(TreeSelect).call(this, props));
    _this.state = {
      show: false,
      anchorEl: null,
      checked: [],
      halfChecked: [],
      data: {
        toggled: false,
        loading: true,
        synced: false,
        children: [],
        childrenLink: props.parent,
        name: props.label,
        id: props.parent,
        path: '',
        checked: !Boolean(props.value.length),
        halfChecked: false,
        type: 'parent'
      }
    };
    _this.onToggle = _this.onToggle.bind(_assertThisInitialized(_assertThisInitialized(_this)));
    _this.onShowPopup = _this.onShowPopup.bind(_assertThisInitialized(_assertThisInitialized(_this)));
    _this.onClosePopup = _this.onClosePopup.bind(_assertThisInitialized(_assertThisInitialized(_this)));
    _this.onCheckHandler = _this.onCheckHandler.bind(_assertThisInitialized(_assertThisInitialized(_this)));
    _this.renderHeader = _this.renderHeader.bind(_assertThisInitialized(_assertThisInitialized(_this)));
    return _this;
  }

  _createClass(TreeSelect, [{
    key: "loadOptions",
    value: function loadOptions(node) {
      var _this2 = this;

      var _this$props = this.props,
          hierarchy = _this$props.hierarchy,
          value = _this$props.value;
      var checkedNodeIds = value == '' ? [] : value.map(function (child) {
        return child.id;
      });
      var link = node.childrenLink;
      var isParent = typeof hierarchy[link] != 'undefined';
      var formatedTerms = {};

      if (isParent) {
        var searchTerms = hierarchy[link].term;

        if (typeof searchTerms != 'undefined') {
          formatedTerms = this.resolveParameteredObject(node.id, searchTerms);
        }
      }

      _agent__WEBPACK_IMPORTED_MODULE_2__["default"].Crud.dropdown(link, '', formatedTerms, false).then(function (data) {
        node.children = data.map(function (opt, i) {
          return {
            name: opt.label,
            childrenLink: isParent ? hierarchy[link].children : undefined,
            loading: true,
            synced: false,
            toggled: false,
            children: isParent && typeof hierarchy[link].children != 'undefined' ? [] : undefined,
            id: node.id + '-' + opt.value,
            path: (node.path == '' ? 'children.' : node.path + '.children.') + i,
            checked: node.checked || checkedNodeIds.includes(node.id + '-' + opt.value),
            halfChecked: node.halfChecked || node.checked,
            type: node.childrenLink
          };
        });
        console.log(node.children);
        console.log(checkedNodeIds);
        node.loading = false;
        node.synced = true;
        node.toggled = true;

        _this2.updateNode(node);
      });
    }
  }, {
    key: "resolveParameteredObject",
    value: function resolveParameteredObject(id, obj) {
      var path = id.split('-').slice(1);

      var formatedObj = _objectSpread({}, obj);

      Object.keys(formatedObj).forEach(function (name) {
        var pattern = formatedObj[name];

        if (pattern.search(/\{\d+\}/) + 1) {
          formatedObj[name] = path[pattern.split(/\{(\d+)\}/)[1] - 1];
        }
      });
      return formatedObj;
    }
  }, {
    key: "renderHeader",
    value: function renderHeader(props) {
      var _this3 = this;

      var _this$state = this.state,
          checked = _this$state.checked,
          halfChecked = _this$state.halfChecked;
      return react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
        style: props.style
      }, react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_material_ui_core_FormControlLabel__WEBPACK_IMPORTED_MODULE_11___default.a, {
        control: react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_material_ui_core_Checkbox__WEBPACK_IMPORTED_MODULE_10___default.a, {
          checked: props.node.checked,
          onChange: function onChange(e) {
            _this3.onCheckHandler(props.node);
          },
          indeterminate: props.node.halfChecked
        }),
        label: props.node.name
      }));
    }
  }, {
    key: "setParentsStatus",
    value: function setParentsStatus(data, path, checked, halfChecked, toggled) {
      var modifiedNodes = _objectSpread({}, data);

      if (path == '') return;
      path = path.substr(9);
      var parentPaths = path.split('.children.').slice(0, -1);
      var parentPathsLength = parentPaths.length;

      for (var index = 0; index < parentPathsLength; index++) {
        var parentPath = 'children.' + parentPaths.join('.children.');
        parentPaths.pop();
        var parent = lodash_get__WEBPACK_IMPORTED_MODULE_16___default()(modifiedNodes, parentPath);
        if (typeof checked != 'undefined') parent.checked = checked;
        if (typeof halfChecked != 'undefined') parent.halfChecked = halfChecked;
        if (typeof toggled != 'undefined') parent.toggled = toggled;
        modifiedNodes = lodash_set__WEBPACK_IMPORTED_MODULE_15___default()(modifiedNodes, parentPath, parent);
      }

      if (typeof checked != 'undefined') modifiedNodes.checked = checked;
      if (typeof halfChecked != 'undefined') modifiedNodes.halfChecked = halfChecked;
      if (typeof toggled != 'undefined') modifiedNodes.toggled = toggled;
      return modifiedNodes;
    }
  }, {
    key: "setChildrenStatus",
    value: function setChildrenStatus(data, checked, halfChecked, toggled) {
      var _this4 = this;

      var modifiedNodes = _objectSpread({}, data);

      if (typeof modifiedNodes.children == 'undefined') return modifiedNodes;
      modifiedNodes.children = data.children.map(function (child) {
        if (typeof checked != 'undefined') child.checked = checked;
        if (typeof halfChecked != 'undefined') child.halfChecked = halfChecked;
        if (typeof toggled != 'undefined') child.toggled = toggled;
        if (typeof child.children != 'undefined') child = _this4.setChildrenStatus(child, checked, halfChecked, toggled);
        return child;
      });
      return modifiedNodes;
    }
  }, {
    key: "updateNode",
    value: function updateNode(modifiedNode) {
      var data = this.state.data;
      var modifiedNodes;

      if (modifiedNode.path == '') {
        modifiedNodes = _objectSpread({}, modifiedNode);
      } else {
        modifiedNodes = lodash_set__WEBPACK_IMPORTED_MODULE_15___default()(data, modifiedNode.path, modifiedNode);
      }

      this.setState({
        data: modifiedNodes
      });
    }
  }, {
    key: "onToggle",
    value: function onToggle(node, toggled) {
      var modifiedNode = _objectSpread({}, node);

      modifiedNode.toggled = !modifiedNode.toggled;

      if (modifiedNode.synced) {
        this.updateNode(modifiedNode);
      } else {
        this.loadOptions(modifiedNode);
      }
    }
  }, {
    key: "onClosePopup",
    value: function onClosePopup() {
      this.setState({
        show: false
      });
    }
  }, {
    key: "onShowPopup",
    value: function onShowPopup(_ref) {
      var currentTarget = _ref.currentTarget;
      this.setState({
        show: true,
        anchorEl: currentTarget
      });
    }
  }, {
    key: "onCheckHandler",
    value: function onCheckHandler(node) {
      if (!node.checked && !node.halfChecked) {
        this.checkNode(node);
      } else {
        this.uncheckNode(node);
      }
    }
  }, {
    key: "getParentByNode",
    value: function getParentByNode(node) {
      var data = this.state.data;
      var parentPaths = node.path.substr(9).split('.children.').slice(0, -1);
      return parentPaths.length ? lodash_get__WEBPACK_IMPORTED_MODULE_16___default()(data, 'children.' + parentPaths.join('.children.')) : _objectSpread({}, data);
    }
  }, {
    key: "checkNode",
    value: function checkNode(node) {
      var data = this.state.data;

      var modifiedNodes = _objectSpread({}, data);

      var modifiedNode = _objectSpread({}, node);

      if (node.path != '') {
        var parent = this.getParentByNode(node);
        var sibilingSelected = true;
        parent.children.forEach(function (child) {
          if (!child.checked && child.id != node.id) sibilingSelected = false;
        });

        if (sibilingSelected) {
          this.checkNode(parent);
          return;
        }
      }

      modifiedNode.checked = true;
      modifiedNode.halfChecked = false;
      modifiedNode = this.setChildrenStatus(modifiedNode, true, true);

      if (modifiedNode.path != '') {
        modifiedNodes = lodash_set__WEBPACK_IMPORTED_MODULE_15___default()(modifiedNodes, modifiedNode.path, modifiedNode);
        modifiedNodes = this.setParentsStatus(modifiedNodes, modifiedNode.path, false, true);
      } else {
        modifiedNodes = _objectSpread({}, modifiedNode);
      }

      this.updateNode(modifiedNodes);
      this.props.onChange(this.filterChecked(modifiedNodes));
    }
  }, {
    key: "uncheckNode",
    value: function uncheckNode(node) {
      var data = this.state.data;

      var modifiedNode = _objectSpread({}, node);

      var modifiedNodes = _objectSpread({}, data);

      if (node.path != '') {
        var parent = this.getParentByNode(node);
        var sibilingsUnchecked = true;
        parent.children.forEach(function (child) {
          if (child.checked && child.id != node.id) sibilingsUnchecked = false;
        });

        if (sibilingsUnchecked) {
          this.uncheckNode(parent);
          return;
        }

        parent.children = parent.children.map(function (child) {
          child.halfChecked = false;
          return child;
        });
        modifiedNodes = parent.path == '' ? _objectSpread({}, parent) : lodash_set__WEBPACK_IMPORTED_MODULE_15___default()(modifiedNodes, parent.path, parent);
      }

      modifiedNode = this.setChildrenStatus(modifiedNode, false, false);
      modifiedNode.checked = false;
      modifiedNode.halfChecked = false;

      if (modifiedNode.path != '') {
        modifiedNodes = lodash_set__WEBPACK_IMPORTED_MODULE_15___default()(modifiedNodes, modifiedNode.path, modifiedNode);
        modifiedNodes = this.setParentsStatus(modifiedNodes, modifiedNode.path, false, true);
      } else {
        modifiedNodes = _objectSpread({}, modifiedNode);
      }

      this.updateNode(modifiedNodes);
      this.props.onChange(this.filterChecked(modifiedNodes));
    }
  }, {
    key: "filterChecked",
    value: function filterChecked(node) {
      var _this5 = this;

      var hierarchy = this.props.hierarchy;
      if (typeof node == 'undefined') node = _objectSpread({}, this.state.data);
      var checked = Array();
      node.children.forEach(function (child) {
        if (child.checked && !child.halfChecked) {
          var formatedParams = hierarchy[child.type].param;
          if (typeof formatedParams == 'undefined') formatedParams = {};
          formatedParams = _this5.resolveParameteredObject(child.id, formatedParams);
          formatedParams.type = child.type;
          formatedParams.name = child.name;
          formatedParams.id = child.id;
          checked.push(formatedParams);
        }

        if (typeof child.children != 'undefined') {
          var childChecked = _this5.filterChecked(child);

          checked = checked.concat(childChecked);
        }
      });
      return checked;
    }
  }, {
    key: "render",
    value: function render() {
      var _this6 = this;

      var _this$props2 = this.props,
          label = _this$props2.label,
          name = _this$props2.name,
          classes = _this$props2.classes,
          value = _this$props2.value;
      var _this$state2 = this.state,
          show = _this$state2.show,
          anchorEl = _this$state2.anchorEl,
          data = _this$state2.data;
      react_treebeard__WEBPACK_IMPORTED_MODULE_1__["decorators"].Header = this.renderHeader;
      var checked = value == '' ? [] : _toConsumableArray(value);
      return react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", null, react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_material_ui_core_TextField__WEBPACK_IMPORTED_MODULE_5___default.a, {
        label: label,
        id: name,
        value: checked.length ? checked.map(function (node) {
          return node.name;
        }).join(",") : "All Selected.",
        margin: "dense",
        variant: "outlined",
        onClick: this.onShowPopup
      }), react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_material_ui_core_Popper__WEBPACK_IMPORTED_MODULE_12___default.a, {
        className: classes.popper,
        open: show,
        placement: "top-end",
        anchorEl: anchorEl,
        transition: true
      }, function (_ref2) {
        var TransitionProps = _ref2.TransitionProps;
        return react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_material_ui_core_Fade__WEBPACK_IMPORTED_MODULE_7___default.a, _extends({}, TransitionProps, {
          timeout: 350
        }), react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_material_ui_core_ClickAwayListener__WEBPACK_IMPORTED_MODULE_9___default.a, {
          onClickAway: _this6.onClosePopup
        }, react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_material_ui_core_Paper__WEBPACK_IMPORTED_MODULE_6___default.a, {
          style: typeof anchorEl != 'undefined' ? {
            width: anchorEl.offsetWidth
          } : undefined,
          className: classes.paper
        }, react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(react_treebeard__WEBPACK_IMPORTED_MODULE_1__["Treebeard"], {
          data: data,
          onToggle: _this6.onToggle,
          decorators: react_treebeard__WEBPACK_IMPORTED_MODULE_1__["decorators"],
          style: _constants_treeSelectTheme__WEBPACK_IMPORTED_MODULE_3__["default"],
          animations: false
        }))));
      }));
    }
  }]);

  return TreeSelect;
}(react__WEBPACK_IMPORTED_MODULE_0__["Component"]);

/* harmony default export */ __webpack_exports__["default"] = (_material_ui_core_styles_withStyles__WEBPACK_IMPORTED_MODULE_8___default()(styles)(TreeSelect));

/***/ }),

/***/ "./resources/js/components/CrudPage/Input/TreeSelectContainer.jsx":
/*!************************************************************************!*\
  !*** ./resources/js/components/CrudPage/Input/TreeSelectContainer.jsx ***!
  \************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var prop_types__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! prop-types */ "./node_modules/prop-types/index.js");
/* harmony import */ var prop_types__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(prop_types__WEBPACK_IMPORTED_MODULE_1__);
function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }




var Container =
/*#__PURE__*/
function (_React$Component) {
  _inherits(Container, _React$Component);

  function Container() {
    _classCallCheck(this, Container);

    return _possibleConstructorReturn(this, _getPrototypeOf(Container).apply(this, arguments));
  }

  _createClass(Container, [{
    key: "render",
    value: function render() {
      var _this = this;

      var _this$props = this.props,
          style = _this$props.style,
          decorators = _this$props.decorators,
          terminal = _this$props.terminal,
          onClick = _this$props.onClick,
          node = _this$props.node;
      return react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
        style: style.container
      }, react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(decorators.Header, {
        node: node,
        style: style.header,
        onClick: onClick
      }), react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
        style: style.toggle,
        onClick: onClick,
        ref: function ref(_ref) {
          return _this.clickableRef = _ref;
        }
      }, !terminal ? this.renderToggle() : null));
    }
  }, {
    key: "renderToggle",
    value: function renderToggle() {
      var _this$props2 = this.props,
          node = _this$props2.node,
          decorators = _this$props2.decorators,
          style = _this$props2.style,
          onClick = _this$props2.onClick;
      var toggleProps = {
        onClick: onClick
      };
      return node.toggled ? react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(decorators.MoreToggle, toggleProps) : react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(decorators.LessToggle, toggleProps);
    }
  }]);

  return Container;
}(react__WEBPACK_IMPORTED_MODULE_0___default.a.Component);

Container.propTypes = {
  style: prop_types__WEBPACK_IMPORTED_MODULE_1___default.a.object.isRequired,
  decorators: prop_types__WEBPACK_IMPORTED_MODULE_1___default.a.object.isRequired,
  terminal: prop_types__WEBPACK_IMPORTED_MODULE_1___default.a.bool.isRequired,
  onClick: prop_types__WEBPACK_IMPORTED_MODULE_1___default.a.func.isRequired,
  animations: prop_types__WEBPACK_IMPORTED_MODULE_1___default.a.oneOfType([prop_types__WEBPACK_IMPORTED_MODULE_1___default.a.object, prop_types__WEBPACK_IMPORTED_MODULE_1___default.a.bool]).isRequired,
  node: prop_types__WEBPACK_IMPORTED_MODULE_1___default.a.object.isRequired
};
/* harmony default export */ __webpack_exports__["default"] = (Container);

/***/ }),

/***/ "./resources/js/constants/treeSelectTheme.js":
/*!***************************************************!*\
  !*** ./resources/js/constants/treeSelectTheme.js ***!
  \***************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony default export */ __webpack_exports__["default"] = ({
  tree: {
    base: {
      listStyle: 'none',
      backgroundColor: '#fff',
      margin: 0,
      padding: 0,
      color: 'rgba(0, 0, 0, 0.87)',
      fontFamily: '"Open Sans", sans-serif',
      fontSize: '14px'
    },
    node: {
      width: '100%',
      base: {
        position: 'relative',
        paddingBottom: '4px'
      },
      link: {
        cursor: 'pointer',
        position: 'relative',
        padding: '0px 5px',
        display: 'block'
      },
      activeLink: {
        background: '#31363F'
      },
      toggle: {
        height: 14,
        display: 'inline-block'
      },
      header: {
        base: {},
        connector: {},
        title: {
          lineHeight: '24px',
          verticalAlign: 'middle'
        },
        width: '80%',
        display: 'inline-block'
      },
      subtree: {
        listStyle: 'none',
        paddingLeft: '19px',
        borderTop: '1px solid rgba(0,0,0,0.3)'
      },
      loading: {
        color: '#E2C089'
      }
    }
  }
});

/***/ })

}]);