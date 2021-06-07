<?php

/**
 * @package    3slab/VdmLibraryDoctrineTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryDoctrineTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryDoctrineTransportBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Vdm\Bundle\LibraryDoctrineTransportBundle\Transport\DoctrineOdmTransportFactory;
use Vdm\Bundle\LibraryDoctrineTransportBundle\Transport\DoctrineOrmTransportFactory;

/**
 * Class DoctrineManagerCompilerPass
 * @package Vdm\Bundle\LibraryDoctrineTransportBundle\DependencyInjection\Compiler
 */
class DoctrineManagerCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    public function process(ContainerBuilder $container)
    {
        if (
            !$container->hasDefinition(DoctrineOdmTransportFactory::class)
            || !$container->hasDefinition(DoctrineOrmTransportFactory::class)
        ) {
            return;
        }

        $definitionOrm = $container->getDefinition(DoctrineOrmTransportFactory::class);
        if ($container->hasDefinition('doctrine')) {
            $definitionOrm->addMethodCall('setDoctrine', [new Reference('doctrine')]);
        }

        $definitionOdm = $container->getDefinition(DoctrineOdmTransportFactory::class);
        if ($container->hasDefinition('doctrine_mongodb')) {
            $definitionOdm->addMethodCall('setDoctrine', [new Reference('doctrine_mongodb')]);
        }
    }
}
