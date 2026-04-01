<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2026 Moselwal Digitalagentur <info@moselwal.de>
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Moselwal\SecretResolver\Infrastructure\Provider;

use Moselwal\SecretResolver\Domain\Contract\SecretProviderInterface;
use Moselwal\SecretResolver\Domain\ValueObject\SecretKey;

final readonly class FileEnvSecretProvider implements SecretProviderInterface
{
    public function supports(SecretKey $key): bool
    {
        $filePath = trim((string)(getenv($key->fileEnvKey) ?: ''));

        return $filePath !== '' && is_readable($filePath);
    }

    public function resolve(SecretKey $key): ?string
    {
        $filePath = trim((string)(getenv($key->fileEnvKey) ?: ''));
        if ($filePath === '' || !is_readable($filePath)) {
            return null;
        }

        $value = trim((string)file_get_contents($filePath));

        return $value !== '' ? $value : null;
    }

    public function priority(): int
    {
        return 30;
    }
}
