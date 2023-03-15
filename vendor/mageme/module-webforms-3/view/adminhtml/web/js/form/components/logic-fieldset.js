define([
    'jquery',
    'Magento_Ui/js/form/components/fieldset'
], function ($, Fieldset) {
    'use strict';

    return Fieldset.extend({

        /**
         * Calls parent's initElement method.
         * Assigns callbacks on various events of incoming element.
         *
         * @param  {Object} elem
         * @return {Object} - reference to instance
         */
        initElement: function (elem) {
            elem.initContainer(this);

            elem.on({
                'update': this.onChildrenUpdate,
                'loading': this.onContentLoading,
                'error': this.onChildrenError
            });

            if (this.disabled) {
                try {
                    elem.disabled(true);
                }
                catch (e) {

                }
            }
            this.checkVisibility(this.field_type);

            return this;
        },


        /**
         * Check fieldset and button visibility
         *
         * @param {string} data
         */
        checkVisibility: function (data) {
            if (this.field_id) {
                if (Array.isArray(this.logic_types)) {
                    if (this.logic_types.includes(data)) {
                        this.show();
                        return;
                    }
                }
            }
            this.hide();
        },

        /**
         * Show element.
         *
         * @returns {this} Chainable.
         */
        show: function () {
            this.visible(true);

            var button = $('#add_logic');
            if (button.length) {
                button.show();
            }

            return this;
        },

        /**
         * Hide element.
         *
         * @returns {this} Chainable.
         */
        hide: function () {
            this.visible(false);

            var button = $('#add_logic');
            if (button.length) {
                button.hide();
            }

            return this;
        },
    });
});
