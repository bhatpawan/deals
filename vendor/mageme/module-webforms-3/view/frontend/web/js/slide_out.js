define(['jquery'], function ($) {

    function initSlideOut(options) {
        $('#webform-slide-out-button-' + options.uid).click(function() {
            $('#webform-slide-out-' + options.uid).toggleClass( "show" );
        });

        // For scrollbar in IE
        if (navigator.userAgent.match(/msie/i) || navigator.userAgent.match(/trident/i) ) {
            $(window).on('resize', function () {
                $('#webform-slide-out-content-' + options.uid).css('max-height', ($(this).height() * 0.9) + 'px');
            });
            $(window).resize();
        }

        if (options.isDisplayedAfterSubmission) {
            $('#webform_' + options.uid).bind("reset", function () {
                $('#webform-slide-out-button-' + options.uid).click();
            });
        }
    }

    return initSlideOut;
});