define([
    'MageMe_WebForms/js/grid/columns/textarea'
], function (Column) {
    'use strict';

    return Column.extend({

        getLabel: function (record) {

            return record[this.index];
        }

    });
});
