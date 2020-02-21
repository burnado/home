/**
 * Main field class.
 *
 * @since 1.4.0
 */
class Field {

    /**
     * Class constructor.
     *
     * @since 1.4.0
     *
     * @param {jQuery} $field
     * @param {string} type
     */
    constructor($field, type) {

        this.$field   = $field;
        this.$wrapper = $field.closest('.fieldhelpers-field');
        this.type     = type;
        this.name     = this.$wrapper.attr('data-fieldhelpers-name');
        this.instance = this.$wrapper.attr('data-fieldhelpers-instance');

        this.getRepeater();

        this.getOptions();

        if ( this.repeater ) {

            this.repeaterSupport();
        }
    }

    /**
     * Initializes the field.
     *
     * @since 1.4.0
     */
    initField() {
    }

    /**
     * Gets field options.
     *
     * @since 1.4.0
     */
    getOptions() {

        this.options = {};

        if ( typeof RBM_FieldHelpers[this.instance] === 'undefined' ) {

            console.error(`Field Helpers Error: Data for ${this.instance} instance cannot be found.`);
            return;
        }

        if ( this.repeater ) {

            if ( typeof RBM_FieldHelpers[this.instance]['repeaterFields'][this.repeater] === 'undefined' ) {

                console.error(`Field Helpers Error: Data for repeater ${this.type} sub-fields cannot be found.`);
                return;
            }

            if ( typeof RBM_FieldHelpers[this.instance]['repeaterFields'][this.repeater][this.name] === 'undefined' ) {

                console.error(`Field Helpers Error: Cannot find field options for repeater ${this.type} sub-field with name: ${this.name}.`);
                return;
            }

            this.options = RBM_FieldHelpers[this.instance]['repeaterFields'][this.repeater][this.name];

        } else {

            if ( typeof RBM_FieldHelpers[this.instance][this.type] === 'undefined' ) {

                console.error(`Field Helpers Error: Data for ${this.type} fields cannot be found.`);
                return;
            }

            if ( typeof RBM_FieldHelpers[this.instance][this.type][this.name] === 'undefined' ) {

                console.error(`Field Helpers Error: Cannot find field options for ${this.type} field with name: ${this.name}.`);
                return;
            }

            this.options = RBM_FieldHelpers[this.instance][this.type][this.name];
        }
    }

    /**
     * If field is in a Repeater, it will need support.
     *
     * @since 1.4.0
     */
    getRepeater() {

        if ( this.$field.closest('[data-fieldhelpers-field-repeater]').length ) {

            this.$repeater = this.$field.parent().closest('[data-fieldhelpers-field-repeater]');
            this.repeater  = this.$repeater.closest('.fieldhelpers-field-repeater').attr('data-fieldhelpers-name');
        }
    }

    /**
     * Runs some functions if inside a Repeater.
     *
     * @since 1.4.0
     */
    repeaterSupport() {

        // Triggers fields can utilize. Wrapped in anonymous to utilize self access.
        this.$repeater.on('repeater-init', () => {
            this.repeaterOnInit();
        });
        this.$repeater.on('repeater-before-add-item', () => {
            this.repeaterBeforeAddItem();
        });
        this.$repeater.on('repeater-add-item', () => {
            this.repeaterOnAddItem();
        });
        this.$field.closest('[data-repeater-item]').on('repeater-before-delete-item', () => {
            this.repeaterBeforeDeleteSelf();
        });
        this.$repeater.on('repeater-before-delete-item', () => {
            this.repeaterBeforeDeleteItem();
        });
        this.$repeater.on('repeater-delete-item', () => {
            this.repeaterOnDeleteItem();
        });
        this.$repeater.find('.fieldhelpers-field-repeater-list').on('list-update', () => {
            this.repeaterOnSort();
        });

        this.repeaterSetID();
        this.fieldCleanup();
    }

    /**
     * Fires on Repeater init.
     *
     * @since 1.4.0
     */
    repeaterOnInit() {
    }

    /**
     * Fires before Repeater add item.
     *
     * @since 1.4.0
     */
    repeaterBeforeAddItem() {
    }

    /**
     * Fires on Repeater add item.
     *
     * @since 1.4.0
     */
    repeaterOnAddItem() {
    }

    /**
     * Fires before Repeater delete item (localized to self).
     *
     * @since 1.4.0
     */
    repeaterBeforeDeleteSelf() {
    }

    /**
     * Fires before Repeater delete item.
     *
     * @since 1.4.0
     */
    repeaterBeforeDeleteItem() {
    }

    /**
     * Fires on Repeater delete item.
     *
     * @since 1.4.0
     */
    repeaterOnDeleteItem() {
    }

    /**
     * Fires on Repeat sort item.
     *
     * @since 1.4.0
     */
    repeaterOnSort() {
    }

    /**
     * Sets the ID to be unique, based off the repeater item index.
     *
     * @since 1.4.0
     */
    repeaterSetID() {

        let index = this.$field.closest('[data-repeater-item]').index();
        let newID = `${this.options.id}_${index}`;

        this.$field.attr('id', newID);
    }

    /**
     * Cleans up after a repeater add/init.
     *
     * @since 1.4.0
     */
    fieldCleanup() {
    }

    /**
     * Sets the field to default. Override in child class if need different method.
     *
     * @since 1.4.0
     */
    setDefault() {

        if ( this.options.default ) {

            this.$field.val(this.options.default).change();
        }
    }
}

export default Field;