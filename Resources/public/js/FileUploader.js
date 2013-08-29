/**
 * Javascript che viene chaiamto dal bundle esterno Punkave
 * ho aggiunto dei nuovi valori che mi faccio ritornare come la medium_url e il numero della foto
 * chiamo anche una funzione attiva_pulsante, che mi serve per far venire fuori un pulsante di salvataggio, quando esiste almeno una foto. questo attiva_pulsante è in un altro js
 * Ho aggiunto al momento dell'append della foto la possibilità di agganciarci l'isotope, la variabile elem_isotope arrivata da un altro js 
 * verifico che esista, se esiste faccio il comportamente isotope altrimenti comportamento append classico
 * 
 * @see https://github.com/desandro/isotope/issues/259
 * @see http://isotope.metafizzy.co/docs/adding-items.html
 * 
 * @type Number
 * numero_foto mi serve per gestire il Jcrop
 */

var numero_foto = 0;

function PunkAveFileUploader(options)
{

    var self = this,
            uploadUrl = options.uploadUrl,
            viewUrl = options.viewUrl,
            $el = $(options.el),
            uploaderTemplate = _.template($('#file-uploader-template').html());
    $el.html(uploaderTemplate({}));

    //$el è il pezzo di codice che si trova in templates  id="file-uploader-template"

    var fileTemplate = _.template($('#file-uploader-file-template').html()),
            editor = $el.find('[data-files="1"]'),
            thumbnails = $el.find('[data-thumbnails="1"]');

    //editor è il pulsante file per caricare i FILE

    self.uploading = false;

    self.errorCallback = 'errorCallback' in options ? options.errorCallback : function(info) {
        if (window.console && console.log) {
            console.log(info)
        }
    },
            self.addExistingFiles = function(files)
    {
        _.each(files, function(file) {
            appendEditableImage({
                // cmsMediaUrl is a global variable set by the underscoreTemplates partial of MediaItems.html.twig
                'medium_url': viewUrl + '/medium/' + file,
                'thumbnail_url': viewUrl + '/thumbnails/' + file,
                'url': viewUrl + '/originals/' + file,
                'name': file,
                'numero_foto': numero_foto //nuemro foto globale, parametri che passo a templates.html.twig nel blocco che inizia a riga 33
            });
        });
    };

    // Delay form submission until upload is complete.
    // Note that you are welcome to examine the
    // uploading property yourself if this isn't
    // quite right for you
    self.delaySubmitWhileUploading = function(sel)
    {
        $(sel).submit(function(e) {
            if (!self.uploading)
            {
                return true;
            }
            function attempt()
            {
                if (self.uploading)
                {
                    setTimeout(attempt, 100);
                }
                else
                {
                    $(sel).submit();
                }
            }
            attempt();
            return false;
        });
    }

    if (options.blockFormWhileUploading)
    {
        self.blockFormWhileUploading(options.blockFormWhileUploading);
    }

    if (options.existingFiles)
    {
        self.addExistingFiles(options.existingFiles);
    }


//editor è il pulsante file per caricare i FILE

    editor.fileupload({//https://github.com/blueimp/jQuery-File-Upload/wiki/API     
        dataType: 'json', // => http://api.jquery.com/jQuery.getJSON/
        url: uploadUrl, //Salva nelle cartelle temp route upload_img ( NB Modificato salva subito dentro quella permanente
        dropZone: $("#drop_zone"),
        done: function(e, data) {
            if (data)
            {
                //console.log(data); oggetto
                _.each(data.result, function(item) {

                    /**
                     console.log(item);
                     * Item Con Errore
                     * Object {name: "9672157abc5f89c0a52c88418bb27ba1439d396a.JPG", size: 2816104, type: "image/jpeg", error: "maxFileSize"} 
                     * 
                     * Senza Errore
                     * Object {name: "49fee74aa042df105bc120a06996835bb9c83863.png", size: 72576, type: "image/png", url: "/uploads/attachments/solangione--ea492dc6bda379cc3…nals/49fee74aa042df105bc120a06996835bb9c83863.png", thumbnail_url: "/uploads/attachments/solangione--ea492dc6bda379cc3…ails/49fee74aa042df105bc120a06996835bb9c83863.png"…}
                     * delete_type: "DELETE"
                     * delete_url: "http://sn/app_dev.php/upload-upload-img?editId=solangione--ea492dc6bda379cc37367f11d19881cc9caa58c3?file=49fee74aa042df105bc120a06996835bb9c83863.png"
                     * large_url: "/uploads/attachments/solangione--ea492dc6bda379cc37367f11d19881cc9caa58c3/large/49fee74aa042df105bc120a06996835bb9c83863.png"
                     * medium_url: "/uploads/attachments/solangione--ea492dc6bda379cc37367f11d19881cc9caa58c3/medium/49fee74aa042df105bc120a06996835bb9c83863.png"
                     * name: "49fee74aa042df105bc120a06996835bb9c83863.png"
                     * profilo_url: "/uploads/attachments/solangione--ea492dc6bda379cc37367f11d19881cc9caa58c3/profilo/49fee74aa042df105bc120a06996835bb9c83863.png"
                     * size: 72576
                     * small_url: "/uploads/attachments/solangione--ea492dc6bda379cc37367f11d19881cc9caa58c3/small/49fee74aa042df105bc120a06996835bb9c83863.png"
                     * thumbnail_url: "/uploads/attachments/solangione--ea492dc6bda379cc37367f11d19881cc9caa58c3/thumbnails/49fee74aa042df105bc120a06996835bb9c83863.png"
                     * type: "image/png"
                     * url: "/uploads/attachments/solangione--ea492dc6bda379cc37367f11d19881cc9caa58c3/originals/49fee74aa042df105bc120a06996835bb9c83863.png"
                     */

                    appendEditableImage(item); //aggiunge al <li>


                    //Se trovo un errore non incremento il numero delle foto
                    if (!item.error) {
                        //attiva il pulnsate per salvare le foto permanentemente
                        if (testFunction('callbackDragDrop')) {
                            callbackDragDrop(item, numero_foto);
                        }
                        attiva_pulsante(numero_foto);
                        //console.log(numero_foto)
                        numero_foto++;
                    }
                });
            }
        },
        start: function(e) {
            $el.find('[data-spinner="1"]').show();
            self.uploading = true;
        },
        stop: function(e) {
            $el.find('[data-spinner="1"]').hide();
            self.uploading = false;
        }
    });
    /**
     * funzioni che vengono chiamate quando vengono caricati i file
     * per verificare se far vedere i pulsanti s
     * 
     * @param {int} numero_foto è un sequenziale che mi arriva da fileUploader.js, è in numero della foto Caricata
     * @returns {undefined},
     */
    function attiva_pulsante(numero_foto) {

        //checkPrivata(numero_foto);

        //Booleano che lo imposto dal Twig...
        //se ho didascalia faccio venire fuori la possibilita di aggiungere dei campi al che descrivono la foto e la possibilita di effetturare un Jcrop
        if (testaVariabile('didascalia') && didascalia) {
            validaFormFotoUpdate('form_carica_foto-' + numero_foto);
            //Click per impostare la foto profilo faccio aprire il Jcrop, quello che mi fa aprire il fancybox
            $("#modifica_foto-" + numero_foto).click(function() {
                $(".coordinate_jcrop").empty();
                $("#jcrop_foto-" + numero_foto + " #coordinate_jcrop-" + numero_foto).append(html);
            });
        }

        //if ($(".thumbnails .element").length === 1) {
        console.log(numero_foto);
        console.log($("#upload_foto_btn").length <= 0);
        if ($('#iso-container .isotope-item:not(:first)').length > 0) { //dopo la modifica che tutto è isotope
            if ($('#fine_configurazione').children().length == 0) {
                creaElemento('#fine_configurazione', '<a href="' + $('#url_action').val() + '"><p>' + Translator.get('SNFotoBundle:continua') + '</p></a>', 0.5);
            }
//        if ($("#upload_foto_btn").length <= 0) { //verifica se il pulsante esiste di già
//            creaElemento('#upload_foto', '<button id="upload_foto_btn" class="small" onclick="caricaFoto()" type="submit">' + Translator.get('SNFotoBundle:form_upload_foto.save') + '</button>', 0.5);
//        }
        }

        //Tooltip sulle immagini appena caricate
        $(".element").on("mouseenter", function() {
            $(this).find('.caption').css('opacity', '1');
        });

        $(".element").on("mouseleave", function() {
            $(this).find('.caption').css('opacity', '.3');
        });

    }
    
    // Expects thumbnail_url, url, and name properties. thumbnail_url can be undefined if
    // url does not end in gif, jpg, jpeg or png. This is designed to work with the
    // result returned by the UploadHandler class on the PHP side
    //Qui decido anche in base alla variaible globale elem_isotope se fare un append classico oppure agganciare il comportamento di isotope
    function appendEditableImage(info)
    {
        if (info.error)
        {
            self.errorCallback(info);
            return;
        }

        //è tutto il <li> della foto
        //console.log(fileTemplate(info));
        var li = $.parseHTML ? $($.parseHTML(fileTemplate(info))) : $(fileTemplate(info));

        li.find('[data-action="delete"]').click(function(event) {

            //domando se è sicuro di cancellare la foto
            if (!confirm(Translator.get('EphpDragDropBundle:elimina'))) {
                return false;
            }

            var file = $(this).closest('[data-name]');
            var name = file.attr('data-name');
            $.ajax({
                type: 'delete',
                url: setQueryParameter(uploadUrl, 'file', name),
                beforeSend: function() {
                    disabilitaElemento($(":button"));
                },
                success: function() {
                    file.remove();

                    //verifica se far vedere il pulsante upload permanente oppure no (se non ci sono piu foto)
                    verifica_pulsante();

                    abilitaElemento($(":button"), 1000);

                },
                error: function(msg) {
                    abilitaElemento($(":button"), 3000);
                },
                dataType: 'json'
            });
            return false;
        });

        thumbnails.css('opacity', '1');

        //$("#button").click(function() {  
        if (typeof elem_isotope === 'undefined') {
            thumbnails.append(li);
        } else {

            var di = $(li);
            // elem_isotope.prepend(di).isotope('reloadItems').isotope({sortBy: 'original-order'});
            elem_isotope = $('#iso-container');
            elem_isotope.isotope('insert', di, function() {
                elem_isotope.isotope();
            });

//        $("#button").click(function() {  
//            var di = $(li);
//            iso.isotope('insert', di);
//            iso.isotope();
//        });
//
//        $("#button2").click(function() {
//            iso.isotope('shuffle');
//        });

        }
        //});
        //thumbnails.append(li);
    }

    function setQueryParameter(url, param, paramVal)
    {
        var newAdditionalURL = "";
        var tempArray = url.split("?");
        var baseURL = tempArray[0];
        var additionalURL = tempArray[1];
        var temp = "";
        if (additionalURL)
        {
            var tempArray = additionalURL.split("&");
            var i;
            for (i = 0; i < tempArray.length; i++)
            {
                if (tempArray[i].split('=')[0] != param)
                {
                    newAdditionalURL += temp + tempArray[i];
                    temp = "&";
                }
            }
        }
        var newTxt = temp + "" + param + "=" + encodeURIComponent(paramVal);
        var finalURL = baseURL + "?" + newAdditionalURL + newTxt;
        return finalURL;
    }
}


