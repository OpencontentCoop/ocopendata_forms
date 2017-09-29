;(function ($, window, document, undefined) {

    "use strict";

    var pluginName = "opendataBrowse",
        defaults = {            
            subtree: 1,
            classes: false,
            selectionType: 'multiple',
            language: 'ita-IT',
            browsePaginationLimit: 10,
            browseSort: 'published',
            browseOrder: '0',
            openInSearchMode: false,
            addCloseButton: false,
            addCreateButton: false,
            createSettings: {
                'connector': 'default',
                'options': {}
            }
        };

    function Plugin(element, options) {
        this.element = element;
        this.settings = $.extend({}, defaults, options);
        this._defaults = defaults;
        this._name = pluginName;
        this.iconStyle = 'line-height: 0.7;display:table-cell;padding-right:5px;font-size:1.5em';
        this.selection = [];
        this.browseParameters = {};

        if (
            this.settings.classes == false
            || typeof $.fn.alpaca == 'undefined'
        ){
            this.settings.addCreateButton = false;
        }

        this.resetBrowseParameters();

        this.init();

        this.reset = function(){
            this.emptySelection();            
            this.resetBrowseParameters();
            this.buildTreeSelect();             
        };
    }

    // Avoid Plugin.prototype conflicts
    $.extend(Plugin.prototype, {
        
        init: function () {            
            this.browserContainer = $('<div></div>').appendTo($(this.element));
            this.selectionContainer = $('<div></div>').appendTo($(this.element));   
            this.buildTreeSelect();
            if (this.settings.openInSearchMode){
                this.resetBrowseParameters();
                this.buildSearchSelect();
                this.searchInput.trigger('keyup');
            }            
        },   

        resetBrowseParameters: function(){
            this.browseParameters = {
                subtree: this.settings.subtree || 1,
                limit: this.settings.browsePaginationLimit || 25,
                offset: 0,
                sort: this.settings.browseSort || 'priority',
                order: this.settings.browseOrder || 1
            };
        },

        buildPanelHeader: function(panel,isTree,isSearch,isCreate){
            var self = this;
            var panelHeading = $('<div class="panel-heading clearfix" style="padding: 5px 15px 0"></div>').appendTo(panel);

            if (self.settings.addCloseButton) {
                var closeButton = $('<a class="btn btn-lg btn-link pull-right" href="#"><span class="glyphicon glyphicon-remove"></span></a>');
                closeButton.bind('click', function (e) {
                    if (isCreate){
                        if (self.settings.openInSearchMode){
                            self.resetBrowseParameters();
                            self.buildSearchSelect();
                            self.searchInput.trigger('keyup');
                        }else{
                            self.resetBrowseParameters();
                            self.buildTreeSelect();
                        }
                    }
                    $(self.element).trigger('opendata.browse.close', self);
                    e.preventDefault();
                });
                panelHeading.append(closeButton);
            }

            if (isTree || isCreate) {
                var searchButton = $('<a class="btn btn-lg btn-link pull-right" href="#"><span class="glyphicon glyphicon-search"></span></a>');
                searchButton.bind('click', function (e) {
                    self.resetBrowseParameters();
                    self.buildSearchSelect();
                    e.preventDefault();
                });
                panelHeading.append(searchButton);
            }

            if (isSearch || isCreate){
                var treeButton = $('<a class="btn btn-lg btn-link pull-right" href="#"><span class="glyphicon glyphicon-th-list"></span></a>');
                treeButton.bind('click', function(e){
                    self.resetBrowseParameters();
                    self.buildTreeSelect();
                    e.preventDefault();
                });
                panelHeading.append(treeButton);
            }

            if (self.settings.addCreateButton == true) {
                var detectError = function(response,jqXHR){
                    if(response.error_message || response.error_code){
                        console.log(response.error_message);
                        return true;
                    }
                    return false;
                };
                var createButtonGroup = $('<div class="btn-group pull-right"></div>');
                createButtonGroup.append($('<button type="button" class="btn btn-lg btn-link dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-plus"></span> <span class="caret"></span> </button>'));
                var list = $('<ul class="dropdown-menu"></ul>');
                $.each(self.settings.classes, function(){
                    var classIdentifier = this;
                    var listItem = $('<li></li>');
                    $.ajax({
                        type: "GET",
                        url: '/opendata/api/classes/'+classIdentifier,
                        contentType: "application/json; charset=utf-8",
                        dataType: "json",
                        success: function (response,textStatus,jqXHR) {
                            if (!detectError(response,jqXHR)){
                                var className = response.name[self.settings.language];
                                var className = typeof response.name[self.settings.language] != 'undefined' ?
                                    response.name[self.settings.language] :
                                    response.name[Object.keys(response.name)[0]];
                                var listItemLink = $('<a href="#">'+className+'</a>')
                                    .bind('click', function (e) {
                                        self.buildCreateForm(classIdentifier);
                                        e.preventDefault();
                                    })
                                    .appendTo(listItem);
                                listItem.appendTo(list);
                            }
                        },
                        error: function (jqXHR) {
                            var error = {
                                error_code: jqXHR.status,
                                error_message: jqXHR.statusText
                            };
                            detectError(error,jqXHR);
                            var listItemLink = $('<a href="#">'+classIdentifier+'</a>')
                                .bind('click', function (e) {
                                    self.buildCreateForm(classIdentifier);
                                    e.preventDefault();
                                })
                                .appendTo(listItem);
                            listItem.appendTo(list);
                        }
                    });
                });
                createButtonGroup.append(list);
                panelHeading.append(createButtonGroup);
            }

            return panelHeading;
        },

        buildTreeSelect: function () {
            var self = this;

            $(this.browserContainer).html('');
            var panel = $('<div class="panel panel-default"></div>').appendTo($(this.browserContainer));
            var panelHeading = self.buildPanelHeader(panel, true, false, false);

            var panelContent = $('<div class="panel-content"></div>').appendTo(panel); 
            var panelFooter = $('<div class="panel-footer clearfix"></div>');

            if (this.browseParameters.subtree > 1){
                $.getJSON('/ezjscore/call/ezjscnode::load::'+self.browseParameters.subtree, function(data){
                    if (data.error_text == ''){
                        var name = $('<h3 class="panel-title" style="line-height: 2.5em;"></h3>');
                        var itemName = (data.content.name.length > 50) ? data.content.name.substring(0,47)+'...' : data.content.name;
                        var back = $('<a href="#" data-node_id="'+data.content.parent_node_id+'"><span class="glyphicon glyphicon-circle-arrow-up" style="vertical-align:sub;font-size:1.5em"></span> '+itemName+'</a>').prependTo(name);
                        back.bind('click', function(e){
                            self.browseParameters.subtree = $(this).data('node_id');
                            self.buildTreeSelect();
                            e.preventDefault();
                        });                                                
                        panelHeading.append(name);
                    }else{
                        alert(data.error_text);
                    }
                });
            }else{
                panelHeading.append('<h3 class="panel-title" style="line-height: 2.5em;">Nodi di livello principale</h3>');
            }

            $.getJSON('/ezjscore/call/ezjscnode::subtree::'+self.browseParameters.subtree+'::'+self.browseParameters.limit+'::'+self.browseParameters.offset+'::'+self.browseParameters.sort+'::'+self.browseParameters.order, function(data){                
                if (data.error_text == ''){
                    if (data.content.list.length > 0){
                        var list = $('<ul class="list-group" style="margin-bottom:0"></ul>');
                        
                        $.each(data.content.list, function(){                            
                            
                            var item = {
                                contentobject_id: this.contentobject_id,
                                node_id: this.node_id,
                                name: this.name,
                                class_name: this.class_name,
                                class_identifier: this.class_identifier
                            };

                            var listItem = self.makeListItem(item);
                            listItem.appendTo(list);                            
                        });
                        list.appendTo(panelContent);

                    }else{
                       panelContent.append($('<div class="panel-body">Nessun contenuto</div>'));
                    }

                    if(data.content.offset > 0){
                        var prevPaginationOffset = self.browseParameters.offset - self.browseParameters.limit;
                        var prevButton = $('<a href="#" class="pull-left"><span class="glyphicon glyphicon-chevron-left" style="font-size:1.5em"></span></a>')
                            .bind('click', function(e){
                                self.browseParameters.offset = prevPaginationOffset;
                                self.buildTreeSelect();
                                e.preventDefault();
                            })
                            .appendTo(panelFooter);
                        panelFooter.appendTo(panel);
                    }

                    if(data.content.total_count > data.content.list.length + self.browseParameters.offset){
                        var nextPaginationOffset = self.browseParameters.offset + self.browseParameters.limit;
                        var nextButton = $('<a href="#" class="pull-right"><span class="glyphicon glyphicon-chevron-right" style="font-size:1.5em"></span></a>')
                            .bind('click', function(e){
                                self.browseParameters.offset = nextPaginationOffset;
                                self.buildTreeSelect();
                                e.preventDefault();
                            })
                            .appendTo(panelFooter);
                        panelFooter.appendTo(panel);
                    }

                }else{
                    alert(data.error_text);
                }
            });             
        },
        
        buildSearchSelect: function () {  
            var self = this;  
            $(this.browserContainer).html('');
            var panel = $('<div class="panel panel-default"></div>').appendTo($(this.browserContainer));
            var panelHeading = self.buildPanelHeader(panel, false, true, false);
            panelHeading.append('<h3 class="panel-title" style="line-height: 2.5em;">Cerca</h3>');

            var inputGroup = $('<div class="input-group"></div>');
            this.searchInput = $('<input class="form-control" type="text" placeholder="" value=""/>').appendTo(inputGroup);
            var inputGroupButtonContainer = $('<div class="input-group-btn"></div>');
            var searchButton = $('<button type="button" class="btn btn-default">Cerca</button>').appendTo(inputGroupButtonContainer);            
            inputGroupButtonContainer.appendTo(inputGroup);
            inputGroup.appendTo(panel);
            this.searchInput.focus();

            var panelContent = $('<div class="panel-content"></div>').appendTo(panel); 
            var panelFooter = $('<div class="panel-footer clearfix"></div>').hide().appendTo(panel);

            searchButton.bind('click', function(e){
                e.preventDefault();
                self.resetBrowseParameters();
                var query = self.buildQuery();
                self.doSearch(query, panelContent, panelFooter);
                
            });

            this.searchInput.on('keyup', function (e) {
                if (e.keyCode == 13) {
                    searchButton.trigger('click');
                    e.preventDefault();
                }
            });
        },

        buildCreateForm: function(classIdentifier){
            var self = this;
            $(this.browserContainer).html('');
            var panel = $('<div class="panel panel-default"></div>').appendTo($(this.browserContainer));
            var panelHeading = self.buildPanelHeader(panel, false, false, true);
            panelHeading.append('<h3 class="panel-title" style="line-height: 2.5em;">Crea nuovo</h3>');

            var queryStringParameter = '';
            if (self.settings.subtree != 1){
                queryStringParameter = '&parent='+self.settings.subtree;
            }

            var d = new Date();
            queryStringParameter += '&nocache='+d.getTime();

            var options = $.extend(true, {
                "dataSource": "/forms/connector/"+self.settings.createSettings.connector+"/data?class="+classIdentifier+queryStringParameter,
                "schemaSource": "/forms/connector/"+self.settings.createSettings.connector+"/schema?class="+classIdentifier+queryStringParameter,
                "optionsSource": "/forms/connector/"+self.settings.createSettings.connector+"/options?class="+classIdentifier+queryStringParameter,
                "viewSource": "/forms/connector/"+self.settings.createSettings.connector+"/view?class="+classIdentifier+queryStringParameter,
                "options": {
                    "form": {
                        "buttons": {
                            "submit": {
                                "click": function () {
                                    this.refreshValidationState(true);
                                    if (this.isValid(true)) {
                                        hideButtons();
                                        var promise = this.ajaxSubmit();
                                        promise.done(function (response) {
                                            if (response.error) {
                                                alert(response.error);
                                                showButtons();
                                            } else {

                                                var name = typeof response.content.metadata.name[self.settings.language] != 'undefined' ?
                                                    response.content.metadata.name[self.settings.language] :
                                                    response.content.metadata.name[Object.keys(response.content.metadata.name)[0]];

                                                var item = {
                                                    contentobject_id: response.content.metadata.id,
                                                    node_id: response.content.metadata.mainNodeId,
                                                    name: name,
                                                    class_name: response.content.metadata.classIdentifier, //@todo
                                                    class_identifier: response.content.metadata.classIdentifier
                                                };
                                                self.appendToSelection(item);

                                                if (self.settings.openInSearchMode){
                                                    self.resetBrowseParameters();
                                                    self.buildSearchSelect();
                                                    self.searchInput.trigger('keyup');
                                                }else{
                                                    self.resetBrowseParameters();
                                                    self.buildTreeSelect();
                                                }
                                            }
                                        });
                                        promise.fail(function (error) {
                                            alert(error);
                                            showButtons();
                                        });
                                    }
                                },
                                "id": 'sub-form-submit',
                                "value": "Salva",
                                "styles": "btn btn-success pull-right"
                            }
                        }
                    }
                }
            }, self.settings.createSettings.options);

            var hideButtons = function () {
                $.each(options.options.form.buttons, function(){
                    var button = $('#'+this.id);
                    button.data('original-text', button.text());
                    button.text('Salvataggio in corso....');
                    button.attr('disabled', 'disabled');
                });
            };
            var showButtons = function () {
                $.each(options.options.form.buttons, function(){
                    var button = $('#'+this.id);
                    button.text(button.data('original-text'));
                    button.attr('disabled', false);
                });
            };

            var panelContent = $('<div class="panel-content clearfix" style="padding: 20px"></div>')
                .alpaca('destroy').alpaca(options)
                .appendTo(panel);

            var panelFooter = $('<div class="panel-footer clearfix"></div>').hide().appendTo(panel);
        },

        buildQuery: function(){
            var searchText = this.searchInput.val();
            searchText = searchText.replace(/'/g, "\\'");
            var subtreeQuery = " and subtree ["+this.browseParameters.subtree+"]";
            var classesQuery = '';
            if ($.isArray(this.settings.classes) && this.settings.classes.length > 0){
                classesQuery = " and classes ["+this.settings.classes.join(',')+"]";
            }
            return "q = '"+searchText+"'"+subtreeQuery+classesQuery+" limit "+this.browseParameters.limit+" offset " +this.browseParameters.offset; 
        },

        doSearch: function(query, panelContent, panelFooter){
            var self = this; 

            var detectError = function(response,jqXHR){
                if(response.error_message || response.error_code){
                    alert(response.error_message);
                    return true;
                }
                return false;
            };

            panelContent.html('');
            panelFooter.html('').hide();

            $.ajax({
                type: "GET",
                url: '/opendata/api/content/search/',
                data: {q: encodeURIComponent(query)},
                contentType: "application/json; charset=utf-8",
                dataType: "json",
                success: function (data,textStatus,jqXHR) {
                    if (!detectError(data,jqXHR)){
                        if(data.totalCount > 0){
                            var list = $('<ul class="list-group" style="margin-bottom:0"></ul>');
                            $.each(data.searchHits, function(){                            
                                var name = typeof this.metadata.name[self.settings.language] != 'undefined' ? 
                                    this.metadata.name[self.settings.language] : 
                                    this.metadata.name[Object.keys(this.metadata.name)[0]];
                                
                                var item = {
                                    contentobject_id: this.metadata.id,
                                    node_id: this.metadata.mainNodeId,
                                    name: name,
                                    class_name: this.metadata.classIdentifier, //@todo
                                    class_identifier: this.metadata.classIdentifier
                                };

                                var listItem = self.makeListItem(item);
                                listItem.appendTo(list);                            
                            });
                            list.appendTo(panelContent);
                        }else{
                            panelContent.html($('<div class="panel-body">Nessun contenuto</div>'));
                        }

                        if(self.browseParameters.offset > 0){
                            var prevPaginationOffset = self.browseParameters.offset - self.browseParameters.limit;
                            var prevButton = $('<a href="#" class="pull-left"><span class="glyphicon glyphicon-chevron-left" style="font-size:1.5em"></span></a>')
                                .bind('click', function(e){                                    
                                    self.browseParameters.offset = prevPaginationOffset;
                                    var query = self.buildQuery();
                                    self.doSearch(query, panelContent, panelFooter);
                                    e.preventDefault();
                                })
                                .appendTo(panelFooter);
                            panelFooter.show();                            
                        }

                        if(data.nextPageQuery){                            
                            var nextButton = $('<a href="#" class="pull-right"><span class="glyphicon glyphicon-chevron-right" style="font-size:1.5em"></span></a>')
                                .bind('click', function(e){                                    
                                    self.browseParameters.offset += self.browseParameters.limit;
                                    self.doSearch(data.nextPageQuery, panelContent, panelFooter);
                                    e.preventDefault();
                                })
                                .appendTo(panelFooter);  
                            panelFooter.show();                          
                        }
                    }
                },
                error: function (jqXHR) {
                    var error = {
                        error_code: jqXHR.status,
                        error_message: jqXHR.statusText
                    };
                    detectError(error,jqXHR);
                }
            }); 
        },

        makeListItem: function(item){        
            var self = this;

            var name = $('<a href="#" data-node_id="'+item.node_id+'" style="display:table-cell;"> '+item.name+ ' <small>' +item.class_name + '</small></a>');
            name.bind('click', function(e){
                self.browseParameters.subtree = $(this).data('node_id');
                self.buildTreeSelect();
                e.preventDefault();
            });
            var listItem = $('<li class="list-group-item"></li>');
            if (typeof $.fn.alpaca != 'undefined') {
                var detail = $('<a href="#" data-object_id="' + item.contentobject_id + '" style="display:table-cell;" class="btn btn-xs btn-info pull-right"><small>ANTEPRIMA</small></a>');
                detail.bind('click', function (e) {
                    var objectId = $(this).data('object_id');
                    var panelContent = $(this).closest('.panel-content');
                    var previewContainer = $('<div class="panel-content" style="margin: 5px"></div>');

                    var closePreviewButton = $('<a class="btn btn-xs btn-info pull-right" href="#">CHIUDI ANTEPRIMA</a>');
                    closePreviewButton.bind('click', function (e) {
                        panelContent.show();
                        previewContainer.remove();
                        e.preventDefault();
                    }).prependTo(previewContainer);
                    panelContent.hide();
                    previewContainer.insertBefore(panelContent);

                    var d = new Date();
                    var queryStringParameter = '&nocache='+d.getTime();
                    previewContainer.alpaca('destroy').alpaca({
                        "dataSource": "/forms/connector/" + self.settings.createSettings.connector + "/data?view=display&object=" + objectId+queryStringParameter,
                        "schemaSource": "/forms/connector/" + self.settings.createSettings.connector + "/schema?view=display&object=" + objectId+queryStringParameter,
                        "optionsSource": "/forms/connector/" + self.settings.createSettings.connector + "/options?view=display&object=" + objectId+queryStringParameter,
                        "viewSource": "/forms/connector/" + self.settings.createSettings.connector + "/view?view=display&object=" + objectId+queryStringParameter
                    });
                    e.preventDefault();
                });
                listItem.append(detail);
            }
            var input = '';
            if (self.isSelectable(item)){
                if (!self.isInSelection(item)){
                    input = $('<span class="glyphicon glyphicon-unchecked pull-left" data-selection="'+item.contentobject_id+'" style="cursor:pointer;'+self.iconStyle+'"></span>');
                    input.data('item', item);
                    input.bind('click', function(e){
                        e.preventDefault();
                        self.appendToSelection($(this).data('item'));
                        $(this).removeClass('glyphicon-unchecked').addClass('glyphicon-check');
                    });
                }else{
                    input = $('<span class="glyphicon glyphicon-check pull-left" data-selection="'+item.contentobject_id+'" style="cursor:pointer;'+self.iconStyle+'"></span>');
                }
            }else{                
                input = $('<span class="glyphicon glyphicon-ban-circle text-muted pull-left" data-selection="'+item.contentobject_id+'" style="'+self.iconStyle+'"></span>');                
            }

            listItem.append(input);
            listItem.append(name);


            return listItem;
        },  

        emptySelection: function () {
            this.selection = [];
            this.refreshSelection();
            $('.glyphicon-check').removeClass('glyphicon-check').addClass('glyphicon-unchecked');
        },      

        appendToSelection: function (item){
            if (this.settings.selectionType != 'multiple'){
                this.emptySelection();
                $(this.browserContainer).find('[data-selection="'+item.contentobject_id+'"]').removeClass('glyphicon-unchecked').addClass('glyphicon-check');
            }
            this.selection.push(item);
            this.refreshSelection();
        },

        refreshSelection: function(){
            var self = this;
            this.selectionContainer.html('');
            if (this.selection.length > 0){
                var panel = $('<div class="panel panel-default"></div>').appendTo($(this.selectionContainer));
                var panelHeading = $('<div class="panel-heading"><h3 class="panel-title">Elementi selezionati</h3></div>').appendTo(panel); 
                var panelContent = $('<div class="panel-content"></div>').appendTo(panel); 
                var list = $('<ul class="list-group" style="margin-bottom:0"></ul>');
                
                $.each(this.selection, function(){
                    var name = '<span style="display: table-cell;">' + this.name + ' <small>' +this.class_name + '</small></span>';
                    var listItem = $('<li class="list-group-item"></li>');                        
                    var input = $('<span class="glyphicon glyphicon-remove" style="cursor:pointer;'+self.iconStyle+'"></span>');
                    input.data('item', this);
                    input.bind('click', function(e){
                        self.removeFromSelection($(this).data('item'));
                        $(self.browserContainer).find('[data-selection="'+$(this).data('item').contentobject_id+'"]').removeClass('glyphicon-check').addClass('glyphicon-unchecked');
                        $(this).closest('li').remove();
                        self.refreshSelection();
                    });
                    listItem.append(input);
                    listItem.append(name);                
                    listItem.appendTo(list);
                    
                });
                list.appendTo(panelContent);  
                var panelFooter = $('<div class="panel-footer clearfix"></div>').appendTo(panel); 

                var selectButton = $('<button class="btn btn-success pull-right">Procedi</button>')
                    .bind('click', function(e){
                        e.preventDefault();                    
                        $(self.element).trigger('opendata.browse.select', self);
                    })
                    .appendTo(panelFooter);
            }                
        },

        removeFromSelection: function (item){
            for(var i in this.selection){
                if(this.selection[i].contentobject_id == item.contentobject_id){
                    this.selection.splice(i,1);
                    break;
                }
            }            
        },

        isInSelection: function (item){            
            for(var i in this.selection){
                if(this.selection[i].contentobject_id == item.contentobject_id){
                    return true;
                    break;
                }
            } 

            return false;
        },

        isSelectable: function (item){            
            if ($.isArray(this.settings.classes) && this.settings.classes.length > 0){
                return $.inArray( item.class_identifier, this.settings.classes ) > -1;
            }

            return true;
        }
    });

    $.fn[pluginName] = function (options) {
        return this.each(function () {
            if (!$.data(this, "plugin_" + pluginName)) {
                $.data(this, "plugin_" +
                    pluginName, new Plugin(this, options));
            }
        });
    };

})(jQuery, window, document);
