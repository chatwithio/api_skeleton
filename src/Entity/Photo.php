<?php

namespace App\Entity;

use App\Repository\PhotoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PhotoRepository::class)]
class Photo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $whatsappIdentifier;

    #[ORM\Column(type: 'datetime')]
    private $created;

    #[ORM\ManyToOne(targetEntity: WarehouseMessage::class, inversedBy: 'photos')]
    #[ORM\JoinColumn(nullable: false)]
    private $WarehouseMessage;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWhatsappIdentifier(): ?string
    {
        return $this->whatsappIdentifier;
    }

    public function setWhatsappIdentifier(string $whatsappIdentifier): self
    {
        $this->whatsappIdentifier = $whatsappIdentifier;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getWarehouseMessage(): ?WarehouseMessage
    {
        return $this->WarehouseMessage;
    }

    public function setWarehouseMessage(?WarehouseMessage $WarehouseMessage): self
    {
        $this->WarehouseMessage = $WarehouseMessage;

        return $this;
    }
}
