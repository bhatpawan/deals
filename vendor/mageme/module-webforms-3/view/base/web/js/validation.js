define([
    'jquery',
    'mage/validation',
    'mage/translate'], function ($) {
    function initValidation() {
        var check = $.validator.prototype.check;
        $.validator.prototype.check = function (element) {
            if ($(element).hasClass('validate-hidden')) {
                var uid = $(element).data('uid');
                if (uid && $("#field_" + uid + ":hidden").length) {
                    return true;
                }
            }
            return check.call(this, element);
        };
        $.validator.addMethod(
            'validate-match-value', function (v, elm) {
                var matchInputId = $(elm).data('match-id');
                this.validateMessage = $.mage.__('Incorrect match validation data, please check validation rules.');
                if (!matchInputId) {
                    return false;
                }
                var matchInput = $("#" + matchInputId);
                if (!matchInput) {
                    return false;
                }
                var label = $("label[for='" + matchInputId + "']");
                var fieldLabel = label.text() ? label.text() : matchInput.attr('placeholder');
                this.validateMessage = $.mage.__('Please make sure your input matches the <i>%1</i>.').replace('%1', fieldLabel);
                return matchInput.val() === v;
            },
            function () {
                return this.validateMessage;
            }
        );
        $.validator.addMethod(
            'validate-dropzone', function (v, elm) {
                var uid = $(elm).data('uid');
                if (!uid) {
                    return false;
                }
                return !$('#field' + uid + '_preview').has('.drop-zone-error').length;

            },
            function () {
                return this.validateMessage;
            }
        );
        $.validator.addMethod(
            'validate-dropzone-required', function (v, elm) {
                var result = false,
                    uid = $(elm).data('uid');
                if (!uid) {
                    return false;
                }
                if ($('#field' + uid + '_preview').has('.drop-zone-preview-file').length) {
                    result = true;
                }
                if (!result) {
                    if (getDropzoneUploadedCount(uid)) {
                        result = true;
                    }
                }
                return result;
            },
            function () {
                return this.validateMessage;
            }
        );
        $.validator.addMethod(
            'validate-dropzone-max-files', function (v, elm) {
                var uid = $(elm).data('uid'),
                    max = $(elm).data('max-files');
                if (!uid) {
                    return false;
                }
                if (!max) {
                    return true;
                }
                var newFiles = $('#field' + uid + '_preview').find('.drop-zone-preview-file').length;
                var uploaded = getDropzoneUploadedCount(uid);
                return newFiles + uploaded <= max;
            },
            function () {
                return this.validateMessage;
            }
        );
        $.validator.addMethod(
            'required-dropzone-file', function (v, elm) {
                var result = !$.mage.isEmptyNoTrim(v),
                    ovId,
                    uid = $(elm).data('uid');

                if (!result) {
                    ovId = $('#' + $(elm).attr('id') + '_value');

                    if (ovId.length > 0) {
                        result = !$.mage.isEmptyNoTrim(ovId.val());
                    }
                }

                if (!result && uid) {
                    if (getDropzoneUploadedCount(uid)) {
                        result = true;
                    }
                }

                return result;
            },
            $.mage.__('Please select a file.')
        );
        $.validator.addMethod(
            'validate-options-checkbox-min',
            function (v, elm) {
                var validator = this,
                    counter = 0,
                    data = $(elm).data('validate').replace(/'/g, '"'),
                    uid = $(elm).data('uid'),
                    count = parseInt(JSON.parse(data)['validate-options-checkbox-min']);
                validator.validateMessage =
                    $.mage.__('Please check at least %1 options').replace('%1', count);
                if (isNaN(count)) {
                    validator.validateMessage =
                        $.mage.__('Incorrect min value, please check validation rules.');
                    return false;
                }
                $("#field_" + uid).find('input').each(function(i, val) {
                    if(val.checked) {
                        counter++;
                    }
                });
                return counter === 0 || counter >= count;
            }, function () {
                return this.validateMessage;
            }
        );
        $.validator.addMethod(
            'validate-options-checkbox-max',
            function (v, elm) {
                var validator = this,
                    counter = 0,
                    data = $(elm).data('validate').replace(/'/g, '"'),
                    uid = $(elm).data('uid'),
                    count = parseInt(JSON.parse(data)['validate-options-checkbox-max']);
                validator.validateMessage =
                    $.mage.__('Please check not more than %1').replace('%1', count);
                if (isNaN(count)) {
                    validator.validateMessage =
                        $.mage.__('Incorrect max value, please check validation rules.');
                    return false;
                }
                $("#field_" + uid).find('input').each(function(i, val) {
                    if(val.checked) {
                        counter++;
                    }
                });
                return counter === 0 || counter <= count;
            }, function () {
                return this.validateMessage;
            }
        );
        $.validator.addMethod(
            'validate-length-min',
            function (v, elm) {
                var validator = this,
                    data = $(elm).data('validate').replace(/'/g, '"'),
                    length = parseInt(JSON.parse(data)['validate-length-min']);
                validator.validateMessage =
                    $.mage.__('Please enter more or equal than %1 symbols.').replace('%1', length);
                if (isNaN(length)) {
                    validator.validateMessage =
                        $.mage.__('Incorrect min value, please check validation rules.');
                    return false;
                }
                if (length <= 0 || !v) {
                    return true;
                }
                return v.length >= length;
            }, function () {
                return this.validateMessage;
            }
        );
        $.validator.addMethod(
            'validate-length-max',
            function (v, elm) {
                var validator = this,
                    data = $(elm).data('validate').replace(/'/g, '"'),
                    length = parseInt(JSON.parse(data)['validate-length-max']);
                validator.validateMessage =
                    $.mage.__('Please enter less or equal than %1 symbols.').replace('%1', length);
                if (isNaN(length)) {
                    validator.validateMessage =
                        $.mage.__('Incorrect max value, please check validation rules.');
                    return false;
                }
                if (!v) {
                    return true;
                }
                return v.length <= length;
            }, function () {
                return this.validateMessage;
            }
        );
        $.validator.addMethod(
            'validate-intl-phone-number',
            function (v, elm) {
                if (!window.intlTelInput) {
                    return true;
                }
                var validator = this;
                validator.validateMessage = $.mage.__('Incorrect number.');
                if (!v) {
                    return true;
                }
                var phone = window.intlTelInputGlobals.getInstance(elm);
                return phone.isValidNumber();
            }, function () {
                return this.validateMessage;
            }
        );
        $.validator.addMethod(
            'validate-input-mask-complete',
            function (v, elm) {
                if (!window.Inputmask) {
                    return true;
                }
                var validator = this;
                validator.validateMessage = $.mage.__('Incorrect value.');
                if (!v) {
                    return true;
                }
                return elm.inputmask.isComplete();
            }, function () {
                return this.validateMessage;
            }
        );
        $.validator.addMethod(
            'mm-pattern',
            function (v, elm, param) {
                var validator = this,
                    pattern = param ?? '',
                    flags = $(elm).data('mm-pattern-flags') ?? '';
                validator.validateMessage = $.mage.__('Invalid format.');
                if (!v) {
                    return true;
                }
                return new RegExp(pattern, flags).test(v);
            }, function () {
                return this.validateMessage;
            }
        );
    }

    function getDropzoneUploadedCount(uid) {
        var counter = 0,
            selectAll = $("#" + uid + 'selectall').prop( "checked" );
        if (selectAll) {
            return 0;
        }
        $("#" + uid + 'filepool').find("input[name^='delete_file_']").each(function(i, val) {
            if (!val.checked) {
                counter++;
            }
        });
        return counter;
    }

    return initValidation;
});
