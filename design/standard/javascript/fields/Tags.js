(function ($) {

    var Alpaca = $.alpaca;

    Alpaca.Fields.EzTags = Alpaca.Fields.TextField.extend({
        getFieldType: function () {
            return "eztags";
        },

        beforeRenderControl: function(model, callback)
        {
            //this.field.css("position", "relative");
            //console.log(this.field);
            //this.getControlEl().attr('value', this.data);

            callback();
        },

        /**
         * @see Alpaca.Fields.TextField#afterRenderControl
         */
        afterRenderControl: function(model, callback) {

            var self = this;
            this.base(model, function() {
                self.getControlEl().attr('value', self.data);
                self.getControlEl().tagEditor({
                    forceLowercase: false,
                    onChange: function(field, editor, tags) {
                        self.getControlEl().attr('value', tags.join(', '));
                    },
                    beforeTagSave: function(field, editor, tags, tag, val) {
                        self.getControlEl().attr('value', tags.join(', '));
                    },
                    beforeTagDelete: function(field, editor, tags, val) {
                        self.getControlEl().attr('value', tags.join(', '));
                    },
                    autocomplete: {
                        minLength: 1,
                        source: function( request, response ) {
                            $.ajax( {
                                url: "/ezjscore/call/",
                                dataType: "json",
                                type:"POST",
                                data: {
                                    search_string: request.term,
                                    subtree_limit: self.options.subtree_limit,
                                    hide_root_tag: 0,
                                    locale: self.options.locale,
                                    ezjscServer_function_arguments: 'ezjsctags::autocomplete',
                                    ezxform_token: ''
                                },
                                success: function( data ) {
                                    response( $.map( data.content.tags, function( item ) {
                                        return {
                                            label: item.name,
                                            value: item.name,
                                        }
                                    }));
                                }
                            } );
                        },
                    }
                });

                callback();

            });
        }
    });

    Alpaca.registerFieldClass("eztags", Alpaca.Fields.EzTags);

})(jQuery);
