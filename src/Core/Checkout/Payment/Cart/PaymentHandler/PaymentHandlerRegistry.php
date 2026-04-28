<?php declare(strict_types=1);

namespace Shopware\Core\Checkout\Payment\Cart\PaymentHandler;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Contracts\Service\ServiceProviderInterface;

#[Package('checkout')]
class PaymentHandlerRegistry
{
    /**
     * @var array<string, AbstractPaymentHandler>
     */
    private array $handlers = [];

    /**
     * @internal
     *
     * @param ServiceProviderInterface<AbstractPaymentHandler> $paymentHandlers
     */
    public function __construct(
        ServiceProviderInterface $paymentHandlers,
        private readonly Connection $connection
    ) {
        foreach (\array_keys($paymentHandlers->getProvidedServices()) as $serviceId) {
            $handler = $paymentHandlers->get($serviceId);
            $this->handlers[(string) $serviceId] = $handler;
        }
    }

    public function getPaymentMethodHandler(string $paymentMethodId): ?AbstractPaymentHandler
    {
        $handlerIdentifier = $this->connection->createQueryBuilder()
            ->select('payment_method.handler_identifier')
            ->from('payment_method')
            ->andWhere('payment_method.id = :paymentMethodId')
            ->setParameter('paymentMethodId', Uuid::fromHexToBytes($paymentMethodId))
            ->executeQuery()
            ->fetchOne();

        if ($handlerIdentifier === false) {
            return null;
        }

        return $this->handlers[(string) $handlerIdentifier] ?? null;
    }
}
