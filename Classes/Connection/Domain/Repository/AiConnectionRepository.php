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

namespace Madj2k\AiAssistant\Connection\Domain\Repository;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Class AiConnectionRepository
 *
 * Extbase repository for AI connection records.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 *
 * @extends \TYPO3\CMS\Extbase\Persistence\Repository<\Madj2k\AiAssistant\Connection\Domain\Model\AiConnection>
 */
class AiConnectionRepository extends Repository
{
    /**
     * Disables the storage PID restriction because connection records are shared configuration.
     *
     * @return void
     */
    public function initializeObject(): void
    {
        /** @var \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings $querySettings */
        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($querySettings);
    }


    /**
     * Returns all AI connections ordered by title.
     *
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface<\Madj2k\AiAssistant\Connection\Domain\Model\AiConnection> AI connections.
     */
    public function findAll(): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->setOrderings([
            'title' => QueryInterface::ORDER_ASCENDING,
            'uid' => QueryInterface::ORDER_ASCENDING,
        ]);

        return $query->execute();
    }
}
