import FieldNumberInitialize from "./fields/field-number";
import FieldColorPickerInitialize from "./fields/field-colorpicker";
import FieldDatePickerInitialize from "./fields/field-datepicker";
import FieldTimePickerInitialize from "./fields/field-timepicker";
import FieldDateTimePickerInitialize from "./fields/field-datetimepicker";
import FieldTableInitialize from "./fields/field-table";
import FieldMediaInitialize from "./fields/field-media";
import FieldListInitialize from "./fields/field-list";
import FieldRepeaterInitialize from "./fields/field-repeater";
import FieldSelectInitialize from "./fields/field-select";
import FieldTextAreaInitialize from "./fields/field-textarea";
import FieldCheckboxInitialize from "./fields/field-checkbox";
import FieldRadioInitialize from "./fields/field-radio";
import FieldToggleInitialize from "./fields/field-toggle";

/**
 * Handles all field initializations.
 *
 * @since 1.4.0
 */
class FieldsInitialize {

    /**
     * Class constructor.
     *
     * @since 1.4.0
     *
     * @param {jQuery} $root Root element to initialize fields inside.
     */
    constructor($root) {

        this.fields = {
            checkbox: new FieldCheckboxInitialize($root),
            toggle: new FieldToggleInitialize($root),
            radio: new FieldRadioInitialize($root),
            select: new FieldSelectInitialize($root),
            textarea: new FieldTextAreaInitialize($root),
            number: new FieldNumberInitialize($root),
            colorpicker: new FieldColorPickerInitialize($root),
            datepicker: new FieldDatePickerInitialize($root),
            timepicker: new FieldTimePickerInitialize($root),
            datetimepicker: new FieldDateTimePickerInitialize($root),
            table: new FieldTableInitialize($root),
            media: new FieldMediaInitialize($root),
            list: new FieldListInitialize($root),
            repeater: new FieldRepeaterInitialize($root),
        };
    }
}

export default FieldsInitialize;