define([
    'Magento_Ui/js/form/element/abstract',
    'MageMe_WebForms/js/dropzone'
], function (Abstract, Dropzone) {
    'use strict';

    return Abstract.extend({

        defaults: {
            url: '',
            maxFiles: 1,
            allowedSize: 0,
            restrictedExtensions: [],
        },

        /**
         * Handler function which is supposed to be invoked when
         * file input element has been rendered.
         *
         * @param {HTMLInputElement} fileInput
         */
        onElementRender: function (fileInput) {
            new Dropzone({
                url: this.url,
                fieldId: this.uid,
                fieldName: this.name,
                dropZoneText: this.label,
                maxFiles: this.maxFiles,
                allowedSize: this.allowedSize,
                restrictedExtensions: this.restrictedExtensions,
                ui: this.value,
            });
        },
    });
});
