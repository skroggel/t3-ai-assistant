<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 extension ai_assistant.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Madj2k\AiAssistant\Backend\Dto;

/**
 * Class BackendPreviewViewData
 *
 * Contains legacy preview view data for the backend module.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final class BackendPreviewViewData
{
    /**
     * View data.
     *
     * @var array<string, mixed>
     */
    protected array $data = [];


    /**
     * Constructor.
     *
     * @param array<string, mixed> $data View data.
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }


    /**
     * Returns the view data as array.
     *
     * @return array<string, mixed> View data.
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
