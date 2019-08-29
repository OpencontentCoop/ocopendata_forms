<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<head>

  <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {ezscript_load(array(
        'ezjsc::jquery',
        'ezjsc::jqueryUI',
        'ezjsc::jqueryio',
        'bootstrap.min.js',
        'handlebars.min.js',
        'moment-with-locales.min.js',
        'bootstrap-datetimepicker.min.js',
        'jquery.fileupload.js',
        'jquery.fileupload-process.js',
        'jquery.fileupload-ui.js',
        'jquery.caret.min.js',
        'jquery.tag-editor.js',
        'popper.min.js',
        'alpaca.js',
        'leaflet/leaflet.0.7.2.js',
        'leaflet/Control.Geocoder.js',
        'leaflet/Control.Loading.js',
        'leaflet/Leaflet.MakiMarkers.js',
        'leaflet/leaflet.activearea.js',
        'leaflet/leaflet.markercluster.js',
        'jquery.price_format.min.js',
        'jquery.opendatabrowse.js',
        'fields/OpenStreetMap.js',
        'fields/RelationBrowse.js',
        'fields/LocationBrowse.js',
        'fields/Tags.js',
        'fields/Ezxml.js',
        ezini('JavascriptSettings', 'IncludeScriptList', 'ocopendata_connectors.ini'),
        'jquery.opendataform.js'
    ))}

    {def $fieldSettings = ezini('FieldSettings', 'FieldConnectors', 'ocopendata_connectors.ini')}
    {if $fieldSettings|contains('\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\EzOnlineEditorXmlField')}
        {def $plugin_list = ezini('EditorSettings', 'Plugins', 'ezoe.ini',,true() )
             $ez_locale = ezini( 'RegionalSettings', 'Locale', 'site.ini')
             $language = '-'|concat( $ez_locale )
             $dependency_js_list = array( 'ezoe::i18n::'|concat( $language ) )}
        {foreach $plugin_list as $plugin}
            {set $dependency_js_list = $dependency_js_list|append( concat( 'plugins/', $plugin|trim, '/editor_plugin.js' ))}
        {/foreach}
        <script id="tinymce_script_loader" type="text/javascript" src={"javascript/tiny_mce_jquery.js"|ezdesign} charset="utf-8"></script>
        {ezscript( $dependency_js_list )}
    {else}
        <script type="text/javascript" src={'javascript/tinymce/tinymce.min.js'|ezdesign()} charset="utf-8"></script>
        <script type="text/javascript" src={'javascript/summernote/summernote-bs4.js'|ezdesign()} charset="utf-8"></script>
    {/if}

    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
    {ezcss_load(array(        
        'alpaca.min.css',
        'leaflet/leaflet.0.7.2.css',
        'leaflet/Control.Loading.css',
        'leaflet/MarkerCluster.css',
        'leaflet/MarkerCluster.Default.css',
        'bootstrap-datetimepicker.min.css',
        'jquery.fileupload.css',
        'summernote/summernote-bs4.css',
        'jquery.tag-editor.css',
        'alpaca-custom.css'
    ))}

</head>
<body>

    <div id="modal" class="modal fade">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="clearfix">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    </div>
                    <div id="form" class="clearfix"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="container" style="margin-bottom: 30px">
        <h1>Opendata Forms Demo</h1>

        <p class="lead">Implementazione di <a href="http://www.alpacajs.org/">alpacajs <i class="fa fa-external-link"></i> </a>
            per eZPublish con OpenContentOpendata</p>

        <h2>Demo form</h2>
        <p>Form dimostrativo: non utitlizza nessun valore dinamico e non salva alcun dato. E' l'implemetazione del tutorial di
            <a href="http://www.alpacajs.org/tutorial.html">alpacajs <i class="fa fa-external-link"></i> </a></p>
        <button id="showdemo" class="btn btn-lg btn-success">Open Demo Form</button>
        <div id="staticform"></div>
        <hr/>
        <p>Utilizza la classe <code>\Opencontent\Ocopendata\Forms\Connectors\DemoConnector</code></p>

        <h2>Class form</h2>
        <p>Form di creazione e modifica dinamico per ciascuna classe. <strong>Crea e modifica realmente i dati ez!</strong></p>
        <div class="container">
            <div class="row">
                <div class="col-sm-6">
                    <div class="input-group">
                        <select id="selectclass" class="form-control input-lg">
                            {def $class_list = fetch(class, list, hash(sort_by, array(name, true())))}
                            {foreach $class_list as $class}
                                <option value="{$class.identifier}">{$class.name|wash()}</option>
                            {/foreach}
                        </select>
                        <span class="input-group-btn">
                            <button id="showclass" class="btn btn-lg btn-success">Create</button>
                        </span>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="input-group">
                        <input id="selectobject" type="text" class="form-control input-lg" placeholder="Object ID" value=""/>
                        <span class="input-group-btn">
                            <button id="editobject" class="btn btn-lg btn-success">Edit</button>
                        </span>
                    </div>
                </div>
            </div>
            <div class="row" id="demo-contents-containers">
                <div class="col-sm-12">
                    <hr/>
                    <p>In questa tabella puoi vedere i contenuti che gengeri in questa sessione di demo</p>
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Class</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody id="demo-contents">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <hr/>
        <p>Connettore: <code>Opencontent\Ocopendata\Forms\Connectors\OpendataConnector</code></p>
        <p>Handler: <code>Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\ClassConnector</code></p>
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active"><a href="#mapped" aria-controls="mapped" role="tab" data-toggle="tab">Datatype mappati</a></li>
            <li role="presentation"><a href="#unmapped" aria-controls="unmapped" role="tab" data-toggle="tab">Datatype non mappati</a></li>
        </ul>
        <div class="tab-content">
            <div role="tabpanel" class="tab-pane active" id="mapped">
                <table class="table">
                    {foreach $connector_by_datatype as $datatype => $connector}
                        <tr>
                            <td>{$datatype|wash()}</td>
                            <td><code>{$connector|wash()}</code></td>
                        </tr>
                    {/foreach}
                </table>
            </div>
            <div role="tabpanel" class="tab-pane" id="unmapped">
                <table class="table">
                    {foreach $not_found_connector_by_datatype as $datatype => $connector}
                        <tr>
                            <td>{$datatype|wash()}</td>
                        </tr>
                    {/foreach}
                </table>
            </div>
        </div>



        <h2>Browse demo</h2>
        <p>Plugin jQuery per il content browse dinamico <code>jquery.opendatabrowse.js</code></p>
        <button id="showdemobrowse" class="btn btn-lg btn-success">Show/Hide</button>
        <div id="browse"></div>
    </div>

    {literal}
    <script type="text/javascript">
    $(document).ready(function () {

        $.opendataFormSetup({
            onBeforeCreate: function(){
                $('#modal').modal('show')
            },
            onSuccess: function(data){
                $('#modal').modal('hide');
            }
        });

        $('#demo-contents-containers').hide();

        var classSelect = $('#selectclass');

        var showDemoForm = function () {
            $('#modal').modal('show');
            $("#form").alpaca('destroy').alpaca({
                "dataSource": "{/literal}{'/forms/connector/demo/data?demo=1'|ezurl(no)}{literal}",
                "schemaSource": "{/literal}{'/forms/connector/demo/schema'|ezurl(no)}{literal}",
                "optionsSource": "{/literal}{'/forms/connector/demo/options'|ezurl(no)}{literal}",
                "viewSource": "{/literal}{'/forms/connector/demo/view'|ezurl(no)}{literal}"
            });
        };

        var appendNewData = function(data){
            $('#demo-contents-containers').show();
            var language = 'ita-IT';
            var newRow = $('<tr id="object-'+data.content.metadata.id+'"></tr>');
            newRow.append($('<td>'+data.content.metadata.id+'</td>'));
            newRow.append($('<td><a href="">'+data.content.metadata.name[language]+'</a></td>'));
            newRow.append($('<td><a href="">'+data.content.metadata.classIdentifier+'</a></td>'));
            var buttonCell = $('<td width="1" style="white-space:nowrap"></td>');
            $('<button class="btn btn-warning" data-object="'+data.content.metadata.id+'"><i class="fa fa-edit"></i></button>')
                .bind('click', function(e){
                    $('#form').opendataFormEdit({object: $(this).data('object')});
                    e.preventDefault();
                }).appendTo(buttonCell);
            $('<button class="btn btn-success" data-object="'+data.content.metadata.id+'"><i class="fa fa-eye"></i></button>')
                .bind('click', function(e){
                    $('#form').opendataFormView({object: $(this).data('object')});
                    e.preventDefault();
                }).appendTo(buttonCell);
            $('<button class="btn btn-danger" data-object="'+data.content.metadata.id+'"><i class="fa fa-trash"></i></button>')
                .bind('click', function(e){
                    var object = $(this).data('object');
                    $('#form').opendataFormDelete({object: object},{
                        onSuccess: function(data){
                            $('#demo-contents-containers').find('#object-'+object).remove();
                            $('#modal').modal('hide');
                        }
                    });
                    e.preventDefault();
                }).appendTo(buttonCell);
            $('<button class="btn btn-info" data-node="'+data.content.metadata.mainNodeId+'"><i class="fa fa-code-fork"></i></button>')
                .bind('click', function(e){
                    var node = $(this).data('node');
                    $('#form').opendataFormManageLocation({source: node});
                    e.preventDefault();
                }).appendTo(buttonCell);    
            buttonCell.appendTo(newRow);
            $('#demo-contents').append(newRow);
        };

        $('#showclass').on('click', function (e) {
            $('#form').opendataFormCreate({class: classSelect.val()}, {
                onSuccess: function(data){
                    appendNewData(data);
                    $('#modal').modal('hide');
                }
            });

            e.preventDefault();
        });

        $('#showdemo').on('click', function (e) {
            showDemoForm();
            e.preventDefault();
        });

        $('#editobject').on('click', function (e) {
            $('#form').opendataFormEdit({object: $('#selectobject').val()});
            e.preventDefault();
        });

        $('#browse').opendataBrowse({
            'subtree': {/literal}{ezini('NodeSettings', 'RootNode', 'content.ini')}{literal},
            'addCloseButton': true,
            'addCreateButton': true,
            'classes': ['folder','image']
        }).on('opendata.browse.select', function (event, opendataBrowse) {
            alert(JSON.stringify(opendataBrowse.selection));
        }).on('opendata.browse.close', function (event, opendataBrowse) {
            $('#browse').toggle();
        }).hide();

        $('#showdemobrowse').on('click', function (e) {
            $('#browse').toggle();
        });


    });
    </script>
    {/literal}

{* This comment will be replaced with actual debug report (if debug is on). *}
<!--DEBUG_REPORT-->
</body>
</html>
