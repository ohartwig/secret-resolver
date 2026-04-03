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
        $value = $key->isExtended()
            ? $this->resolveTargeted($key)
            : $this->resolveCascade($key);

        if ($value === null) {
            return null;
        }

        if ($key->subKey !== null) {
            return $this->extractSubKey($value, $key->subKey);
        }

        return $value;
    }

    private function resolveTargeted(SecretKey $key): ?string
    {
        foreach ($this->providers as $provider) {
            if ($provider->getName() !== $key->provider) {
                continue;
            }

            if (!$provider->supports($key)) {
                return null;
            }

            return $provider->resolve($key);
        }

        return null;
    }

    private function resolveCascade(SecretKey $key): ?string
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

    private function extractSubKey(string $value, string $subKey): ?string
    {
        $decoded = json_decode($value, true);
        if (!is_array($decoded) || !array_key_exists($subKey, $decoded)) {
            return null;
        }

        $result = $decoded[$subKey];

        return is_string($result) ? $result : json_encode($result, JSON_THROW_ON_ERROR);
    }
}
