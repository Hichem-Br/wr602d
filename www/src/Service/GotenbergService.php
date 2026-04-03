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

    public function generatePdfFromUrl(string $url): string
    {
        $formFields = [
            'url' => $url,
        ];

        $formData = new FormDataPart($formFields);

        $response = $this->client->request(
            'POST',
            $this->gotenbergUrl . '/forms/chromium/convert/url',
            [
                'headers' => $formData->getPreparedHeaders()->toArray(),
                'body' => $formData->bodyToIterable(),
            ]
        );

        return $response->getContent();
    }

    public function generatePdfFromFile(\Symfony\Component\HttpFoundation\File\UploadedFile $file): string
    {
        $formFields = [
            'files' => new DataPart(fopen($file->getPathname(), 'r'), $file->getClientOriginalName()),
        ];

        $formData = new FormDataPart($formFields);

        // Determine endpoint based on file type
        $endpoint = '/forms/libreoffice/convert'; // Default to LibreOffice for documents
        
        // If it's an HTML file, use Chromium
        if ($file->guessExtension() === 'html' || $file->getClientMimeType() === 'text/html') {
             $endpoint = '/forms/chromium/convert/html';
             // Chromium expects 'index.html' for the main file usually, but specific file upload might work differently. 
             // Gotenberg /forms/chromium/convert/html takes index.html.
             // If we upload a file, we might need to rename it to index.html in the DataPart if it's the main file.
             $formFields = [
                'index.html' => new DataPart(fopen($file->getPathname(), 'r'), 'index.html', 'text/html'),
            ];
            $formData = new FormDataPart($formFields);
        }

        $response = $this->client->request(
            'POST',
            $this->gotenbergUrl . $endpoint,
            [
                'headers' => $formData->getPreparedHeaders()->toArray(),
                'body' => $formData->bodyToIterable(),
            ]
        );

        return $response->getContent();
    }

    public function generateScreenshotFromUrl(string $url): string
    {
        $formFields = [
            'url' => $url,
            'format' => 'png',
        ];

        $formData = new FormDataPart($formFields);

        $response = $this->client->request(
            'POST',
            $this->gotenbergUrl . '/forms/chromium/screenshot/url',
            [
                'headers' => $formData->getPreparedHeaders()->toArray(),
                'body' => $formData->bodyToIterable(),
            ]
        );

        return $response->getContent();
    }

    public function mergePdfs(array $filePaths): string
    {
        $formFields = [];
        foreach ($filePaths as $index => $filePath) {
            $formFields["file{$index}.pdf"] = new DataPart(fopen($filePath, 'r'), basename($filePath));
        }

        $formData = new FormDataPart($formFields);

        $response = $this->client->request(
            'POST',
            $this->gotenbergUrl . '/forms/pdfengines/merge',
            [
                'headers' => $formData->getPreparedHeaders()->toArray(),
                'body' => $formData->bodyToIterable(),
            ]
        );

        return $response->getContent();
    }
}
