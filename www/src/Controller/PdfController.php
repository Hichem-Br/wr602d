<?php

namespace App\Controller;

use App\Service\GotenbergService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Generation;
use App\Entity\GenerationQueue;
use App\Entity\User;
use App\Repository\GenerationRepository;
use App\Repository\UserContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Parsedown;

#[IsGranted('ROLE_USER')]
class PdfController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private GenerationRepository $generationRepository,
        private MailerInterface $mailer
    ) {}

    #[Route('/pdf', name: 'app_pdf_dashboard')]
    public function index(): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $plan = $user->getPlan();
        $limit = $plan ? $plan->getLimitGeneration() : 0;
        
        $start = new \DateTimeImmutable('today midnight');
        $end = new \DateTimeImmutable('tomorrow midnight');
        $count = $this->generationRepository->countGenerationsByUserAndDateRange($user, $start, $end);

        return $this->render('pdf/index.html.twig', [
            'quota_limit' => $limit,
            'quota_count' => $count,
        ]);
    }

    #[Route('/pdf/url', name: 'app_pdf_url')]
    public function generateFromUrl(Request $request, GotenbergService $gotenbergService): Response
    {
        return $this->handleGeneration($request, $gotenbergService, 'url');
    }

    #[Route('/pdf/file', name: 'app_pdf_file')]
    public function generateFile(Request $request, GotenbergService $gotenbergService): Response
    {
        return $this->handleGeneration($request, $gotenbergService, 'file');
    }

    #[Route('/pdf/wysiwyg', name: 'app_pdf_wysiwyg')]
    #[IsGranted('ROLE_PREMIUM')]
    public function generateWysiwyg(Request $request, GotenbergService $gotenbergService): Response
    {
        return $this->handleGeneration($request, $gotenbergService, 'wysiwyg');
    }

    #[Route('/pdf/markdown', name: 'app_pdf_markdown')]
    #[IsGranted('ROLE_PREMIUM')]
    public function generateMarkdown(Request $request, GotenbergService $gotenbergService): Response
    {
        return $this->handleGeneration($request, $gotenbergService, 'markdown');
    }

    #[Route('/pdf/html', name: 'app_pdf_html')]
    #[IsGranted('ROLE_ENTERPRISE')]
    public function generateHtml(Request $request, GotenbergService $gotenbergService): Response
    {
        return $this->handleGeneration($request, $gotenbergService, 'html');
    }

    #[Route('/pdf/screenshot', name: 'app_pdf_screenshot')]
    #[IsGranted('ROLE_ENTERPRISE')]
    public function generateScreenshot(Request $request, GotenbergService $gotenbergService): Response
    {
        return $this->handleGeneration($request, $gotenbergService, 'screenshot');
    }

    #[Route('/convert/merge', name: 'app_pdf_merge')]
    #[IsGranted('ROLE_ENTERPRISE')]
    public function submitMerge(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        if ($request->isMethod('POST')) {
            $files = $request->files->get('files');
            if (!$files || count($files) < 2) {
                $this->addFlash('danger', 'Veuillez sélectionner au moins deux fichiers PDF à fusionner.');
                return $this->redirectToRoute('app_pdf_merge');
            }

            if (!$this->checkSubscriptionLimit($user)) {
                $this->addFlash('danger', 'Vous avez atteint votre limite d\'abonnement pour aujourd\'hui.');
                return $this->redirectToRoute('app_history'); 
            }

            $savedFiles = [];
            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/pdf';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            foreach ($files as $file) {
                if ($file && $file->getClientOriginalExtension() === 'pdf') {
                    $newFilename = uniqid('merge_part_') . '.pdf';
                    $file->move($uploadDir, $newFilename);
                    $savedFiles[] = $newFilename;
                }
            }

            if (count($savedFiles) < 2) {
                $this->addFlash('danger', 'Fichiers invalides. Assurez-vous d\'envoyer des PDF.');
                return $this->redirectToRoute('app_pdf_merge');
            }

            $queue = new GenerationQueue();
            $queue->setUser($user);
            $queue->setFiles($savedFiles);
            $queue->setStatus(GenerationQueue::STATUS_PENDING);

            $this->entityManager->persist($queue);
            $this->entityManager->flush();

            $this->addFlash('success', 'Vos PDF ont été ajoutés à la file d\'attente. Ils seront fusionnés d\'ici 10 minutes !');
            return $this->redirectToRoute('app_history');
        }

        return $this->render('pdf/merge.html.twig');
    }

    private function handleGeneration(Request $request, GotenbergService $gotenbergService, string $type): Response
    {
        $user = $this->getUser();
        
        // Access control check (redundant with IsGranted but good for type safety)
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        if ($request->isMethod('POST')) {
            // 1. Check Subscription Limit
            if (!$this->checkSubscriptionLimit($user)) {
                $this->addFlash('danger', 'Vous avez atteint votre limite d\'abonnement pour aujourd\'hui.');
                return $this->redirectToRoute('app_history'); 
            }

            try {
                $pdfContent = null;
                $inputForFilename = '';
                 if ($type === 'url') {
                     $inputForFilename = $request->request->get('url');
                 } elseif ($type === 'file') {
                     $file = $request->files->get('file');
                     $inputForFilename = $file ? $file->getClientOriginalName() : 'file';
                 }

                 $filename = $this->generateSmartFilename($inputForFilename, $type);

                if ($type === 'url') {
                    $url = $request->request->get('url');
                    if (!$url) throw new \Exception('URL is required');
                    $pdfContent = $gotenbergService->generatePdfFromUrl($url);
                } elseif ($type === 'screenshot') {
                    $url = $request->request->get('url');
                    if (!$url) throw new \Exception('URL is required');
                    $pdfContent = $gotenbergService->generateScreenshotFromUrl($url);
                } elseif ($type === 'file') {
                    $file = $request->files->get('file');
                    if (!$file) throw new \Exception('File is required');
                    $pdfContent = $gotenbergService->generatePdfFromFile($file);
                } elseif ($type === 'wysiwyg' || $type === 'html') {
                    $html = $request->request->get('content');
                    if (!$html) throw new \Exception('Content is required');
                    $pdfContent = $gotenbergService->generatePdfFromHtml($html);
                } elseif ($type === 'markdown') {
                    $markdown = $request->request->get('content');
                    if (!$markdown) throw new \Exception('Content is required');
                    
                    $parsedown = new Parsedown();
                    $html = $parsedown->text($markdown);
                    
                    // Wrap the markdown HTML with basic structure for proper rendering
                    $fullHtml = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>body{font-family:sans-serif;line-height:1.6;padding:20px;}</style></head><body>' . $html . '</body></html>';
                    $pdfContent = $gotenbergService->generatePdfFromHtml($fullHtml);
                }

                if ($pdfContent) {
                    // 2. Log Generation
                    $this->logGeneration($user, $filename);

                    // 3. Save File
                    $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/pdf';
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    $filePath = $uploadDir . '/' . $filename;
                    file_put_contents($filePath, $pdfContent);

                    // 4. Send Email to User
                    try {
                        $email = (new TemplatedEmail())
                            ->from('no-reply@docupdf.com')
                            ->to($user->getEmail())
                            ->subject('Votre document PDF généré - DocuPDF')
                            ->htmlTemplate('email/user_pdf.html.twig')
                            ->context([
                                'user' => $user,
                                'filename' => $filename
                            ])
                            ->attachFromPath($filePath);
                        
                        $this->mailer->send($email);
                    } catch (\Exception $mailException) {
                        // Silently fail email but preserve generation
                    }

                    $contentType = ($type === 'screenshot') ? 'image/png' : 'application/pdf';

                    return new Response($pdfContent, 200, [
                        'Content-Type' => $contentType,
                        'Content-Disposition' => 'inline; filename="' . $filename . '"',
                    ]);
                }
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Erreur lors de la génération du PDF : ' . $e->getMessage());
            }
        }

        return $this->render("pdf/{$type}.html.twig");
    }

    #[Route('/pdf/download/{id}', name: 'app_pdf_download')]
    public function download(Generation $generation): Response
    {
        // Check access
        if ($generation->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/pdf/' . $generation->getFile();

        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('The file does not exist.');
        }

        return $this->file($filePath, $generation->getFile(), ResponseHeaderBag::DISPOSITION_ATTACHMENT);
    }

    #[Route('/pdf/{id}/share', name: 'app_pdf_share', methods: ['POST'])]
    public function share(
        Generation $generation,
        Request $request,
        UserContactRepository $contactRepository,
    ): Response {
        if ($generation->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $contactId = $request->request->get('contact_id');
        $contact = $contactRepository->find($contactId);

        if (!$contact || $contact->getUser() !== $this->getUser()) {
            $this->addFlash('danger', 'Contact introuvable.');
            return $this->redirectToRoute('app_history');
        }

        $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/pdf/' . $generation->getFile();

        if (!file_exists($filePath)) {
            $this->addFlash('danger', 'Le fichier PDF introuvable, re-générez-le.');
            return $this->redirectToRoute('app_history');
        }

        try {
            $email = (new TemplatedEmail())
                ->from('no-reply@docupdf.com')
                ->to($contact->getEmail())
                ->subject($this->getUser()->getEmail() . ' vous a partagé un document - DocuPDF')
                ->htmlTemplate('email/shared_pdf.html.twig')
                ->context([
                    'sender' => $this->getUser(),
                    'contact' => $contact,
                    'filename' => $generation->getFile(),
                ])
                ->attachFromPath($filePath);

            $this->mailer->send($email);
            $this->addFlash('success', 'PDF envoyé avec succès à ' . $contact->getFirstname() . ' !');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur lors de l\'envoi : ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_history');
    }

    private function generateSmartFilename(string $input, string $type): string
    {
        $identifier = 'document';
        
        if ($type === 'url' || $type === 'screenshot') {
            $parsed = parse_url($input);
            $host = $parsed['host'] ?? '';
            // Remove www.
            $host = preg_replace('/^www\./', '', $host);
            // Get first part of domain
            $parts = explode('.', $host);
            if (!empty($parts[0])) {
                $identifier = ucfirst($parts[0]);
            }
        } elseif ($type === 'file') {
            $identifier = pathinfo($input, PATHINFO_FILENAME);
            // Sanitize
            $identifier = preg_replace('/[^a-zA-Z0-9_-]/', '', $identifier);
            if (empty($identifier)) $identifier = 'document';
        }

        $extension = ($type === 'screenshot') ? '.png' : '.pdf';
        return $identifier . '_' . date('YmdHis') . $extension;
    }

    private function checkSubscriptionLimit(User $user): bool
    {
        $plan = $user->getPlan();
        if (!$plan) return false; 

        $limit = $plan->getLimitGeneration();
        
        // Count generations created TODAY
        $start = new \DateTimeImmutable('today midnight');
        $end = new \DateTimeImmutable('tomorrow midnight');
        
        $count = $this->generationRepository->countGenerationsByUserAndDateRange($user, $start, $end);
        
        return $count < $limit;
    }

    private function logGeneration(User $user, string $filename): void
    {
        $generation = new Generation();
        $generation->setUser($user);
        $generation->setFile($filename);
        $generation->setCreatedAt(new \DateTimeImmutable());
        
        $this->entityManager->persist($generation);
        $this->entityManager->flush();
    }
}
