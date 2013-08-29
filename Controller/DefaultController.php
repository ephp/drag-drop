<?php

namespace Ephp\DragDropBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/drag-drop")
 */
class DefaultController extends Controller {
    
    use \Ephp\UtilityBundle\Controller\Traits\BaseController;

    /**
     * viene chiamata tramite Js e salva le foto nella cartella PERMANENTE (cambiato il comportamento, non esiste piu Temp
     * Ecco $immagine ovvero cosa restituisce il servizio
     * var_dump(json_decode($immagine));
     * <b>array</b> <i>(size=1)</i>
      0 <font color='#888a85'>=&gt;</font>
      <b>object</b>(<i>stdClass�K�c���</i>)[<i>1009</i>]
      <i>public</i> 'name' <font color='#888a85'>=&gt;</font> <small>string</small> <font color='#cc0000'>'4177ff6ec8eaf58b9382787daec26bce8ab17a57.png'</font> <i>(length=44)</i>
      <i>public</i> 'size' <font color='#888a85'>=&gt;</font> <small>int</small> <font color='#4e9a06'>418453</font>
      <i>public</i> 'type' <font color='#888a85'>=&gt;</font> <small>string</small> <font color='#cc0000'>'image/png'</font> <i>(length=9)</i>
      <i>public</i> 'url' <font color='#888a85'>=&gt;</font> <small>string</small> <font color='#cc0000'>'/uploads/attachments/solangione--c14704c810363ca96228f4bf9fd8c98143b4c475/originals/4177ff6ec8eaf58b9382787daec26bce8ab17a57.png'</font> <i>(length=128)</i>
      <i>public</i> 'thumbnail_url' <font color='#888a85'>=&gt;</font> <small>string</small> <font color='#cc0000'>'/uploads/attachments/solangione--c14704c810363ca96228f4bf9fd8c98143b4c475/thumbnails/4177ff6ec8eaf58b9382787daec26bce8ab17a57.png'</font> <i>(length=129)</i>
      <i>public</i> 'profilo_url' <font color='#888a85'>=&gt;</font> <small>string</small> <font color='#cc0000'>'/uploads/attachments/solangione--c14704c810363ca96228f4bf9fd8c98143b4c475/profilo/4177ff6ec8eaf58b9382787daec26bce8ab17a57.png'</font> <i>(length=126)</i>
      <i>public</i> 'small_url' <font color='#888a85'>=&gt;</font> <small>string</small> <font color='#cc0000'>'/uploads/attachments/solangione--c14704c810363ca96228f4bf9fd8c98143b4c475/small/4177ff6ec8eaf58b9382787daec26bce8ab17a57.png'</font> <i>(length=124)</i>
      <i>public</i> 'medium_url' <font color='#888a85'>=&gt;</font> <small>string</small> <font color='#cc0000'>'/uploads/attachments/solangione--c14704c810363ca96228f4bf9fd8c98143b4c475/medium/4177ff6ec8eaf58b9382787daec26bce8ab17a57.png'</font> <i>(length=125)</i>
      <i>public</i> 'large_url' <font color='#888a85'>=&gt;</font> <small>string</small> <font color='#cc0000'>'/uploads/attachments/solangione--c14704c810363ca96228f4bf9fd8c98143b4c475/large/4177ff6ec8eaf58b9382787daec26bce8ab17a57.png'</font> <i>(length=124)</i>
      <i>public</i> 'delete_url' <font color='#888a85'>=&gt;</font> <small>string</small> <font color='#cc0000'>'http://sn/app_dev.php/upload-upload-img?editId=solangione--c14704c810363ca96228f4bf9fd8c98143b4c475?file=4177ff6ec8eaf58b9382787daec26bce8ab17a57.png'</font> <i>(length=149)</i>
      <i>public</i> 'delete_type' <font color='#888a85'>=&gt;</font> <small>string</small> <font color='#cc0000'>'DELETE'</font> <i>(length=6)</i>
      </pre>
     * @Route("/upload", name="drag_drop_save")
     * @Template("SNFotoBundle:CaricaFoto:upload_foto.html.twig")
     */
    public function caricaAction() {
        if ($this->get('request')->isXmlHttpRequest()) {
            $editId = $this->getRequest()->get('editId'); //ID CARTELLA
            $user = $this->getUser();
            //Se vengo da una cancellazione di un'immagine appena uppata, non entra nel classico servizio, ma entro nel servizio
            //che mi sono creato io, perche voglio tenere traccia almeno di un'immagine
            if ($this->get('request')->getMethod() !== "DELETE") {
                $immagine = $this->get('punk_ave.file_uploader')->handleFileUpload(array('folder' => 'attachments/' . $editId));
            } else {
                $file = $this->findOneBy('\Ephp\DragDropBundle\Entity\File')->findOneBy(array('nome' => $this->getRequest()->get('file'), 'user' => $user ? $user->getProfilo() : null));

                //Elimino le foto Fisicamente ma tengo sempre la originals per pararci il culetto
                foreach ($this->get('gestioneImmagini')->getSizes() as $version => $options) {
                    $file = $file->getUploadRootDir() . "/" . $file->getPath() . "/" . $options["folder"] . "/" . $file->getNome();
                    unlink($file);
                }

                try {
                    $this->getEm()->beginTransaction();
                    $this->remove($file);
                    $this->getEm()->commit();

                    $out_json = array(
                        'status' => "OK",
                    );
                } catch (\Exception $e) {
                    $this->getEm()->rollback();
                    $out_json = array(
                        'status' => "KO",
                    );
                }

                return new \Symfony\Component\HttpFoundation\Response(json_encode($out_json));
            }

            try {
                $this->getEm()->beginTransaction();
                $imgs = json_decode($immagine);
                foreach ($imgs as $key => $value) {
                    if (!isset($value->error)) { //Controllo che sia un File che deve essere salvato nel DB
                        $file = new \Ephp\DragDropBundle\Entity\File();
                        $file->setNome($value->name); //oggetto stdClass, vedi sui commenti come è fatto
                        $file->setPath($editId);
                        $file->setUser($user);
                        $file->setSize($value->size);
                        $file->setType($value->type);
                        $urls = array();
                        foreach($value as $k => $v) {
                            if(strpos('url', $k) !== false) {
                                $k = str_replace('_url', '', $k);
                                $urls[$k] = $v;
                            }
                        }
                        $file->setUrls($urls);
                        $this->persist($file);
                        $imgs[$key]->id = $file->getId(); 
                    } else {
//                        exit(0);
                        //return;
                    }
                }

                $this->getEm()->commit();
            } catch (\Exception $e) {
                $this->getEm()->rollback();
            }
//            exit(0); //Perche restituisce un Json
            return new \Symfony\Component\HttpFoundation\Response(json_encode($imgs));
        } else {
            //non è una chiamata Ajax
            return new RedirectResponse($this->container->get('router')->generate('index'));
        }
    }

}
