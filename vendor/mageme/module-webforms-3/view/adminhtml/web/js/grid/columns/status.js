define([
    'underscore',
    'Magento_Ui/js/grid/columns/column',
    'mage/url',
    'mage/translate',
    'jquery',
    'ko'
], function (_, Column, url, __, $, ko) {
    'use strict';

    var self;

    return Column.extend({
        defaults: {
            bodyTmpl: 'MageMe_WebForms/grid/columns/status',
            isActionsAllowed: true,
        },

        initialize: function () {
            self = this;
            this._super();

        },

        getCurrentStatusClass: function (record) {
            if (typeof record.approved !== "function") {
                record.approved = ko.observable(record.approved);
            }

            var value = record.approved().toString();

            var css = 'grid-status';
            switch (value) {
                case '-1':
                    css+= ' notapproved';
                    break;
                case '0':
                    css+= ' pending';
                    break;
                case '1':
                    css+= ' approved';
                    break;
                case '2':
                    css+= ' completed';
                    break;
            }

            record.loading = ko.observable(0);

            record.labelText = ko.computed(function(){
                var options = self.options || [],
                    label = [];
                var values = this.approved();


                if (_.isString(values)) {
                    values = values.split(',');
                }

                if (!_.isArray(values)) {
                    values = [values];
                }

                values = values.map(function (value) {
                    return value + '';
                });

                options = self.flatOptions(options);

                options.forEach(function (item) {
                    if (_.contains(values, item.value + '')) {
                        label.push(item.label);
                    }
                });

                return label.join(', ');

            },record);

            return css;
        },

        getApproveLabel: function () {
            return $.mage.__('Approve');
        },

        getCompleteLabel: function () {
            return $.mage.__('Complete');
        },

        getRejectLabel: function () {
            return $.mage.__('Reject');
        },

        getSendingMsg: function () {
            return $.mage.__('Sending...');
        },

        setStatus: function (record, status) {
            record.loading(1);
            $.ajax({
                url: this.url,
                type: 'GET',
                contentType: 'application/json; charset=utf-8',
                data: {
                    result_id: record.result_id,
                    status: status,
                    form_key: window.FORM_KEY
                },
                success: function (response) {
                    record.loading(0);

                    if (response.error) {
                        alert(response.message);
                    }
                    record.approved(response.status);
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    alert(xhr.status);
                    alert(thrownError);
                }
            });
        },

        /**
         * Transformation tree options structure to liner array.
         *
         * @param {Array} options
         * @returns {Array}
         */
        flatOptions: function (options) {
            var self = this;

            if (!_.isArray(options)) {
                options = _.values(options);
            }

            return options.reduce(function (opts, option) {
                if (_.isArray(option.value)) {
                    opts = opts.concat(self.flatOptions(option.value));
                } else {
                    opts.push(option);
                }

                return opts;
            }, []);
        }
    });
});
