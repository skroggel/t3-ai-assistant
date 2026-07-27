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


namespace Madj2k\AiAssistant\Assistant\Context;

use Madj2k\AiAssistant\Assistant\DTO\AssistantRequest;
use Madj2k\AiAssistant\Assistant\Context\Assistant\AssistantContextFactory;


/**
 * Class ContextFactory
 *
 * Creates the state object for one pipeline run.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final readonly class ContextFactory
{

    /**
     * Constructor.
     *
     * @param \Madj2k\AiAssistant\Assistant\Context\Assistant\AssistantContextFactory $assistantContextFactory Assistant context factory.
     */
    public function __construct(
        private AssistantContextFactory $assistantContextFactory,
    ) {
    }


    /**
     * Creates the context.
     *
     * @param \Madj2k\AiAssistant\Assistant\DTO\AssistantRequest $chatTurnRequest
     * @param array<int,array<string,string>> $history Visible history messages.
     * @return \Madj2k\AiAssistant\Assistant\Context\Context
     */
    public function create(
        AssistantRequest $chatTurnRequest,
        array            $history
    ): Context {
        return new Context(
            $this->assistantContextFactory->create($chatTurnRequest->assistantProfile),
            new Request\Request(
                $chatTurnRequest->query,
                $chatTurnRequest->chatIdentifier,
                $chatTurnRequest->serverRequest,
                $chatTurnRequest->runtimeSettings
            ),
            new Request\History($history),
            new Retrieval\RetrievalResult(),
            new Answer\AnswerState(),
            new Trace\ProcessingTrace()
        );
    }
}
