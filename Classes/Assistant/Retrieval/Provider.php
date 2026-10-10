<?php
declare(strict_types=1);

namespace Madj2k\AiAssistant\Assistant\Retrieval;

use Madj2k\AiAssistant\Assistant\Domain\Repository\AssistantProfileRepository;
use Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface;
use Madj2k\AiCore\Assistant\Context\Context;
use Madj2k\AiCore\Assistant\Context\Retrieval\RetrievalTarget;
use Madj2k\AiCore\Assistant\Context\Retrieval\RetrievalTargetProviderInterface;
use Madj2k\AiCore\Assistant\Enum\AssistantPipelineProcessorType;

/**
 * Provides configured retrieval targets to the selector processor.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
final readonly class Provider implements RetrievalTargetProviderInterface
{
    /**
     * Constructor.
     *
     * @param \Madj2k\AiAssistant\Assistant\Domain\Repository\AssistantProfileRepository $profiles Assistant profile repository.
     */
    public function __construct(private AssistantProfileRepository $profiles) {}

    /**
     * @param \Madj2k\AiCore\Assistant\Context\Context $context Assistant context.
     * @param \Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface $step Selector step.
     * @return array<int,\Madj2k\AiCore\Assistant\Context\Retrieval\RetrievalTarget> Retrieval targets.
     */
    public function getTargets(Context $context, PipelineStepConfigurationInterface $step): array
    {
        $profile = $this->profiles->findByUid($context->getAssistant()->getUid());
        $targets = [];
        foreach ($profile?->getChatPipelineSteps() ?? [] as $candidate) {
            if ($candidate->getType() !== AssistantPipelineProcessorType::Retriever || !method_exists($candidate, 'getRetrievalIdentifier')) {
                continue;
            }
            $identifier = trim($candidate->getRetrievalIdentifier());
            if ($identifier !== '') {
                $targets[] = new RetrievalTarget($identifier, $candidate->getTitle());
            }
        }
        return $targets;
    }
}
