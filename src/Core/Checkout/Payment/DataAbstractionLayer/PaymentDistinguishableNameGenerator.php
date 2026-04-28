<?php declare(strict_types=1);

namespace Shopware\Core\Checkout\Payment\DataAbstractionLayer;

use Shopware\Core\Checkout\Payment\PaymentMethodCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Plugin\PluginEntity;

#[Package('checkout')]
class PaymentDistinguishableNameGenerator
{
    /**
     * @internal
     *
     * @param EntityRepository<PaymentMethodCollection> $paymentMethodRepository
     */
    public function __construct(private readonly EntityRepository $paymentMethodRepository)
    {
    }

    public function generateDistinguishablePaymentNames(Context $context): void
    {
        $context->scope(Context::SYSTEM_SCOPE, function (Context $context): void {
            $payments = $this->getInstalledPayments($context);

            $upsertablePayments = $this->generateDistinguishableNamesPayload($payments);
            if ($upsertablePayments === []) {
                return;
            }

            $this->paymentMethodRepository->upsert($upsertablePayments, $context);
        });
    }

    private function getInstalledPayments(Context $context): PaymentMethodCollection
    {
        $criteria = new Criteria();
        $criteria
            ->addAssociation('translations')
            ->addAssociation('plugin.translations');

        return $this->paymentMethodRepository->search($criteria, $context)->getEntities();
    }

    /**
     * @return array<array{id: string, distinguishableName: array<string, string>}>
     */
    private function generateDistinguishableNamesPayload(PaymentMethodCollection $payments): array
    {
        $upsertablePayments = [];
        foreach ($payments as $payment) {
            $plugin = $payment->getPlugin();
            if ($plugin === null || $payment->getTranslations() === null) {
                continue;
            }

            $distinguishableNames = [];
            foreach ($payment->getTranslations() as $translation) {
                $languageId = $translation->getLanguageId();

                $distinguishableNames[$languageId] = $this->generatePaymentName(
                    $plugin,
                    $languageId,
                    $translation->getName() ?? $payment->getTranslation('name'),
                );
            }

            $distinguishableNames = array_filter($distinguishableNames);
            if ($distinguishableNames === []) {
                continue;
            }

            $upsertablePayments[] = [
                'id' => $payment->getId(),
                'distinguishableName' => $distinguishableNames,
            ];
        }

        return $upsertablePayments;
    }

    private function generatePaymentName(
        PluginEntity $entity,
        string $languageId,
        string $paymentName,
    ): ?string {
        $label = $entity->getTranslations()?->filterByProperty('languageId', $languageId)->first()?->getLabel()
            ?? $entity->getTranslation('label');

        if (!\is_string($label)) {
            return null;
        }

        return \sprintf(
            '%s | %s',
            $paymentName,
            $label
        );
    }
}
