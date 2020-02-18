import Field from './field.js';

/**
 * Number Field functionality.
 *
 * @since 1.4.0
 */
class FieldNumber extends Field {

    /**
     * Class constructor.
     *
     * @since 1.4.0
     */
    constructor($field) {

        super($field, 'number');

        this.initField();
    }

    /**
     * Initializes the Number field.
     *
     * @since 1.4.0
     */
    initField() {

        this.$ui = {
            container: this.$field,
            input: this.$field.find('.fieldhelpers-field-input'),
            increase: this.$field.find('[data-number-increase]'),
            decrease: this.$field.find('[data-number-decrease]'),
        };

        this.intervals = {
            increase: {
                normal: parseInt(this.options.increaseInterval),
                alt: parseInt(this.options.altIncreaseInterval),
            },
            decrease: {
                normal: parseInt(this.options.decreaseInterval),
                alt: parseInt(this.options.altDecreaseInterval),
            },
        }

        let constrainMax = this.options.max;
        let constrainMin = this.options.min

        this.constraints = {
            max: constrainMax !== 'none' ? parseInt(constrainMax) : false,
            min: constrainMin !== 'none' ? parseInt(constrainMin) : false,
        }

        this.shiftKeyUtility();
        this.setupHandlers();

        let initialValue = this.$ui.input.val();
        this.value       = !initialValue ? 0 : parseInt(initialValue);

        // Initializes the field
        this.validateInput();
    }

    /**
     * Helps determine shift key press status.
     *
     * @since 1.4.0
     */
    shiftKeyUtility() {

        this.shiftKeyDown = false;

        jQuery(document).on('keydown', (e) => {

            if ( e.which === 16 ) {

                this.shiftKeyDown = true;
            }
        });

        jQuery(document).on('keyup', (e) => {

            if ( e.which === 16 ) {

                this.shiftKeyDown = false;
            }
        });
    }

    /**
     * Sets up the class handlers.
     *
     * @since 1.4.0
     */
    setupHandlers() {

        this.$ui.increase.click((e) => {

            this.increaseNumber(e);
        });

        this.$ui.decrease.click((e) => {

            this.decreaseNumber(e);
        });

        this.$ui.input.change((e) => {

            this.inputExternalChange(e);
        });
    }

    /**
     * Increases the input number.
     *
     * @since 1.4.0
     */
    increaseNumber() {

        let amount    = this.shiftKeyDown ? this.intervals.increase.alt : this.intervals.increase.normal;
        let newNumber = this.value + amount;

        this.$ui.input.val(newNumber);
        this.$ui.input.trigger('change');
    }

    /**
     * Decreases the input number.
     *
     * @since 1.4.0
     */
    decreaseNumber() {

        let amount    = this.shiftKeyDown ? this.intervals.decrease.alt : this.intervals.decrease.normal;
        let newNumber = this.value - amount;

        this.$ui.input.val(newNumber);
        this.$ui.input.trigger('change');
    }

    /**
     * Fires on the input change. Typically from user typing or other scripts modifying.
     *
     * @since 1.4.0
     */
    inputExternalChange() {

        this.validateInput();
    }

    /**
     * Runs number through constrains.
     *
     * @param {int} number
     *
     * @return {Object}
     */
    constrainNumber(number) {

        let status = 'unmodified';

        if ( this.constraints.max !== false && number > this.constraints.max ) {

            status = 'max';
            number = this.constraints.max;

        } else if ( this.constraints.min !== false && number < this.constraints.min ) {

            status = 'min';
            number = this.constraints.min;
        }


        return {
            status,
            number,
        }
    }

    /**
     * Runs input value through constraints to ensure it is accurate.
     *
     * @since 1.4.0
     */
    validateInput() {

        let currentValue = this.$ui.input.val();

        // Constrain to numbers
        let matches  = currentValue.match(/^-?[0-9]\d*(\.\d+)?$/);
        currentValue = (matches && parseInt(matches[0])) || 0;

        let constraints = this.constrainNumber(currentValue);

        switch ( constraints.status ) {

            case 'max':

                this.toggleDecreaseDisabledUI(true);
                this.toggleIncreaseDisabledUI(false);
                break;

            case 'min':

                this.toggleIncreaseDisabledUI(true);
                this.toggleDecreaseDisabledUI(false);
                break;

            default:

                this.toggleIncreaseDisabledUI(true);
                this.toggleDecreaseDisabledUI(true);

        }

        this.value = constraints.number;
        this.$ui.input.val(this.value);

        if ( currentValue !== this.value ) {

            this.$ui.input.trigger('change');
        }
    }

    /**
     * Disables/Enables the increase button.
     *
     * @since 1.4.0
     *
     * @param {bool} enable True to set to enabled, false to set to disabled
     */
    toggleIncreaseDisabledUI(enable) {

        this.$ui.increase.prop('disabled', !enable);
    }

    /**
     * Disables/Enables the decrease button.
     *
     * @since 1.4.0
     *
     * @param {bool} enable True to set to enabled, false to set to disabled
     */
    toggleDecreaseDisabledUI(enable) {

        this.$ui.decrease.prop('disabled', !enable);
    }
}

/**
 * Finds and initializes all Number fields.
 *
 * @since 1.4.0
 */
class FieldNumberInitialize {

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

        let $fields = $root.find('[data-fieldhelpers-field-number]');

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
            api: new FieldNumber($field),
        });
    }
}

export default FieldNumberInitialize;