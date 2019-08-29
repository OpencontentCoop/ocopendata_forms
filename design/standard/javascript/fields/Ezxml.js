(function ($) {

    var Alpaca = $.alpaca;

    Alpaca.Fields.EzOnLineEditorField = Alpaca.Fields.TextAreaField.extend(
        {
            getFieldType: function () {
                return "ezoe";
            },

            setup: function () {
                var self = this;

                if (!this.data) {
                    this.data = "";
                }

                if (!self.options.oesettings) {
                    self.options.oesettings = {};
                }

                this.base();
            },

            setValue: function (value) {
                var self = this;

                // be sure to call into base method
                this.base(value);

                if (self.editor) {
                    self.editor.setContent(value);
                }
            },

            getControlValue: function () {
                var self = this;

                var value = null;

                if (self.editor) {
                    value = self.editor.getContent()
                }

                return value;
            },

            afterRenderControl: function (model, callback) {
                var self = this;

                this.base(model, function () {

                    if (!self.isDisplayOnly() && self.control && typeof(tinyMCE) !== "undefined") {
                        // wait for Alpaca to declare the DOM swapped and ready before we attempt to do anything with editor
                        self.on("ready", function () {

                            if (!self.editor) {
                                var rteFieldID = $(self.control)[0].id;

                                (function(){
                                    var uri = document.location.protocol + '//' + document.location.host + self.options.oesettings.ez_tinymce_url, tps = self.options.oesettings.plugins.split(','), pm = tinymce.PluginManager, tp;
                                    tinymce.ScriptLoader.markDone( uri.replace( 'tiny_mce', 'langs/' + self.options.oesettings.language ) );
                                    for (var i = 0, l = tps.length; i < l; i++)
                                    {
                                        tp = tps[i].slice(1);
                                        pm.urls[ tp ] = uri.replace( 'tiny_mce.js', 'plugins/' + tp );
                                    }
                                }());

                                var tinyOptions = $.extend(true,
                                    self.options.oesettings,
                                    {
                                        init_instance_callback: function (editor) {
                                            self.editor = editor;
                                        },
                                        paste_preprocess : function(pl, o) {
                                            var ed = pl.editor, uid, elt, prev, bm;

                                            // Strip <a> HTML tags from clipboard content (Happens on Internet Explorer)
                                            o.content = o.content.replace( /(\s[a-z]+=")<a\s[^>]+>([^<]+)<\/a>/gi, '$1$2' );
                                            // Strip namespaced tags, avoids issues with Word's "Smart Tags"
                                            o.content = o.content.replace(/<\/?[^<>\s]+:[^<>]+>/g, '');

                                            // Workaround for https://jira.ez.no/browse/EZP-21903
                                            // http://www.tinymce.com/develop/bugtracker_view.php?id=6483
                                            if ( tinymce.isWebKit ) {
                                                uid = tinymce.DOM.uniqueId();
                                                ed.focus();
                                                bm = ed.selection.getBookmark();
                                                ed.execCommand('mceInsertContent', false, '<span id="' + uid + '"></span>');
                                                elt = ed.getDoc().getElementById(uid);

                                                if ( !elt.nextSibling || elt.nextSibling.nodeValue === "" ) {
                                                    // we are at the end of the line
                                                    // if it ends with only a non break space, we transform it into a normal space
                                                    prev = elt.previousSibling;

                                                    if ( prev && prev.nodeType === 3 && !prev.nodeValue.match(/ \u00a0$/) ) {
                                                        prev.nodeValue = prev.nodeValue.replace(/\u00a0$/, ' ');
                                                    }
                                                }
                                                ed.dom.remove(elt);
                                                ed.selection.moveToBookmark(bm);
                                            }
                                        },
                                        paste_postprocess: function(pl, o) {
                                            // removes \n after <br />, this is for paste of text
                                            // with soft carriage return from Word in Firefox
                                            // see issue http://issues.ez.no/18702
                                            o.node.innerHTML = o.node.innerHTML.replace(/<br\s?.*\/?>\n/gi,'<br>');
                                            if (
                                                pl.editor.pasteAsPlainText
                                                && o.node.childNodes.length === 1
                                                && o.node.firstChild.tagName
                                                && o.node.firstChild.tagName.toLowerCase() === 'pre'
                                            ) {
                                                o.node.innerHTML = o.node.firstChild.innerHTML.replace(/\n/g, "<br />");
                                            }
                                        },
                                        selector: "#" + rteFieldID
                                    }
                                );
                                new tinymce.Editor(rteFieldID, tinyOptions).render();
                            }
                        });
                    }

                    callback();
                });
            },

            destroy: function () {
                var self = this;

                // destroy the plugin instance
                if (self.editor) {
                    self.editor.remove();
                    self.editor = null;
                }

                // call up to base method
                this.base();
            },


            getTitle: function () {
                return "eZ Online Editor";
            },

            getDescription: function () {
                return "Provides an instance of a eZ Online Editor control for use in editing HTML.";
            },

            getSchemaOfOptions: function () {
                return Alpaca.merge(this.base(), {
                    "properties": {
                        "settings": {
                            "title": "eZ Online Editor TinyMCE settings options",
                            "description": "Settings for eZ Online Editor TinyMCE plugin.",
                            "type": "string"
                        }
                    }
                });
            },

            getOptionsForOptions: function () {
                return Alpaca.merge(this.base(), {
                    "fields": {
                        "settings": {
                            "type": "object"
                        }
                    }
                });
            }

        });

    Alpaca.registerFieldClass("ezoe", Alpaca.Fields.EzOnLineEditorField);

})(jQuery);
