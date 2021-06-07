<?php

/**
 * @package    3slab/VdmLibraryDoctrineTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryDoctrineTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryDoctrineTransportBundle\Executor;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Vdm\Bundle\LibraryDoctrineTransportBundle\Exception\NoConnectionException;
use Vdm\Bundle\LibraryBundle\Model\Message;

class DefaultDoctrineExecutor extends AbstractDoctrineExecutor
{
    /**
     * {@inheritDoc}
     */
    public function execute(Message $message): void
    {
        if (!$this->manager) {
            throw new NoConnectionException('No connection was defined.');
        }

        $entityMetadatas = $message->getMetadatasByKey('entity');
        if (count($entityMetadatas) > 0) {
            $entityMetadata = array_shift($entityMetadatas);
            $entityClass = $entityMetadata->getValue();
        } else {
            $entityClass = $this->getDefaultEntity();
        }

        $entity = $this->serializer->denormalize($message->getPayload(), $entityClass);
        $entity = $this->matchEntity($entity);

        $this->manager->persist($entity);
        $this->manager->flush();
    }

    /**
     * Defines logic to try and fetch previously existing entity and merges it with the new one.
     *
     * @param  object $entity
     *
     * @return object|null
     */
    protected function matchEntity(object $entity): ?object
    {
        $fqcn = get_class($entity);
        $repository = $this->getRepository($fqcn);

        // We'll be using different methods according to the options passed to the transport
        if (static::SELECTION_MODE_IDENTIFER === $this->getFetchMode($fqcn)) {
            $id            = $this->getIdentifier($fqcn);
            $matchedEntity = $repository->find($id);
        } else {
            $filters       = $this->computeFilters($entity);
            $id            = json_encode($filters);
            $matchedEntity = $repository->findOneBy($filters);
        }

        if ($matchedEntity) {
            // If the entity already exist, merge it.
            $entity     = $this->merge($matchedEntity, $entity);
            $logMessage = 'Updating entity of class {fqcn} with identity {id}.';
        } else {
            // If entity was not found, then we just have to create it.
            $logMessage = 'Creating entity of class {fqcn} with identity {id}.';
        }

        // Log what happened, and return entity
        $this->logger->info($logMessage, [
            'fqcn' => $fqcn,
            'id'   => $id,
        ]);

        return $entity;
    }

    /**
     * Creates filter array with values from the entity.
     *
     * @param  object $entity
     *
     * @return array
     */
    protected function computeFilters(object $entity): array
    {
        $fqcn = get_class($entity);

        $accessor = PropertyAccess::createPropertyAccessorBuilder()
            ->enableMagicCall()
            ->getPropertyAccessor()
        ;

        $filterValues = [];

        foreach ($this->filters[$fqcn] as $propety => $getter) {
            $filterValues[] = $accessor->getValue($entity, $getter);
        }

        $filter = array_combine(array_keys($this->filters[$fqcn]), $filterValues);

        return $filter;
    }

    /**
     * Merges older entity with values from the new one.
     *
     * @param  object $previousEntity
     * @param  object $newerEntity
     *
     * @return object
     */
    public function merge(object $previousEntity, object $newerEntity): object
    {
        $fqcn          = get_class($previousEntity);
        $metadata      = $this->manager->getClassMetadata($fqcn);
        $mapping       = $metadata->getFieldNames();
        $identifierKey = array_search($metadata->getIdentifier(), $mapping, true);

        // Remove identifer because it usually doesn't have a setter.
        unset($mapping[$identifierKey]);

        $accessor       = PropertyAccess::createPropertyAccessor();

        foreach ($mapping as $property) {
            $newValue = $accessor->getValue($newerEntity, $property);
            $accessor->setValue($previousEntity, $property, $newValue);
        }

        return $previousEntity;
    }
}
