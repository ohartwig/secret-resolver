# moselwal/secret-resolver

Runtime secret resolution for TYPO3 site configuration.

## What does this extension do?

TYPO3 supports `%env(VAR)%` in site configuration YAML — but only for plain environment variables. In container and Kubernetes environments, secrets are often mounted as files (`/run/secrets/`) or referenced via `*_FILE` environment variables.

This extension adds the `%secret(KEY)%` syntax that resolves secrets from files:

1. **`KEY_FILE` env** — Reads the file path from `${KEY}_FILE` and reads the file content (Docker Swarm pattern)
2. **`/run/secrets/`** — Reads `/run/secrets/${key}` (Docker/K8s secret mount)

For plain environment variables, use TYPO3 Core's `%env(KEY)%`.

## Installation

```bash
composer require moselwal/secret-resolver
```

## Usage

```yaml
# config/sites/main/config.yaml
base: 'https://example.com/'

apiKey: '%secret(API_KEY)%'
dbPassword: '%secret(DB_PASSWORD)%'

# Inline in strings:
dsn: 'mysql://user:%secret(DB_PASSWORD)%@db:3306/app'
```

## Resolution cascade

For `%secret(DB_PASSWORD)%`:

| Step | Source | Example |
|---|---|---|
| 1 | `DB_PASSWORD_FILE` env → read file | `DB_PASSWORD_FILE=/vault/secrets/db-pass` |
| 2 | `/run/secrets/db_password` | Docker/K8s secret mount |

First match wins. Empty values and whitespace-only files are skipped.

## Caching

Resolved values are cached by TYPO3 in `cache.core` (identical to `%env()%`). After secret rotation:

```bash
vendor/bin/typo3 cache:flush
```

## Custom providers

The extension is extensible via `SecretProviderInterface`:

```php
use Moselwal\SecretResolver\Domain\Contract\SecretProviderInterface;
use Moselwal\SecretResolver\Domain\ValueObject\SecretKey;

final readonly class VaultSecretProvider implements SecretProviderInterface
{
    public function supports(SecretKey $key): bool
    {
        // Vault-specific logic
        return true;
    }

    public function resolve(SecretKey $key): ?string
    {
        // Call Vault API
        return $vaultClient->getSecret($key->raw);
    }

    public function priority(): int
    {
        return 40; // Before FileEnv (30)
    }
}
```

Providers are automatically registered via `_instanceof` tag in `Services.yaml`.

## Requirements

- PHP ^8.3
- TYPO3 ^14.0
