<?php

/**
 * @package    3slab/VdmLibraryDoctrineTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryDoctrineTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryDoctrineTransportBundle\Transport;

class DoctrineOrmTransportFactory extends AbstractDoctrineTransportFactory
{
    protected const DSN_PROTOCOL_DOCTRINE = 'vdm+doctrine_orm://';

    /**
     * @return string
     */
    protected function getDsnProtocolDoctrine(): string
    {
        return self::DSN_PROTOCOL_DOCTRINE;
    }
}
