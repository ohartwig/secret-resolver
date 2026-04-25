<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2026 Moselwal Digitalagentur <info@moselwal.de>
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Moselwal\SecretResolver\Infrastructure\Processor;

use Moselwal\SecretResolver\Application\Service\SecretResolverService;
use Moselwal\SecretResolver\Domain\ValueObject\SecretKey;
use Moselwal\SecretResolver\Infrastructure\Provider\FileEnvSecretProvider;
use Moselwal\SecretResolver\Infrastructure\Provider\RunSecretsSecretProvider;
use TYPO3\CMS\Core\Configuration\Processor\Placeholder\PlaceholderProcessorInterface;

final readonly class SecretPlaceholderProcessor implements PlaceholderProcessorInterface
{
    /** @param array<string, mixed> $referenceArray */
    public function canProcess(string $placeholder, array $referenceArray): bool
    {
        return str_contains($placeholder, '%secret(');
    }

    /**
     * @param array<string, mixed> $referenceArray
     *
     * Constructor injection is not possible here — TYPO3 instantiates
     * PlaceholderProcessors via the YAML loader without the DI container.
     */
    public function process(string $value, array $referenceArray): string
    {
        $key = new SecretKey($value);

        $service = new SecretResolverService([
            new FileEnvSecretProvider(),
            new RunSecretsSecretProvider(),
        ]);

        $resolved = $service->resolve($key);

        if ($resolved === null) {
            throw new \UnexpectedValueException(
                'A configured secret could not be resolved from any source',
                1743500000,
            );
        }

        return $resolved;
    }
}
