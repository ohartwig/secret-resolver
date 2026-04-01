<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2026 Moselwal Digitalagentur <info@moselwal.de>
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Moselwal\SecretResolver\Tests\Unit\Domain\ValueObject;

use Moselwal\SecretResolver\Domain\ValueObject\SecretKey;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SecretKey::class)]
final class SecretKeyTest extends TestCase
{
    #[Test]
    public function constructorNormalizesUpperCase(): void
    {
        $key = new SecretKey('db_password');

        self::assertSame('DB_PASSWORD', $key->upperCase);
    }

    #[Test]
    public function constructorNormalizesLowerCase(): void
    {
        $key = new SecretKey('DB_PASSWORD');

        self::assertSame('db_password', $key->lowerCase);
    }

    #[Test]
    public function constructorDerivesFileEnvKey(): void
    {
        $key = new SecretKey('api_key');

        self::assertSame('API_KEY_FILE', $key->fileEnvKey);
    }

    #[Test]
    public function constructorDerivesRunSecretsPath(): void
    {
        $key = new SecretKey('DB_PASSWORD');

        self::assertSame('/run/secrets/db_password', $key->runSecretsPath);
    }

    #[Test]
    public function rawValueIsPreserved(): void
    {
        $key = new SecretKey('My_Mixed_Case_Key');

        self::assertSame('My_Mixed_Case_Key', $key->raw);
    }

    /** @return array<string, array{string, string, string, string, string}> */
    public static function keyVariantsProvider(): array
    {
        return [
            'lower input' => ['db_password', 'DB_PASSWORD', 'db_password', 'DB_PASSWORD_FILE', '/run/secrets/db_password'],
            'upper input' => ['DB_PASSWORD', 'DB_PASSWORD', 'db_password', 'DB_PASSWORD_FILE', '/run/secrets/db_password'],
            'mixed input' => ['Api_Key', 'API_KEY', 'api_key', 'API_KEY_FILE', '/run/secrets/api_key'],
        ];
    }

    #[Test]
    #[DataProvider('keyVariantsProvider')]
    public function allDerivedValuesAreCorrect(
        string $raw,
        string $expectedUpper,
        string $expectedLower,
        string $expectedFileEnv,
        string $expectedRunSecrets,
    ): void {
        $key = new SecretKey($raw);

        self::assertSame($expectedUpper, $key->upperCase);
        self::assertSame($expectedLower, $key->lowerCase);
        self::assertSame($expectedFileEnv, $key->fileEnvKey);
        self::assertSame($expectedRunSecrets, $key->runSecretsPath);
    }
}
