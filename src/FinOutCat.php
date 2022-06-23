<?php
namespace Klassnoenazvanie;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="fin_out_cats")
 */
class FinOutCat
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $title;

    /**
     * @ORM\Column(type="string")
     */
    private $text;

    /**
     * @ORM\OneToMany(targetEntity="Klassnoenazvanie\FinOut", mappedBy="out")
     */
    private $outs;

    public function getId(): int
    {
        return $this->id;
    }

    public function setFinOut(FinOut $outs): void
    {
        $this->outs = $outs;
    }

    public function getFinOut(): FinOut
    {
        return $this->outs;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): void
    {
        $this->text = $text;
    }
}
