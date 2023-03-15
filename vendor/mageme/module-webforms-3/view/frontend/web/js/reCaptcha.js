define(
    [
        'uiComponent',
        'jquery',
        'ko',
        'webformsReCaptchaLoader'
    ], function (Component, $, ko, reCaptchaLoader) {
        'use strict';

        return Component.extend({

            defaults: {
                template: 'MageMe_WebForms/reCaptcha',
                globalOnLoadCallback: 'webformsRecaptchaOnload',
                recaptchaReadyEvent: 'webformsRecaptchaApiReady',
                languageCode: '',
                version: '3',
                publicKey: '',
                position: 'inline',
                theme: 'standard'
            },

            /**
             * @inheritdoc
             */
            initialize: function () {
                this._super();
                this._loadApi();
            },

            /**
             * Loads recaptchaapi API and triggers event, when loaded
             * @private
             */
            _loadApi: function () {
                if (this._isApiRegistered !== undefined) {
                    if (this._isApiRegistered === true) {
                        $(window).trigger(this.recaptchaReadyEvent);
                    }

                    return;
                }
                this._isApiRegistered = false;

                window[this.globalOnLoadCallback] = function () {
                    this._isApiRegistered = true;
                    $(window).trigger(this.recaptchaReadyEvent);
                }.bind(this);

                reCaptchaLoader.addReCaptchaScriptTag(this.globalOnLoadCallback, 'explicit', this.languageCode, this.position);
            },

            /**
             * Checking that reCAPTCHA is invisible type
             * @returns {Boolean}
             */
            getIsInvisibleRecaptcha: function () {
                return this.version === '3';
            },

            /**
             * Render reCAPTCHA
             */
            renderReCaptcha: function () {
                if (window.grecaptcha && window.grecaptcha.render) { // Check if reCAPTCHA is already loaded
                    this.initCaptcha();
                } else { // Wait for reCAPTCHA to be loaded
                    $(window).on(this.recaptchaReadyEvent, function () {
                        this.initCaptcha();
                    }.bind(this));
                }
            },

            getPositionClass: function () {
                return `recaptcha-position-${this.position}`;
            },

            /**
             * Initialize reCAPTCHA after first rendering
             */
            initCaptcha: function () {
                var params = {theme: this.theme, sitekey: this.publicKey};
                if (this.getIsInvisibleRecaptcha()) {
                    params.size = 'invisible';
                } else {
                    params.callback = this.saveToken;
                }
                var self = this;
                grecaptcha.ready(function () {
                    var captchaDivs = document.querySelectorAll('[class="recaptcha-container"]');
                    var widgetIds = [];
                    for (var i = 0; i < captchaDivs.length; i++) {
                        try {
                            widgetIds.push(grecaptcha.render(captchaDivs[i], params));
                        } catch (e) { /* recaptcha was already rendered */ }
                    }
                    if (self.getIsInvisibleRecaptcha()) {
                        function getCaptchaToken() {
                            for (var i = 0; i < widgetIds.length; i++) {
                                grecaptcha.execute(widgetIds[i]).then(self.saveToken);
                            }
                        }

                        getCaptchaToken();
                        setInterval(getCaptchaToken, 60000);
                    }
                })
            },

            saveToken: function (token) {
                var rFields = document.querySelectorAll('[name="g-recaptcha-response"]');
                for (var i = 0; i < rFields.length; i++) {
                    rFields[i].value = token;
                }
            }
        });
    });