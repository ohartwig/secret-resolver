# moselwal/secret-resolver

Runtime secret resolution for TYPO3 YAML configuration.

## What does this extension do?

TYPO3 supports `%env(VAR)%` in site configuration YAML — but only for plain environment variables. In container and Kubernetes environments, secrets are often mounted as files (`/run/secrets/`) or referenced via `*_FILE` environment variables. And in production setups with HashiCorp Vault, AWS Secrets Manager or similar tools, you may want to resolve secrets directly from these backends.

This extension adds the `%secret(KEY)%` syntax that resolves secrets from configurable sources — with a built-in cascade for file-based secrets and an extensible provider architecture for direct backend integration.

## Installation

```bash
composer require moselwal/secret-resolver
```text

## Usage

### Simple keys (cascade resolution)

```yaml
# config/sites/main/config.yaml
apiKey: '%secret(API_KEY)%'
dbPassword: '%secret(DB_PASSWORD)%'

# Inline in strings:
dsn: 'mysql://user:%secret(DB_PASSWORD)%@db:3306/app'
```

The key is resolved through all registered providers in priority order. First match wins.

### Extended keys (provider-targeted resolution)

```yaml
# Direct Vault lookup — bypasses cascade, routes to "vault" provider
dbPassword: '%secret(vault:kv-v2/database.password)%'
apiToken: '%secret(vault:transit/api_token)%'

# AWS Secrets Manager
dbPassword: '%secret(aws-sm:prod/database.password)%'
```text

Extended key format: `%secret(provider:path/to/secret.subKey)%`

| Part | Required | Description |
|---|---|---|
| `provider` | Yes | Provider name (e.g. `vault`, `aws-sm`) — routes directly to that provider |
| `path` | No | Secret path with `/` separators (e.g. `kv-v2/database`) |
| `subKey` | No | JSON sub-key after last `.` in the final path segment — extracts a field from a JSON response |

**Sub-key extraction**: If the provider returns a JSON string like `{"password":"s3cret","username":"admin"}`, the sub-key `password` extracts `"s3cret"` automatically.

Simple keys (without `:`) continue to work exactly as before — fully backward-compatible.

## Built-in resolution cascade

For simple keys like `%secret(DB_PASSWORD)%`:

| Priority | Provider | Source | Example |
|---|---|---|---|
| 30 | FileEnvSecretProvider | `DB_PASSWORD_FILE` env → read file | `DB_PASSWORD_FILE=/vault/secrets/db-pass` |
| 20 | RunSecretsSecretProvider | `/run/secrets/db_password` | Docker/K8s secret mount |

First match wins. Empty values and whitespace-only files are skipped.

## Works in all TYPO3 YAML files

The `%secret()%` placeholder hooks into TYPO3's central `YamlFileLoader`, so it works in **all** TYPO3 YAML configurations — not just Site Configuration:

- Site Configuration (`config/sites/*/config.yaml`)
- Form Framework YAML definitions
- Services.yaml (Dependency Injection)
- Any YAML loaded through TYPO3's standard YAML loader

## Caching

Resolved values are cached by TYPO3 in `cache.core` (identical to `%env()%`). After secret rotation:

```bash
vendor/bin/typo3 cache:flush
```

## Implementing a custom SecretProvider

The extension is designed for extensibility. Any TYPO3 extension can add its own secret provider — no modification of the core package required.

### Step 1: Implement `SecretProviderInterface`

```php
<?php

declare(strict_types=1);

namespace MyVendor\MyExtension\Infrastructure\Provider;

use Moselwal\SecretResolver\Domain\Contract\SecretProviderInterface;
use Moselwal\SecretResolver\Domain\ValueObject\SecretKey;

final readonly class VaultSecretProvider implements SecretProviderInterface
{
    public function __construct(
        private VaultClient $client,
    ) {}

    public function getName(): string
    {
        // Return a unique provider name for extended key format targeting.
        // Users can then write %secret(vault:kv-v2/db.password)%
        // Return '' to participate only in the cascade (simple keys).
        return 'vault';
    }

    public function supports(SecretKey $key): bool
    {
        // For extended keys targeting this provider, check if the secret exists.
        // For cascade (simple keys), decide if this provider should attempt resolution.
        if ($key->isExtended()) {
            $path = $key->getSecretPath() ?? $key->getKeyName();
            return $this->client->secretExists($path);
        }

        // In cascade mode, optionally check by key name
        return $this->client->secretExists($key->lowerCase);
    }

    public function resolve(SecretKey $key): ?string
    {
        $path = $key->isExtended()
            ? ($key->getSecretPath() ?? $key->getKeyName())
            : $key->lowerCase;

        try {
            $value = $this->client->readSecret($path);
        } catch (\Throwable) {
            return null; // Fallback to next provider in cascade
        }

        return $value !== '' ? $value : null;
    }

    public static function priority(): int
    {
        // Higher priority = checked first.
        // Built-in: FileEnv=30, RunSecrets=20
        return 40; // Vault is checked before file-based providers
    }
}
```text

### Step 2: Register via Services.yaml

No manual registration needed — the provider is automatically discovered. TYPO3's DI container picks up all `SecretProviderInterface` implementations via the `_instanceof` auto-tagging configured by this extension.

Just make sure your extension's `Configuration/Services.yaml` has autowiring enabled (TYPO3 default):

```yaml
services:
  _defaults:
    autowire: true
    autoconfigure: true
    public: false

  MyVendor\MyExtension\:
    resource: '../Classes/*'
```

The `VaultClient` dependency is injected automatically if registered in your extension's DI container.

### Step 3: Use it

```yaml
# Simple key — goes through cascade (Vault at priority 40 is checked first)
apiKey: '%secret(API_KEY)%'

# Extended key — routes directly to Vault, extracts "password" from JSON response
dbPassword: '%secret(vault:kv-v2/database.password)%'

# Extended key — full secret path, no sub-key extraction
certificate: '%secret(vault:pki/issue/my-cert)%'
```text

### SecretKey properties available to providers

| Property | Type | Description |
|---|---|---|
| `$key->raw` | `string` | Original input (`DB_PASSWORD` or `vault:kv-v2/db.password`) |
| `$key->upperCase` | `string` | Upper-cased key name (without provider prefix) |
| `$key->lowerCase` | `string` | Lower-cased key name (without provider prefix) |
| `$key->provider` | `?string` | Provider name (`vault`) or `null` for simple keys |
| `$key->path` | `?string` | Path segment (`kv-v2/db`) or `null` |
| `$key->subKey` | `?string` | Sub-key for JSON extraction (`password`) or `null` |
| `$key->isExtended()` | `bool` | `true` if provider prefix is present |
| `$key->getKeyName()` | `string` | Key without provider prefix (`kv-v2/db.password`) |
| `$key->getSecretPath()` | `?string` | Path without sub-key (`kv-v2/db`) or `null` |

### Priority guidelines

| Priority | Use case |
|---|---|
| 50+ | Override everything (e.g. local dev mock provider) |
| 40 | Primary backend (Vault, AWS SM, Azure Key Vault) |
| 30 | File-based env vars (built-in) |
| 20 | Docker/K8s secret mounts (built-in) |
| 10 | Fallback / last resort |

## Requirements

- PHP ^8.3
- TYPO3 ^14.0
