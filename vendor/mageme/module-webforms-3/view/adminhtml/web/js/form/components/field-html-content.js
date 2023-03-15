define([
    'jquery',
    'Magento_Ui/js/form/components/html'
], function ($, Html) {
    'use strict';

    return Html.extend({

        /**
         * Show element.
         *
         * @returns {this} Chainable.
         */
        show: function () {
            this.visible(true);

            return this;
        },

        /**
         * Hide element.
         *
         * @returns {this} Chainable.
         */
        hide: function () {
            this.visible(false);

            return this;
        },
    });
});
