define([
    'Magento_Ui/js/grid/columns/column',
    'jquery',
    'jquery/ui',
    'Magento_Ui/js/modal/modal'
], function (Column, $) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'MageMe_WebForms/grid/columns/subject'
        },

        getUrl: function (record) {
            return record['subject-url'];
        },

        getPopup: function (record) {
            var dialog = $("#webforms-dialog");
            if (dialog.length === 0) {
                dialog = $('<div id="webforms-dialog" style="display:none"></div>').appendTo('body');
            }

            $.ajax({
                url: this.getUrl(record),
                type: 'GET',
                showLoader: true,
                data: {}
            }).done($.proxy(function (data) {
                dialog.html(data);
                dialog.modal({title: this.getLabel(record), innerScroll: true}).modal('openModal');
            }, this));
        },
    });
});
