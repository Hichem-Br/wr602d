<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Component\Mime\Part\DataPart;

class GotenbergService
{
    public function __construct(
        private HttpClientInterface $client,
        private string $gotenbergUrl,
    ) {
    }

    public function generatePdfFromHtml(string $html): string
    {
        $formFields = [
            'index.html' => new DataPart($html, 'index.html', 'text/html'),
        ];

        $formData = new FormDataPart($formFields);

        $response = $this->client->request(
            'POST',
            $this->gotenbergUrl . '/forms/chromium/convert/html',
            [
                'headers' => $formData->getPreparedHeaders()->toArray(),
                'body' => $formData->bodyToIterable(),
            ]
        );

        return $response->getContent();
    }
}
