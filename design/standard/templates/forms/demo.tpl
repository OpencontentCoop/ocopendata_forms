<script type="text/javascript" src={'javascript/tinymce/tinymce.min.js'|ezdesign()} charset="utf-8"></script>


{ezscript_require(array(
'ezjsc::jquery',
'bootstrap/bootstrap.min.js',
'handlebars.min.js',
'moment-with-locales.min.js',
'bootstrap-datetimepicker.min.js',
'jquery.fileupload.js',
'jquery.fileupload-process.js',
'jquery.fileupload-ui.js',
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
'fields/LocationBrowse.js'
))}

{ezcss_require(array(
'alpaca.min.css',
'leaflet/leaflet.0.7.2.css',
'leaflet/Control.Loading.css',
'leaflet/MarkerCluster.css',
'leaflet/MarkerCluster.Default.css',
'bootstrap-datetimepicker.min.css',
'jquery.fileupload.css',
'alpaca-custom.css'
))}

<div id="modal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <div class="clearfix">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div id="form"></div>
            </div>
        </div>
    </div>
</div>

<h1>Opendata Forms Demo</h1>

<p class="lead">Implementazione di <a href="http://www.alpacajs.org/">alpacajs <i class="fa fa-external-link"></i> </a> per eZPublish con OpenContentOpendata</p>

<h2>Demo form</h2>
<p>Form dimostrativo: non utitlizza nessun valore dinamico e non salva alcun dato. E' l'implemetazione del tutorial di <a href="http://www.alpacajs.org/tutorial.html">alpacajs <i class="fa fa-external-link"></i> </a></p>
<button id="showdemo" class="btn btn-lg btn-success">Open Demo Form</button>
<div id="staticform"></div>
<hr />
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
            <hr />
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
<hr />
<p>Utilizza la classe <code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector</code> che richiama l'handler di default <code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\ClassConnector</code></p>
<p>Sono mappati gli attibuti di tipo;</p>
<table class="table">
    <tr>
        <td>ezselection</td><td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\SelectionField</code></td>
    </tr>
    <tr>
        <td>ezprice</td><td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\PriceField</code></td>
    </tr>
    <tr>
        <td>ezkeyword</td><td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\KeywordsField</code></td>
    </tr>
    <tr>
        <td>eztags</td><td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\TagsField</code></td>
    </tr>
    <tr>
        <td>ezgmaplocation</td><td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\GeoField</code></td>
    </tr>
    <tr>
        <td>ezdate</td><td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\DateField</code></td>
    </tr>
    <tr>
        <td>ezdatetime</td><td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\DateTimeField</code></td>
    </tr>
    <tr>
        <td>eztime</td><td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\TimeField</code></td>
    </tr>
    <tr>
        <td>ezmatrix</td><td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\MatrixField</code></td>
    </tr>
    <tr>
        <td>ezxmltext</td><td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\EzXmlField</code></td>
    </tr>
    <tr>
        <td>ezauthor</td><td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\AuthorField</code></td>
    </tr>
    <tr>
        <td>ezobjectrelation</td><td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\RelationField</code></td>
    </tr>
    <tr>
        <td>ezobjectrelationlist</td><td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\RelationsField</code></td>
    </tr>
    <tr>
        <td>ezbinaryfile</td><td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\FileField</code></td>
    </tr>
    <tr>
        <td>ezimage</td><td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\ImageField</code></td>
    </tr>
    <tr>
        <td>ezpage</td><td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\PageField</code></td>
    </tr>
    <tr>
        <td>ezboolean</td><td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\BooleanField</code></td>
    </tr>
    <tr>
        <td>ezuser</td><td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\UserField</code></td>
    </tr>
    <tr>
        <td>ezfloat</td><td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\FloatField</code></td>
    </tr>
    <tr>
        <td>ezinteger</td><td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\IntegerField</code></td>
    </tr>
    <tr>
        <td>ezstring</td><td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\StringField</code></td>
    </tr>
    <tr>
        <td>ezsrrating</td><td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\RatingField</code></td>
    </tr>
    <tr>
        <td>ezemail</td><td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\EmailField</code></td>
    </tr>
    <tr>
        <td>ezcountry</td><td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\CountryField</code></td>
    </tr>
    <tr>
        <td>ezurl</td><td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\UrlField</code></td>
    </tr>
    <tr>
        <td>eztext</td><td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\TextField</code></td>
    </tr>
</table>


<h2>Browse demo</h2>
<p>Plugin jQuery per il content browse dinamico <code>jquery.opendatabrowse.js</code></p>
<button id="showdemobrowse" class="btn btn-lg btn-success">Show/Hide</button>
<div id="browse"></div>

{literal}
<script type="text/javascript">

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

    $(document).ready(function () {

        $('#demo-contents-containers').hide();

        var classSelect = $('#selectclass');
        var editObjectForm = function (objectId, containerId) {
            var d = new Date();
            $('#modal').modal('show');
            $(containerId).alpaca('destroy').alpaca({
                "dataSource": "{/literal}{'/forms/connector/full/data?object='|ezurl(no)}{literal}" + objectId + '&nocache=' + d.getTime(),
                "schemaSource": "{/literal}{'/forms/connector/full/schema?object='|ezurl(no)}{literal}" + objectId + '&nocache=' + d.getTime(),
                "optionsSource": "{/literal}{'/forms/connector/full/options?object='|ezurl(no)}{literal}" + objectId + '&nocache=' + d.getTime(),
                "viewSource": "{/literal}{'/forms/connector/full/view?object='|ezurl(no)}{literal}" + objectId + '&nocache=' + d.getTime(),
                "options": {
                    "form": {
                        "buttons": {
                            "submit": {
                                "click": function () {
                                    var button = $('#form-submit');
                                    this.refreshValidationState(true);
                                    if (this.isValid(true)) {
                                        button.hide();
                                        var promise = this.ajaxSubmit();
                                        promise.done(function (data) {
                                            if (data.error){
                                                alert(data.error);
                                                button.show();
                                            }else{
                                                $('#modal').modal('hide');
                                            }
                                        });
                                        promise.fail(function (error) {
                                            alert(error);
                                            button.show();
                                        });
                                    }
                                },
                                "id": 'form-submit'
                            }
                        }
                    }
                }
            });
        };
        var showClassForm = function (classIdentifier, containerId) {
            var d = new Date();
            $('#modal').modal('show');
            $(containerId).alpaca('destroy').alpaca({
                "dataSource": "{/literal}{'/forms/connector/full/data?class='|ezurl(no)}{literal}" + classIdentifier + '&nocache=' + d.getTime(),
                "schemaSource": "{/literal}{'/forms/connector/full/schema?class='|ezurl(no)}{literal}" + classIdentifier + '&nocache=' + d.getTime(),
                "optionsSource": "{/literal}{'/forms/connector/full/options?class='|ezurl(no)}{literal}" + classIdentifier + '&nocache=' + d.getTime(),
                "viewSource": "{/literal}{'/forms/connector/full/view?class='|ezurl(no)}{literal}" + classIdentifier + '&nocache=' + d.getTime(),
                "options": {
                    "form": {
                        "buttons": {
//                            "validate": {
//                                "title": "Validate and view JSON!",
//                                "click": function () {
//                                    this.refreshValidationState(true);
//                                    if (this.isValid(true)) {
//                                        var value = this.getValue();
//                                        alert(JSON.stringify(value));
//                                    }
//                                }
//                            },
                            "submit": {
                                "click": function () {
                                    var button = $('#form-submit');
                                    this.refreshValidationState(true);
                                    if (this.isValid(true)) {
                                        button.hide();
                                        var promise = this.ajaxSubmit();
                                        promise.done(function (data) {
                                            if (data.error){
                                                alert(data.error);
                                                button.show();
                                            }else{
                                                $('#demo-contents-containers').show();
                                                $('#modal').modal('hide');
                                                var language = 'ita-IT';
                                                var newRow = $('<tr></tr>');
                                                newRow.append($('<td>'+data.content.metadata.id+'</td>'));
                                                newRow.append($('<td><a href="">'+data.content.metadata.name[language]+'</a></td>'));
                                                newRow.append($('<td><a href="">'+data.content.metadata.classIdentifier+'</a></td>'));
                                                var buttonCell = $('<td></td>');
                                                var edit = $('<button class="btn btn-default" data-object="'+data.content.metadata.id+'"><i class="fa fa-edit"></i></button>')
                                                        .bind('click', function(e){
                                                            editObjectForm($(this).data('object'), '#form');
                                                            e.preventDefault();
                                                        }).appendTo(buttonCell);
                                                buttonCell.appendTo(newRow);
                                                $('#demo-contents').append(newRow);
                                            }
                                        });
                                        promise.fail(function (error) {
                                            alert(error);
                                            button.show();
                                        });
                                    }
                                },
                                "id": 'form-submit'
                            }
                        }
                    }
                }
            });
        };

        var showDemoForm = function () {
            $('#modal').modal('show');
            $("#form").alpaca('destroy').alpaca({
                "dataSource": "{/literal}{'/forms/connector/demo/data?demo=1'|ezurl(no)}{literal}",
                "schemaSource": "{/literal}{'/forms/connector/demo/schema'|ezurl(no)}{literal}",
                "optionsSource": "{/literal}{'/forms/connector/demo/options'|ezurl(no)}{literal}",
                "viewSource": "{/literal}{'/forms/connector/demo/view'|ezurl(no)}{literal}"
            });
        };

        $('#showclass').on('click', function (e) {
            showClassForm(classSelect.val(), '#form');
            e.preventDefault();
        });

        $('#showdemo').on('click', function (e) {
            showDemoForm();
            e.preventDefault();
        });

        $('#editobject').on('click', function (e) {
            editObjectForm($('#selectobject').val(), '#form');
            e.preventDefault();
        });

        $('#browse').opendataBrowse().on('opendata.browse.select', function (event, opendataBrowse) {
            alert(JSON.stringify(opendataBrowse.selection));
        }).hide();

        $('#showdemobrowse').on('click', function (e) {
            $('#browse').toggle();
        });


    });
</script>
{/literal}
