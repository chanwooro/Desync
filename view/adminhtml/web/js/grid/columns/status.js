/**
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
define([
    'Magento_Ui/js/grid/columns/column'
], function (Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/html'
        },

        /**
         * Returns the html string
         *
         * @returns {String} html span for grid.
         */
        getLabel: function (row) {
            return row[this.index + '_html'];
        }
    });
});
