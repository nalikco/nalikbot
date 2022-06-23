<?php
namespace Klassnoenazvanie;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="fin_outs")
 */
class FinOut
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="integer")
     */
    private $count;

    /**
     * @ORM\Column(type="string")
     */
    private $text;

    /**
     * @ORM\ManyToOne(targetEntity="Klassnoenazvanie\FinOutCat", inversedBy="fin_outs")
     * @ORM\JoinColumn(name="cat_id", referencedColumnName="id")
     */
    private $cat;

    public function getId(): int
    {
        return $this->id;
    }

    public function setCat(FinOutCat $cat): void
    {
        $this->cat = $cat;
    }

    public function getCat(): FinOutCat
    {
        return $this->cat;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): void
    {
        $this->date = $date;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): void
    {
        $this->text = $text;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function setCount(int $count): void
    {
        $this->count = $count;
    }
}
