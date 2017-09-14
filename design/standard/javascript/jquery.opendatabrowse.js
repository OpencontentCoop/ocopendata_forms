;(function ($, window, document, undefined) {

    "use strict";

    var pluginName = "opendataBrowse",
        defaults = {            
            subtree: 1,
            classes: false,
            selectionType: 'multiple',
            language: 'ita-IT',
            browsePaginationLimit: 25,
            browseSort: 'published',
            browseOrder: '0',
            openInSearchMode: false
        };

    function Plugin(element, options) {
        this.element = element;
        this.settings = $.extend({}, defaults, options);
        this._defaults = defaults;
        this._name = pluginName;
        this.iconStyle = 'line-height: 0.7;display:table-cell;padding-right:5px;font-size:1.5em';
        this.selection = [];
        this.browseParameters = {};

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

        buildTreeSelect: function () {
            var self = this;

            $(this.browserContainer).html('');
            var panel = $('<div class="panel panel-default"></div>').appendTo($(this.browserContainer));
            var panelHeading = $('<div class="panel-heading"></div>').appendTo(panel);                        
            
            var searchButton = $('<a class="pull-right" href="#"><span class="glyphicon glyphicon-search" style="vertical-align:sub;font-size:1.5em"></span></a>');
            searchButton.bind('click', function(e){
                self.resetBrowseParameters();
                self.buildSearchSelect();
                e.preventDefault();
            });            
            panelHeading.append(searchButton);

            var panelContent = $('<div class="panel-content"></div>').appendTo(panel); 
            var panelFooter = $('<div class="panel-footer clearfix"></div>');

            if (this.browseParameters.subtree > 1){
                $.getJSON('/ezjscore/call/ezjscnode::load::'+self.browseParameters.subtree, function(data){
                    if (data.error_text == ''){
                        var name = $('<h3 class="panel-title" style="line-height: 1.5em;"></h3>');
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
                panelHeading.append('<h3 class="panel-title" style="line-height: 1.5em;">Nodi di livello principale</h3>');
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
            var panelHeading = $('<div class="panel-heading"></div>').appendTo(panel);            

            var treeButton = $('<a class="pull-right" href="#"><span class="glyphicon glyphicon-th-list" style="vertical-align:sub;font-size:1.5em"></span></a>');
            treeButton.bind('click', function(e){                
                self.resetBrowseParameters();
                self.buildTreeSelect();
                e.preventDefault();
            });
            panelHeading.append(treeButton);       
            panelHeading.append('<h3 class="panel-title" style="line-height: 1.5em;">Cerca</h3>');      

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
                        $(this).parents('li').remove();
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
