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

namespace Madj2k\AiAssistant\Backend\Form;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\FormProtection\BackendFormProtection;
use TYPO3\CMS\Core\FormProtection\FormProtectionFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class BackendFormTokenService
 *
 * Centralizes token generation and validation for all backend module forms.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
final class BackendFormTokenService
{
    private const string TOKEN_TYPE = 'core';
    private const string TOKEN_ACTION = 'moduleCall';
    private const string TOKEN_INSTANCE = 'tx_aiassistant';


    /**
     * Creates the TYPO3 form protection object from the current backend request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request Backend request.
     * @return \TYPO3\CMS\Core\FormProtection\BackendFormProtection Form protection.
     */
    public function createFormProtection(ServerRequestInterface $request): BackendFormProtection
    {
        return GeneralUtility::makeInstance(FormProtectionFactory::class)->createFromRequest($request);
    }


    /**
     * Generates the module form token.
     *
     * @param \TYPO3\CMS\Core\FormProtection\BackendFormProtection $formProtection Form protection.
     * @return string Token.
     */
    public function generate(BackendFormProtection $formProtection): string
    {
        return $formProtection->generateToken(self::TOKEN_TYPE, self::TOKEN_ACTION, self::TOKEN_INSTANCE);
    }


    /**
     * Validates the submitted module form token.
     *
     * The backend module can receive plain POST fields through the PSR-7 backend
     * request while Extbase arguments may stay empty for module route forms.
     * Therefore both request types are supported here.
     *
     * @param object $request PSR-7 backend request or Extbase request.
     * @param \TYPO3\CMS\Core\FormProtection\BackendFormProtection $formProtection Form protection.
     * @return bool True when valid.
     */
    public function validate(object $request, BackendFormProtection $formProtection): bool
    {
        $token = $this->extractToken($request);

        return $token !== ''
            && $formProtection->validateToken($token, self::TOKEN_TYPE, self::TOKEN_ACTION, self::TOKEN_INSTANCE);
    }


    /**
     * Extracts the form token from PSR-7 or Extbase request data.
     *
     * @param object $request PSR-7 backend request or Extbase request.
     * @return string Submitted token.
     */
    private function extractToken(object $request): string
    {
        if ($request instanceof ServerRequestInterface) {
            $parsedBody = $request->getParsedBody();
            if (is_array($parsedBody) && isset($parsedBody['formToken'])) {
                return trim((string)$parsedBody['formToken']);
            }
        }

        if (method_exists($request, 'hasArgument') && method_exists($request, 'getArgument') && $request->hasArgument('formToken')) {
            return trim((string)$request->getArgument('formToken'));
        }

        if (isset($_POST['formToken'])) {
            return trim((string)$_POST['formToken']);
        }

        return '';
    }
}
