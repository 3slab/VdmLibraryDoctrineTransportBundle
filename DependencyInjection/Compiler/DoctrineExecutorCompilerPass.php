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
use Vdm\Bundle\LibraryDoctrineTransportBundle\Executor\DoctrineExecutorRegistry;

/**
 * Class DoctrineExecutorCompilerPass
 * @package Vdm\Bundle\LibraryDoctrineTransportBundle\DependencyInjection\Compiler
 */
class DoctrineExecutorCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(DoctrineExecutorRegistry::class)) {
            return;
        }

        $definition = $container->getDefinition(DoctrineExecutorRegistry::class);
        $taggedServices = $container->findTaggedServiceIds('vdm_library.doctrine_executor');
        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addExecutor', [new Reference($id), $id]);
        }
    }
}
