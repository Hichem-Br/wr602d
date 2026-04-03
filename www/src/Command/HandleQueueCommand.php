<?php

namespace App\Command;

use App\Entity\GenerationQueue;
use App\Repository\GenerationQueueRepository;
use App\Service\GotenbergService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
    name: 'app:handle-queue',
    description: 'Traite les demandes de fusion de PDF en file d\'attente.',
)]
class HandleQueueCommand extends Command
{
    public function __construct(
        private GenerationQueueRepository $queueRepository,
        private EntityManagerInterface $em,
        private GotenbergService $gotenbergService,
        private ParameterBagInterface $params,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Nombre de tâches à traiter', 5);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $limit = (int) $input->getOption('limit');

        // Retrieve pending jobs
        $jobs = $this->em->getRepository(GenerationQueue::class)->findBy(
            ['status' => GenerationQueue::STATUS_PENDING],
            ['createdAt' => 'ASC'],
            $limit
        );

        if (empty($jobs)) {
            $io->info('Aucune tâche en attente.');
            return Command::SUCCESS;
        }

        $io->title("Traitement de " . count($jobs) . " tâche(s)...");
        $uploadDir = $this->params->get('kernel.project_dir') . '/public/uploads/pdf';

        foreach ($jobs as $job) {
            $job->setStatus(GenerationQueue::STATUS_PROCESSING);
            $this->em->flush();

            try {
                // Build the list of file paths to merge
                $filePaths = array_map(
                    fn($f) => $uploadDir . '/' . $f,
                    $job->getFiles()
                );

                // Merge using GotenbergService
                $mergedContent = $this->gotenbergService->mergePdfs($filePaths);

                $resultFilename = 'merge_' . date('YmdHis') . '_' . $job->getId() . '.pdf';
                file_put_contents($uploadDir . '/' . $resultFilename, $mergedContent);

                $job->setResultFile($resultFilename);
                $job->setStatus(GenerationQueue::STATUS_DONE);
                $io->success("Job #{$job->getId()} traité : {$resultFilename}");
            } catch (\Exception $e) {
                $job->setStatus(GenerationQueue::STATUS_ERROR);
                $io->error("Job #{$job->getId()} échoué : " . $e->getMessage());
            }

            $this->em->flush();
        }

        $io->success('File d\'attente traitée.');
        return Command::SUCCESS;
    }
}
