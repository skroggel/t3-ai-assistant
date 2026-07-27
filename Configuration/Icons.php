<?php
declare(strict_types=1);
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

$iconList = [];
foreach (
[
    'aiassistant-plugin-chat' => 'Extension.svg',
    'aiassistant-module' => 'Extension.svg',
] as $identifier => $path) {
    $iconList[$identifier] = [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:ai_assistant/Resources/Public/Icons/' . $path,
    ];
}

return $iconList;
