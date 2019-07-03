/**
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
define([
    'jquery'
], function ($) {
    'use strict';

    return {
        disableKeys: function () {
            $('.dsnyc-primary-key').prop('disabled', true);
        },
        enableKeys: function () {
            $('.dsnyc-primary-key').prop('disabled', false);
        }
    };
});
