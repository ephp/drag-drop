<?php

namespace Ephp\DragDropBundle\BlueImp;

/*
 * 
 * Ho sovrascritto la funzione perche non mi rinominava correttamente il file, o non mi Sanitizzava il nome del file
 * Ho aggiunto un nome Randomico Unico
 * 
 * jQuery File Upload Plugin PHP Class 5.11
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

use PunkAve\FileUploaderBundle\BlueImp\UploadHandler as UploadHandlerBase;

class UploadHandler extends UploadHandlerBase {

    public function trim_file_name($name, $type, $index) {
        // Remove path information and dots around the filename, to prevent uploading
        // into different directories or replacing hidden system files.
        // Also remove control characters and spaces (\x00..\x20) around the filename:
        $path_info = pathinfo($name);

        $nome_univoco = sha1(uniqid($name, true));


        $file_name = $nome_univoco . "." . $path_info['extension'];



        //$file_name = trim(basename(stripslashes($name)), ".\x00..\x20");
        // Add missing file extension for known image types:
//        if (strpos($file_name, '.') === false &&
//            preg_match('/^image\/(gif|jpe?g|png)/', $type, $matches)) {
//            $file_name .= '.'.$matches[1];
//        }
        if ($this->options['discard_aborted_uploads']) {
            while (is_file($this->options['upload_dir'] . $file_name)) {
                $file_name = $this->upcount_name($file_name);
            }
        }
        return $file_name;
    }

    public function get() {
        $file_name = isset($_REQUEST['file']) ?
                basename(stripslashes($_REQUEST['file'])) : null;
        if ($file_name) {
            $info = $this->get_file_object($file_name);
        } else {
            $info = $this->get_file_objects();
        }
//        header('Content-type: application/json');
//        echo json_encode($info);
        return ($info);
    }

    /**
     * Faccio ritornare il Json che poi passo alle azioni dopo che ho caricato un'immagini
     * Questolo facico perche è staot modificato il comportamento "standard" del Bundle, evitando la cartella Tmp
     * @return Json
     */
    public function post() {
        if (isset($_REQUEST['_method']) && $_REQUEST['_method'] === 'DELETE') {
            return $this->delete();
        }
        $upload = isset($_FILES[$this->options['param_name']]) ?
                $_FILES[$this->options['param_name']] : null;
        $info = array();
        if ($upload && is_array($upload['tmp_name'])) {
            // param_name is an array identifier like "files[]",
            // $_FILES is a multi-dimensional array:
            foreach ($upload['tmp_name'] as $index => $value) {
                $info[] = $this->handle_file_upload(
                        $upload['tmp_name'][$index], isset($_SERVER['HTTP_X_FILE_NAME']) ?
                                $_SERVER['HTTP_X_FILE_NAME'] : $upload['name'][$index], isset($_SERVER['HTTP_X_FILE_SIZE']) ?
                                $_SERVER['HTTP_X_FILE_SIZE'] : $upload['size'][$index], isset($_SERVER['HTTP_X_FILE_TYPE']) ?
                                $_SERVER['HTTP_X_FILE_TYPE'] : $upload['type'][$index], $upload['error'][$index], $index
                );
            }
        } elseif ($upload || isset($_SERVER['HTTP_X_FILE_NAME'])) {
            // param_name is a single object identifier like "file",
            // $_FILES is a one-dimensional array:
            $info[] = $this->handle_file_upload(
                    isset($upload['tmp_name']) ? $upload['tmp_name'] : null, isset($_SERVER['HTTP_X_FILE_NAME']) ?
                            $_SERVER['HTTP_X_FILE_NAME'] : (isset($upload['name']) ?
                                    $upload['name'] : null), isset($_SERVER['HTTP_X_FILE_SIZE']) ?
                            $_SERVER['HTTP_X_FILE_SIZE'] : (isset($upload['size']) ?
                                    $upload['size'] : null), isset($_SERVER['HTTP_X_FILE_TYPE']) ?
                            $_SERVER['HTTP_X_FILE_TYPE'] : (isset($upload['type']) ?
                                    $upload['type'] : null), isset($upload['error']) ? $upload['error'] : null
            );
        }
//        header('Vary: Accept');
        $json = json_encode($info);
//        $redirect = isset($_REQUEST['redirect']) ?
//                stripslashes($_REQUEST['redirect']) : null;
//        if ($redirect) {
//            header('Location: ' . sprintf($redirect, rawurlencode($json)));
//            return;
//        }
//        if (isset($_SERVER['HTTP_ACCEPT']) &&
//                (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
//            header('Content-type: application/json');
//        } else {
//            header('Content-type: text/plain');
//        }
//        echo $json;
        return ($json);
    }
    
    public function delete() {
        $file_name = isset($_REQUEST['file']) ?
            basename(stripslashes($_REQUEST['file'])) : null;
        $file_path = $this->options['upload_dir'].$file_name;
        $success = is_file($file_path) && $file_name[0] !== '.' && unlink($file_path);
        if ($success) {
            foreach($this->options['image_versions'] as $version => $options) {
                $file = $options['upload_dir'].$file_name;
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
//        header('Content-type: application/json');
//        echo json_encode($success);
        return ($success);
    }

}