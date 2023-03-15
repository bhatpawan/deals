define([
    'Magento_Ui/js/form/element/multiselect',
    'jquery',
    'imagePicker'
], function (Multiselect, $) {
    'use strict';

    return Multiselect.extend({

        defaults: {
            multiple: false,
            imagesWidth: 150,
            imagesHeight: 150,
            hideSelect: true,
            showLabel: false
        },

        /**
         * Handler function which is supposed to be invoked when
         * select input element has been rendered.
         *
         * @param {HTMLInputElement} fileInput
         */
        onElementRender: function (fileInput) {
            $("#" + this.uid).imagepicker({
                hide_select: this.hideSelect,
                show_label: this.showLabel
            });
        },
    });
});
