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
    // --- Legacy format tests ---

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
    public function rawValueIsPreserved(): void
    {
        $key = new SecretKey('My_Mixed_Case_Key');

        self::assertSame('My_Mixed_Case_Key', $key->raw);
    }

    #[Test]
    public function legacyKeyHasNoProvider(): void
    {
        $key = new SecretKey('DB_PASSWORD');

        self::assertNull($key->provider);
        self::assertNull($key->path);
        self::assertNull($key->subKey);
        self::assertFalse($key->isExtended());
    }

    #[Test]
    public function legacyKeyNameEqualsRaw(): void
    {
        $key = new SecretKey('DB_PASSWORD');

        self::assertSame('DB_PASSWORD', $key->getKeyName());
    }

    /** @return array<string, array{string, string, string}> */
    public static function keyVariantsProvider(): array
    {
        return [
            'lower input' => ['db_password', 'DB_PASSWORD', 'db_password'],
            'upper input' => ['DB_PASSWORD', 'DB_PASSWORD', 'db_password'],
            'mixed input' => ['Api_Key', 'API_KEY', 'api_key'],
        ];
    }

    #[Test]
    #[DataProvider('keyVariantsProvider')]
    public function allDerivedValuesAreCorrect(
        string $raw,
        string $expectedUpper,
        string $expectedLower,
    ): void {
        $key = new SecretKey($raw);

        self::assertSame($expectedUpper, $key->upperCase);
        self::assertSame($expectedLower, $key->lowerCase);
    }

    /** @return array<string, array{string}> */
    public static function invalidKeyProvider(): array
    {
        return [
            'empty string' => [''],
            'starts with digit' => ['1PASSWORD'],
            'starts with underscore' => ['_SECRET'],
            'contains dot' => ['DB.PASSWORD'],
            'contains space' => ['DB PASSWORD'],
            'null byte simulation' => ["KEY\x00"],
        ];
    }

    #[Test]
    #[DataProvider('invalidKeyProvider')]
    public function constructorRejectsInvalidKeys(string $raw): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new SecretKey($raw);
    }

    #[Test]
    public function constructorAcceptsKeyAtMaxLength(): void
    {
        $key = new SecretKey(str_repeat('A', 128));

        self::assertSame(str_repeat('A', 128), $key->raw);
    }

    // --- Extended format tests ---

    #[Test]
    public function extendedKeyParsesProvider(): void
    {
        $key = new SecretKey('vault:DATABASE_PASSWORD');

        self::assertTrue($key->isExtended());
        self::assertSame('vault', $key->provider);
        self::assertNull($key->path);
        self::assertNull($key->subKey);
        self::assertSame('DATABASE_PASSWORD', $key->getKeyName());
    }

    #[Test]
    public function extendedKeyParsesProviderAndPath(): void
    {
        $key = new SecretKey('vault:kv-v2/myapp');

        self::assertSame('vault', $key->provider);
        self::assertSame('kv-v2/myapp', $key->path);
        self::assertNull($key->subKey);
        self::assertSame('kv-v2/myapp', $key->getKeyName());
    }

    #[Test]
    public function extendedKeyParsesProviderPathAndSubKey(): void
    {
        $key = new SecretKey('vault:kv-v2/db.password');

        self::assertSame('vault', $key->provider);
        self::assertSame('kv-v2/db', $key->path);
        self::assertSame('password', $key->subKey);
        self::assertSame('kv-v2/db.password', $key->getKeyName());
    }

    #[Test]
    public function extendedKeyWithDeepPath(): void
    {
        $key = new SecretKey('vault:secret/data/prod/database.username');

        self::assertSame('vault', $key->provider);
        self::assertSame('secret/data/prod/database', $key->path);
        self::assertSame('username', $key->subKey);
    }

    #[Test]
    public function extendedKeyUpperLowerCaseApplyToRemainder(): void
    {
        $key = new SecretKey('vault:DB_PASSWORD');

        self::assertSame('DB_PASSWORD', $key->upperCase);
        self::assertSame('db_password', $key->lowerCase);
    }

    #[Test]
    public function extendedKeyRejectsEmptyProvider(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1743500102);

        new SecretKey(':DB_PASSWORD');
    }

    #[Test]
    public function extendedKeyRejectsEmptyRemainder(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1743500103);

        new SecretKey('vault:');
    }

    #[Test]
    public function extendedKeyRejectsInvalidProviderName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1743500102);

        new SecretKey('VAULT:secret');
    }

    #[Test]
    public function extendedKeyWithHyphenatedProvider(): void
    {
        $key = new SecretKey('aws-sm:my_secret');

        self::assertSame('aws-sm', $key->provider);
        self::assertSame('my_secret', $key->getKeyName());
    }
}
