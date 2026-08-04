<?php
declare(strict_types=1);

namespace Madj2k\AiAssistant\Exception;

/** @deprecated Use \\Madj2k\AiCore\Exception\ApiException directly. */
class_alias(\Madj2k\AiCore\Exception\ApiException::class, __NAMESPACE__ . '\\ApiException');
