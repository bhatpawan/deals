define([
    'jquery',
    'mage/translate',
    'Magento_Ui/js/dynamic-rows/action-delete'
], function ($, $t, Action) {
    'use strict';

    return Action.extend({
        defaults: {
            url: '',
            message_id: '',
        },

        /**
         * Delete record handler.
         *
         * @param {Number} index
         * @param {Number} message_id
         */
        deleteRecord: function (index, message_id) {
            var self = this;
            if (!confirm($t('Delete this message?'))) {
                return;
            }
            $.ajax({
                url: this.url,
                data: {
                    message_id: this.message_id
                },
                type: 'POST',
                dataType: 'json',
                success: function (data, status, xhr) {
                    self.bubble('deleteRecord', index, message_id);
                }
            });
        }
    });
});
