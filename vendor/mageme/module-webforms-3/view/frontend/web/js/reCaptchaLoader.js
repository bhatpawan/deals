define([], function () {
    'use strict';

    var scriptTagAdded = false;

    return {
        /**
         * Add script tag. Script tag should be added once
         */
        addReCaptchaScriptTag: function (onload, render = 'explicit', languageCode = '', badge = '') {
            var element, scriptTag;

            if (!scriptTagAdded) {
                element = document.createElement('script');
                scriptTag = document.getElementsByTagName('script')[0];

                element.async = true;
                element.defer = true;

                element.src = `https://www.google.com/recaptcha/api.js?onload=${onload}&render=${render}`;
                if (languageCode) {
                    element.src += `&hl=${languageCode}`
                }
                if (badge) {
                    element.src += `&badge=${badge}`
                }
                scriptTag.parentNode.insertBefore(element, scriptTag);
                scriptTagAdded = true;
            }
        }
    };
});