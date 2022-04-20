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

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $whatsappImageIdentifier;


    #[ORM\Column(type: 'datetime')]
    private $created;

    #[ORM\ManyToOne(targetEntity: WarehouseMessage::class, inversedBy: 'photos')]
    #[ORM\JoinColumn(nullable: false)]
    private $WarehouseMessage;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $mime;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWhatsappImageIdentifier(): ?string
    {
        return $this->whatsappImageIdentifier;
    }

    public function setWhatsappImageIdentifier(string $whatsappImageIdentifier): self
    {
        $this->whatsappImageIdentifier = $whatsappImageIdentifier;

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

    public function getMime(): ?string
    {
        return $this->mime;
    }

    public function setMime(?string $mime): self
    {
        $this->mime = $mime;

        return $this;
    }


}
