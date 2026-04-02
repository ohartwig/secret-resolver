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
    private const PRIORITY = 20;
    private const BASE_PATH = '/run/secrets/';

    public function supports(SecretKey $key): bool
    {
        return is_readable(self::BASE_PATH . $key->lowerCase);
    }

    public function resolve(SecretKey $key): ?string
    {
        $path = self::BASE_PATH . $key->lowerCase;

        $value = @file_get_contents($path);
        if ($value === false) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
    }

    public static function priority(): int
    {
        return self::PRIORITY;
    }
}
