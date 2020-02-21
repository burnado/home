import Field from './field.js';

/**
 * Table Field functionality.
 *
 * @since 1.4.0
 */
class FieldTable extends Field {

    /**
     * Class constructor.
     *
     * @since 1.4.0
     */
    constructor($field) {

        super($field, 'table');

        this.initField();
    }

    /**
     * Initializes the Table field.
     *
     * @since 1.4.0
     */
    initField() {

        this.$ui = {
            actions: this.$field.find('.fieldhelpers-field-table-actions'),
            loading: this.$field.find('.fieldhelpers-field-table-loading'),
            table: this.$field.find('table'),
            thead: this.$field.find('thead'),
            tbody: this.$field.find('tbody'),
            addRow: this.$field.find('[data-table-create-row]'),
            addColumn: this.$field.find('[data-table-create-column]'),
        }

        this.l10n = RBM_FieldHelpers.l10n['field_table'] || {};

        this.name = this.$field.attr('data-table-name');

        let data = JSON.parse(this.$ui.table.attr('data-table-data'));

        this.data      = {};
        this.data.head = data.head || [];
        this.data.body = data.body || [];

        this.setupHandlers();

        // Initial build
        this.buildTable();

        // Show initially
        this.$ui.table.show();
        this.$ui.actions.show();
        this.$ui.loading.hide();
    }

    /**
     * Sets up the class handlers.
     *
     * @since 1.4.0
     */
    setupHandlers() {

        const api = this;

        this.$ui.addRow.click((e) => {

            e.preventDefault();
            this.addRow();
        });

        this.$ui.addColumn.click((e) => {

            e.preventDefault();
            this.addColumn();
        });

        this.$ui.table.on('click', '[data-delete-row]', function (e) {

            let index = jQuery(this).closest('tr').index();

            api.deleteRow(index);
        });

        this.$ui.table.on('click', '[data-delete-column]', function (e) {

            let index = jQuery(this).closest('td').index();

            api.deleteColumn(index);
        });

        this.$ui.table.on('change', 'input[type="text"]', (e) => {

            this.updateTableData();
        });
    }

    /**
     * Gathers all data from the table.
     */
    updateTableData() {

        const api = this

        // Head
        let $headCells  = this.$ui.table.find('thead th');
        let dataHead    = [];
        let currentCell = 0;

        $headCells.each(function () {

            let $input = jQuery(this).find(`input[name="${api.name}[head][${currentCell}]"]`);

            if ( !$input.length ) {

                console.error('Field Helpers Error: Table head data corrupted.');
                return false;
            }

            dataHead.push($input.val());

            currentCell++;
        });

        this.data.head = dataHead;

        // Body
        let $bodyRows  = this.$ui.table.find('tbody tr');
        let dataBody   = [];
        let currentRow = 0;

        $bodyRows.each(function () {

            // Skip delete row
            if ( jQuery(this).hasClass('fieldhelpers-field-table-delete-columns') ) {

                return true;
            }

            let rowData     = [];
            let $cells      = jQuery(this).find('td');
            let currentCell = 0;

            $cells.each(function () {

                // Skip delete cell
                if ( jQuery(this).hasClass('fieldhelpers-field-table-delete-row') ) {

                    return true;
                }

                let $input = jQuery(this).find(`input[name="${api.name}[body][${currentRow}][${currentCell}]"]`);

                if ( !$input.length ) {

                    console.error('Field Helpers Error: Table body data corrupted.');
                    return false;
                }

                rowData.push($input.val());

                currentCell++;
            });

            dataBody.push(rowData);

            currentRow++;
        });

        this.data.body = dataBody;
    }

    /**
     * Adds a row to the table.
     *
     * @since 1.4.0
     */
    addRow() {

        if ( !this.data.head.length ) {

            this.data.head.push('');
        }

        if ( !this.data.body.length ) {

            // Push 1 empty row with 1 empty cell
            this.data.body.push(['']);

        } else {

            let columns = this.data.body[0].length;
            let row     = [];

            for ( let i = 0; i < columns; i++ ) {
                row.push('');
            }

            this.data.body.push(row);
        }

        this.buildTable();
    }

    /**
     * Adds a column to the table.
     *
     * @since 1.4.0
     */
    addColumn() {

        if ( !this.data.body.length ) {

            // Push 1 empty row with 1 empty cell
            this.data.head.push(['']);
            this.data.body.push(['']);

        } else {

            this.data.head.push('');

            this.data.body.map((row) => {
                row.push('');
            });
        }

        this.buildTable();
    }

    /**
     * Deletes a row from the table.
     *
     * @since 1.4.0
     *
     * @param {int} index Index of row to delete.
     */
    deleteRow(index) {

        // Decrease to compensate for "delete row" at top
        index--;

        if ( this.data.body.length === 1 ) {

            this.data.head = [];
            this.data.body = [];

        } else {

            this.data.body.splice(index, 1);
        }


        this.buildTable();
    }

    /**
     * Deletes a column from the table.
     *
     * @since 1.4.0
     *
     * @param {int} index Index of column to delete.
     */
    deleteColumn(index) {

        if ( this.data.body[0].length === 1 ) {

            this.data.head = [];
            this.data.body = [];

        } else {

            this.data.head.splice(index, 1);

            this.data.body.map((row) =>
                row.splice(index, 1)
            );
        }

        this.buildTable();
    }

    /**
     * Builds the table based on the table data.
     *
     * @since 1.4.0
     */
    buildTable() {

        this.$ui.thead.html('');
        this.$ui.tbody.html('');

        if ( this.data.head.length ) {

            let $row = jQuery('<tr />');

            this.data.head.map((cell, cell_i) => {

                let $cell = jQuery('<th />');

                $cell.append(`<input type="text" name="${this.name}[head][${cell_i}]" />`);
                $cell.find('input[type="text"]').val(cell);

                $row.append($cell);
            });

            this.$ui.thead.append($row);
        }

        if ( this.data.body.length ) {

            let $deleteRow = jQuery('<tr class="fieldhelpers-field-table-delete-columns"></tr>');

            for ( let i = 0; i < this.data.body[0].length; i++ ) {

                $deleteRow.append(
                    '<td>' +
                    `<button type="button" data-delete-column aria-label="${this.l10n['delete_column']}">` +
                    '<span class="dashicons dashicons-no" />' +
                    '</button>' +
                    '</td>'
                );
            }

            this.$ui.tbody.append($deleteRow);

            this.data.body.map((row, row_i) => {

                let $row = jQuery('<tr/>');

                row.map((cell, cell_i) => {

                    let $cell = jQuery('<td/>');

                    $cell.append(`<input type="text" name="${this.name}[body][${row_i}][${cell_i}]" />`);
                    $cell.find('input[type="text"]').val(cell);

                    $row.append($cell);
                });

                $row.append(
                    '<td class="fieldhelpers-field-table-delete-row">' +
                    `<button type="button" data-delete-row aria-label="${this.l10n['delete_row']}">` +
                    '<span class="dashicons dashicons-no" />' +
                    '</button>' +
                    '</td>'
                );

                this.$ui.tbody.append($row);
            });
        }
    }
}

/**
 * Finds and initializes all Table fields.
 *
 * @since 1.4.0
 */
class FieldTableInitialize {

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

        let $fields = $root.find('[data-fieldhelpers-field-table]');

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
            api: new FieldTable($field),
        });
    }
}

export default FieldTableInitialize;