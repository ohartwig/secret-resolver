<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2026 Moselwal Digitalagentur <info@moselwal.de>
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Moselwal\SecretResolver\Domain\ValueObject;

final readonly class SecretKey
{
    private const MAX_LENGTH = 256;
    private const LEGACY_PATTERN = '/^[A-Za-z][A-Za-z0-9_]*$/';
    private const PROVIDER_PATTERN = '/^[a-z][a-z0-9_-]*$/';
    private const SUBKEY_PATTERN = '/^[A-Za-z][A-Za-z0-9_]*$/';

    public string $upperCase;
    public string $lowerCase;
    public ?string $provider;
    public ?string $path;
    public ?string $subKey;

    public function __construct(
        public string $raw,
    ) {
        if ($raw === '' || strlen($raw) > self::MAX_LENGTH) {
            throw new \InvalidArgumentException(
                sprintf('Secret key must not be empty and not exceed %d characters', self::MAX_LENGTH),
                1743500100,
            );
        }

        if (str_contains($raw, ':')) {
            [$this->provider, $remainder] = $this->parseExtendedFormat($raw);
            [$keyPart, $this->subKey] = $this->extractSubKey($remainder);
            $this->path = str_contains($keyPart, '/') ? $keyPart : null;
            $this->upperCase = strtoupper($remainder);
            $this->lowerCase = strtolower($remainder);
        } else {
            $this->validateLegacyKey($raw);
            $this->provider = null;
            $this->path = null;
            $this->subKey = null;
            $this->upperCase = strtoupper($raw);
            $this->lowerCase = strtolower($raw);
        }
    }

    public function isExtended(): bool
    {
        return $this->provider !== null;
    }

    /**
     * The key name without provider prefix — used by providers for lookup.
     * For legacy keys this equals raw, for extended keys it's the part after the colon.
     */
    public function getKeyName(): string
    {
        if ($this->provider !== null) {
            $colonPos = strpos($this->raw, ':');
            return $colonPos !== false ? substr($this->raw, $colonPos + 1) : $this->raw;
        }
        return $this->raw;
    }

    /**
     * The path without sub-key — used for Vault secret path lookup.
     * Returns null if no path segment with / exists.
     */
    public function getSecretPath(): ?string
    {
        if ($this->path === null) {
            return null;
        }

        if ($this->subKey !== null) {
            $lastDot = strrpos($this->path, '.');
            if ($lastDot !== false) {
                return substr($this->path, 0, $lastDot);
            }
        }

        return $this->path;
    }

    /** @return array{string, string} */
    private function parseExtendedFormat(string $raw): array
    {
        $colonPos = strpos($raw, ':');
        if ($colonPos === false) {
            throw new \InvalidArgumentException('Invalid extended key format', 1743500101);
        }

        $provider = substr($raw, 0, $colonPos);
        $remainder = substr($raw, $colonPos + 1);

        if ($provider === '' || preg_match(self::PROVIDER_PATTERN, $provider) !== 1) {
            throw new \InvalidArgumentException(
                sprintf('Provider name must match %s, got: "%s"', self::PROVIDER_PATTERN, $provider),
                1743500102,
            );
        }

        if ($remainder === '') {
            throw new \InvalidArgumentException('Secret key after provider prefix must not be empty', 1743500103);
        }

        return [$provider, $remainder];
    }

    /** @return array{string, ?string} */
    private function extractSubKey(string $remainder): array
    {
        if (!str_contains($remainder, '/')) {
            return [$remainder, null];
        }

        $lastSlashPos = strrpos($remainder, '/');
        if ($lastSlashPos === false) {
            return [$remainder, null];
        }

        $lastSegment = substr($remainder, $lastSlashPos + 1);
        if (str_contains($lastSegment, '.')) {
            $lastDotPos = strrpos($lastSegment, '.');
            if ($lastDotPos !== false) {
                $subKey = substr($lastSegment, $lastDotPos + 1);
                $pathPart = substr($remainder, 0, $lastSlashPos + 1 + $lastDotPos);

                if ($subKey !== '' && preg_match(self::SUBKEY_PATTERN, $subKey) === 1) {
                    return [$pathPart, $subKey];
                }
            }
        }

        return [$remainder, null];
    }

    private function validateLegacyKey(string $raw): void
    {
        if (preg_match(self::LEGACY_PATTERN, $raw) !== 1) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Secret key must match %s, got: "%s"',
                    self::LEGACY_PATTERN,
                    $raw,
                ),
                1743500100,
            );
        }
    }
}
