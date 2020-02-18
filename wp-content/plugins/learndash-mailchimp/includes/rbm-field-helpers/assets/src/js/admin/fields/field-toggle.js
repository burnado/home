import Field from './field.js';

/**
 * Toggle Field functionality.
 *
 * @since 1.4.0
 */
class FieldToggle extends Field {

    /**
     * Class constructor.
     *
     * @since 1.4.0
     */
    constructor($field) {

        super($field, 'toggle');

        this.initField();
    }

    /**
     * Initializes the select.
     *
     * @since 1.4.0
     */
    initField() {

        this.getUI();

        // Initial change trigger to help other plugins
        setTimeout(() => {
            this.$field.trigger('change', [this.$ui.input.val()]);
        }, 1);

        this.setupHandlers();
    }

    /**
     * Retrieves the UI.
     *
     * @since 1.4.0
     */
    getUI() {

        this.$ui = {
            slider: this.$field.find('.fieldhelpers-field-toggle-slider'),
            input: this.$field.find('input[type="hidden"]'),
        }
    }

    /**
     * Sets up class handlers.
     *
     * @since 1.4.0
     */
    setupHandlers() {

        const api = this;

        this.$ui.slider.click(() => {
            api.handleClick();
        });
    }

    /**
     * Return if field is checked or not.
     *
     * @since 1.4.0
     *
     * @returns {*}
     */
    isChecked() {

        return this.$field.hasClass('checked');
    }

    /**
     * Fires on toggle change.
     *
     * @since 1.4.0
     */
    handleClick() {

        if ( this.isChecked() ) {

            this.$ui.input.val(this.options.uncheckedValue);
            this.$field.removeClass('checked');

        } else {

            this.$ui.input.val(this.options.checkedValue);
            this.$field.addClass('checked');
        }

        this.$field.trigger('change', [this.$ui.input.val()]);
    }
}

/**
 * Finds and initializes all Toggle fields.
 *
 * @since 1.4.0
 */
class FieldToggleInitialize {

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

        let $fields = $root.find('[data-fieldhelpers-field-toggle]');

        if ( $fields.length ) {

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
            api: new FieldToggle($field),
        });
    }
}

export default FieldToggleInitialize;