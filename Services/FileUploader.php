<?php

namespace Ephp\DragDropBundle\Services;

use PunkAve\FileUploaderBundle\Services\FileUploader as FileUploaderBase;

class FileUploader extends FileUploaderBase
{

    /**
     * Handles a file upload. Call this from an action, after validating the user's
     * right to upload and delete files and determining your 'folder' option. A good
     * example:
     *
     * $id = $this->getRequest()->get('id');
     * // Validate the id, make sure it's just an integer, validate the user's right to edit that 
     * // object, then...
     * $this->get('punkave.file_upload').handleFileUpload(array('folder' => 'photos/' . $id))
     * 
     * DOES NOT RETURN. The response is generated in native PHP by BlueImp's UploadHandler class.
     *
     * Note that if %file_uploader.file_path%/$folder already contains files, the user is 
     * permitted to delete those in addition to uploading more. This is why we use a
     * separate folder for each object's associated files.
     *
     * Any passed options are merged with the service parameters. You must specify
     * the 'folder' option to distinguish this set of uploaded files
     * from others.
     * 
     * MODIFICA BARNO
     * Ho aggiunto max_file_size, min_width,min_height che le prende dal service che
     * a sua volta le prende da parameters come parametri globali
     * 
     * Ho cambiato anche il percorso della classe alla 
     * $upload_handler = new \SN\UploadFotoBundle\BlueImp\UploadHandler
     * Facendolo Puntare al mio bundle
     * 
     * Abbiamo anche cambiato il return, abbiamo restituito un Json che al suo interno è un stdClass
     * lo utilizziamo per salvare sul database. un esempio lo abbiamo in:
     * /Users/Corrado/Documents/sn/src/SN/FotoBundle/Controller/UploadFotoController.php 
     * Action => caricaAction
     *
     */
    public function handleFileUpload($options = array())
    {
        if (!isset($options['folder']))
        {
            throw new \Exception("You must pass the 'folder' option to distinguish this set of files from others");
        }

        $options = array_merge($this->options, $options);
        
        
        $allowedExtensions = $options['allowed_extensions'];

        // Build a regular expression like /(\.gif|\.jpg|\.jpeg|\.png)$/i
        $allowedExtensionsRegex = '/(' . implode('|', array_map(function($extension) { return '\.' . $extension; }, $allowedExtensions)) . ')$/i';

        $sizes = (isset($options['sizes']) && is_array($options['sizes'])) ? $options['sizes'] : array();

        $filePath = $options['file_base_path'] . '/' . $options['folder'];
        $webPath = $options['web_base_path'] . '/' . $options['folder'];

        foreach ($sizes as &$size)
        {
            $size['upload_dir'] = $filePath . '/' . $size['folder'] . '/';
            $size['upload_url'] = $webPath . '/' . $size['folder'] . '/';
        }

        $originals = $options['originals'];

        $uploadDir = $filePath . '/' . $originals['folder'] . '/';

        foreach ($sizes as &$size)
        {
            @mkdir($size['upload_dir'], 0777, true);
        }

        @mkdir($uploadDir, 0777, true);

        $upload_handler = new \Ephp\DragDropBundle\BlueImp\UploadHandler(
            array(
                'upload_dir' => $uploadDir, 
                'upload_url' => $webPath . '/' . $originals['folder'] . '/', 
                'script_url' => $options['request']->getUri(),
                'image_versions' => $sizes,
                'accept_file_types' => $allowedExtensionsRegex,
                'max_file_size' => $options["dimensione_massima_immagine"],                
                'min_width' => $options["larghezza_minima_immagine"],                
                'min_height' => $options["altezza_minima_immagine"],                
            ));
/*
        // From https://github.com/blueimp/jQuery-File-Upload/blob/master/server/php/index.php
        // There's lots of REST fanciness here to support different upload methods, so we're
        // keeping the blueimp implementation which goes straight to the PHP standard library.
        // TODO: would be nice to port that code fully to Symfonyspeak.

        header('Pragma: no-cache');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Content-Disposition: inline; filename="files.json"');
        header('X-Content-Type-Options: nosniff');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: OPTIONS, HEAD, GET, POST, PUT, DELETE');
        header('Access-Control-Allow-Headers: X-File-Name, X-File-Type, X-File-Size');
*/
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'OPTIONS':
                break;
            case 'HEAD':
            case 'GET':
                $result = $upload_handler->get();
                break;
            case 'POST':
                if (isset($_REQUEST['_method']) && $_REQUEST['_method'] === 'DELETE') {
                    $result = $upload_handler->delete();
                } else {
                    $result = $upload_handler->post();
                    
                }
                break;
            case 'DELETE':
                $result = $upload_handler->delete();
                break;
            default:
                header('HTTP/1.1 405 Method Not Allowed');
        }

        // Without this Symfony will try to respond; the BlueImp upload handler class already did,
        // so it's time to hush up
        /**
         * Modifica Barno
         * Ho creato $result che prende quello che restituisce quelle funzioni, in particolare $upload_handler->post();
         * Questo perche abbiamo cambiato il comportamento, abbiamo bypassato la cartella tmp e quindi anche il sync
         */
        return($result);
        //exit(0);
    }
}
