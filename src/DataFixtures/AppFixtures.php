<?php


namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Advice;
use App\Enum\Month;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        // Admin creation
        $admin = new User();
        $admin->setLogin('admin');
        $admin->setEmail('admin@example.com');
        $admin->setCity('Paris');
        $admin->setZipCode('75000');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setCreatedAt(new \DateTimeImmutable());
        $admin->setUpdatedAt(new \DateTimeImmutable());

        $hashedPassword = $this->passwordHasher->hashPassword($admin, 'adminpassword');
        $admin->setPassword($hashedPassword);

        $manager->persist($admin);

        // Users creation
        $users = [];
        for ($i = 1; $i <= 3; $i++) {
            $user = new User();
            $user->setLogin("user{$i}");
            $user->setEmail("user{$i}@example.com");
            $user->setCity('Lyon');
            $user->setZipCode('69000');
            $user->setRoles(['ROLE_USER']);
            $user->setCreatedAt(new \DateTimeImmutable());
            $user->setUpdatedAt(new \DateTimeImmutable());

            $hashedPassword = $this->passwordHasher->hashPassword($user, 'password');
            $user->setPassword($hashedPassword);

            $manager->persist($user);
            $users[] = $user;
        }

        // Advices creation
        $advicesData = [
            ['Taillez vos rosiers pour favoriser une belle floraison.', Month::MARCH],
            ['Protégez vos plantes du gel avec un voile d’hivernage.', Month::DECEMBER],
            ['Paillez le sol pour garder l’humidité en été.', Month::JULY],
            ['Semez vos légumes d’automne.', Month::SEPTEMBER],
        ];

        foreach ($advicesData as [$content, $month]) {
            $advice = new Advice();
            $advice->setTitle('Conseil de ' . $month->label());
            $advice->setContent($content);
            $advice->setMonth($month);
            $advice->setAuthor($admin);
            $advice->setCreatedAt(new \DateTimeImmutable());
            $advice->setUpdatedAt(new \DateTimeImmutable());

            $manager->persist($advice);
        }

        $manager->flush();
    }
}
