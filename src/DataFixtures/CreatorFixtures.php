<?php

    namespace App\DataFixtures;

    use App\Entity\Creator;
    use Doctrine\Bundle\FixturesBundle\Fixture;
    use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
    use Doctrine\Persistence\ObjectManager;
    use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

    class CreatorFixtures extends Fixture  implements FixtureGroupInterface
    {
        public function __construct(
            private readonly UserPasswordHasherInterface $passwordHasher
        ){}


        public function load(ObjectManager $manager): void
        {
            for ($i=1; $i<=15; $i++) {
                $creator = new Creator();
                $creator->setEmail('creator-'.$i.'@free.fr');
                $creator->setFirstName('First Name - '.$i);
                $creator->setLastName('Last Name - '.$i);
                $creator->setPassword($this->passwordHasher->hashPassword($creator, 'password'));

                $manager->persist($creator);
            }

            $manager->flush();
        }

        public static function getGroups(): array
        {
            return ['creator_fixtures'];
        }
    }
