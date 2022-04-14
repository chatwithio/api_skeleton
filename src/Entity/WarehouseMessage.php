<?php

namespace App\Entity;

use App\Repository\WarehouseMessageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Message;

#[ORM\Entity(repositoryClass: WarehouseMessageRepository::class)]
class WarehouseMessage extends Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 20)]
    private $code1;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private $code2;

    #[ORM\OneToMany(mappedBy: 'WarehouseMessage', targetEntity: Photo::class)]
    private $photos;

    public function __construct()
    {
        $this->photos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode1(): ?string
    {
        return $this->code1;
    }

    public function setCode1(?string $code1): self
    {
        $this->code1 = $code1;

        return $this;
    }

    public function getCode2(): ?string
    {
        return $this->code2;
    }

    public function setCode2(?string $code2): self
    {
        $this->code2 = $code2;

        return $this;
    }

    /**
     * @return Collection|Photo[]
     */
    public function getPhotos(): Collection
    {
        return $this->photos;
    }

    public function addPhoto(Photo $photo): self
    {
        if (!$this->photos->contains($photo)) {
            $this->photos[] = $photo;
            $photo->setWarehouseMessage($this);
        }

        return $this;
    }

    public function removePhoto(Photo $photo): self
    {
        if ($this->photos->removeElement($photo)) {
            // set the owning side to null (unless already changed)
            if ($photo->getWarehouseMessage() === $this) {
                $photo->setWarehouseMessage(null);
            }
        }

        return $this;
    }
}
