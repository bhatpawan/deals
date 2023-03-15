define([
        'jquery',
        'mage/translate',
        'ko',
        'uiClass'
    ],
    function ($, $t, ko, Class) {
        'use strict';
        return Class.extend({
            defaults: {
                moduleId: '',
                moduleName: '',
                isActive: false,
                messages: [],
                title: 'Activate',
                text: 'Activate License',
                activateUrl: '',
                deactivateUrl: '',
                getUrl: '',
                controls: {
                    container: null,
                    input: null,
                    button: null,
                    messages: null,
                },
            },

            initialize: function (options) {
                this.initConfig(options);
                this.initControls();
                this.initObservable();

                return this;
            },

            initControls: function () {
                this.controls.container = $('#row_' + this.moduleId + '_license_serial');
                this.controls.input = $('#' + this.moduleId + '_license_serial');
                this.controls.button = $('#' + this.moduleId + '_license_button');
                this.controls.messages = $('#' + this.moduleId + '_license_messages');
            },

            initObservable: function () {
                var self = this;
                this.isActive = ko.observable(this.isActive);
                this.isActive.subscribe(function (newValue) {
                    self.controls.input.prop('readonly', newValue);
                });
                this.title = ko.computed(function () {
                    return self.isActive() ? $t('Deactivate') : $t('Activate');
                }, this);
                this.text = ko.computed(function () {
                    return self.isActive() ? $t('Deactivate License') : $t('Activate License');
                }, this);
                this.messages = ko.observable(this.messages);
                this.messages.subscribe(function (newValue) {
                    self.controls.messages.empty();
                    if (Array.isArray(newValue)) {
                        for (var i = 0; i < newValue.length; i++) {
                            self.controls.messages.append(newValue[i]);
                        }
                    } else {
                        self.controls.messages.append(newValue);
                    }
                });
                this.getUrl = ko.computed(function () {
                    return self.isActive() ? self.deactivateUrl : self.activateUrl;
                }, this);

                this.isActive.valueHasMutated();
                this.messages.valueHasMutated();
            },

            click: function () {
                var self = this;
                $.ajax({
                    url: self.getUrl(),
                    data: {
                        serial: self.controls.input.val(),
                        module_id: self.moduleId,
                        module_name: self.moduleName,
                    },
                    type: 'GET',
                    dataType: 'json',
                    beforeSend: function () {
                        $('body').trigger('processStart');
                    },
                    success: function (response) {
                        var responseMessages = [];
                        if (response.messages) {
                            for (var i = 0; i < response.messages.length; i++) {
                                responseMessages.push($("<div class='message message-success success'></div>").text($t(response.messages[i])));
                            }
                        }
                        if (response.warnings) {
                            for (var i = 0; i < response.warnings.length; i++) {
                                responseMessages.push($("<div class='message message-warning warning'></div>").text($t(response.warnings[i])));
                            }
                        }
                        if (response.errors) {
                            for (var i = 0; i < response.errors.length; i++) {
                                responseMessages.push($("<div class='message message-error error'></div>").text($t(response.errors[i])));
                            }
                        }
                        self.messages(responseMessages);
                        self.isActive(response.is_active);
                    },
                    error: function (error) {
                        self.messages($("<div class='message message-error'></div>").text($t('Can\'t connect to license server.')));
                    },
                    complete: function () {
                        $('body').trigger('processStop');
                    }
                });
            }
        })
    }
);
