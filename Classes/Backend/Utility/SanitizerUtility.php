<?php
declare(strict_types=1);

namespace Madj2k\AiAssistant\Backend\Utility;

/**
 * Class SanitizerUtility
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final class SanitizerUtility
{

    /**
     * cleanString sanitizes a string by normalizing line endings and limiting length.
     *
     * @param string $value The string to clean.
     * @param int $maxLen The maximum length allowed for the cleaned string.
     * @return string The cleaned string.
     */
    public static function cleanString(string $value, int $maxLen = 2000): string
    {
        $value = trim($value);

        // Normalize CRLF -> LF
        $value = str_replace(["\r\n", "\r"], "\n", $value);

        // remove Unicode-Control-Chars (außer \n und \t)
        $value = preg_replace('/[^\P{C}\n\t]/u', '', $value) ?? '';

        // length restriction
        if (mb_strlen($value, 'UTF-8') > $maxLen) {
            $value = mb_substr($value, 0, $maxLen, 'UTF-8');
        }

        return $value;
    }
}
