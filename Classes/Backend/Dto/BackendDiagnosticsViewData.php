<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, version 3.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Madj2k\AiAssistant\Backend\Dto;

/**
 * Class BackendDiagnosticsViewData
 *
 * Contains diagnostics and log view data for the backend module.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
final class BackendDiagnosticsViewData
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
