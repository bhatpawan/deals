define(['jquery', 'jqueryMagnificPopup'], function ($) {
    function popup(options, node) {
        var o = {
            url: '',
            containerId: '',
            modalClass: ''
        };
        for (var k in options) {
            if (options.hasOwnProperty(k)) o[k] = options[k];
        }

        $(node).magnificPopup({
            tLoading: '',
            items: {
                src: o.url
            },
            mainClass: o.modalClass,
            closeOnContentClick: false,
            closeOnBgClick: false,
            type: 'ajax',
            settings: {
                async: true
            },
            callbacks: {
                ajaxContentAdded: function() {
                    this.content.trigger('contentUpdated');
                    try {
                        this.content.applyBindings();
                    } catch (e) {}
                    var evt = document.createEvent("Event");
                    evt.initEvent("mm_button_webform_loaded" + o.containerId, false, false);
                    window.dispatchEvent(evt);
                }
            }
        });
    }

    return popup;
});
