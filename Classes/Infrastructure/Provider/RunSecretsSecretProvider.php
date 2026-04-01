<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2026 Moselwal Digitalagentur <info@moselwal.de>
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Moselwal\SecretResolver\Infrastructure\Provider;

use Moselwal\SecretResolver\Domain\Contract\SecretProviderInterface;
use Moselwal\SecretResolver\Domain\ValueObject\SecretKey;

final readonly class RunSecretsSecretProvider implements SecretProviderInterface
{
    public function supports(SecretKey $key): bool
    {
        return is_readable($key->runSecretsPath);
    }

    public function resolve(SecretKey $key): ?string
    {
        if (!is_readable($key->runSecretsPath)) {
            return null;
        }

        $value = trim((string)file_get_contents($key->runSecretsPath));

        return $value !== '' ? $value : null;
    }

    public function priority(): int
    {
        return 20;
    }
}
