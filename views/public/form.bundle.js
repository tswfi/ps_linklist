/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// identity function for calling harmony imports with the correct context
/******/ 	__webpack_require__.i = function(value) { return value; };
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 11);
/******/ })
/************************************************************************/
/******/ ({

/***/ 0:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__admin_dev_themes_new_theme_js_components_translatable_input__ = __webpack_require__(8);
/**
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2018 PrestaShop SA
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */



var $ = window.$;

$(function () {
    new __WEBPACK_IMPORTED_MODULE_0__admin_dev_themes_new_theme_js_components_translatable_input__["a" /* default */]({ localeInputSelector: '.js-locale-input' });
    $('.custom_collection .col-sm-12').each(function (index, customBlock) {
        appendDeleteButton($(customBlock));
    });

    $('body').on('click', '.add-collection-btn', appendPrototype);

    function appendPrototype(event) {
        event.stopImmediatePropagation();

        var button = event.target;
        var collectionId = button.dataset.collectionId;
        var collection = document.getElementById(collectionId);
        var collectionPrototype = collection.dataset.prototype;
        var newChild = collectionPrototype.replace(/__name__/g, collection.children.length + 1);
        var $newChild = $(newChild);
        $('#' + collectionId).append($newChild);
        appendDeleteButton($newChild);
    }

    function appendDeleteButton(customBlock) {
        var collection = customBlock.closest('.custom_collection');
        var $button = $('<a class="remove_custom_url btn btn-primary mt-1">' + collection.data('deleteButtonLabel') + '</a>');
        $button.on('click', function (event) {
            var $button = $(event.target);
            var $row = $button.closest('.row');
            $row.remove();
        });
        customBlock.find('.locale-input-group').first().closest('.col-sm-12').append($button);
    }
});

/***/ }),

/***/ 11:
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(0);


/***/ }),

/***/ 8:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/**
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2018 PrestaShop SA
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

const $ = window.$;

class TranslatableInput {
    constructor(options) {
        options = options || {};

        const self = this;
        self.localeItemSelector = options.localeItemSelector || '.js-locale-item';
        self.localeButtonSelector = options.localeButtonSelector || '.js-locale-btn';
        self.localeInputSelector = options.localeInputSelector || 'input.js-locale-input';

        $('body').on('click', self.localeItemSelector, this.toggleInputs.bind(this));
    }

    /**
     * Toggle all translatable inputs in form in which locale was changed
     *
     * @param {Event} event
     */
    toggleInputs(event) {
        const localeItem = $(event.target);
        const form = localeItem.closest('form');
        const selectedLocale = localeItem.data('locale');
        const self = this;

        form.find(self.localeButtonSelector).text(selectedLocale);

        form.find(self.localeInputSelector).addClass('d-none');
        form.find(self.localeInputSelector+'.js-locale-' + selectedLocale).removeClass('d-none');
    }
}

/* harmony default export */ __webpack_exports__["a"] = (TranslatableInput);


/***/ })

/******/ });