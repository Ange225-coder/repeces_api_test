<?php

    namespace App\Entity;

    use App\Repository\CreatorRepository;
    use Doctrine\Common\Collections\ArrayCollection;
    use Doctrine\Common\Collections\Collection;
    use Doctrine\ORM\Mapping as ORM;
    use JMS\Serializer\Annotation\Groups;
    use JMS\Serializer\Annotation\Since;
    use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
    use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
    use Symfony\Component\Security\Core\User\UserInterface;
    use Symfony\Component\Validator\Constraints as Assert;
    use Hateoas\Configuration\Annotation as Hateoas;

    // Self-discoverability for {{ GET }}
    #[Hateoas\Relation(
        'self',
        href: new Hateoas\Route(
            'creator_details',
            parameters: ['id' => 'expr(object.getId())']
        ),
        exclusion: new Hateoas\Exclusion(
            groups: ['getCreator']
        )
    )]

    // Self-discoverability for {{ UPDATE }}
    #[Hateoas\Relation(
        'update',
        href: new Hateoas\Route(
            'update_creator',
            //parameters: ['id' => 'expr(object.getId())']
        ),
        exclusion: new Hateoas\Exclusion(
            groups: ['getCreator'],
            excludeIf: "expr(not is_granted('ROLE_CREATOR'))"
        )
    )]

    // Self-discoverability for {{ DELETE }}
    #[Hateoas\Relation(
        'delete',
        href: new Hateoas\Route(
            'delete_creator',
            //parameters: ['id' => 'expr(object.getId())']
        ),
        exclusion: new Hateoas\Exclusion(
            groups: ['getCreator'],
            excludeIf: "expr(not is_granted('ROLE_CREATOR'))"
        )
    )]

    #[ORM\Entity(repositoryClass: CreatorRepository::class)]
    #[UniqueEntity('email', message: 'Il semble que cette utilisateur existe déjà')]
    class Creator implements UserInterface, PasswordAuthenticatedUserInterface
    {
        #[ORM\Id]
        #[ORM\GeneratedValue]
        #[ORM\Column]
        #[Groups(['getCreator', 'getRecepe'])]
        private ?int $id = null;

        #[ORM\Column(length: 180, unique: true)]
        #[Groups(['getCreator', 'getRecepe'])]
        #[Assert\Email(message: 'Le format de l\'email est incorrect')]
        private ?string $email = null;

        #[ORM\Column]
        //#[Groups(['getCreator', 'getRecepe'])]
        private array $roles = [];

        #[ORM\Column]
        private ?string $password = null;

        #[ORM\Column(length: 128)]
        #[Groups(['getCreator', 'getRecepe'])]
        #[Assert\NotBlank(message: 'Le créateur doit posséder un nom')]
        private ?string $lastName = null;

        #[ORM\Column(length: 128, nullable: true)]
        #[Groups(['getCreator', 'getRecepe'])]
        private ?string $firstName = null;

        #[ORM\ManyToMany(targetEntity: Recepe::class, mappedBy: 'creator')]//, cascade: ['remove'], orphanRemoval: true)]
        #[Groups(['getCreator'])]
        private Collection $recepes;

        #[ORM\Column(length: 25, nullable: true)]
        #[Groups(['getCreator', 'getRecepe'])]
        #[Since('2.0')]
        private ?string $gender = null;

        public function __construct()
        {
            $this->recepes = new ArrayCollection();
        }



        // Setters and Getters
        public function getId(): ?int
        {
            return $this->id;
        }

        public function getEmail(): ?string
        {
            return $this->email;
        }

        public function setEmail(string $email): static
        {
            $this->email = $email;

            return $this;
        }

        public function getUserIdentifier(): string
        {
            return (string) $this->email;
        }

        // Will be used by lexitJWT for authentication
        public function getUsername(): string
        {
            return $this->getUserIdentifier();
        }


        public function getRoles(): array
        {
            $roles = $this->roles;
            // guarantee every user at least has ROLE_CREATOR
            $roles[] = 'ROLE_CREATOR';

            return array_unique($roles);
        }

        public function setRoles(array $roles): static
        {
            $this->roles = $roles;

            return $this;
        }

        public function getPassword(): ?string
        {
            return $this->password;
        }

        public function setPassword(string $password): static
        {
            $this->password = $password;

            return $this;
        }


        public function __serialize(): array
        {
            $data = (array) $this;
            $data["\0" . self::class . "\0password"] = hash('crc32c', $this->password);

            return $data;
        }

        public function getLastName(): ?string
        {
            return $this->lastName;
        }

        public function setLastName(string $lastName): static
        {
            $this->lastName = $lastName;

            return $this;
        }

        public function getFirstName(): ?string
        {
            return $this->firstName;
        }

        public function setFirstName(?string $firstName): static
        {
            $this->firstName = $firstName;

            return $this;
        }

        /**
         * @return Collection<int, Recepe>
         */
        public function getRecepes(): Collection
        {
            return $this->recepes;
        }

        public function addRecepe(Recepe $recepe): static
        {
            if (!$this->recepes->contains($recepe)) {
                $this->recepes->add($recepe);
                $recepe->addCreator($this);
            }

            return $this;
        }

        public function removeRecepe(Recepe $recepe): static
        {
            if ($this->recepes->removeElement($recepe)) {
                $recepe->removeCreator($this);
            }

            return $this;
        }

        public function getGender(): ?string
        {
            return $this->gender;
        }

        public function setGender(?string $gender): static
        {
            $this->gender = $gender;

            return $this;
        }
    }
