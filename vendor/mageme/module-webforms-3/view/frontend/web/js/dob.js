define([
    'jquery',
    'jquery/ui',
    'jquery/validate',
    'mage/translate',
    'mage/validation'
], function ($) {

    function dob(options) {
        function setDate() {
            var dayVal = $('#day' + options.id).val(),
                monthVal = $('#month' + options.id).val(),
                yearVal = $('#year' + options.id).val();
            var fullVal = options.dateFormat.replace(/d/i, dayVal).replace(/m/i, monthVal).replace(/y/i, yearVal);
            $('#' + options.id).val(fullVal);
        }
        $('#day' + options.id).change(setDate);
        $('#month' + options.id).change(setDate);
        $('#year' + options.id).change(setDate);

        dobValidation(options.id, options.isRequired, options.validationMessage);
    }



    function dobValidation(id, isRequired, validationMessage) {
        var REQUIRED_MESSAGE = $.mage.__(validationMessage ? validationMessage : 'This is a required field.');
        var FULL_DATE_MESSAGE = $.mage.__('Please enter a valid full date.');

        // Function for return validation messages without duplicates
        function dobMessage() {
            for (var i = 0; i < this.errorList.length; i++) {
                if (this.errorList[i].message !== this.dobErrorMessage ||
                    $(this.errorList[i].element).parents('.webforms-datepicker').length < 1
                ) {
                    continue;
                }
                for (var j = 0; j < this.toHide.length; j++) {
                    if (this.toHide[j].textContent !== this.dobErrorMessage) {
                        continue;
                    }
                    $(this.toHide[j]).remove();
                }
                return;
            }
            return this.dobErrorMessage;
        }

        $.validator.addMethod(
            'validate-dobd', function () {
                var dayVal = $('#day' + id).val(),
                    monthVal = $('#month' + id).val(),
                    yearVal = $('#year' + id).val(),
                    dobLength = dayVal.length + monthVal.length + yearVal.length;
                if (dobLength === 0) {
                    this.dobErrorMessage = REQUIRED_MESSAGE;
                    return !isRequired;
                }
                var day = parseInt(dayVal, 10) || 0,
                    month = parseInt(monthVal, 10) || 0,
                    year = parseInt(yearVal, 10) || 0;

                if (!day) {
                    this.dobErrorMessage = FULL_DATE_MESSAGE;
                    return false;
                }

                var validateDayInMonth = new Date(year, month, 0).getDate();
                if (day < 1 || day > validateDayInMonth) {
                    var validDateMessage = $.mage.__('Please enter a valid day (1-%1).');
                    this.dobErrorMessage = validDateMessage.replace('%1', validateDayInMonth.toString());
                    return false;
                }

                var today = new Date(),
                    dateEntered = new Date();
                dateEntered.setFullYear(year, month - 1, day);
                if (dateEntered > today) {
                    this.dobErrorMessage = $.mage.__('Please enter a date from the past.');
                    return false;
                }

                return true;
            },
            dobMessage,
        );

        $.validator.addMethod(
            'validate-dobm', function () {
                var dayVal = $('#day' + id).val(),
                    monthVal = $('#month' + id).val(),
                    yearVal = $('#year' + id).val(),
                    dobLength = dayVal.length + monthVal.length + yearVal.length;
                if (dobLength === 0) {
                    this.dobErrorMessage = REQUIRED_MESSAGE;
                    return !isRequired;
                }

                var month = parseInt(monthVal, 10) || 0;

                if (!month) {
                    this.dobErrorMessage = FULL_DATE_MESSAGE;
                    return false;
                }

                if (month < 1 || month > 12) {
                    this.dobErrorMessage = $.mage.__('Please select a valid month.');
                    return false;
                }

                return true;
            },
            dobMessage,
        );

        $.validator.addMethod(
            'validate-doby', function () {
                var dayVal = $('#day' + id).val(),
                    monthVal = $('#month' + id).val(),
                    yearVal = $('#year' + id).val(),
                    dobLength = dayVal.length + monthVal.length + yearVal.length;

                if (dobLength === 0) {
                    this.dobErrorMessage = REQUIRED_MESSAGE;
                    return !isRequired;
                }

                var day = parseInt(dayVal, 10) || 0,
                    month = parseInt(monthVal, 10) || 0,
                    year = parseInt(yearVal, 10) || 0,
                    curYear = (new Date()).getFullYear();

                if (!year) {
                    this.dobErrorMessage = FULL_DATE_MESSAGE;
                    return false;
                }

                var today = new Date(),
                    dateEntered = new Date();
                dateEntered.setFullYear(year, month - 1, day);
                if (!(dateEntered > today)) {
                    if (year < 1900 || year > curYear) {
                        var validYearMessage = $.mage.__('Please enter a valid year (1900-%1).');
                        this.dobErrorMessage = validYearMessage.replace('%1', curYear.toString());
                        return false;
                    }
                }

                day = day % 10 === day ? '0' + day : day;
                month = month % 10 === month ? '0' + month : month;
                $('#dob').val(month + '/' + day + '/' + year);
                return true;
            },
            dobMessage,
        );
    }

    return dob;
});
