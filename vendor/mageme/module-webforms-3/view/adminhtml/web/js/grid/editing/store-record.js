define([
    'underscore',
    'Magento_Ui/js/grid/editing/record'
], function (_, Record) {
    'use strict';

    return Record.extend({
        defaults: {
            fieldTmpl: 'MageMe_WebForms/grid/editing/service-field',
            templates: {
                fields: {
                    base: {
                        imports: {
                            disabled: '${ $.$data.record.name }:data.grid_default.${ $.$data.column.index }'
                        },
                    },
                }
            },
        },

        /**
         * Filters provided object extracting from it values
         * that can be matched with an existing fields.
         *
         * @param {Object} data - Object to be processed.
         * @returns {Object}
         */
        filterData: function (data) {
            var fields = _.pluck(this.elems(), 'index');

            _.each(this.preserveFields, function (enabled, field) {
                if (enabled && !_.contains(fields, field)) {
                    fields.push(field);
                }
            });
            if (data.use_default) {
                fields.push('use_default');
            }
            if(data.store) {
                fields.push('store');
            }

            return _.pick(data, fields);
        },
    });
});