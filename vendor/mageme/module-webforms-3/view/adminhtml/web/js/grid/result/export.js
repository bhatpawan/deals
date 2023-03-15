/**
 * @deprecated replaced with custom UI export
 */

define([
    'Magento_Ui/js/grid/export'
], function (Export) {
    'use strict';

    return Export.extend({
        defaults: {
            imports: {
                params: '${ $.provider }:params'
            }
        },

        getParams: function () {
            var url = location.toString();
            var urlParam = url.match(/form_id\/(\d*)\//);
            var result = this.params;
            result['form_id'] = urlParam[1];
            return result;
        }
    });
});