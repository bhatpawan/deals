define([
    'jquery',
    'Magento_Ui/js/form/element/abstract'
], function ($, Component) {
    return Component.extend({
        toggleVisibility: function () {
            this.setVisible(!this.visible());
            return this;
        }
    });
});
