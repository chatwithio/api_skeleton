<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Annotation\ApiFilter;



#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[ORM\Table(name: '`message`')]
#[ApiResource(
    collectionOperations: ['get'],
    itemOperations: ['get'],
)]
#[ApiFilter(DateFilter::class, properties: ['created'])]

class Message
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

}
