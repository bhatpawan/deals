/*eslint-disable */

function _inheritsLoose(subClass, superClass) { subClass.prototype = Object.create(superClass.prototype); subClass.prototype.constructor = subClass; subClass.__proto__ = superClass; }

define(["Magento_PageBuilder/js/mass-converter/widget-directive-abstract", "Magento_PageBuilder/js/utils/object"], function (_widgetDirectiveAbstract, _object) {
    /**
     * Copyright © Magento, Inc. All rights reserved.
     * See COPYING.txt for license details.
     */

    /**
     * @api
     */
    var WidgetDirective =
        /*#__PURE__*/
        function (_widgetDirectiveAbstr) {
            "use strict";

            _inheritsLoose(WidgetDirective, _widgetDirectiveAbstr);

            function WidgetDirective() {
                return _widgetDirectiveAbstr.apply(this, arguments) || this;
            }

            var _proto = WidgetDirective.prototype;

            /**
             * Convert value to internal format
             *
             * @param {object} data
             * @param {object} config
             * @returns {object}
             */
            _proto.fromDom = function fromDom(data, config) {
                var attributes = _widgetDirectiveAbstr.prototype.fromDom.call(this, data, config);

                data.form_id = attributes.form_id;
                data.page_size = attributes.page_size;
                data.image_width = attributes.image_width;
                data.image_height = attributes.image_height;
                data.image_link = attributes.image_link;
                data.store_id = attributes.store_id;
                data.template = attributes.template;
                return data;
            };

            /**
             * Convert value to knockout format
             *
             * @param {object} data
             * @param {object} config
             * @returns {object}
             */
            _proto.toDom = function toDom(data, config) {
                var attributes = {
                    type: "MageMe\\WebForms\\Block\\Widget\\Result",
                    form_id: data.form_id,
                    page_size: data.page_size,
                    image_width: data.image_width,
                    image_height: data.image_height,
                    image_link: data.image_link,
                    store_id: data.store_id,
                    template: data.template,
                };

                if (!attributes.form_id || !attributes.template) {
                    return data;
                }

                (0, _object.set)(data, config.html_variable, this.buildDirective(attributes));
                return data;
            };

            return WidgetDirective;
        }(_widgetDirectiveAbstract);

    return WidgetDirective;
});
//# sourceMappingURL=widget-directive.js.map
