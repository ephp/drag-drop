<style type="text/css">
    
    #<?php echo $id ?>row, #<?php echo $field ?>_tmb {
        border: 1px dashed #999;
        padding: 5px;
        display: block;
        width: 400px;
        height: 90px;
        position: relative;
        float: left;
        margin-right: 10px;
        text-align: center;
    }
    #<?php echo $id ?>row {
        background: url(/images/user_placeholder.jpg) no-repeat 5px 5px;
    }
    #<?php echo $id ?>row:before {
        content: "Trascina quì il file";
        font-size: 12px;
        font-style: italic;
        color: #666;
    }
    
    
</style>
<?php
// Questo DIV è quello che compare quando è stata caricata l'immagine
?>
<div id="cont_<?php echo $field ?>_tmb">
    <div id="<?php echo $field ?>_tmb" class="left rounded-5">
        <?php if ($value): ?>
            <img src="<?php echo $tmb ?>" alt="anteprima" />
        <?php endif; ?>
    </div>
    <button id="<?php echo $field ?>_delete" type="button" class="button-orange left" onclick="cancella_<?php echo $field ?>()">Elimina</button>
</div>
<?php
// Questo DIV è la progress bar
?>

<div class="row fileuploadrow <?php echo $id ?>_row rounded-5" id="<?php echo $id ?>row">
    <div class="fileupload-buttonbar">
        <div class="progressbar fileupload-progressbar absolute" style="width: 140px; top: 0px; right: -150px;"><div style="width:0%;"></div></div>
        <input type="hidden" name="d" value="<?php echo $dir ?>">
        <input type="hidden" name="x" value="<?php echo $x ?>">
        <input type="hidden" name="y" value="<?php echo $y ?>">
        <input type="hidden" name="f" value="<?php echo $id ?>">
        <button type="reset" class="btn info cancel button-orange smaller" id="<?php echo $id ?>_bt_reset">Annulla</button>
    </div>
</div>
<div class="btn success fileinput-button left" id="<?php echo $id ?>_bt_add">
    <button class="button-cyan smaller">oppure seleziona da quì un file</button>
    <input type="file" name="files[]" multiple>
</div>
<?php
// Questo DIV contiene la tabella con lo stato di caricamento
?>
<div class="row fileuploadrow <?php echo $id ?>_row">
    <div class="no-display">
        <table class="zebra-striped"><tbody class="files" id="table_files_<?php echo $field ?>"></tbody></table>
    </div>
</div>

<script id="template-upload" type="text/html">
    {% for (var i=0, files=o.files, l=files.length, file=files[0]; i<l; file=files[++i]) { %}
        <tr class="template-upload fade" style="display:none">
            <td class="preview"><span class="fade"></span></td>
            <td class="name">{%=file.name%}</td>
            <td class="size">{%=o.formatFileSize(file.size)%}</td>
            {% if (file.error) { %}
            <td class="error" colspan="2"><span class="label important">Error</span> {%=fileUploadErrors[file.error] || file.error%}</td>
            {% } else if (o.files.valid && !i) { %}
            <td class="progress"><div class="progressbar"><div style="width:0%;"></div></div></td>
            <td class="start">{% if (!o.options.autoUpload) { %}<button class="btn primary">Carica</button>{% } %}</td>
            {% } else { %}
            <td colspan="2"></td>
            {% } %}
            <td class="cancel">{% if (!i) { %}<button class="btn info">Annulla</button>{% } %}</td>
        </tr>
    {% } %}
</script>
<script id="template-download" type="text/html">
    {% for (var i=0, files=o.files, l=files.length, file=files[0]; i<l; file=files[++i]) { %}
        <tr class="template-download fade" style="display:none">
            {% if (file.error) { %}
            <td></td>
            <td class="name">{%=file.name%}</td>
            <td class="size">{%=o.formatFileSize(file.size)%}</td>
            <td class="error" colspan="2"><span class="label important">Errore</span> {%=fileUploadErrors[file.error] || file.error%}</td>
            {% } else { %}
            <td class="preview">{% if (file.thumbnail_url) { %}
                <a href="{%=file.url%}" title="{%=file.name%}" rel="gallery"><img src="{%=file.thumbnail_url%}"></a>
                {% } %}</td>
            <td class="name">
                <a href="{%=file.url%}" title="{%=file.name%}" rel="{%=file.thumbnail_url&&'gallery'%}">{%=file.name%}</a>
            </td>
            <td class="size">{%=o.formatFileSize(file.size)%}</td>
            <td colspan="2"></td>
            {% } %}
            <td class="delete">
                <button class="btn danger" data-type="{%=file.delete_type%}" data-url="{%=file.delete_url%}">Cancella</button>
                <input type="checkbox" name="delete" value="1">
            </td>
        </tr>
    {% } %}
</script>
<script type="text/javascript">  
    var drag_drop_form_id = '';
    $('#<?php echo $field ?>_delete').<?php echo $value ? 'show' : 'hide' ?>();
    $('.<?php echo $id ?>_row').<?php echo $value ? 'hide' : 'show' ?>();
    $('#<?php echo $id ?>_bt_add').<?php echo $value ? 'hide' : 'show' ?>();
    $('#<?php echo $field ?>_tmb').<?php echo $value ? 'show' : 'hide' ?>();
    $('#<?php echo $id ?>').mouseover(function(){
        drag_drop_form_id = '<?php echo $id ?>';
    });
    $('#<?php echo $id ?>').fileupload({
        dataType: 'json',
        url: '/upload.php',
        <?php if($mimetype): ?>acceptFileTypes: <?php echo $mimetype; ?>,<?php endif; ?>
        <?php if($minFileSize): ?>minFileSize: <?php echo $minFileSize; ?>,<?php endif; ?>
        <?php if($maxFileSize): ?>maxFileSize: <?php echo $maxFileSize; ?>,<?php endif; ?>
        autoUpload: true,
        progressall: function (e, data) {
        var progress = parseInt(data.loaded / data.total * 100, 10);
            $('#progressbar').css(
                'width',
                progress + '%'
            );
        },
        add: function(e, data) {
            var that = $(this).data('fileupload'),
                files = data.files;
            that._adjustMaxNumberOfFiles(-files.length);
            data.isAdjusted = true;
            data.files.valid = data.isValidated = that._validate(files);
            if(!data.isValidated){
                n = 0;
                filesname = "",
                $.each(files, function (index, file) {
                    if(that._hasError(file)) {
                        switch(that._hasError(file)) {
                            <?php if($minFileSize): ?>
                            case 'minFileSize':
                                fancyAlert('Il documento ' +file.name+' è troppo piccolo (max: <?php echo $minFileSize ?> byte)');
                                resetAddButton();
                                break;
                            <?php endif; ?>
                            <?php if($maxFileSize): ?>
                            case 'maxFileSize':
                                fancyAlert('Il documento ' +file.name+' è troppo grande (max: <?php echo $maxFileSize ?> byte)');
                                resetAddButton();
                                break;
                            <?php endif; ?>
                            <?php if($mimetype): ?>
                            case 'acceptFileTypes':
                                fancyAlert('Il documento ' +file.name+' non è del tipo accettato (<?php echo str_replace(array('$/i','/','(','.','|',')'), array('','','','','',''), $mimetype) ?>)');
                                resetAddButton();
                                break;
                            <?php endif; ?>
                            default:
                                fancyAlert('Errore sul documento ' +file.name+': '+that._hasError(file));     
                                resetAddButton();
                            }
                    }
                });
                $('#<?php echo $id ?>_bt_reset').hide();
            }
            data.context = that._renderUpload(files)
                .appendTo(that._files)
                .data('data', data);
            // Force reflow:
            data.context.addClass('in');
            if ((that.options.autoUpload || data.autoUpload) &&
                    data.isValidated) {
                data.submit();
            }
        }
    });
    $('#<?php echo $id ?>')
    .bind('fileuploaddone', function (e, data) {
        if(data.jqXHR.responseText) {
            response = JSON.parse(data.jqXHR.responseText).last();
        } else {
            response = data.result.last();
        }
        if(response.form_id == drag_drop_form_id) {
            $('#<?php echo $field ?>').val(response.url);
            if(response.thumbnail_url) {
                $('#<?php echo $field ?>_tmb').html('<img src="'+response.thumbnail_url+'" alt="preview" />');
            } else {
                $('#<?php echo $field ?>_tmb').html(response.url);
            }
            $('#<?php echo $field ?>_tmb').show();
            $('#<?php echo $field ?>_delete').show();
            $('#<?php echo $id ?>_bt_add').hide();
            delete_url = response.delete_url;
            $('.<?php echo $id ?>_row').hide();
            $('#<?php echo $id ?>_bt_reset').hide();
            $('#table_files_<?php echo $field ?>').html('');
        }
        if(testFunction('singleUploadDoneCB')) {
            singleUploadDoneCB(response.url);
        }
    })
    .bind('fileuploadfail', function (e, data) {
        $('#<?php echo $id ?>_bt_reset').hide();
        $('#<?php echo $id ?>_bt_add').show();
        $('#<?php echo $field ?>_tmb').hide();
    })
    .bind('fileuploaddrop', function (e, data) {
        $('#<?php echo $id ?>row').css('background-color', '#ffffff'); 
        $('#<?php echo $id ?>_bt_reset').show();
        $('#<?php echo $id ?>_bt_add').hide();
    })
    .bind('fileuploaddragover', function (e) {
        $('#<?php echo $id ?>row').css('background-color', '#ffeeaa');
    });
    $('#<?php echo $id ?>_bt_reset').hide();
    
    delete_url = <?php echo $delete ? "'{$delete}'" : "false" ?>;
    
    function cancella_<?php echo $field ?>() {
        if(delete_url != false) {
            $.ajax({
                url: delete_url,
                success: function(){
                    $('#<?php echo $field ?>').val('');
                    $('#<?php echo $field ?>_tmb').html('');
                    $('#<?php echo $field ?>_tmb').hide();
                    $('.<?php echo $id ?>_row').show();
                    $('#<?php echo $id ?>_bt_add').show();
                    $('#<?php echo $field ?>_delete').hide();
                }        
            });
        }
    }
</script>
