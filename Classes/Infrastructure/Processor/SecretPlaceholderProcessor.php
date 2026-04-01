<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2026 Moselwal Digitalagentur <info@moselwal.de>
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Moselwal\SecretResolver\Infrastructure\Processor;

use Moselwal\SecretResolver\Application\Service\SecretResolverService;
use Moselwal\SecretResolver\Domain\ValueObject\SecretKey;
use TYPO3\CMS\Core\Configuration\Processor\Placeholder\PlaceholderProcessorInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final readonly class SecretPlaceholderProcessor implements PlaceholderProcessorInterface
{
    public function canProcess(string $placeholder, array $referenceArray): bool
    {
        return str_contains($placeholder, '%secret(');
    }

    public function process(string $value, array $referenceArray): string
    {
        $key = new SecretKey($value);
        $service = GeneralUtility::makeInstance(SecretResolverService::class);
        $resolved = $service->resolve($key);

        if ($resolved === null) {
            throw new \UnexpectedValueException(
                sprintf('Secret "%s" could not be resolved from any source', $value),
                1743500000
            );
        }

        return $resolved;
    }
}
