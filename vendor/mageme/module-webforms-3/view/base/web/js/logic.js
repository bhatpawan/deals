/* WebForms 3.0.0 */
'use strict'
define(['jquery'], function ($) {

    var Targets = [];

    function JsWebFormsLogicRuleCheck(logic, fieldNamePart, isAdmin) {
        var FLAG = false;
        var input = [];
        var field_type = 'select';
        var selected = 'selected';
        var fieldSelector = isAdmin ? "[name^='" + fieldNamePart + "[field][" + logic["field_id"] + "]']" :
            "[name^='field[" + logic["field_id"] + "]']";
        $(fieldSelector).each(function (i, element) {
            if (element.type === 'radio') {
                field_type = 'radio';
                selected = 'checked';
                input.push(element);
            } else if(element.type === 'checkbox') {
                field_type = 'checkbox';
                selected = 'checked';
                input.push(element);
            } else {
                input.push({'value': $(this).val(), selected: true, input: this});
            }
        });
        var value;
        if (logic['aggregation'] === 'any' || (logic['aggregation'] === 'all' && logic['logic_condition'] === 'notequal')) {
            if (logic['logic_condition'] === 'equal') {
                for (var i = 0; i < input.length; i++) {
                    if (typeof(input[i]) == 'object' && input[i]) {
                        if (input[i][selected]) {
                            for (var j = 0; j < logic['value'].length; j++) {
                                value = FieldIsVisible(logic["field_id"], fieldNamePart, isAdmin) ? input[i].value : false;
                                if (!value) {
                                    value = GetLogicDataValue(input[i]);
                                }
                                if (!Array.isArray(value)) value = [value];
                                if (value.includes(logic['value'][j])) FLAG = true;
                            }
                        }
                    }
                }
            } else {
                FLAG = true;
                var checked = false;
                for (var i = 0; i < logic['value'].length; i++) {
                    for (var j = 0; j < input.length; j++) {
                        if (typeof(input[j]) == 'object' && input[j])
                            if (input[j][selected]) {
                                checked = true;
                                value = FieldIsVisible(logic["field_id"], fieldNamePart, isAdmin) ? input[j].value : false;
                                if (!value) {
                                    value = GetLogicDataValue(input[j]);
                                }
                                if (!Array.isArray(value)) value = [value];
                                if (value.includes(logic['value'][i])) FLAG = false;
                            }
                    }
                }
                if (!checked) FLAG = false;
            }
        } else {
            FLAG = true;
            for (var i = 0; i < logic['value'].length; i++) {
                for (var j = 0; j < input.length; j++) {
                    if (typeof(input[j]) == 'object' && input[j]) {
                        value = FieldIsVisible(logic["field_id"], fieldNamePart, isAdmin) ? input[j].value : false;
                        if (!value) {
                            value = GetLogicDataValue(input[j]);
                        }
                        if (!Array.isArray(value)) value = [value];
                        if (!input[j][selected] && value.includes(logic['value'][i])) FLAG = false;
                    }
                }
            }
        }
        return FLAG;
    }

    function JsWebFormsLogicTargetCheck(target, logicRules, fieldMap, fieldNamePart, isAdmin) {
        if (typeof(target) != 'object') return false;
        for (var i = 0; i < logicRules.length; i++) {
            if (typeof(logicRules[i]) == 'object') {
                for (var j = 0; j < logicRules[i]['target'].length; j++) {
                    if (target["id"] === logicRules[i]['target'][j]) {
                        var FLAG = JsWebFormsLogicRuleCheck(logicRules[i], fieldNamePart, isAdmin);
                        var currentRule = logicRules[i];
                        var display = 'block';
                        if (FLAG) {
                            if (currentRule['action'] === 'hide') {
                                display = 'none';
                            }
                            Targets[target["id"]] = {
                                display: display,
                                flag: true
                            };
                        } else {
                            if (currentRule['action'] === 'show') {
                                display = 'none';
                            }
                            if (Targets[target["id"]]) {
                                if (!Targets[target["id"]].flag) {
                                    Targets[target["id"]] = {
                                        display: display,
                                        flag: false
                                    };
                                }
                            } else {
                                Targets[target["id"]] = {
                                    display: display,
                                    flag: false
                                };
                            }
                        }
                        var jTargetId = '#' + target["id"];
                        if (isAdmin) {
                            jTargetId += '_container';
                        }
                        var jTarget = $(jTargetId);
                        if (jTarget.length &&
                            jTarget[0] !== null &&
                            jTarget[0].style !== undefined) {

                            if (Targets[target["id"]].display === 'none') {
                                jTarget.hide();

                                // Fix for multistep
                                jTarget[0].querySelectorAll("[data-logic-value]").forEach((el) => {
                                    el.setAttribute("data-logic-value", "");
                                })
                            } else {
                                jTarget.show();

                                // Fix for multistep
                                jTarget[0].querySelectorAll("[data-logic-value]").forEach((el) => {
                                    if (GetLogicDataValue(el)) {
                                        return;
                                    }
                                    if (el.value) {
                                        el.setAttribute("data-logic-value", el.value);
                                        try {
                                            el.onchange();
                                        } catch (e) {
                                            try {
                                                el.onclick();
                                            } catch (e) {
                                            }
                                        }
                                    }
                                });
                            }

                            if (isAdmin) {
                                if (Targets[target["id"]].display === 'none') {
                                    jTarget.find('.required-entry').attr('disabled', 'disabled');
                                } else {
                                    jTarget.find('.required-entry').removeAttr('disabled');
                                }
                            }
                        }

                        var jTargetRow = $(jTargetId + '_row');
                        if (jTargetRow.length && jTargetRow[0] !== null && jTargetRow[0].style !== undefined) {
                            Targets[target["id"]].display === 'none' ? jTargetRow.hide() : jTargetRow.show();
                        }

                        if (FLAG) {
                            for (var k = 0; k < logicRules.length; k++) {
                                if (typeof(logicRules[k]) == 'object' && logicRules[k] !== currentRule) {
                                    var nextRule = logicRules[k];
                                    if (typeof(target) == 'object') {
                                        var fieldsetId = isAdmin ? target["id"] : parseInt(target["id"].replace('fieldset_' + fieldNamePart, ''));
                                        var targetId = isAdmin ? 'field_' + nextRule['field_id'] : 'field_' + fieldNamePart + nextRule['field_id'];
                                        if (target["id"] === targetId || FieldInFieldset(nextRule['field_id'], fieldsetId, fieldMap, isAdmin)) {
                                            for (var n = 0; n < nextRule['target'].length; n++) {
                                                var visibility;
                                                if (nextRule['action'] === 'show') visibility = 'hidden';
                                                if (nextRule['action'] === 'hide') visibility = 'visible';
                                                if (typeof(nextRule['target'][n]) == 'string') {
                                                    var newTarget = {
                                                        'id': nextRule['target'][n],
                                                        'logic_visibility': visibility
                                                    };
                                                    JsWebFormsLogicTargetCheck(newTarget, logicRules, fieldMap, fieldNamePart, isAdmin);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    function JSWebFormsLogic(targets, logicRules, fieldMap, fieldNamePart, isAdmin = false) {
        if (!isAdmin) {
            var addTargets = [];
            for (var i = 0; i < targets.length; i++) {
                if (!targets[i].id.includes('fieldset')) continue;
                addTargets.push({...targets[i], id: 'title_' + targets[i].id});
                for (var j = 0; j < logicRules.length; j++) {
                    if (logicRules[j].target.includes(targets[i].id)) {
                        logicRules[j].target.push('title_' + targets[i].id);
                    }
                }
            }
            targets = targets.concat(addTargets);
        }
        for (var i = 0; i < logicRules.length; i++) {
            if (Array.isArray(logicRules[i]['value'])) {
                for (var j = 0; j < logicRules[i]['value'].length; j++) {
                    logicRules[i]['value'][j] = logicRules[i]['value'][j].toString();
                }
            }
        }
        for (var i = 0; i < logicRules.length; i++) {
            var rule = logicRules[i];
            if (typeof(rule) == 'object') {
                var fieldSelector = isAdmin ? "[name^='" + fieldNamePart + "[field][" + rule["field_id"] + "]']" :
                    "[name^='field[" + rule["field_id"] + "]']";
                $(fieldSelector).each(function (k, input) {
                    var trigger_function = 'onchange';
                    if (typeof(input) != 'object') {
                        trigger_function = 'onclick';
                        if (input.type === 'select-multiple') {
                            trigger_function = 'onchange';
                        }
                    } else {
                        if (input.type === 'radio') {
                            trigger_function = 'onclick';
                        }
                    }
                    if (trigger_function === 'onchange') {
                        input.onchange = function () {
                            LogicEvent(targets, logicRules, fieldMap, fieldNamePart, isAdmin, input);
                        }
                        if (input.value) {
                            input.onchange();
                        }
                    } else {
                        input.onclick = function () {
                            LogicEvent(targets, logicRules, fieldMap, fieldNamePart, isAdmin, input);
                        }
                        if (input.value) {
                            input.onclick();
                        }
                    }
                });
            }
        }
    }

    function LogicEvent(targets, logicRules, fieldMap, fieldNamePart, isAdmin, input) {
        Targets = [];
        for (var i = 0; i < targets.length; i++) {
            JsWebFormsLogicTargetCheck(targets[i], logicRules, fieldMap, fieldNamePart, isAdmin);
        }
        if (input.value) {
            input.setAttribute("data-logic-value", input.value);
        } else {
            input.removeAttribute("data-logic-value");
        }
    }

    function GetLogicDataValue(input) {
        try {
            return input.getAttribute("data-logic-value") ? input.getAttribute("data-logic-value") : false;
        } catch (e) {
            input = input.input;
            try {
                return input.getAttribute("data-logic-value") ? input.getAttribute("data-logic-value") : false;
            } catch (e) {
                return false;
            }
        }
    }

    function FieldIsVisible(fieldId, fieldNamePart, isAdmin) {
        if (isAdmin) {
            return FieldIsVisibleAdmin(fieldId);
        }
        return FieldIsVisibleFront(fieldId, fieldNamePart);
    }

    function FieldIsVisibleAdmin(fieldId) {
        var el = $('#field_' + fieldId + '_container');
        if (el.length) {
            return $(el).is(":visible");
        }
        return false;
    }

    function FieldIsVisibleFront(fieldId, uid) {
        var el = $('#field_' + uid + fieldId)[0];
        if (el !== undefined) {
            if (el.offsetWidth === 0 || el.offsetWidth === undefined) return false;
        } else {
            return false;
        }
        return true;
    }

    function FieldInFieldset(fieldId, fieldsetId, fieldMap, isAdmin) {
        if (isAdmin) {
            return FieldInFieldsetAdmin(fieldId, fieldsetId);
        }
        return FieldInFieldsetFront(fieldId, fieldsetId, fieldMap);
    }

    function FieldInFieldsetAdmin(fieldId, fieldsetId) {
        if (typeof(fieldsetId) != 'string') return false;
        return !!$('#' + fieldsetId).has('#' + fieldId).length;
    }

    function FieldInFieldsetFront(fieldId, fieldsetId, fieldMap) {
        if (isNaN(fieldsetId)) return false;
        if (!fieldMap['fieldset_' + fieldsetId]) return false;
        return fieldMap['fieldset_' + fieldsetId].includes(fieldId);
    }

    return JSWebFormsLogic;
});