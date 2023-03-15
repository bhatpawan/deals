/*eslint-disable */

function _inheritsLoose(subClass, superClass) {
    subClass.prototype = Object.create(superClass.prototype);
    subClass.prototype.constructor = subClass;
    subClass.__proto__ = superClass;
}

define(
    [
        "jquery",
        "knockout",
        "mage/translate",
        "Magento_PageBuilder/js/config",
        "Magento_PageBuilder/js/content-type-menu/hide-show-option",
        "Magento_PageBuilder/js/content-type/preview"
    ],
    function ($, $k, $t, _config, _hideShowOption, _preview) {


        /**
         * @api
         */
        var Preview =
            /*#__PURE__*/
            function (_preview2) {
                "use strict";

                _inheritsLoose(Preview, _preview2);

                /**
                 * @inheritdoc
                 */
                function Preview(contentType, config, observableUpdater) {
                    var _this;

                    _this = _preview2.call(this, contentType, config, observableUpdater) || this;
                    _this.popupBinded = $k.observable(false);
                    _this.messages = {
                        EMPTY: (0, $t)("Please select web-form."),
                        NO_RESULTS: (0, $t)("No popups were found."),
                        LOADING: (0, $t)("Loading..."),
                        UNKNOWN_ERROR: (0, $t)("An unknown error occurred. Please try again.")
                    };
                    _this.placeholderText = $k.observable(_this.messages.EMPTY);
                    _this.buttonPlaceholder = (0, $t)("Edit Button Text");
                    return _this;
                }

                var _proto = Preview.prototype;

                /**
                 * Return an array of options
                 *
                 * @returns {OptionsInterface}
                 */
                _proto.retrieveOptions = function retrieveOptions() {
                    var options = _preview2.prototype.retrieveOptions.call(this);

                    options.hideShow = new _hideShowOption({
                        preview: this,
                        icon: _hideShowOption.showIcon,
                        title: _hideShowOption.showText,
                        action: this.onOptionVisibilityToggle,
                        classes: ["hide-show-content-type"],
                        sort: 40
                    });
                    return options;
                };

                /**
                 * @inheritdoc
                 */
                _proto.afterObservablesUpdated = function afterObservablesUpdated() {
                    _preview2.prototype.afterObservablesUpdated.call(this);

                    this.popupBinded(false);
                    var data = this.contentType.dataStore.getState();

                    if (!data.form_id) {
                        this.placeholderText(this.messages.EMPTY);
                        return;
                    }

                    this.popupBinded(true);
                };

                return Preview;
            }(_preview);

        return Preview;
    });

//# sourceMappingURL=preview.js.map
