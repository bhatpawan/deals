var config = {
    map: {
        '*': {
            webformsReCaptcha: 'MageMe_WebForms/js/reCaptcha',
            webformsReCaptchaLoader: 'MageMe_WebForms/js/reCaptchaLoader',
            accessibleDatePicker: 'MageMe_WebForms/js/pickadate/picker.date',
            accessibleTranslation: 'MageMe_WebForms/js/pickadate/translation',
        }
    },
    paths: {
        jquerySteps: 'MageMe_WebForms/js/jquery.steps',
        jqueryBarRating: 'MageMe_WebForms/js/jquery.barrating',
    },
    shim: {
        jquerySteps: {
            deps: ['jquery']
        },
        jqueryBarRating: {
            deps: ['jquery']
        },
    }
};
