var config = {
    map: {
        '*': {
        }
    },
    paths: {
        jqueryMagnificPopup: 'MageMe_Core/js/jquery.magnific-popup',
        swal: 'MageMe_Core/js/sweetalert2',
        polyfill: 'MageMe_Core/js/polyfill'
    },
    shim: {
        jqueryMagnificPopup: {
            deps: ['jquery']
        },
        swal: {
            deps: ['polyfill']
        }
    }
};
