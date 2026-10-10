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
 */

namespace Madj2k\AiAssistant\Connection\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class VectorFilterCondition
 *
 * TYPO3 persistence model for one vector retrieval filter condition.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
class VectorFilterCondition extends AbstractEntity
{
    protected string $field = '';
    protected string $operator = 'equals';
    protected string $value = '';

    /** @return string Payload field path. */
    public function getField(): string
    {
        return trim($this->field);
    }

    /** @param string $field Payload field path. @return void */
    public function setField(string $field): void
    {
        $this->field = $field;
    }

    /** @return string Filter operator. */
    public function getOperator(): string
    {
        return $this->operator;
    }

    /** @param string $operator Filter operator. @return void */
    public function setOperator(string $operator): void
    {
        $this->operator = $operator;
    }

    /** @return string Filter value. */
    public function getValue(): string
    {
        return $this->value;
    }

    /** @param string $value Filter value. @return void */
    public function setValue(string $value): void
    {
        $this->value = $value;
    }
}
