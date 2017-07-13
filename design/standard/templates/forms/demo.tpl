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
<p>Utilizza la classe <code>\Opencontent\Ocopendata\Forms\Connectors\DemoConnector</code></p>
<button id="showdemo" class="btn btn-lg btn-success">Open Demo Form</button>
<div id="staticform"></div>

<h2>Class form</h2>
<p>Form di creazione e modifica dinamico per ciascuna classe. <strong>Crea e modifica realmente i dati ez!</strong></p>
<p>Utilizza la classe <code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector</code> che richiama l'handler di default <code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\ClassConnector</code></p>
<p>Sono mappati gli attibuti di tipo;</p>
<ul>
    <li>ezselection <code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\SelectionField</code></li>
    <li>ezprice <code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\PriceField</code></li>
    <li>ezkeyword <code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\KeywordsField</code></li>
    <li>eztags <code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\TagsField</code></li>
    <li>ezgmaplocation <code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\GeoField</code></li>
    <li>ezdate <code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\DateField</code></li>
    <li>ezdatetime <code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\DateTimeField</code></li>
    <li>eztime <code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\TimeField</code></li>
    <li>ezmatrix <code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\MatrixField</code></li>
    <li>ezxmltext <code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\EzXmlField</code></li>
    <li>ezauthor <code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\AuthorField</code></li>
    <li>ezobjectrelation <code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\RelationField</code></li>
    <li>ezobjectrelationlist <code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\RelationsField</code></li>
    <li>ezbinaryfile <code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\FileField</code></li>
    <li>ezimage <code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\ImageField</code></li>
    <li>ezpage <code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\PageField</code></li>
    <li>ezboolean <code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\BooleanField</code></li>
    <li>ezuser <code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\UserField</code></li>
    <li>ezfloat <code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\FloatField</code></li>
    <li>ezinteger <code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\IntegerField</code></li>
    <li>ezstring <code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\StringField</code></li>
    <li>ezsrrating <code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\RatingField</code></li>
    <li>ezemail <code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\EmailField</code></li>
    <li>ezcountry <code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\CountryField</code></li>
    <li>ezurl <code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\UrlField</code></li>
    <li>eztext <code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\TextField</code></li>
</ul>
<div class="container">
    <div class="row">
        <div class="col-sm-6">
            <div class="input-group">
                <select id="selectclass" class="form-control input-lg"></select>
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
</div>


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

        var classEndpoint = "/api/opendata/v2/classes";
        var classSelect = $('#selectclass');
        $.ajax({
            type: "GET",
            url: classEndpoint,
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            success: function (data) {
                $.each(data.classes, function () {
                    classSelect.append($('<option value="' + this.identifier + '">' + this.name + '</option>'));
                });
            },
            error: function (data) {
                alert(error.error_message);
            }
        });

        var showClassForm = function (classIdentifier, containerId) {
            $(containerId).alpaca('destroy').alpaca({
                "dataSource": "{/literal}{'/forms/connector/full/data?class='|ezurl(no)}{literal}" + classIdentifier,
                "schemaSource": "{/literal}{'/forms/connector/full/schema?class='|ezurl(no)}{literal}" + classIdentifier,
                "optionsSource": "{/literal}{'/forms/connector/full/options?class='|ezurl(no)}{literal}" + classIdentifier,
                "viewSource": "{/literal}{'/forms/connector/full/view?class='|ezurl(no)}{literal}" + classIdentifier,
                "options": {
                    "form": {
                        "buttons": {
                            "validate": {
                                "title": "Validate and view JSON!",
                                "click": function () {
                                    this.refreshValidationState(true);
                                    if (this.isValid(true)) {
                                        var value = this.getValue();
                                        alert(JSON.stringify(value));
                                    }
                                }
                            },
                            "submit": {
                                "click": function () {
                                    this.refreshValidationState(true);
                                    if (this.isValid(true)) {
                                        var promise = this.ajaxSubmit();
                                        promise.done(function (data) {
                                            if (data.error){
                                                alert(data.error);
                                            }else{
                                                $('#modal').modal('hide');
                                            }
                                        });
                                        promise.fail(function (error) {
                                            alert(error);
                                        });
                                    }
                                }
                            }
                        }
                    }
                }
            });
        };
        var editObjectForm = function (objectId, containerId) {
            $(containerId).alpaca('destroy').alpaca({
                "dataSource": "{/literal}{'/forms/connector/full/data?object='|ezurl(no)}{literal}" + objectId,
                "schemaSource": "{/literal}{'/forms/connector/full/schema?object='|ezurl(no)}{literal}" + objectId,
                "optionsSource": "{/literal}{'/forms/connector/full/options?object='|ezurl(no)}{literal}" + objectId,
                "viewSource": "{/literal}{'/forms/connector/full/view?object='|ezurl(no)}{literal}" + objectId,
                "options": {
                    "form": {
                        "buttons": {
                            "validate": {
                                "title": "Validate and view JSON!",
                                "click": function () {
                                    this.refreshValidationState(true);
                                    if (this.isValid(true)) {
                                        var value = this.getValue();
                                        alert(JSON.stringify(value));
                                    }
                                }
                            },
                            "submit": {
                                "click": function () {
                                    this.refreshValidationState(true);
                                    if (this.isValid(true)) {
                                        var promise = this.ajaxSubmit();
                                        promise.done(function (data) {
                                            if (data.error){
                                                alert(data.error);
                                            }else{
                                                $('#modal').modal('hide');
                                            }
                                        });
                                        promise.fail(function (error) {
                                            alert(error);
                                        });
                                    }
                                }
                            }
                        }
                    }
                }
            });
        };
        var showDemoForm = function () {
            $("#form").alpaca('destroy').alpaca({
                "dataSource": "{/literal}{'/forms/connector/demo/data?demo=1'|ezurl(no)}{literal}",
                "schemaSource": "{/literal}{'/forms/connector/demo/schema'|ezurl(no)}{literal}",
                "optionsSource": "{/literal}{'/forms/connector/demo/options'|ezurl(no)}{literal}",
                "viewSource": "{/literal}{'/forms/connector/demo/view'|ezurl(no)}{literal}"
            });
        };

        $('#showclass').on('click', function (e) {
            $('#modal').modal('show');
            showClassForm(classSelect.val(), '#form');
            e.preventDefault();
        });

        $('#showdemo').on('click', function (e) {
            $('#modal').modal('show');
            showDemoForm();
            e.preventDefault();
        });

        $('#editobject').on('click', function (e) {
            $('#modal').modal('show');
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
