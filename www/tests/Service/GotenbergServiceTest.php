<?php

namespace App\Tests\Service;

use App\Service\GotenbergService;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class GotenbergServiceTest extends TestCase
{
    public function testGeneratePdf()
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())
            ->method('getContent')
            ->willReturn('PDF_CONTENT');

        $client = $this->createMock(HttpClientInterface::class);
        // We expect a POST request. Validating exact arguments for multipart is complex in mock,
        // so we focus on ensuring request is called and returns expected content.
        $client->expects($this->once())
            ->method('request')
            ->willReturn($response);

        $service = new GotenbergService($client, 'http://gotenberg:3000');
        $pdf = $service->generatePdfFromHtml('<html></html>');

        $this->assertEquals('PDF_CONTENT', $pdf);
    }
}
