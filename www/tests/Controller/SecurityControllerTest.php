<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tests de sécurité : vérifie que les routes protégées redirigent vers /login
 * quand l'utilisateur n'est pas authentifié.
 */
class SecurityControllerTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return \App\Kernel::class;
    }

    #[DataProvider('protectedRoutes')]
    public function testProtectedRoutesRedirectToLogin(string $url): void
    {
        self::bootKernel();
        $kernel = self::$kernel;
        
        $request = Request::create($url, 'GET');
        $response = $kernel->handle($request);

        $this->assertContains(
            $response->getStatusCode(),
            [Response::HTTP_FOUND, Response::HTTP_MOVED_PERMANENTLY],
            "La route $url devrait retourner une redirection (302) vers /login"
        );

        $location = $response->headers->get('Location');
        $this->assertStringContainsString('/login', $location ?? '', "La route $url devrait rediriger vers /login");
    }

    public static function protectedRoutes(): array
    {
        return [
            ['/pdf'],
            ['/history'],
            ['/contacts'],
            ['/subscription/change'],
        ];
    }

    public function testLoginPageLoads(): void
    {
        self::bootKernel();
        $request = Request::create('/login', 'GET');
        $response = self::$kernel->handle($request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertStringContainsString('form', $response->getContent());
    }

    public function testRegisterPageLoads(): void
    {
        self::bootKernel();
        $request = Request::create('/register', 'GET');
        $response = self::$kernel->handle($request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testHomePageLoads(): void
    {
        self::bootKernel();
        $request = Request::create('/', 'GET');
        $response = self::$kernel->handle($request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }
}
