define([
    'ko',
    'jquery',
    'mage/translate',
    'Magento_Ui/js/form/element/select'
], function (ko, $, $t, Select) {
    return Select.extend({
        defaults: {
            url: '',
        },

        getQuickresponse: function () {
            if (!this.url) return;
            if (!this.value()) {
                alert($t('Please select quick response from the list'));
                return;
            }
            var self = this;
            $.ajax({
                url: this.url,
                data: {
                    quickresponse_id: this.value(),
                },
                type: 'POST',
                dataType: 'json',
                success: function (data, status, xhr) {
                    self.insertAtCursor('message', data.message || '');
                }
            });
        },

        insertAtCursor: function(myFieldName, myValue) {
            var myField = $("textarea[name='" + myFieldName + "']")[0];

            //IE support
            if (document.selection) {
                myField.focus();
                sel = document.selection.createRange();
                sel.text = myValue;
            }
            //MOZILLA and others
            else if (myField.selectionStart || myField.selectionStart == '0') {
                var startPos = myField.selectionStart;
                var endPos = myField.selectionEnd;
                myField.value = myField.value.substring(0, startPos)
                    + myValue
                    + myField.value.substring(endPos, myField.value.length);
                myField.selectionStart = startPos + myValue.length;
                myField.selectionEnd = startPos + myValue.length;
            } else {
                myField.value += myValue;
            }

            if (tinyMCE) {
                if (tinyMCE.activeEditor) {
                    tinyMCE.activeEditor.execCommand('mceInsertContent', false, myValue);
                } else {
                    if(tinyMCE.execInstanceCommand) {
                        tinyMCE.execInstanceCommand(myFieldName, "mceInsertContent", false, myValue);
                    } else {
                        $(myFieldName).val(function( i, val ) {
                            return val + myValue;
                        });
                    }
                }
            }
            var target = ko.contextFor(myField);
            if (target) {
                target.element.value(myField.value);
            }
        },
    });
});
