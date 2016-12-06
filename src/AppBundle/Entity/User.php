<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */

class User extends BaseUser
{

    /**
     * One User has Many orders.
     * @ORM\OneToMany(targetEntity="uzsakymas", mappedBy="user")
     */

    private $uzsakymas;

    public function __construct()
    {
        parent::__construct();
        $this->uzsakymas = new ArrayCollection();

    }

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="vardas", type="string", length=50, nullable=true)
     */
    private $vardas;

    /**
     * @var string
     *
     * @ORM\Column(name="pavarde", type="string", length=50, nullable=true)
     */
    private $pavarde;

    /**
     * @var string
     *
     * @ORM\Column(name="adresas", type="string", length=150, nullable=true)
     */
    private $adresas;

    /**
     * @var string
     *
     * @ORM\Column(name="tel_nr", type="string", length=20, nullable=true)
     */
    private $telNr;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set vardas
     *
     * @param string $vardas
     *
     * @return User
     */
    public function setVardas($vardas)
    {
        $this->vardas = $vardas;

        return $this;
    }

    /**
     * Get vardas
     *
     * @return string
     */
    public function getVardas()
    {
        return $this->vardas;
    }

    /**
     * Set pavarde
     *
     * @param string $pavarde
     *
     * @return User
     */
    public function setPavarde($pavarde)
    {
        $this->pavarde = $pavarde;

        return $this;
    }

    /**
     * Get pavarde
     *
     * @return string
     */
    public function getPavarde()
    {
        return $this->pavarde;
    }

    /**
     * Set adresas
     *
     * @param string $adresas
     *
     * @return User
     */
    public function setAdresas($adresas)
    {
        $this->adresas = $adresas;

        return $this;
    }

    /**
     * Get adresas
     *
     * @return string
     */
    public function getAdresas()
    {
        return $this->adresas;
    }

    /**
     * Set telNr
     *
     * @param string $telNr
     *
     * @return User
     */
    public function setTelNr($telNr)
    {
        $this->telNr = $telNr;

        return $this;
    }

    /**
     * Get telNr
     *
     * @return string
     */
    public function getTelNr()
    {
        return $this->telNr;
    }
}

