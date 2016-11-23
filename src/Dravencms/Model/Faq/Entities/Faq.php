<?php
namespace Dravencms\Model\Faq\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Nette;

/**
 * Class Faq
 * @package App\Model\Faq\Entities
 * @ORM\Entity(repositoryClass="Gedmo\Sortable\Entity\Repository\SortableRepository")
 * @ORM\Table(name="faqFaq")
 */
class Faq extends Nette\Object
{
    use Identifier;
    use TimestampableEntity;


    /**
     * @var string
     * @Gedmo\Translatable
     * @ORM\Column(type="text",nullable=false)
     */
    private $q;

    /**
     * @var string
     * @Gedmo\Translatable
     * @ORM\Column(type="text",nullable=false)
     */
    private $a;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isActive;

    /**
     * @var integer
     * @Gedmo\SortablePosition
     * @ORM\Column(type="integer")
     */
    private $position;

    /**
     * @Gedmo\Locale
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     * and it is not necessary because globally locale can be set in listener
     */
    private $locale;

    /**
     * Faq constructor.
     * @param string $q
     * @param string $a
     * @param bool $isActive
     */
    public function __construct($q, $a, $isActive = true)
    {
        $this->q = $q;
        $this->a = $a;
        $this->isActive = $isActive;
    }

    /**
     * @param string $q
     */
    public function setQ($q)
    {
        $this->q = $q;
    }

    /**
     * @param string $a
     */
    public function setA($a)
    {
        $this->a = $a;
    }

    /**
     * @param boolean $isActive
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }

    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @param $locale
     */
    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return string
     */
    public function getQ()
    {
        return $this->q;
    }

    /**
     * @return string
     */
    public function getA()
    {
        return $this->a;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->isActive;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }
}

