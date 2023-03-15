/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

import {ConverterConfigInterface, ConverterDataInterface} from "../../../mass-converter/converter-interface";
import BaseWidgetDirective from "../../../mass-converter/widget-directive-abstract";
import {set} from "../../../utils/object";

/**
 * @api
 */
export default class WidgetDirective extends BaseWidgetDirective {
    /**
     * Convert value to internal format
     *
     * @param {object} data
     * @param {object} config
     * @returns {object}
     */
    public fromDom(data: ConverterDataInterface, config: ConverterConfigInterface): object {
        const attributes = super.fromDom(data, config) as {
            form_id: number;
            template: string;
            after_submission_form: number;
            scroll_to: number;
            async_load: number;
        };

        data.form_id = attributes.form_id;
        data.template = attributes.template;
        data.after_submission_form = attributes.after_submission_form;
        data.scroll_to = attributes.scroll_to;
        data.async_load = attributes.async_load;
        return data;
    }

    /**
     * Convert value to knockout format
     *
     * @param {object} data
     * @param {object} config
     * @returns {object}
     */
    public toDom(data: ConverterDataInterface, config: ConverterConfigInterface): object {
        const attributes = {
            type: "MageMe\\WebForms\\Block\\Widget\\Form",
            form_id: data.form_id,
            template: data.template,
            scroll_to: data.scroll_to,
            after_submission_form: data.after_submission_form,
            async_load: data.async_load,
        };

        if (!attributes.form_id || !attributes.template) {
            return data;
        }

        set(data, config.html_variable, this.buildDirective(attributes));
        return data;
    }
}
