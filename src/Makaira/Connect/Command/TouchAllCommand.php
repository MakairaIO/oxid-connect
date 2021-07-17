<?php

/**
 * This file is part of a Makaira GmbH project
 * It is not Open Source and may not be redistributed.
 * For contact information please visit http://www.marmalade.de
 * Version:    1.0
 * Author:     Martin Schnabel <ms@marmalade.group>
 * Author URI: https://www.makaira.io/
 */

namespace Makaira\Connect\Command;

use OxidEsales\Facts\Facts;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Makaira\Connect\Repository;
use Makaira\Connect\Connect;

use function class_exists;

class TouchAllCommand extends Command
{
    protected function configure()
    {
        $this->setName('makaira:touch-all')
            ->setDescription('Touch all')
            ->setHelp('Trigger update of everything');

        $isEnterpriseEdition = false;

        // Do not add the '--shop-id' option for OXID EE 6.2, because OXID add it without any existence checks.
        $hasCompatibilityModule = class_exists('\\Makaira\\ConnectCompat\\ContainerCompat');
        if (!$hasCompatibilityModule) {
            $facts               = new Facts();
            $isEnterpriseEdition = $facts->isEnterprise();
        }

        if ($hasCompatibilityModule || !$isEnterpriseEdition) {
            $this->addOption(
                'shop-id',
                null,
                InputOption::VALUE_REQUIRED,
                'Touch objects only for the given shop.'
            );
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Touch all');
        $container = Connect::getContainerFactory()->getContainer();

        $repo = $container->get(Repository::class);
        $repo->touchAll($input->getOption('shop-id'));

        $output->writeln('Done.');
    }
}
