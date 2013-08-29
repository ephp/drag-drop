<?php

namespace Ephp\DragDropBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Foto
 *
 * @ORM\Table(name="up_files")
 * @ORM\Entity(repositoryClass="Ephp\DragDropBundle\Entity\FileRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class File {
    
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="nome", type="string", length=255)
     */
    private $nome;

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=255)
     */
    private $path;

    /**
     * @var string
     *
     * @ORM\Column(name="titolo", type="string", length=255, nullable=true)
     */
    private $titolo;

    /**
     * @var string
     *
     * @ORM\Column(name="descrizione", type="string", length=255, nullable=true)
     */
    private $descrizione;

    /**
     * @var string
     *
     * @ORM\Column(name="size", type="integer")
     */
    private $size;
    
    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=64)
     */
    private $type;
    
    /**
     * @var string
     *
     * @ORM\Column(name="urls", type="array")
     */
    private $urls;

    /**
     * @ORM\ManyToOne(targetEntity="Ephp\AclBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true) 
     */
    protected $user;
    
    private $path_default = "/uploads/files/default";
    private $nome_default = "avatar.png";
    
    public function __construct() {
        $this->foto_votata = new \Doctrine\Common\Collections\ArrayCollection();
        $this->foto_moderate = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set nome
     *
     * @param string $nome
     * @return Foto
     */
    public function setNome($nome) {
        $this->nome = $nome;

        return $this;
    }

    /**
     * Get nome
     *
     * @return string 
     */
    public function getNome() {
        return $this->nome;
    }

    /**
     * Set path
     *
     * @param string $path
     * @return Foto
     */
    public function setPath($path) {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string 
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * Set user
     *
     * @param \Ephp\ACLBundle\Entity\User $user
     * @return Foto
     */
    public function setUser(\Ephp\ACLBundle\Entity\User $user = null) {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Ephp\ACLBundle\Entity\User
     */
    public function getUser() {
        return $this->user;
    }
    
    public function getSize() {
        return $this->size;
    }

    public function setSize($size) {
        $this->size = $size;
    }

    public function getType() {
        return $this->type;
    }

    public function setType($type) {
        $this->type = $type;
    }

    public function getUrls() {
        return $this->urls;
    }

    public function setUrls($urls) {
        $this->urls = $urls;
    }

    public function getImageSize($size) {
        if(isset($this->urls[$size])) {
            return $this->urls[$size];
        }
        return $this->urls['url'];
    }
    
    /**
     * @ORM\PostPersist()

      public function postSalvataggio() {
      if($this->user->getFaseRegistrazione() != 100) { //100 vuol dire che è finita la registrazione
      $this->user->postSalvataggio();
      }
      } */
    public function getAbsolutePath() {
        return !$this->path ? null : $this->getUploadRootDir() . '/' . $this->path;
    }

    public function getWebPath() {
        return !$this->path ? null : $this->getUploadDir() . '/' . $this->path;
    }

    public function getUploadRootDir() {
        // il percorso assoluto della cartella dove i documenti caricati verranno salvati
        return __DIR__ . '/../../../../web/' . $this->getUploadDir();
    }

    protected function getUploadDir() {
        // togliamo __DIR__ in modo da visualizzare correttamente nella vista il file caricato
        return 'uploads/attachments';
    }

    public function getAbsoluteUrl($size) {
        if (!in_array($size, array('large', 'medium', 'originals', 'small', 'thumbnails', 'user','bannato_thumb','bannato_medium'))) {
            $size = 'thumbnails';
        }
        return !$this->path ? null : $this->getUploadRootDir() . '/' . $this->path . '/' . $size . '/' . $this->nome;
    }

    /**
     * 
     * @param string $size 'large', 'medium', 'originals', 'small', 'thumbnails'
     * @return string path percorso dell'immagine 
     */
    public function getWebUrl($size) {
        if (!in_array($size, array('large', 'medium', 'originals', 'small', 'thumbnails', 'user','bannato_thumb','bannato_medium'))) {
            $size = 'thumbnails';
        }
        return !$this->path ? null : '/' . $this->getWebPath() . '/' . $size . '/' . $this->nome;
    }
    
    /**
     * 
     * @param string $size 'large', 'medium', 'originals', 'small', 'thumbnails'
     * @return string path percorso dell'immagine 
     */
    public static function getWebUrlDefault($size) {
        if (!in_array($size, array('large', 'medium', 'originals', 'small', 'thumbnails', 'user'))) {
            $size = 'thumbnails';
        }
        return $this->path_default . '/' . $size . '/' . $this->nome_default;
    }

    /**
     * Set titolo
     *
     * @param string $titolo
     * @return Foto
     */
    public function setTitolo($titolo) {
        $this->titolo = $titolo;

        return $this;
    }

    /**
     * Get titolo
     *
     * @return string 
     */
    public function getTitolo() {
        return $this->titolo;
    }

    /**
     * Set descrizione
     *
     * @param string $descrizione
     * @return Foto
     */
    public function setDescrizione($descrizione) {
        $this->descrizione = $descrizione;

        return $this;
    }

    /**
     * Get descrizione
     *
     * @return string 
     */
    public function getDescrizione() {
        return $this->descrizione;
    }

}