<?php

namespace App\Entity;

use App\Repository\WarehouseMessageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Message;

#[ORM\Entity(repositoryClass: WarehouseMessageRepository::class)]
class WarehouseMessage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer',name:'id')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $messageFrom;

    #[ORM\Column(type: 'text')]
    private $textBody;

    #[ORM\Column(type: 'string', length: 255)]
    private $profileName;

    #[ORM\Column(type: 'bigint')]
    private $waId;

    #[ORM\Column(type: 'string', length: 1)]
    private $status;

    #[ORM\Column(type: 'string', length: 255)]
    private $code;

    #[ORM\Column(type: 'bigint')]
    private $timestamp;

    #[ORM\Column(type: 'datetime')]
    private $created;

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


    public function getMessageFrom(): ?string
    {
        return $this->messageFrom;
    }

    public function setMessageFrom(string $messageFrom): self
    {
        $this->messageFrom = $messageFrom;

        return $this;
    }

    public function getTextBody(): ?string
    {
        return $this->textBody;
    }

    public function setTextBody(string $textBody): self
    {
        $this->textBody = $textBody;

        return $this;
    }

    public function getTimestamp(): ?string
    {
        return $this->timestamp;
    }

    public function setTimestamp(string $timestamp): self
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    public function getProfileName(): ?string
    {
        return $this->profileName;
    }

    public function setProfileName(string $profileName): self
    {
        $this->profileName = $profileName;

        return $this;
    }

    public function getWaId(): ?string
    {
        return $this->waId;
    }

    public function setWaId(string $waId): self
    {
        $this->waId = $waId;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

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
