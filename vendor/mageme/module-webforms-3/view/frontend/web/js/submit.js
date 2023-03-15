define([
        'jquery',
        'swal',
        'MageMe_WebForms/js/validation',
        'MageMe_WebForms/js/jquery.cookie',
        'mage/mage',
        'mage/validation'],
    function ($, swal, initValidation) {
        function submitForm(options) {
            var url = options.url;
            var uid = options.uid;
            var formId = '#webform_' + uid;
            var isAjax = options.isAjax;
            var enableAfterSubmissionForm = options.isDisplayedAfterSubmission;
            var enableScrollTo = options.isScrolledAfterSubmission;
            var enableCaptcha = options.useCaptcha;

            var controls = {
                block: $(formId + '_form'),
                form: $(formId),
                sendingData: $(formId + '_sending_data'),
                progressText: $(formId + '_progress_text'),
                successText: $(formId + '_success_text'),
                submitButton: $(formId + '_submit_button')
            };

            var ignore = '.validate-hidden';

            controls.form.mage('validation', {
                ignore: ignore ? ':hidden:not(' + ignore + ')' : ':hidden',
                errorPlacement: function (error, element) {
                    var errorPlacement = element,
                        fieldWrapper;

                    // logic for date-picker error placement
                    if (element.hasClass('_has-datepicker')) {
                        errorPlacement = element.siblings('button');
                    }

                    // logic for field wrapper
                    fieldWrapper = element.closest('.addon');

                    if (fieldWrapper.length) {
                        errorPlacement = fieldWrapper.after(error);
                    }

                    //logic for checkboxes/radio
                    if (element.is(':checkbox') || element.is(':radio')) {
                        errorPlacement = element.parents('.control').children().last();

                        //fallback if group does not have .control parent
                        if (!errorPlacement.length) {
                            errorPlacement = element.siblings('label').last();
                        }
                    }

                    //logic for control with tooltip
                    if (element.siblings('.tooltip').length) {
                        errorPlacement = element.siblings('.tooltip');
                    }

                    //logic for select with tooltip in after element
                    if (element.next().find('.tooltip').length) {
                        errorPlacement = element.next();
                    }

                    // logic for dob error placement
                    if (element.hasClass('dob-dd') || element.hasClass('dob-mm') || element.hasClass('dob-yyyy')) {
                        errorPlacement = $(element).parents('.webforms-datepicker');
                        error.addClass('validation-clear');
                        if (!errorPlacement.length) {
                            errorPlacement = element.siblings('label').last();
                        }
                    }

                    // logic for br-rating fields
                    if (element.hasClass('br-rating')) {
                        errorPlacement = $(element).parent().children('.br-widget');
                        error.addClass('validation-clear');
                    }

                    // logic for gallery fields
                    if (element.hasClass('webforms-gallery')) {
                        errorPlacement = $(element).parent().children('.thumbnails');
                        error.addClass('validation-clear');
                    }

                    // logic for password
                    if (element.hasClass('password')) {
                        errorPlacement = $(element).parent('.password-container');
                    }

                    // logic for phone number fields
                    if (element.hasClass('webforms-phone')) {
                        errorPlacement = $(element).parent();
                        error.addClass('validation-clear');
                    }

                    errorPlacement.after(error);
                }
            });

            initValidation();

            $('body').on('submit', formId, function (e) {
                // Validation
                if (!(controls.form.validation() && controls.form.validation('isValid'))) return false;

                // reCAPTCHA
                if (enableCaptcha) {
                    if (!$("[name='g-recaptcha-response']").val()) {
                        swal.fire({
                            title: options.messageError,
                            html: options.messageRecaptcha,
                            type: 'error'
                        });
                        return false;
                    }
                }

                controls.form.find('input[name=form_key]')[0].value = $.cookie('form_key');

                // Ajax submit
                if (isAjax) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    var formData = new FormData(controls.form[0]);
                    formData.append('submitted_from', JSON.stringify(
                        {
                            'url': window.location.href,
                            'title': document.title
                        })
                    );
                    formData.append('referrer_page', document.referrer);

                    $.ajax({
                        url: url,
                        data: formData,
                        type: 'POST',
                        cache: false,
                        dataType: 'json',
                        contentType: false,
                        processData: false,
                        beforeSend: function () {
                            controls.submitButton.prop('disabled', true);
                            controls.sendingData.show();
                        },
                        success: function (data, status, xhr) {
                            if (data === null) {
                                data = {};
                            }
                            if (data.success > 0) {
                                if (data.script) {
                                    eval(data.script);
                                    return;
                                }
                                if (data.after_submission_script) {
                                    eval(data.after_submission_script);
                                }
                                if (data.redirect_url) {
                                    controls.progressText.html(options.messageRedirecting).text();

                                    // decode html entities
                                    window.location = data.redirect_url;
                                    return;
                                }
                                var successText = data.success_text;
                                if (enableAfterSubmissionForm) {
                                    controls.sendingData.hide();
                                    controls.submitButton.prop('disabled', false);
                                    controls.form[0].reset();
                                    if (typeof DROPZONE !== "undefined") {
                                        if (typeof DROPZONE['_' + uid] !== "undefined") {
                                            for (let i = 0; i < DROPZONE['_' + uid].length; i++) {
                                                DROPZONE['_' + uid][i].reset();
                                            }
                                        }
                                    }
                                    swal.fire({
                                        html: successText,
                                        type: 'success'
                                    });
                                } else {
                                    controls.progressText.html(options.messageComplete).text();
                                    controls.block.fadeOut(500, function() {
                                        controls.successText.html(successText).show();
                                        controls.successText.fadeIn(500);
                                        if (enableScrollTo) {
                                            $('html, body').animate({
                                                scrollTop: controls.successText.offset().top
                                            }, 100);
                                        }
                                    });
                                }
                            } else {
                                controls.submitButton.prop('disabled', false);
                                if (controls.sendingData)
                                    controls.sendingData.hide();
                                if (controls.submitButton)
                                    controls.submitButton.prop('disabled', false);
                                let errorTxt;
                                if (data.errors && typeof (data.errors) == "string") {
                                    errorTxt = data.errors;
                                } else {
                                    errorTxt = options.messageUnknownError;
                                }
                                swal.fire({
                                    title: options.messageError,
                                    html: errorTxt,
                                    type: 'error'
                                });
                                if (data.script) {
                                    eval(data.script);
                                }
                            }
                        },
                        error: function (xhr, status, errorThrown) {
                            console.log('Error happens. Try again.');
                            console.log(errorThrown);
                        }
                    });

                // regular submit
                } else {
                    controls.form.attr('action', url);
                    $("<input />").attr('type', 'hidden')
                        .attr('name', 'submitted_from')
                        .attr('value', JSON.stringify(
                            {
                                'url': window.location.href,
                                'title': document.title
                            })
                        )
                        .appendTo(formId);
                    $("<input />").attr('type', 'hidden')
                        .attr('name', 'referrer_page')
                        .attr('value', document.referrer)
                        .appendTo(formId);
                }
            });
        }

        return submitForm;
    });
