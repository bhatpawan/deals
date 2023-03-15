define([
    'Magento_Ui/js/grid/columns/column',
    'ko',
    'jquery',
    'mage/translate',
    'MageMe_WebForms/js/jquery.morelines'
], function (Column, ko, $, __) {
    'use strict';

    var self;

    ko.bindingHandlers.applyReadmore= {
        init: function(element, valueAccessor, allBindingsAccessor, viewModel) {

            $( document ).ready(function() {
                $(element).moreLines({
                    linecount: 5,
                    baseclass: 'b-description',
                    basejsclass: 'js-description',
                    classspecific: '_readmore',
                    buttontxtmore: $.mage.__('Read more'),
                    buttontxtless: $.mage.__('Close'),
                    animationspeed: 250
                });
            });

        }
    };

    return Column.extend({
        defaults: {
            bodyTmpl: 'MageMe_WebForms/grid/columns/textarea'
        },

        initialize: function () {
            self = this;
            this._super();
        },

        /**
         * Ment to preprocess data associated with a current columns' field.
         *
         * @param {Object} record - Data to be preprocessed.
         * @returns {String}
         */
        getLabel: function (record) {
            return record[this.index];
        }

    });
});
