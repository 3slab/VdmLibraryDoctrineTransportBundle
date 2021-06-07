<?php

/**
 * @package    3slab/VdmLibraryDoctrineTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryDoctrineTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryDoctrineTransportBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Vdm\Bundle\LibraryDoctrineTransportBundle\DependencyInjection\Compiler\DoctrineExecutorCompilerPass;
use Vdm\Bundle\LibraryDoctrineTransportBundle\DependencyInjection\Compiler\DoctrineManagerCompilerPass;

class VdmLibraryDoctrineTransportBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new DoctrineExecutorCompilerPass());
        $container->addCompilerPass(new DoctrineManagerCompilerPass());
    }
}
