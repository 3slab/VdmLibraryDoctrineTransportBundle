<?php

/**
 * @package    3slab/VdmLibraryDoctrineTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryDoctrineTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryDoctrineTransportBundle\Transport;

use Vdm\Bundle\LibraryDoctrineTransportBundle\Executor\AbstractDoctrineExecutor;
use Vdm\Bundle\LibraryBundle\Model\Message;

class DoctrineSender
{
    /**
     * @var AbstractDoctrineExecutor
     */
    protected $executor;

    /**
     * @param AbstractDoctrineExecutor $executor
     */
    public function __construct(AbstractDoctrineExecutor $executor)
    {
        $this->executor = $executor;
    }

    /**
     * Sends the message to the executory
     *
     * @param  Message $message
     *
     * @return void
     */
    public function send(Message $message): void
    {
        $this->executor->execute($message);
    }
}
