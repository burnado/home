import Field from './field.js';

/**
 * Color Picker Field functionality.
 *
 * @since 1.4.0
 */
class FieldColorPicker extends Field {

    /**
     * Class constructor.
     *
     * @since 1.4.0
     */
    constructor($field) {

        super($field, 'colorpicker');

        this.initializeColorpicker();
    }

    /**
     * Initializes the Color Picker.
     *
     * @since 1.4.0
     */
    initializeColorpicker() {

        this.$field.wpColorPicker();
    }


    /**
     * Cleans up after a repeater add/init.
     *
     * @since 1.4.0
     */
    fieldCleanup() {

        this.$wrapper.find('[data-fieldhelpers-field-colorpicker]')
            .appendTo(this.$wrapper.find('.fieldhelpers-field-content'));

        this.$wrapper.find('.wp-picker-container').remove();
    }
}

/**
 * Finds and initializes all Color Picker fields.
 *
 * @since 1.4.0
 */
class FieldColorPickerInitialize {

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

        let $fields = $root.find('[data-fieldhelpers-field-colorpicker]');

        if ( $fields.length ) {

            if (!jQuery.isFunction(jQuery.fn.wpColorPicker)) {

                console.error('Field Helpers Error: Trying to initialize Color Picker field but "wp-color-picker" is ' +
                    'not enqueued.');
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
            api: new FieldColorPicker($field),
        });
    }
}

export default FieldColorPickerInitialize;