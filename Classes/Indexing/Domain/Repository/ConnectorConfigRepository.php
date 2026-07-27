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

namespace Madj2k\AiAssistant\Indexing\Domain\Repository;

use Madj2k\AiAssistant\Indexing\Domain\Model\ConnectorConfig;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Class ConnectorConfigRepository
 *
 * Repository for external connector configuration records.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ConnectorConfigRepository extends AbstractRepository
{

    /**
     * Finds enabled connectors by type.
     *
     * @param string $type Connector type.
     * @return array<int,ConnectorConfig> Connector configuration records.
     */
    public function findByType(string $type): array
    {
        $query = $this->createQuery();
        $query->matching(
            $query->equals('type', $type),
        );
        $query->setOrderings(['sorting' => QueryInterface::ORDER_ASCENDING, 'uid' => QueryInterface::ORDER_ASCENDING]);

        return $query->execute();
    }


}
