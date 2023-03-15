define([
    'Magento_Ui/js/grid/columns/column',
    'ko'
], function (Column, ko) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'MageMe_WebForms/grid/columns/result_id'
        },

        initialize: function () {
            self = this;
            this._super();

        },

        getResultClass: function (record) {
            var isRead = !!parseFloat(record.is_read);
            var isReplied = !!parseFloat(record.is_replied);
            var css;
            if(!isRead) css = 'unread';
            if(isReplied) css = 'replied';
            return css;
        }
    });
});
