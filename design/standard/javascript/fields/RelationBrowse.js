(function ($) {

    var Alpaca = $.alpaca;

    Alpaca.Fields.RelationBrowse = Alpaca.Fields.ArrayField.extend({
        getFieldType: function () {
            return "relationbrowse";
        },

        setup: function () {

            Alpaca.merge(this.schema, {
                "type": "array",
                "items": {
                    "type": "object",
                    "properties": {
                        "id": {
                            "type": "string"
                        },
                        "name": {
                            "type": "string"
                        }
                    }
                }
            });

            Alpaca.merge(this.options, {
                "toolbarSticky": true,
                "items": {
                    "fields": {
                        "id": {
                            "view": "bootstrap-display",
                            "type": "hidden"
                        },
                        "name": {"view": "bootstrap-display"}
                    }
                }
            });

            this.base();
        },

        /**
         * @see Alpaca.Field#afterRenderContainer
         */
        afterRenderContainer: function (model, callback) {

            var self = this;

            this.base(model, function () {
                var container = self.getContainerEl();

                self.options.browse.initOnCreate = false;

                self.browser = $('<div></div>')
                    .prependTo(container)
                    .opendataBrowse(self.options.browse)
                    .hide();

                callback();

            });
        },

        addedItems: [],

        /**
         * Adds an item to the array.
         *
         * This gets called from the toolbar when items are added via the user interface.  The method can also
         * be called programmatically to insert items on the fly.
         *
         * @param {Integer} index the index where the item should be inserted
         * @param {Object} schema the json schema
         * @param {Object} options the json options
         * @param {Any} data the data for the newly inserted item
         * @param [Function] callback called after the child is added
         */
        addItem: function (index, schema, options, data, callback) {
            var self = this;
            var toolbarEl = $(self.getFieldEl()).find(".alpaca-array-toolbar[data-alpaca-array-toolbar-field-id='" + self.getId() + "']");

            self.browser.show('fast', function(){
                self.browser.data('plugin_opendataBrowse').init();
            });
            $(toolbarEl).hide();

            self.browser.on('opendata.browse.close', function (event, opendataBrowse) {
                self.browser.hide();
                if (self.children.length === 0){
                    $(toolbarEl).show();
                }
                event.stopPropagation();
            });

            self.browser.on('opendata.browse.select', function (event, opendataBrowse) {

                self.browser.hide();
                self.browser.off('opendata.browse.select');

                $.each(opendataBrowse.selection, function () {
                    var data = {
                        id: this.contentobject_id,
                        name: this.name + ' (' + this.class_name + ')'
                    };

                    if (self._validateEqualMaxItems()) {

                        self.addedItems.push(data);

                        self.createItem(index, schema, options, data, function (item) {
                            // register the child
                            self.registerChild(item, index);

                            // insert into dom
                            self.doAddItem(index, item, function () {

                                // updates dom markers for this element and any siblings
                                self.handleRepositionDOMRefresh();

                                // update the array item toolbar state
                                self.updateToolbars();

                                // refresh validation state
                                self.refreshValidationState();

                                // dispatch event: add
                                self.trigger("add", item);

                                // trigger update
                                self.triggerUpdate();

                                if (callback) {
                                    callback(item);
                                }

                            });
                        });
                    }

                });
                opendataBrowse.reset();
                event.stopPropagation();
            });
        },

        removeItem: function (childIndex, callback) {
            var self = this;

            var toolbarEl = $(self.getFieldEl()).find(".alpaca-array-toolbar[data-alpaca-array-toolbar-field-id='" + self.getId() + "']");

            if (this._validateEqualMinItems()) {
                // unregister the child
                self.unregisterChild(childIndex);

                // remove itemContainerEl from DOM
                self.doRemoveItem(childIndex, function () {

                    self.browser.hide();

                    // updates dom markers for this element and any siblings
                    self.handleRepositionDOMRefresh();

                    // update the array item toolbar state
                    self.updateToolbars();

                    // refresh validation state
                    self.refreshValidationState();

                    // dispatch event: remove
                    self.trigger("remove", childIndex);

                    // trigger update
                    self.triggerUpdate();

                    if (callback) {
                        callback();
                    }

                });
            }
        },

        getSchemaOfOptions: function () {
            return Alpaca.merge(this.base(), {
                "properties": {
                    "browse": {
                        "title": "Browse Configuration",
                        "description": "Optional configuration to be passed to the underlying Browse Plugin.",
                        "type": "object"
                    }
                }
            });
        },

        getOptionsForOptions: function () {
            return Alpaca.merge(this.base(), {
                "fields": {
                    "browse": {
                        "type": "object"
                    }
                }
            });
        },

        getMessage: function(key)
        {
            if (key == "addItemButtonLabel"){
                return "Seleziona";
            }
            return this.view.getMessage(key, this.view.locale);
        }
    });

    Alpaca.registerFieldClass("relationbrowse", Alpaca.Fields.RelationBrowse);

})(jQuery);
