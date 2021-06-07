<?php

/**
 * @package    3slab/VdmLibraryDoctrineTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryDoctrineTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryDoctrineTransportBundle\Executor;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class DoctrineExecutorRegistry
 * @package Vdm\Bundle\LibraryDoctrineTransportBundle\Executor
 */
class DoctrineExecutorRegistry
{
    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * @var AbstractDoctrineExecutor[] $executors
     */
    private $executors;

    /**
     * @var AbstractDoctrineExecutor|null
     */
    private $defaultExecutor;

    /**
     * DoctrineExecutorRegistry constructor.
     * @param LoggerInterface|null $vdmLogger
     */
    public function __construct(LoggerInterface $vdmLogger = null)
    {
        $this->executors = [];
        $this->logger = $vdmLogger ?? new NullLogger();
    }

    /**
     * @param AbstractDoctrineExecutor $executor
     * @param string $id
     */
    public function addExecutor(AbstractDoctrineExecutor $executor, string $id): void
    {
        $this->executors[$id] = $executor;
        if (get_class($executor) === DefaultDoctrineExecutor::class) {
            $this->defaultExecutor = $executor;
        }
    }

    /**
     * @param string $id
     * @return AbstractDoctrineExecutor
     */
    public function get(string $id): AbstractDoctrineExecutor
    {
        if (!array_key_exists($id, $this->executors)) {
            throw new \RuntimeException(sprintf('No executor found with id "%s"', $id));
        }

        return $this->executors[$id];
    }

    /**
     * @return AbstractDoctrineExecutor
     */
    public function getDefault(): AbstractDoctrineExecutor
    {
        if (!$this->defaultExecutor) {
            throw new \RuntimeException('No executor instance of DefaultDoctrineExecutor found');
        }

        return $this->defaultExecutor;
    }
}
