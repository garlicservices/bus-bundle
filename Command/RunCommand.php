<?php

namespace Garlic\Bus\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;


class RunCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('garlic:bus:run')
            ->setDescription('Up and running processors for message bus');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return $this->getApplication()
            ->find('enqueue:consume')
            ->run(
                new ArrayInput(['command' => 'enqueue:consume']),
                $output
            );
    }
}