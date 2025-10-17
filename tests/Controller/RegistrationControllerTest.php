<?php

namespace App\Tests\Controller;

use App\Factory\UserFactory;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Browser\HttpOptions;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

final class RegistrationControllerTest extends WebTestCase
{
    use HasBrowser;
    use ResetDatabase;

    public function testSuccessfulRegistration(): void
    {
        $client = static::createClient();
        $client->request('GET', '/register');

        $this->assertResponseIsSuccessful();

        $client->submitForm('Register', [
            'registration_form[email]' => 'test@example.com',
            'registration_form[firstname]' => 'John',
            'registration_form[lastname]' => 'Doe',
            'registration_form[birthdate]' => '2000-01-01',
            'registration_form[password][first]' => 'Password123!',
            'registration_form[password][second]' => 'Password123!',
        ]);

        $this->assertResponseRedirects('/register/success');

        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'test@example.com']);
        $this->assertNotNull($user);
    }

    public function testInvalidEmailFormat(): void
    {
        $form = $this->browser()
            ->visit('/register')
            ->assertSuccessful();

        $response = $form
            ->fillField('registration_form[email]', 'invalid-email')
            ->fillField('registration_form[firstname]', 'John')
            ->fillField('registration_form[lastname]', 'Doe')
            ->fillField('registration_form[birthdate]', '2000-01-01')
            ->fillField('registration_form[password][first]', 'Password123!')
            ->fillField('registration_form[password][second]', 'Password123!')
            ->click('Register')
        ;

        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertSeeIn('.text-red-700', 'This email is not a valid email address.')
        ;
    }

    public function testWeakPassword(): void
    {
        $client = static::createClient();
        $client->request('GET', '/register');

        $client->submitForm('Register', [
            'registration_form[email]' => 'test2@example.com',
            'registration_form[firstname]' => 'John',
            'registration_form[lastname]' => 'Doe',
            'registration_form[birthdate]' => '2000-01-01',
            'registration_form[password][first]' => 'short',
            'registration_form[password][second]' => 'short',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('.form-error-message', 'Your password must be at least 8 characters long.');
    }

    public function testMismatchedPasswords(): void
    {
        $client = static::createClient();
        $client->request('GET', '/register');

        $client->submitForm('Register', [
            'registration_form[email]' => 'test3@example.com',
            'registration_form[firstname]' => 'John',
            'registration_form[lastname]' => 'Doe',
            'registration_form[birthdate]' => '2000-01-01',
            'registration_form[password][first]' => 'Password123!',
            'registration_form[password][second]' => 'Different123!',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('.form-error-message', 'The password fields must match.');
    }

    public function testExistingEmailRegistration(): void
    {
        $client = static::createClient();
        $client->request('GET', '/register');

        $client->submitForm('Register', [
            'registration_form[email]' => 'test@example.com',
            'registration_form[firstname]' => 'Jane',
            'registration_form[lastname]' => 'Doe',
            'registration_form[birthdate]' => '2000-01-01',
            'registration_form[password][first]' => 'Password123!',
            'registration_form[password][second]' => 'Password123!',
        ]);

        $this->assertResponseRedirects('/register/success');

        $client->request('GET', '/register');
        $client->submitForm('Register', [
            'registration_form[email]' => 'test@example.com',
            'registration_form[firstname]' => 'John',
            'registration_form[lastname]' => 'Doe',
            'registration_form[birthdate]' => '2000-01-01',
            'registration_form[password][first]' => 'Password123!',
            'registration_form[password][second]' => 'Password123!',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('.form-error-message', 'This email is already registered.');
    }

    public function testAgeRestriction(): void
    {
        $client = static::createClient();
        $client->request('GET', '/register');

        $client->submitForm('Register', [
            'registration_form[email]' => 'minor@example.com',
            'registration_form[firstname]' => 'Minor',
            'registration_form[lastname]' => 'User',
            'registration_form[birthdate]' => (new \DateTime())->format('Y-m-d'), // Today's date, making user younger than 18
            'registration_form[password][first]' => 'Password123!',
            'registration_form[password][second]' => 'Password123!',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('.form-error-message', 'You must be at least 18 years old to register.');
    }
}
