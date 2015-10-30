<?php

namespace Vesax\MaintenanceBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * Class DisableSiteCommand
 *
 * @author Artur Vesker
 */
class DisableSiteCommand extends ContainerAwareCommand
{

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('app:disable')
            ->setDescription('Disable app')
            ->addOption(
                'start',
                null,
                InputOption::VALUE_OPTIONAL,
                'Maintenance start datetime.'
            )
            ->addOption(
                'end',
                null,
                InputOption::VALUE_OPTIONAL,
                'Maintenance end datetime.'
            )
            ->addOption(
                'reason',
                null,
                InputOption::VALUE_OPTIONAL,
                'Maintenance reason'
            )
            ->addOption(
                'add-clients',
                null,
                InputOption::VALUE_NONE
            )
            ->addOption(
                'no-interactive',
                null,
                InputOption::VALUE_NONE,
                'Without confirmations mode'
            )
        ;
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $webDir = $this->getContainer()->getParameter('kernel.root_dir') . '/../web/';

        /**
         * @var QuestionHelper $questionHelper
         */
        $questionHelper = $this->getHelper('question');
        $currentAppPhpPath = $webDir . 'app.php';

        if (strpos(file_get_contents($currentAppPhpPath), 'MAINTENANCE')) {
            $output->writeln('<error>App already disabled. Use app:enable for enable app</error>');
            return;
        }

        $allowedClients = $this->getContainer()->getParameter('vesax.maintenance.allowed_clients');

        if ($input->getOption('add-clients') && (!$input->getOption('no-interactive'))) {
            $clientQuestion = new Question('<info>Enter allowed client ip: </info>');
            do {
                $ip = $questionHelper->ask($input, $output, $clientQuestion);
                if ($ip) {
                    $allowedClients[] = $ip;
                }
            } while ($ip);

            $output->writeln('-------------------------');
        }

        $start = null;
        $end = null;

        if ($start = $input->getOption('start')) {
            $start = new \DateTime($start);
        }

        if ($end = $input->getOption('end')) {
            $end = new \DateTime($end);
        }

        if (!$input->getOption('no-interactive')) {
            $question = "App will be disabled <comment>" . ($start ? $start->format('Y-m-d h:i') : 'NOW') . "</comment>";

            if ($end) {
                $question .= " and will be enabled <comment>" . $end->format('Y-m-d h:i') . "</comment>";
            }

            $question .= "\r\nAccess allowed for clients by ip: " . implode(', ', $allowedClients);

            $continue = $questionHelper->ask($input, $output, new ConfirmationQuestion("<info>{$question}\r\nContinue? (y/n) </info>", false));

            if (!$continue) {
                return;
            }
        }

        $twig = $this->getContainer()->get('twig');

        $parameters = [
            'createdAt' => new \DateTime(),
            'allowedClients' => $allowedClients,
            'start' => $start,
            'end' => $end,
            'reason' => $input->getOption('reason')
        ];

        $output->writeln('<info>Rendering maintenance page...</info>');
        file_put_contents(
            $webDir . 'maintenance_rendered.html',
            $twig->render('VesaxMaintenanceBundle:Maintenance:maintenance_page.html.twig', $parameters)
        );

        $originalAppPhpPath = $currentAppPhpPath . '.disabled';

        $output->writeln('<info>Backup original app.php...</info>');
        rename($currentAppPhpPath, $originalAppPhpPath);
        chmod($originalAppPhpPath, 0600);

        $output->writeln('<info>Rendering app.php with maintenance...</info>');
        file_put_contents(
            $currentAppPhpPath,
            $twig->render('VesaxMaintenanceBundle:Maintenance:app_php_skeleton.php.twig', $parameters)
        );
    }

}