<?php
namespace Ephp\DragDropBundle\Controller\Traits;

trait DragDropController  {

    public function singleFile() {
        $request = $this->getRequest();
        $field = $request->get('field', false);
        if (!$field) {
            throw new \Exception("Property 'field' required");
        }
        $id = $request->get('id', 'fileupload');
        $mimetype = $request->get('mimetype', false);
        $minFileSize = $request->get('minFileSize', false);
        $maxFileSize = $request->get('maxFileSize', false);
        $value = $request->get('value', false);
        $dir = $request->get('dir', '');
        $env = $request->get('env', false);
        $x = $request->get('resize_x', 100);
        $y = $request->get('resize_y', 100);
        $delete = false;
        $tmb = false;
        if (preg_match('/.(gif|jpe?g|png)$/i', $value)) {
            $tmb = str_replace('/files/', '/thumbnails/', $value);
            $file = str_replace('/uploads/files/', '', $value);
            $delete = '/upload.php?file=' . $file;
        }
        return array(
            'id' => $id,
            'field' => $field,
            'mimetype' => $mimetype,
            'minFileSize' => $minFileSize,
            'maxFileSize' => $maxFileSize,
            'value' => $value != false,
            'env' => $env,
            'dir' => $dir,
            'x' => $x,
            'y' => $y,
            'tmb' => $tmb,
            'delete' => $delete,
        );
    }

    public function multiFile() {
        $_tmb = array();
        $_values = array();
        $_didascalie = array();
        $_foto_id = array();
        $_delete = array();
        $request = $this->getRequest();
        $field = $request->get('field', false);
        if (!$field) {
            throw new \Exception("Property 'field' required");
        }
        $id = $request->get('id', 'fileupload');
        $mimetype = $request->get('mimetype', false);
        $values = $request->get('values', false);
        $didascalie = $request->get('didascalie', false);
        $foto_id = $request->get('foto_id', false);
        if ($values) {
            $values = json_decode($values);
            foreach ($values as $value) {
                if (preg_match('/.(gif|jpe?g|png)$/i', $value)) {
                    $_tmb[] = str_replace('/files/', '/thumbnails/', $value);
                    $_values[] = str_replace('/thumbnails/', '/files/', $value);
                    $file = str_replace('/uploads/files/', '', $value);
                    $_delete[] = '/upload.php?file=' . $file;
                }
            }
            $_didascalie = $didascalie ? json_decode($didascalie) : $_values;
            $_foto_id = $foto_id ? json_decode($foto_id) : $_values;
            if (!$foto_id) {
                for ($i = 0; $i < count($_foto_id); $i++) {
                    $_foto_id[$i] = '';
                }
            }
        }
        $dir = $request->get('dir', '');
        $env = $request->get('env', false);
        $x = $request->get('resize_x', 100);
        $y = $request->get('resize_y', 100);
        return array(
            'id' => $id,
            'field' => $field,
            'mimetype' => $mimetype,
            'values' => json_encode($_values),
            'didascalie' => json_encode($_didascalie),
            'foto_id' => json_encode($_foto_id),
            'env' => $env,
            'dir' => $dir,
            'x' => $x,
            'y' => $y,
            'tmb' => json_encode($_tmb),
            'delete' => json_encode($_delete),
            'n' => count($_tmb),
        );
    }

}
