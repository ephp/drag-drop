<style type="text/css">
    #<?php echo $id ?>row, #<?php echo $field ?>_tmb {
        border: 1px dashed #999;
        padding: 5px;
        display: block;
        width: <?php echo $x ?>px;
        height: <?php echo $y ?>px;
        position: relative;
        float: left;
        margin-right: 10px;
        text-align: center;
    }
    #<?php echo $id ?>row {
        background: url(/images/user_placeholder.jpg) no-repeat 5px 5px;
        margin-left: 25px;
    }
    #<?php echo $id ?>row:before {
        content: "Trascina quì i file";
        font-size: 12px;
        font-style: italic;
        color: #666;
    }
</style>
<?php
// Questo DIV è quello che compare quando è stata caricata l'immagine
?>
<div id="cont_<?php echo $field ?>_tmb" class="tmb">
    <div id="<?php echo $field ?>_tmb" class="colmono left rounded-5"></div>
</div>
<?php
// Questo DIV è la progress bar
?>

<div class="row fileuploadrow <?php echo $id ?>_row rounded-5" id="<?php echo $id ?>row">
    <div class="fileupload-buttonbar">
        <div class="progressbar fileupload-progressbar">
            <div style="width:0%;"></div>
        </div>
        <input type="hidden" name="d" value="<?php echo $dir ?>">
        <input type="hidden" name="x" value="<?php echo $x ?>">
        <input type="hidden" name="y" value="<?php echo $y ?>">
        <input type="hidden" name="f" value="<?php echo $id ?>">
        <button type="reset" class="btn info cancel button-orange smaller" id="<?php echo $id ?>_bt_reset">Annulla</button>
    </div>
</div>
<div class="btn success fileinput-button left" id="<?php echo $id ?>_bt_add">
    <a href="javascrip:void(0)" class="button-cyan smaller">oppure seleziona da qui i file</a>
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
    var n_file = 0;
    var drag_drop_form_id = '';
    $('#<?php echo $field ?>_delete').<?php echo $values ? 'show' : 'hide' ?>();
    $('.<?php echo $id ?>_row').<?php echo $values ? 'hide' : 'show' ?>();
    $('#<?php echo $id ?>_bt_add').<?php echo $values ? 'hide' : 'show' ?>();
    $('#<?php echo $field ?>_tmb').<?php echo $values ? 'show' : 'hide' ?>();
    $('#<?php echo $id ?>').mouseover(function(){
        drag_drop_form_id = '<?php echo $id ?>';
    });
    $('#<?php echo $id ?>').fileupload({
        dropZone: $('#<?php echo $id ?>row'),
        dataType: 'json',
        url: '/upload.php',
        autoUpload: true,
        <?php if($mimetype): ?>acceptFileTypes: <?php echo $mimetype; ?>,<?php endif; ?>
        minFileSize: 1024,
        maxFileSize: 3145728
    });
    $('#<?php echo $id ?>')
    .bind('fileuploaddone', function (e, data) {
        if(data.jqXHR.responseText) {
            response = JSON.parse(data.jqXHR.responseText).last();
        } else {
            response = data.result.last();
        }
        if(response.form_id == drag_drop_form_id) {
            $('#<?php echo $field ?>').val($('#<?php echo $field ?>').val()+','+response.url);
            $('#<?php echo $field ?>_tmb').append(addFoto(response.thumbnail_url));
            $('#<?php echo $field ?>_tmb').show();
            $('#<?php echo $field ?>_delete').show();
//            $('#<?php echo $id ?>_bt_add').hide();
            delete_url = response.delete_url;
//            $('.<?php echo $id ?>_row').hide();
            $('#<?php echo $id ?>_bt_reset').hide();
            $('#table_files_<?php echo $field ?>').html('');
            n_file++;
            if(testFunction('multiUploadDoneCB')) {
                multiUploadDoneCB(response);
            }
        }
    })
    .bind('fileuploadfail', function (e, data) {
        $('#<?php echo $id ?>_bt_reset').hide();
//        $('#<?php echo $id ?>_bt_add').show();
        $('#<?php echo $field ?>_tmb').hide();
    })
    .bind('fileuploaddrop', function (e, data) {
        $('#<?php echo $id ?>row').css('background-color', '#ffffff'); 
        $('#<?php echo $id ?>_bt_reset').show();
//        $('#<?php echo $id ?>_bt_add').hide();
    })
    .bind('fileuploaddragover', function (e) {
        $('#<?php echo $id ?>row').css('background-color', '#ffeeaa');
    });
    $('#<?php echo $id ?>_bt_reset').hide();
    
    //delete_url = <?php echo $delete ? "'{$delete}'" : "false" ?>;
    
    function cancella_img_<?php echo $field ?>(field) {
        field.closest('div').children('img');
        img = field.closest('div').children('img').attr('src').replace(/\/thumbnails\//g,'/files/');
        field.closest('div').remove();
        $('#<?php echo $field ?>').val($('#<?php echo $field ?>').val().remove(','+img));
        n_file--;
        if($('#<?php echo $field ?>').val().trim() == '') {
            $('.<?php echo $id ?>_row').show();
            $('#<?php echo $id ?>_bt_add').show();
            $('#<?php echo $field ?>_tmb').hide();
        }
    }
    
    function addFoto(thumbnail) {
        return '<div style="float:left;"><img src="'+thumbnail+'" alt="preview" /><button type="button" class="<?php echo $field; ?>_delete button-orange left" onclick="cancella_img_<?php echo $field; ?>($(this))">Elimina</button></div>';
    }
    
    var old_files = JSON.parse('<?php echo $tmb ?>');
    for(i = 0; i < old_files.length; i++) {
        $('#<?php echo $field ?>_tmb').append(addFoto(old_files[i]));
    }
</script>