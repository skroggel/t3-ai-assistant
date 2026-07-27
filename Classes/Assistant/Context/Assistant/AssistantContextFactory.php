<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Madj2k\AiAssistant\Assistant\Context\Assistant;

use Madj2k\AiAssistant\Assistant\Domain\Model\AssistantProfile;

/**
 * Class AssistantContextFactory
 *
 * Converts a TCA-managed assistant profile model into a runtime context object.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final class AssistantContextFactory
{
    /**
     * Creates the assistant context from an Extbase model.
     *
     * @param \Madj2k\AiAssistant\Assistant\Domain\Model\AssistantProfile $assistantProfile Assistant profile.
     * @return \Madj2k\AiAssistant\Assistant\Context\Assistant\AssistantContext
     */
    public function create(AssistantProfile $assistantProfile): AssistantContext
    {
        return new AssistantContext(
            uid: (int)$assistantProfile->getUid(),
            title: trim($assistantProfile->getTitle()),
            assistantLabel: trim($assistantProfile->getAssistantLabel()),
            aiConnection: $assistantProfile->getAiConnection(),
            vectorStoreConnection: $assistantProfile->getVectorStoreConnection(),
            collection: trim($assistantProfile->getCollection()),
            identityPrompt: trim($assistantProfile->getIdentityPrompt()),
            behaviorRules: trim($assistantProfile->getBehaviorRules()),
            retrievalRules: trim($assistantProfile->getRetrievalRules()),
            outputRules: trim($assistantProfile->getOutputRules()),
        );
    }
}
