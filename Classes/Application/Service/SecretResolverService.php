<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2026 Moselwal Digitalagentur <info@moselwal.de>
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Moselwal\SecretResolver\Application\Service;

use Moselwal\SecretResolver\Domain\Contract\SecretProviderInterface;
use Moselwal\SecretResolver\Domain\ValueObject\SecretKey;

final readonly class SecretResolverService
{
    /** @param iterable<SecretProviderInterface> $providers */
    public function __construct(
        private iterable $providers,
    ) {}

    public function resolve(SecretKey $key): ?string
    {
        foreach ($this->providers as $provider) {
            if (!$provider->supports($key)) {
                continue;
            }

            $value = $provider->resolve($key);
            if ($value !== null) {
                return $value;
            }
        }

        return null;
    }
}
