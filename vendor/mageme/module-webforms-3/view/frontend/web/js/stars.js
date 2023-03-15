define([
    'jquery',
    'jqueryBarRating'], function ($) {
    function rating(options) {
        var params = {
            theme: options.theme,
            initialRating: options.initialRating, // initial rating
            allowEmpty: true, // allow empty ratings?
            emptyValue: '', // this is the expected value of the empty rating
            showValues: false, // display rating values on the bars?
            showSelectedRating: true, // append a div with a rating to the widget?
            deselectable: false, // allow to deselect ratings?
            reverse: false, // reverse the rating?
            readonly: false, // make the rating ready-only?
            fastClicks: true, // remove 300ms click delay on touch devices?
            hoverState: true, // change state on hover?
            silent: false, // supress callbacks when controlling ratings programatically
            triggerChange: true, // trigger change event when ratings are set or reset
            onSelect: function (value, text, event) {
            }, // callback fired when a rating is selected
            onClear: function (value, text) {
            }, // callback fired when a rating is cleared
            onDestroy: function (value, text) {
            } // callback fired when a widget is destroyed
        };
        var select = $('#' + options.id);
        select.barrating(params);
    }
    return rating;
});
