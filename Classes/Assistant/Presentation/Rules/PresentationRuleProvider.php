<?php
declare(strict_types=1);

/**
 * Class UIComponentProvider
 *
 * Resolves TYPO3 UI component records for the effective pipeline step.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

namespace Madj2k\AiAssistant\Assistant\Presentation\Rules;

use Madj2k\AiAssistant\Assistant\Domain\Repository\PresentationRuleRepository;
use Madj2k\AiCore\Assistant\Presentation\Rules\PresentationRule;
use Madj2k\AiCore\Assistant\Presentation\Rules\PresentationRuleProviderInterface;
use Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface;
use Madj2k\AiCore\Assistant\Context\Context;
use Doctrine\DBAL\ParameterType;
use TYPO3\CMS\Core\Database\ConnectionPool;

/**
 * Class PresentationRuleProvider
 **
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
final readonly class PresentationRuleProvider implements PresentationRuleProviderInterface
{
    /**
     * @param \Madj2k\AiAssistant\Assistant\Domain\Repository\PresentationRuleRepository $repository
     * @param \TYPO3\CMS\Core\Database\ConnectionPool $connectionPool
     */
    public function __construct(
        private PresentationRuleRepository $repository,
        private ?ConnectionPool $connectionPool = null,
    )
    {
    }

    /**
     * @param string $identifier
     * @return \Madj2k\AiCore\Assistant\Presentation\Rules\PresentationRule|null
     */
    public function resolve(string $identifier): ?PresentationRule
    {
        $value = trim($identifier);
        $record = ctype_digit($value)
            ? $this->repository->findByUid((int)$value)
            : $this->repository->findOneBy(['identifier' => $value]);
        return $record === null ? null : new PresentationRule($record->getIdentifier(), $record->getContent(), $record->getTitle());
    }

    /**
     * @param \Madj2k\AiCore\Assistant\Context\Context $context
     * @param \Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface $step
     * @return array|\Madj2k\AiCore\Assistant\Presentation\Rules\PresentationRule[]
     */
    public function getRules(Context $context, PipelineStepConfigurationInterface $step): array
    {
        $plan = $context->getPresentationRulePlan();
        if ($plan->mode !== 'all') {
            return array_values(array_filter(
                $this->getAll(),
                static fn (PresentationRule $rule): bool => $plan->allows($rule->identifier),
            ));
        }

        $profileIdentifiers = $this->readProfileIdentifiers($context->getAssistant()->getUid());
        $stepIdentifiers = $step->getPresentationRuleIdentifiers();
        $mode = method_exists($step, 'getPresentationRulesMode')
            ? $step->getPresentationRulesMode()
            : 'inherit';
        $identifiers = match ($mode) {
            'replace' => $stepIdentifiers,
            'extend' => array_values(array_unique([...$profileIdentifiers, ...$stepIdentifiers])),
            default => $profileIdentifiers,
        };

        $rules = [];
        foreach ($identifiers as $identifier) {
            $rule = $this->resolve($identifier);
            if ($rule !== null && $context->getPresentationRulePlan()->allows($rule->identifier)) {
                $rules[] = $rule;
            }
        }
        return $rules;
    }

    /** @return array<int,string> */
    private function readProfileIdentifiers(int $profileUid): array
    {
        if ($profileUid <= 0 || $this->connectionPool === null) {
            return [];
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_aiassistant_assistant_profile');
        $value = $queryBuilder
            ->select('presentation_rules')
            ->from('tx_aiassistant_assistant_profile')
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($profileUid, ParameterType::INTEGER)))
            ->executeQuery()
            ->fetchOne();

        return array_values(array_filter(array_map('trim', explode(',', (string)$value))));
    }


    /**
     * @return array
     */
    public function getAll(): array
    {
        $rules = [];
        foreach ($this->repository->findAll() as $record) {
            $rules[] = new PresentationRule($record->getIdentifier(), $record->getContent(), $record->getTitle());
        }
        return $rules;
    }
}
