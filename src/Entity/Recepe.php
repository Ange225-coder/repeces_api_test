<?php
/**
 * Les propriétés addCreator() et getCreator() ont été
 * légèrement modifiée pour éviter l'erreur 500 :
 * Typed property App\\Entity\\Recepe::$creator must
 * not be accessed before initialization
 */

    namespace App\Entity;

    use App\Repository\RecepeRepository;
    use Doctrine\Common\Collections\ArrayCollection;
    use Doctrine\Common\Collections\Collection;
    use Doctrine\DBAL\Types\Types;
    use Doctrine\ORM\Mapping as ORM;
    use JMS\Serializer\Annotation\Groups;
    use Symfony\Component\Validator\Constraints as Assert;

    #[ORM\Entity(repositoryClass: RecepeRepository::class)]
    class Recepe
    {
        #[ORM\Id]
        #[ORM\GeneratedValue]
        #[ORM\Column]
        #[Groups(['getRecepe', 'getCreator'])]
        private ?int $id = null;

        #[ORM\Column(length: 128)]
        #[Groups(['getRecepe', 'getCreator'])]
        #[Assert\NotBlank(message: 'Ajouter un nom à la recette')]
        #[Assert\Length(
            min: 3,
            max: 30,
            minMessage: 'Le nom de la recette doit faire {{ limit }} caractères minimum',
            maxMessage: 'Le nom de la recette doit faire {{ limit }} caractères maximum'
        )]
        private ?string $name = null;

        #[ORM\Column(type: Types::TEXT)]
        #[Groups(['getRecepe', 'getCreator'])]
        #[Assert\NotBlank(message: 'Ajouter une description à la recette')]
        private ?string $description = null;

        #[ORM\ManyToMany(targetEntity: Creator::class, inversedBy: 'recepes')]
        #[Groups(['getRecepe'])]
        //#[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private ?Collection $creator = null;

        public function __construct()
        {
            $this->creator = new ArrayCollection();
        }



        // Setters and Getters
        public function getId(): ?int
        {
            return $this->id;
        }

        public function getName(): ?string
        {
            return $this->name;
        }

        public function setName(string $name): static
        {
            $this->name = $name;

            return $this;
        }

        public function getDescription(): ?string
        {
            return $this->description;
        }

        public function setDescription(string $description): static
        {
            $this->description = $description;

            return $this;
        }

//        public function getCreator(): Collection
//        {
//            return $this->creator;
//        }


//        public function addCreator(Creator $creator): static
//        {
//            if (!$this->creator->contains($creator)) {
//                $this->creator->add($creator);
//            }
//
//            return $this;
//        }

        public function getCreator(): Collection
        {
            // Sécurité au cas où la collection serait null (suite à une désérialisation)
            if ($this->creator === null) {
                $this->creator = new ArrayCollection();
            }

            return $this->creator;
        }

        public function addCreator(Creator $creator): static
        {
            // On s'assure d'utiliser getCreator() pour initialiser la collection si besoin
            $creatorCollection = $this->getCreator();

            if (!$creatorCollection->contains($creator)) {
                $creatorCollection->add($creator);
            }

            return $this;
        }

        public function removeCreator(Creator $creator): static
        {
            $this->creator->removeElement($creator);

            return $this;
        }
    }
