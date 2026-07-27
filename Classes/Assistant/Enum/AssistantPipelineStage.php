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


namespace Madj2k\AiAssistant\Assistant\Enum;

/**
 * Enum AssistantPipelineStage
 *
 * Describes the semantic position of a step in the assistant pipeline.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
enum AssistantPipelineStage: string
{
    case PreRetrieval = 'pre_retrieval';
    case Retrieval = 'retrieval';
    case PostRetrieval = 'post_retrieval';
    case PreAnswer = 'pre_answer';
    case PostAnswer = 'post_answer';
}
