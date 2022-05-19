<?php
namespace Klassnoenazvanie;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 */
class User
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $vkid;

    /**
     * @ORM\Column(type="integer")
     */
    private $app;

    /**
     * @ORM\Column(type="integer")
     */
    private $step;

    public function getId(): int
    {
        return $this->id;
    }

    public function getVkId(): int
    {
        return $this->vkid;
    }

    public function setVkId(int $vkid): void
    {
        $this->vkid = $vkid;
    }

    public function getApp(): int
    {
        return $this->app;
    }

    public function setApp(int $app): void
    {
        $this->app = $app;
    }

    public function getStep(): int
    {
        return $this->step;
    }

    public function setStep(int $step): void
    {
        $this->step = $step;
    }
}