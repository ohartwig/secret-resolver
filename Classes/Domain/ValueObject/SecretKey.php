<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2026 Moselwal Digitalagentur <info@moselwal.de>
 * SPDX-FileCopyrightText: 2026  Kai Ole Hartwig <mail@ole-hartwig.eu>
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Moselwal\SecretResolver\Domain\ValueObject;

final readonly class SecretKey
{
    private const MAX_LENGTH = 256;
    private const LEGACY_PATTERN = '/^[A-Za-z][A-Za-z0-9_]*$/';
    private const PROVIDER_PATTERN = '/^[a-z][a-z0-9_-]*$/';
    private const SUBKEY_PATTERN = '/^[A-Za-z][A-Za-z0-9_]*$/';
    // SECURITY (audit M9): path segments after the provider prefix must
    // be filename-safe — no traversal ('..'), no absolute paths (leading
    // '/'), no shell-metacharacters. Today the only consumer that ever
    // dereferences a path is the Vault provider (HTTP path), so the
    // current attack surface is small; but every future provider (KV
    // store, file-backed dev provider, K8s SecretRef) inherits the
    // ValueObject and would inherit the gap. Validating here closes the
    // door once.
    private const PATH_SEGMENT_PATTERN = '/^[A-Za-z0-9_-][A-Za-z0-9._-]*$/';

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
            $this->validateExtendedRemainder($remainder);
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

    /**
     * Reject path-traversal, absolute-path or shell-metacharacter remainders
     * after the provider prefix. See PATH_SEGMENT_PATTERN above for the
     * full rationale. Empty remainder is already rejected upstream in
     * parseExtendedFormat().
     */
    private function validateExtendedRemainder(string $remainder): void
    {
        if (str_starts_with($remainder, '/')) {
            throw new \InvalidArgumentException(
                'Extended secret-key path must be relative; absolute paths are rejected',
                1743500104,
            );
        }

        foreach (explode('/', $remainder) as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..') {
                throw new \InvalidArgumentException(
                    'Extended secret-key path contains empty / dot / dot-dot segment',
                    1743500105,
                );
            }
            if (preg_match(self::PATH_SEGMENT_PATTERN, $segment) !== 1) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Extended secret-key path segment must match %s, got: "%s"',
                        self::PATH_SEGMENT_PATTERN,
                        $segment,
                    ),
                    1743500106,
                );
            }
        }
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
