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

namespace Madj2k\AiAssistant\Backend\Export;

use Madj2k\AiAssistant\Assistant\Domain\Repository\AssistantProfileRepository;
use Madj2k\AiAssistant\Assistant\UIComponents\Provider as UiComponentProvider;
use Madj2k\AiCore\Assistant\Export\PipelineMarkdownExporter;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class PipelineMarkdownExportProvider
 *
 * Provides assistant profile choices and Markdown pipeline exports.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
final readonly class PipelineMarkdownExportProvider
{
    public function __construct(
        private AssistantProfileRepository $profileRepository,
        private PipelineMarkdownExporter $exporter,
        private UiComponentProvider $uiComponentProvider,
    ) {
    }

    /**
     * @param ServerRequestInterface $request Backend request.
     * @return array<string,mixed> View data.
     */
    public function getViewData(ServerRequestInterface $request): array
    {
        $profiles = [];
        $exports = [];
        foreach ($this->profileRepository->findAll() as $profile) {
            $profiles[] = ['uid' => $profile->getUid(), 'title' => $profile->getTitle()];
            $exports[(string)$profile->getUid()] = $this->exportProfile($profile);
        }

        $query = $request->getQueryParams();
        $selectedUid = (int)(
            $query['assistantProfile']
            ?? $query['tx_aiassistant']['assistantProfile']
            ?? $query['tx_aiassistant_web_aiassistant']['assistantProfile']
            ?? 0
        );
        $profile = $selectedUid > 0 ? $this->profileRepository->findByUid($selectedUid) : null;

        return [
            'pipelineExportProfiles' => $profiles,
            'pipelineExportProfileUid' => $selectedUid,
            'pipelineExportMarkdown' => $profile === null ? '' : $this->exportProfile($profile),
            'pipelineExportEntriesJson' => json_encode($exports, JSON_THROW_ON_ERROR | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
        ];
    }

    /**
     * Exports one assistant profile by UID.
     *
     * @param int $profileUid Assistant profile UID.
     * @return string|null Markdown export or null when the profile is unknown.
     */
    public function export(int $profileUid): ?string
    {
        $profile = $profileUid > 0 ? $this->profileRepository->findByUid($profileUid) : null;

        return $profile === null ? null : $this->exportProfile($profile);
    }

    private function exportProfile(object $profile): string
    {
        return $this->exporter->export(
            $profile,
            $this->uiComponentProvider->getDefinitionsForProfile((int)$profile->getUid()),
        );
    }
}
