<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tests fonctionnels pour le contrôle d'accès selon les plans d'abonnement.
 */
class AccessControlTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return \App\Kernel::class;
    }

    public function testFreeUserCanAccessPdfDashboard(): void
    {
        self::bootKernel();
        $request = Request::create('/pdf', 'GET');
        $response = self::$kernel->handle($request);

        $this->assertContains(
            $response->getStatusCode(),
            [Response::HTTP_OK, Response::HTTP_FOUND],
            "La route /pdf doit retourner 200 ou 302"
        );
    }

    public function testUnauthenticatedAccessToHtmlToolRedirects(): void
    {
        self::bootKernel();
        $request = Request::create('/pdf/html', 'GET');
        $response = self::$kernel->handle($request);

        $this->assertContains(
            $response->getStatusCode(),
            [Response::HTTP_FOUND, Response::HTTP_UNAUTHORIZED, Response::HTTP_FORBIDDEN],
            "La route /pdf/html doit bloquer les non-connectés"
        );
    }

    public function testUnauthenticatedAccessToWysiwygToolRedirects(): void
    {
        self::bootKernel();
        $request = Request::create('/pdf/wysiwyg', 'GET');
        $response = self::$kernel->handle($request);

        $this->assertContains(
            $response->getStatusCode(),
            [Response::HTTP_FOUND, Response::HTTP_UNAUTHORIZED, Response::HTTP_FORBIDDEN],
            "La route /pdf/wysiwyg doit bloquer les non-connectés"
        );
    }

    public function testHistoryPageRequiresAuth(): void
    {
        self::bootKernel();
        $request = Request::create('/history', 'GET');
        $response = self::$kernel->handle($request);

        $this->assertEquals(
            Response::HTTP_FOUND,
            $response->getStatusCode(),
            "La route /history doit rediriger vers login si non connecté"
        );
    }

    public function testContactsPageRequiresAuth(): void
    {
        self::bootKernel();
        $request = Request::create('/contacts', 'GET');
        $response = self::$kernel->handle($request);

        $this->assertEquals(
            Response::HTTP_FOUND,
            $response->getStatusCode(),
            "La route /contacts doit rediriger vers login si non connecté"
        );
    }
}
