import Field from './field.js';

/**
 * List Field functionality.
 *
 * @since 1.4.0
 */
class FieldList extends Field {

    /**
     * Class constructor.
     *
     * @since 1.4.0
     */
    constructor($field) {

        super($field, 'list');

        this.initField();
    }

    /**
     * Initializes the list.
     *
     * @since 1.4.0
     */
    initField() {

        this.$field.sortable(this.options);
    }
}

/**
 * Finds and initializes all List fields.
 *
 * @since 1.4.0
 */
class FieldListInitialize {

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

        let $fields = $root.find('[data-fieldhelpers-field-list]');

        if ( $fields.length ) {

            if ( !jQuery.isFunction(jQuery.fn.sortable) ) {

                console.error('Field Helpers Error: Trying to initialize List field but "jquery-ui-sortable" ' +
                    'is not enqueued.');
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
            api: new FieldList($field),
        });
    }
}

export default FieldListInitialize;