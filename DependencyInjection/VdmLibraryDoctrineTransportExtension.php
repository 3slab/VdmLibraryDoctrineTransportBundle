<?php

/**
 * @package    3slab/VdmLibraryDoctrineTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryDoctrineTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryDoctrineTransportBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Vdm\Bundle\LibraryDoctrineTransportBundle\Executor\AbstractDoctrineExecutor;

/**
 * Class VdmLibraryExtension
 *
 * @package Vdm\Bundle\LibraryDoctrineTransportBundle\DependencyInjection
 */
class VdmLibraryDoctrineTransportExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $container->registerForAutoconfiguration(AbstractDoctrineExecutor::class)
            ->addTag('vdm_library.doctrine_executor')
        ;

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.yaml');
    }

    /**
     * {@inheritDoc}
     */
    public function getAlias()
    {
        return 'vdm_library_doctrine_transport';
    }
}
