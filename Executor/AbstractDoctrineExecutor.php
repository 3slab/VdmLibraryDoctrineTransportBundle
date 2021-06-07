<?php

/**
 * @package    3slab/VdmLibraryDoctrineTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryDoctrineTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryDoctrineTransportBundle\Executor;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Vdm\Bundle\LibraryBundle\Model\Message;

abstract class AbstractDoctrineExecutor
{
    public const SELECTION_MODE_IDENTIFER = 0b000;
    public const SELECTION_MODE_FILTER    = 0b001;

    /**
     * @var array
     */
    protected $fetchMode = [];

    /**
     * @var array
     */
    protected $identifiers = [];
    /**
     * @var array
     */
    protected $filters = [];

    /**
     * @var ObjectRepository[]
     */
    protected $repositories = [];

    /**
     * @var string
     */
    protected $defaultEntity;

    /**
     * @var ObjectManager $manager
     */
    protected $manager;

    /**
     * @var LoggerInterface $logger
     */
    protected $logger;

    /**
     * @var SerializerInterface $serializer
     */
    protected $serializer;

    /**
     * @param Message $message
     */
    abstract public function execute(Message $message): void;

    /**
     * @return ObjectManager
     */
    public function getManager(): ObjectManager
    {
        return $this->manager;
    }

    /**
     * @param ObjectManager $manager
     * @return AbstractDoctrineExecutor
     */
    public function setManager(ObjectManager $manager): self
    {
        $this->manager = $manager;

        return $this;
    }

    /**
     * @param string $key
     * @param int    $fetchMode
     *
     * @return self
     */
    public function setFetchMode(string $key, int $fetchMode): self
    {
        $this->fetchMode[$key] = $fetchMode;

        return $this;
    }

    /**
     * @param int $fetchMode
     *
     * @return int
     */
    public function getFetchMode(string $key): int
    {
        return $this->fetchMode[$key];
    }

    /**
     * @param string $key
     * @param string $identifier
     *
     * @return self
     */
    public function setIdentifier(string $key, string $identifier): self
    {
        $this->identifiers[$key] = $identifier;

        return $this;
    }

    /**
     * @param string $key
     *
     * @return self
     */
    public function getIdentifier(string $key): string
    {
        return $this->identifiers[$key];
    }

    /**
     * @param string $key
     * @param array  $filters
     *
     * @return self
     */
    public function setFilters(string $key, array $filters): self
    {
        $this->filters[$key] = $filters;

        return $this;
    }

    /**
     * @param string $key
     *
     * @return array
     */
    public function getFilters(string $key): array
    {
        return $this->filters[$key];
    }

    /**
     * @param string $className
     * @return $this
     */
    public function setDefaultEntity(string $className): self
    {
        $this->defaultEntity = $className;

        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultEntity(): string
    {
        return $this->defaultEntity;
    }

    /**
     * @param string           $key
     * @param ObjectRepository $repository
     *
     * @return self
     */
    public function addRepository(string $key, ObjectRepository $repository)
    {
        $this->repositories[$key] = $repository;

        return $this;
    }

    /**
     * @param string $key
     *
     * @return self
     */
    public function getRepository(string $key): ObjectRepository
    {
        return $this->repositories[$key];
    }

    /**
     * @param LoggerInterface $logger $logger
     *
     * @return self
     */
    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @param SerializerInterface $serializer $serializer
     *
     * @return self
     */
    public function setSerializer(SerializerInterface $serializer): self
    {
        $this->serializer = $serializer;

        return $this;
    }
}
