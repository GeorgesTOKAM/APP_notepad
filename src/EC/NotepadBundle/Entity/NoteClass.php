<?php

namespace EC\NotepadBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * NoteClass
 *
 * @ORM\Table(name="note_class")
 * @ORM\Entity(repositoryClass="EC\NotepadBundle\Repository\NoteClassRepository")
 */
class NoteClass
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, unique=true)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     */
    private $content;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var Categorie
     *
     * @Assert\NotNull()
     * @ORM\ManyToOne(targetEntity="Categorie", inversedBy="notes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $categorie;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->date = new \DateTime('NOW');
    }

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
     * Set title
     *
     * @param string $title
     *
     * @return NoteClass
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set content
     *
     * @param string $content
     *
     * @return NoteClass
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return NoteClass
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set categorie
     *
     * @param \EC\NotepadBundle\Entity\Categorie $categorie
     *
     * @return NoteClass
     */
    public function setCategorie(\EC\NotepadBundle\Entity\Categorie $categorie)
    {
        $this->categorie = $categorie;

        return $this;
    }

    /**
     * Get categorie
     *
     * @return \EC\NotepadBundle\Entity\Categorie
     */
    public function getCategorie()
    {
        return $this->categorie;
    }
}
