<?php

namespace Vesax\MaintenanceBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class EnableSiteCommand
 *
 * @author Artur Vesker
 */
class EnableSiteCommand extends ContainerAwareCommand
{

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('app:enable');
        $this->setDescription('Enable app');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $webDir = $this->getContainer()->getParameter('kernel.root_dir') . '/../web/';

        $currentAppPhpPath = $webDir . 'app.php';
        $originalAppPath = $webDir . 'app_disabled.php';

        $output->writeln('<info>Checking current status...</info>');

        if (!is_readable($originalAppPath)) {
            $output->writeln('<error>App already enabled. Use app:disable for disable app</error>');
            return;
        }

        $output->writeln('<info>Removing app.php with maintenance...</info>');
        unlink($currentAppPhpPath);

        $output->writeln('<info>Returning original app.php...</info>');
        rename($originalAppPath, $currentAppPhpPath);
        chmod($currentAppPhpPath, 0755);

        $output->writeln('<info>Removing rendered maintenance content...</info>');
        unlink($webDir . 'maintenance_rendered.html');
    }

}