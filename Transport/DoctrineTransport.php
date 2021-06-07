<?php

/**
 * @package    3slab/VdmLibraryDoctrineTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryDoctrineTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryDoctrineTransportBundle\Transport;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Vdm\Bundle\LibraryBundle\Model\Message;
use Vdm\Bundle\LibraryDoctrineTransportBundle\Exception\ReceiverNotSupportedException;
use Vdm\Bundle\LibraryDoctrineTransportBundle\Stamp\VdmDoctrineStamp;

class DoctrineTransport implements TransportInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var DoctrineSender
     */
    protected $sender;

    /**
     * @param DoctrineSender $sender
     * @param LoggerInterface|null $vdmLogger
     */
    public function __construct(DoctrineSender $sender, LoggerInterface $vdmLogger = null)
    {
        $this->sender = $sender;
        $this->logger = $vdmLogger ?? new NullLogger();
    }

    /**
     * @return iterable
     * @throws ReceiverNotSupportedException
     */
    public function get(): iterable
    {
        throw new ReceiverNotSupportedException(sprintf('%s transport does not support receiving messages', __CLASS__));
    }

    /**
     * @param Envelope $envelope
     * @throws ReceiverNotSupportedException
     */
    public function ack(Envelope $envelope): void
    {
        throw new ReceiverNotSupportedException(sprintf('%s transport does not support receiving messages', __CLASS__));
    }

    /**
     * @param Envelope $envelope
     * @throws ReceiverNotSupportedException
     */
    public function reject(Envelope $envelope): void
    {
        throw new ReceiverNotSupportedException(sprintf('%s transport does not support receiving messages', __CLASS__));
    }

    /**
     * {@inheritdoc}
     */
    public function send(Envelope $envelope): Envelope
    {
        /** @var Message $message */
        $message = $envelope->getMessage();
        $this->sender->send($message);

        return $envelope->with(new VdmDoctrineStamp());
    }
}
