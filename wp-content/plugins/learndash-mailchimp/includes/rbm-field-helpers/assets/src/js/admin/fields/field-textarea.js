import Field from './field.js';

/**
 * TextArea Field functionality.
 *
 * @since 1.4.0
 */
class FieldTextArea extends Field {

    /**
     * Class constructor.
     *
     * @since 1.4.0
     */
    constructor($field) {

        super($field, 'textarea');

        this.initField();
    }

    /**
     * Initializes the WYSIWYG.
     *
     * @since 1.4.0
     */
    initField() {

        if ( this.options.wysiwyg ) {

            if ( !wp.editor ) {

                console.error('Field Helpers Error: Trying to initialize a WYSIWYG Text Area field but "wp_editor" ' +
                    'is not enqueued.');
                return;
            }

            let settings = jQuery.extend(this.getDefaultEditorSettings(), this.options.wysiwygOptions);

            console.log(settings);
            wp.editor.initialize(this.$field.attr('id'), settings);
        }
    }

    /**
     * Resets the field.
     *
     * @since 1.4.0
     */
    fieldCleanup() {

        if ( this.options.wysiwyg ) {

            let id = this.$field.attr('id');

            if ( window.tinymce.get(id) ) {

                wp.editor.remove(id);

            } else {

                this.$field.appendTo(this.$wrapper.find('.fieldhelpers-field-content'));
                this.$wrapper.find('.wp-editor-wrap').remove();
            }
        }
    }

    /**
     * Fires before deleting the item from a repeater.
     *
     * Removes from wp.editor.
     *
     * @since 1.4.0
     */
    repeaterBeforeDeleteSelf() {

        this.fieldCleanup();
    }

    /**
     * Fires on Repeat delete item.
     *
     * Adds slight delay to field re-initialization.
     *
     * @since 1.4.0
     */
    repeaterOnDeleteItem() {

        this.fieldCleanup();
        this.repeaterSetID();

        // Add slight delay because all repeater item WYSIWYG's must be unitialized before re-initializing to prevent
        // ID overlap.
        setTimeout(() => {this.initField()}, 1);
    }

    /**
     * Fires on Repeat sort item.
     *
     * Adds slight delay to field re-initialization.
     *
     * @since 1.4.0
     */
    repeaterOnSort() {

        this.fieldCleanup();
        this.repeaterSetID();

        // Add slight delay because all repeater item WYSIWYG's must be unitialized before re-initializing to prevent
        // ID overlap.
        setTimeout(() => {this.initField()}, 1);
    }

    /**
     * Tries to get default editor settings.
     *
     * @since 1.4.0
     *
     * @return {{}}
     */
    getDefaultEditorSettings() {

        if ( ! jQuery.isFunction(wp.editor.getDefaultSettings) ) {

            return {};

        } else {

            return wp.editor.getDefaultSettings();
        }
    }
}

/**
 * Finds and initializes all TextArea fields.
 *
 * @since 1.4.0
 */
class FieldTextAreaInitialize {

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

        let $fields = $root.find('[data-fieldhelpers-field-textarea]');

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
            api: new FieldTextArea($field),
        });
    }
}

export default FieldTextAreaInitialize;