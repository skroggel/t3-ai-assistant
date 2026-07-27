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

namespace Madj2k\AiAssistant\Assistant\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity as CoreAbstractEntity;

/**
 * Class AbstractRecord
 *
 * Provides shared record state and a small legacy bridge for older call sites.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
abstract class AbstractEntity extends CoreAbstractEntity
{

    /**
     * Hidden flag.
     *
     * @var bool
     */
    protected bool $hidden = false;


    /**
     * Creation timestamp.
     *
     * @var int
     */
    protected int $crdate = 0;


    /**
     * Modification timestamp.
     *
     * @var int
     */
    protected int $tstamp = 0;


    /**
     * Manual sorting value.
     *
     * @var int
     */
    protected int $sorting = 0;


    /**
     * Returns the hidden flag.
     *
     * @return bool
     */
    public function isHidden(): bool
    {
        return $this->hidden;
    }


    /**
     * Sets the hidden flag.
     *
     * @param bool $hidden Hidden flag.
     * @return void
     */
    public function setHidden(bool $hidden): void
    {
        $this->hidden = $hidden;
    }


    /**
     * Returns the creation timestamp.
     *
     * @return int
     */
    public function getCrdate(): int
    {
        return $this->crdate;
    }


    /**
     * Sets the creation timestamp.
     *
     * @param int $crdate Creation timestamp.
     * @return void
     */
    public function setCrdate(int $crdate): void
    {
        $this->crdate = $crdate;
    }


    /**
     * Returns the modification timestamp.
     *
     * @return int
     */
    public function getTstamp(): int
    {
        return $this->tstamp;
    }


    /**
     * Sets the modification timestamp.
     *
     * @param int $tstamp Modification timestamp.
     * @return void
     */
    public function setTstamp(int $tstamp): void
    {
        $this->tstamp = $tstamp;
    }


    /**
     * Returns the sorting value.
     *
     * @return int
     */
    public function getSorting(): int
    {
        return $this->sorting;
    }


    /**
     * Sets the sorting value.
     *
     * @param int $sorting Sorting value.
     * @return void
     */
    public function setSorting(int $sorting): void
    {
        $this->sorting = $sorting;
    }

}
