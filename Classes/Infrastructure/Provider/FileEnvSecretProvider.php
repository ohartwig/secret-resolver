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
    private const PRIORITY = 30;

    public function supports(SecretKey $key): bool
    {
        return $this->readFilePathFromEnv($key) !== null;
    }

    public function resolve(SecretKey $key): ?string
    {
        $filePath = $this->readFilePathFromEnv($key);
        if ($filePath === null) {
            return null;
        }

        return $this->readTrimmedFileContent($filePath);
    }

    public static function priority(): int
    {
        return self::PRIORITY;
    }

    public function getName(): string
    {
        return '';
    }

    private function readFilePathFromEnv(SecretKey $key): ?string
    {
        $envKey = $key->upperCase . '_FILE';
        $filePath = trim((string)(getenv($envKey) ?: ''));
        if ($filePath === '' || !is_readable($filePath)) {
            return null;
        }

        return $filePath;
    }

    private function readTrimmedFileContent(string $filePath): ?string
    {
        $value = @file_get_contents($filePath);
        if ($value === false) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
    }
}
