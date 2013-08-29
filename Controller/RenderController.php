<?php

namespace Ephp\DragDropBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/render-drag-drop")
 */
class RenderController extends Controller {

    use \Ephp\UtilityBundle\Controller\Traits\BaseController;

    /**
     * @Route("/s/{form}", name="render_upload_foto_scf", defaults={"form":true})
     * @Route("/s/{form}", name="render_upload_foto_snf", defaults={"form":false})
     * @Template()
     */
    public function uploadAction($form) {
        $percorsoFile = $this->getPercorso();
        return array(
            'id_cartella_upload' => $percorsoFile,
            'form' => $form,
        );
    }
    
    /**
     * @Route("/scf", name="render_upload_foto_scfs")
     * @Template("PunkAveFileUploaderBundle:Render:upload.html.twig")
     */
    public function uploadCfAction() {
        return $this->uploadAction(true);
    }
    
    /**
     * @Route("/snf", name="render_upload_foto_snfs")
     * @Template("PunkAveFileUploaderBundle:Render:upload.html.twig")
     */
    public function uploadNfAction() {
        return $this->uploadAction(false);
    }

    /**
     * @Route("/i/{form}", name="render_upload_foto_icf", defaults={"form":true})
     * @Route("/i/{form}", name="render_upload_foto_inf", defaults={"form":false})
     * @Template()
     */
    public function uploadIsotopeAction($form) {
        $percorsoFile = $this->getPercorso();
        return array(
            'id_cartella_upload' => $percorsoFile,
            'form' => $form
        );
    }
    
    /**
     * @Route("/icf", name="render_upload_foto_icfs")
     * @Template("PunkAveFileUploaderBundle:Render:uploadIsotope.html.twig")
     */
    public function uploadIsotopeCfAction() {
        return $this->uploadIsotopeAction(true);
    }
    
    /**
     * @Route("/inf", name="render_upload_foto_infs")
     * @Template("PunkAveFileUploaderBundle:Render:uploadIsotope.html.twig")
     */
    public function uploadIsotopeNfAction() {
        return $this->uploadIsotopeAction(false);
    }

    /**
     * Genera una stringa unica randomica per id_cartella_upload
     * Perche il bundle delle immaigni pretende delle cartelle uniche
     * anche per lo stesso utente
     */
    private function getPercorso() {
        $user = $this->getUser();
        if ($user) {
            return $user->getSlug() . '--' . sha1(uniqid($user->getEmail(), true));
        } else {
            $date = new \DateTime();
            return $date->format('Ymd') . '--' . sha1(uniqid(microtime(true), true));
        }
    }

}
