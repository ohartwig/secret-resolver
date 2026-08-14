<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2026 Moselwal Digitalagentur <info@moselwal.de>
 * SPDX-FileCopyrightText: 2026  Kai Ole Hartwig <mail@ole-hartwig.eu>
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Moselwal\SecretResolver\Infrastructure\Processor;

use Moselwal\SecretResolver\Application\Service\SecretResolverService;
use Moselwal\SecretResolver\Domain\ValueObject\SecretKey;
use Moselwal\SecretResolver\Infrastructure\Provider\FileEnvSecretProvider;
use Moselwal\SecretResolver\Infrastructure\Provider\RunSecretsSecretProvider;
use TYPO3\CMS\Core\Configuration\Processor\Placeholder\PlaceholderProcessorInterface;

final readonly class SecretPlaceholderProcessor implements PlaceholderProcessorInterface
{
    /**
     * @param array<array-key, mixed> $referenceArray the interface declares a plain
     *        array, so the signature must too — narrowing it there is a
     *        contravariance violation
     */
    public function canProcess(string $placeholder, array $referenceArray): bool
    {
        return str_contains($placeholder, '%secret(');
    }

    /**
     * @param array<array-key, mixed> $referenceArray see canProcess()
     *
     * Constructor injection is not possible here — TYPO3 instantiates
     * PlaceholderProcessors via the YAML loader without the DI container.
     */
    public function process(string $value, array $referenceArray): string
    {
        $key = new SecretKey($value);

        $service = new SecretResolverService([
            new FileEnvSecretProvider(),
            new RunSecretsSecretProvider(),
        ]);

        $resolved = $service->resolve($key);

        if ($resolved === null) {
            $this->reportMiss($key);

            return '';
        }

        return $resolved;
    }

    /**
     * An unresolved secret still yields an empty string, and that is deliberate:
     * this runs while the site configuration is being read, so throwing would
     * take down every environment that legitimately lacks the secret — a
     * developer machine without /run/secrets, a CI container building a cache.
     *
     * What is not acceptable is doing it quietly. An empty string is
     * indistinguishable from a configured empty value, so the symptom surfaces
     * far from the cause: a remote API answering 401, or a credential check
     * that no longer checks anything. This note is the only link between the
     * two, so it names the key that failed.
     *
     * The key NAME is safe to write out — it is what the YAML already says.
     * The value is never touched here; there is none.
     *
     * error_log() rather than the TYPO3 logger on purpose: placeholders are
     * expanded during configuration loading, before logging is configured, and
     * a logger that is not ready yet would turn a warning into a boot failure.
     */
    private function reportMiss(SecretKey $key): void
    {
        error_log(sprintf(
            'secret-resolver: placeholder "%%secret(%s)%%" could not be resolved by any provider; '
            . 'substituting an empty string. Check the %s_FILE environment variable or /run/secrets/%s.',
            $key->raw,
            $key->upperCase,
            $key->lowerCase,
        ));
    }
}
