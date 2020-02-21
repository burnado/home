import Field from './field.js';

/**
 * Time Picker Field functionality.
 *
 * Also includes Date/Time Picker and Time Picker.
 *
 * @since 1.4.0
 */
class FieldTimePicker extends Field {

    /**
     * Class constructor.
     *
     * @since 1.4.0
     */
    constructor($field) {

        super($field, 'timepicker');

        this.initField();
    }

    /**
     * Initializes the Time Picker.
     *
     * @since 1.4.0
     */
    initField() {

        this.$hiddenField = this.$field.next('input[type="hidden"]');

        let option_functions = ['beforeShow', 'beforeShowDay', 'calculateWeek', 'onChangeMonthYear', 'onClose', 'onSelect'];
        let options          = {};

        if ( RBM_FieldHelpers['datepicker_args_' + name] ) {

            options = RBM_FieldHelpers['datepicker_args_' + name];
        }

        // Function support
        jQuery.each(this.options.timepickerOptions, (name, value) => {

            if ( option_functions.indexOf(name) !== -1 &&
                !jQuery.isFunction(this.options.timepickerOptions[name]) &&
                jQuery.isFunction(window[value]) ) {

                this.options.timepickerOptions[name] = window[value];
            }
        });

        options.altField  = this.$hiddenField;

        this.$field.timepicker(this.options.timepickerOptions);
    }

    /**
     * Cleans up after a repeater add/init.
     *
     * @since 1.4.0
     */
    fieldCleanup() {

        this.$field
            .removeClass('hasDatepicker')
            .removeAttr('id');
    }
}

/**
 * Finds and initializes all Time Picker fields.
 *
 * @since 1.4.0
 */
class FieldTimePickerInitialize {

    /**
     * Class constructor.
     *
     * @since 1.4.0
     *
     * @param {jQuery} $root Root element to initialize fields inside.
     */
    constructor($root) {

        const api = this;

        this.fields = [];

        let $fields = $root.find('[data-fieldhelpers-field-timepicker]');

        if ( $fields.length ) {

            if ( !jQuery.isFunction(jQuery.fn.timepicker) ) {

                console.error('Field Helpers Error: Trying to initialize Time Picker field but ' +
                    '"jquery-ui-datetimepicker" is not enqueued.');
                return;
            }

            $fields.each(function () {

                api.initializeField(jQuery(this));
            });
        }
    }

    /**
     * Initializes the field.
     *
     * @since 1.4.0
     *
     * @param {jQuery} $field
     */
    initializeField($field) {

        this.fields.push({
            $field,
            api: new FieldTimePicker($field),
        });
    }
}

export default FieldTimePickerInitialize;