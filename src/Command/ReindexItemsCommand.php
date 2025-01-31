<?php
declare(strict_types=1);

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace Pimcore\Bundle\GenericDataIndexBundle\Command;

use Exception;
use Pimcore\Bundle\GenericDataIndexBundle\Exception\CommandAlreadyRunningException;
use Pimcore\Bundle\GenericDataIndexBundle\Service\SearchIndex\ReindexServiceInterface;
use Pimcore\Console\AbstractCommand;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
final class ReindexItemsCommand extends AbstractCommand
{
    use LockableTrait;

    public function __construct(
        private readonly ReindexServiceInterface $reindexService,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setName('generic-data-index:reindex')
            ->setDescription(
                'Triggers native reindexing of existing indices.'
            );
    }

    /**
     * @throws CommandAlreadyRunningException
     *
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->lock()) {
            throw new CommandAlreadyRunningException(
                'The command is already running in another process.'
            );
        }

        try {
            $output->writeln(
                '<info>Reindex all indices</info>',
                OutputInterface::VERBOSITY_NORMAL
            );

            $this->reindexService->reindexAllIndices();
        } catch (Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        } finally {
            $this->release();
        }

        $output->writeln(
            '<info>Finished</info>',
            OutputInterface::VERBOSITY_NORMAL
        );

        return self::SUCCESS;
    }
}
