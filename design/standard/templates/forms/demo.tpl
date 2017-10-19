<script type="text/javascript" src={'javascript/tinymce/tinymce.min.js'|ezdesign()} charset="utf-8"></script>


{ezscript_require(array(
    'ezjsc::jquery',
    'ezjsc::jqueryUI',
    'bootstrap.min.js',
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
    'fields/LocationBrowse.js',
    'jquery.opendataform.js'
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
<p>Utilizza la classe <code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector</code> che richiama l'handler di
    default <code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\ClassConnector</code></p>
<p>Sono mappati gli attibuti di tipo;</p>
<table class="table">
    <tr>
        <td>ezselection</td>
        <td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\SelectionField</code></td>
    </tr>
    <tr>
        <td>ezprice</td>
        <td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\PriceField</code></td>
    </tr>
    <tr>
        <td>ezkeyword</td>
        <td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\KeywordsField</code></td>
    </tr>
    <tr>
        <td>eztags</td>
        <td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\TagsField</code></td>
    </tr>
    <tr>
        <td>ezgmaplocation</td>
        <td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\GeoField</code></td>
    </tr>
    <tr>
        <td>ezdate</td>
        <td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\DateField</code></td>
    </tr>
    <tr>
        <td>ezdatetime</td>
        <td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\DateTimeField</code></td>
    </tr>
    <tr>
        <td>eztime</td>
        <td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\TimeField</code></td>
    </tr>
    <tr>
        <td>ezmatrix</td>
        <td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\MatrixField</code></td>
    </tr>
    <tr>
        <td>ezxmltext</td>
        <td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\EzXmlField</code></td>
    </tr>
    <tr>
        <td>ezauthor</td>
        <td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\AuthorField</code></td>
    </tr>
    <tr>
        <td>ezobjectrelation</td>
        <td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\RelationField</code></td>
    </tr>
    <tr>
        <td>ezobjectrelationlist</td>
        <td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\RelationsField</code></td>
    </tr>
    <tr>
        <td>ezbinaryfile</td>
        <td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\FileField</code></td>
    </tr>
    <tr>
        <td>ezimage</td>
        <td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\ImageField</code></td>
    </tr>
    <tr>
        <td>ezpage</td>
        <td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\PageField</code></td>
    </tr>
    <tr>
        <td>ezboolean</td>
        <td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\BooleanField</code></td>
    </tr>
    <tr>
        <td>ezuser</td>
        <td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\UserField</code></td>
    </tr>
    <tr>
        <td>ezfloat</td>
        <td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\FloatField</code></td>
    </tr>
    <tr>
        <td>ezinteger</td>
        <td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\IntegerField</code></td>
    </tr>
    <tr>
        <td>ezstring</td>
        <td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\StringField</code></td>
    </tr>
    <tr>
        <td>ezsrrating</td>
        <td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\RatingField</code></td>
    </tr>
    <tr>
        <td>ezemail</td>
        <td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\EmailField</code></td>
    </tr>
    <tr>
        <td>ezcountry</td>
        <td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\CountryField</code></td>
    </tr>
    <tr>
        <td>ezurl</td>
        <td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\UrlField</code></td>
    </tr>
    <tr>
        <td>eztext</td>
        <td><code>\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\TextField</code></td>
    </tr>
</table>


<h2>Browse demo</h2>
<p>Plugin jQuery per il content browse dinamico <code>jquery.opendatabrowse.js</code></p>
<button id="showdemobrowse" class="btn btn-lg btn-success">Show/Hide</button>
<div id="browse"></div>

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
        var newRow = $('<tr></tr>');
        newRow.append($('<td>'+data.content.metadata.id+'</td>'));
        newRow.append($('<td><a href="">'+data.content.metadata.name[language]+'</a></td>'));
        newRow.append($('<td><a href="">'+data.content.metadata.classIdentifier+'</a></td>'));
        var buttonCell = $('<td></td>');
        $('<button class="btn btn-default" data-object="'+data.content.metadata.id+'"><i class="fa fa-edit"></i></button>')
            .bind('click', function(e){
                $('#form').opendataFormEdit({object: data.content.metadata.id});
                e.preventDefault();
            }).appendTo(buttonCell);
        $('<button class="btn btn-default" data-object="'+data.content.metadata.id+'"><i class="fa fa-eye"></i></button>')
            .bind('click', function(e){
                $('#form').opendataFormView({object: data.content.metadata.id});
                e.preventDefault();
            }).appendTo(buttonCell);
        buttonCell.appendTo(newRow);
        $('#demo-contents').append(newRow);
    }

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
        'subtree': 43,
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
