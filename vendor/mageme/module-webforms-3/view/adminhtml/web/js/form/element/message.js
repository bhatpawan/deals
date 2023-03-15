define([
    'ko',
    'jquery',
    'mage/translate',
    'Magento_Ui/js/form/element/abstract'
], function (ko, $, $t, Abstract) {
    'use strict';

    return Abstract.extend({

        defaults: {
            email_url: '',
            message: '',
            author: '',
            signature: '',
            is_customer_emailed: false,
            email: '',
            bcc: '',
            cc: '',
            created_at: '',
            attachments: '',
            emailed_text: '',
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super()
                .observe('is_customer_emailed');
            this.emailed_text = ko.computed(function () {
                var text = this.is_customer_emailed() ? $t('Notified') : $t('Not notified');
                return $t('Customer') + ' ' + text;
            }, this);
            return this;
        },

        emailMessage: function (index, message_id) {
            var self = this;
            if (!this.email_url) return;
            if (!this.email) {
                alert($t('Email required'));
                return;
            }
            if (confirm($t('Email this message?'))) {
                $('body').trigger('processStart');
                $.ajax({
                    url: this.email_url,
                    data: {
                        message_id: message_id,
                        email: this.email,
                        bcc: this.bcc,
                        cc: this.cc,
                    },
                    type: 'POST',
                    dataType: 'json',
                    success: function (data, status, xhr) {
                        if (data.success) {
                            self.is_customer_emailed(true);
                            alert($t('Email has been sent.'));
                        } else {
                            alert(data.errors);
                        }
                    },
                    complete: function() {
                        $('body').trigger('processStop');
                    }
                });
            }
        }

    });
});
