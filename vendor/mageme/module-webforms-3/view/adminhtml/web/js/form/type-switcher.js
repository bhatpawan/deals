/**
 * @api
 */
define([
    'Magento_Ui/js/form/switcher',
    'uiRegistry',
    'uiClass'
], function (Switcher, registry, Class) {
    'use strict';

    return Switcher.extend({
        defaults: {
            rules: [],
            lastValue: false
        },

        /**
         *
         * @param {Object} rule - Rule object.
         * @param {*} value - Current value associated with a rule.
         * @param checkNegativeRules
         */
        applyRule: function (rule, value, checkNegativeRules = true) {
            var actions = rule.actions;

            if (checkNegativeRules) {
                if (this.lastValue) {
                    if (this.lastValue !== value) {
                        var notValue = '!' + this.lastValue;
                        var notRule = this.rules.find((rule) => {
                            return rule.value === notValue;
                        });
                        if (notRule) {
                            this.applyRule(notRule, notValue, false);
                        }
                    }
                }
                this.lastValue = value;
            }

            //TODO Refactor this logic in scope of MAGETWO-48585
            /* eslint-disable eqeqeq */
            if (rule.value != value) {
                return;
            } else if (rule.strict) {
                return;
            }

            /* eslint-enable eqeqeq */
            actions.forEach(this.applyAction, this);
        },
    });
});