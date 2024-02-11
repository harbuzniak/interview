<?php declare(strict_types=1);

namespace App\Entity;

use App\Enum\ContactType;
use App\Repository\PersonContactRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Ignore;

#[ORM\Entity(repositoryClass: PersonContactRepository::class)]
class PersonContact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    private ?string $value = null;

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'contacts')]
    #[ORM\JoinColumn(nullable: false)]
    #[Ignore]
    private ?Person $person = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?ContactType
    {
        return $this->type ? ContactType::from($this->type) : null;
    }

    public function setType(ContactType|string|null $type): static
    {
        $this->type = $type instanceof ContactType ? $type->value : $type;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): static
    {
        $this->person = $person;

        return $this;
    }
}
