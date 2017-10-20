// bug in DateTime field?
Alpaca.defaultDateFormat = "DD/MM/YYYY";
Alpaca.defaultTimeFormat = "HH:mm";

// bug in Tag field?
Array.prototype.toLowerCase = function () {
    var i = this.length;
    while (--i >= 0) {
        if (typeof this[i] === "string") {
            this[i] = this[i].toLowerCase();
        }
    }
    return this;
};

;(function(defaults, $, window, document, undefined) {
    'use strict';
    $.extend({
        opendataFormSetup: function(options) {
            return $.extend(defaults, options);
        }
    }).fn.extend({

        opendataFormEdit: function(params, options) {

            options = $.extend({}, defaults, options);

            if (jQuery.type(params.class) == 'undefined' && jQuery.type(params.object) == 'undefined') {
                throw new Error('Missing class/object parameter');
            }

            var connector = options.connector;

            var tokenNode = document.getElementById('ezxform_token_js');
            if ( tokenNode ){
                Alpaca.CSRF_TOKEN = tokenNode.getAttribute('title');
            }

            if (options.nocache) {
                var d = new Date();
                params.nocache = d.getTime();
            }

            return $(this).each(function() {
                var hideButtons = function() {
                    $.each(alpacaOptions.options.form.buttons, function() {
                        var button = $('#' + this.id);
                        button.data('original-text', button.text());
                        button.text('Salvataggio in corso....');
                        button.attr('disabled', 'disabled');
                    });
                };
                var showButtons = function() {
                    $.each(alpacaOptions.options.form.buttons, function() {
                        var button = $('#' + this.id);
                        button.text(button.data('original-text'));
                        button.attr('disabled', false);
                    });
                };

                var alpacaOptions = $.extend(true, {
                    "dataSource": "/forms/connector/" + connector + "/data?" + $.param(params),
                    "schemaSource": "/forms/connector/" + connector + "/schema?" + $.param(params),
                    "optionsSource": "/forms/connector/" + connector + "/options?" + $.param(params),
                    "viewSource": "/forms/connector/" + connector + "/view?" + $.param(params),
                    "options": {
                        "form": {
                            "buttons": {
                                "submit": {
                                    "click": function() {
                                        this.refreshValidationState(true);
                                        if (this.isValid(true)) {
                                            hideButtons();
                                            var promise = this.ajaxSubmit();
                                            promise.done(function(data) {
                                                if (data.error) {
                                                    if ($.isFunction(options.onError)) {
                                                        options.onError(data);
                                                    }
                                                    showButtons();
                                                } else {
                                                    if ($.isFunction(options.onSuccess)) {
                                                        options.onSuccess(data);
                                                    }
                                                }
                                            });
                                            promise.fail(function(error) {
                                                if ($.isFunction(options.onError)) {
                                                    options.onError(data);
                                                }
                                                showButtons();
                                            });
                                        }
                                    },
                                    "id": 'form-submit',
                                    "value": "Salva",
                                    "styles": "btn btn-lg btn-success pull-right"
                                }
                            }
                        }
                    }
                }, options.alpaca);

                if (params.view == 'display') {
                    $.each(options.options.form.buttons, function() {
                        options.options.form.buttons.styles += ' hide';
                    })
                }

                if ($.isFunction(options.onBeforeCreate)) {
                    options.onBeforeCreate();
                }

                $(this).alpaca('destroy').addClass('clearfix').alpaca(alpacaOptions);

            });
        },

        opendataFormCreate: function(params, options) {

            if (jQuery.type(params.class) == 'undefined') {
                throw new Error('Missing class parameter');
            }

            return $(this).opendataFormEdit(params, options);
        },

        opendataFormView: function(params, options) {

            options = $.extend({}, defaults, options);

            if (jQuery.type(params) == 'string' || jQuery.type(params) == 'number') {
                params = {
                    object: params
                };
            }

            if (jQuery.type(params.object) == 'undefined') {
                throw new Error('Missing object parameter');
            }

            var connector = options.connector;

            if (options.nocache) {
                var d = new Date();
                params.nocache = d.getTime();
            }

            params.view = 'display';

            return $(this).each(function() {

                var alpacaOptions = $.extend(true, {
                    "dataSource": "/forms/connector/" + connector + "/data?" + $.param(params),
                    "schemaSource": "/forms/connector/" + connector + "/schema?" + $.param(params),
                    "optionsSource": "/forms/connector/" + connector + "/options?" + $.param(params),
                    "viewSource": "/forms/connector/" + connector + "/view?" + $.param(params),
                    "options": {
                        "form": {
                            "buttons": {
                                "submit": {
                                    "click": function() {},
                                    "id": '',
                                    "value": "",
                                    "styles": "hide"
                                }
                            }
                        }
                    }
                }, options.alpaca);

                if ($.isFunction(options.onBeforeCreate)) {
                    options.onBeforeCreate();
                }
                $(this).alpaca('destroy').alpaca(alpacaOptions);
            });
        }
    });
})({
    nocache: true,
    onSuccess: null,
    onError: function(data) {
        alert(data.error);
    },
    onBeforeCreate: null,
    alpaca: null,
    connector: 'default'
}, jQuery, window, document);
