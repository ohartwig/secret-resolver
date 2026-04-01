# moselwal/secret-resolver

Runtime-Secret-Auflösung für TYPO3 Site Configuration.

## Was macht diese Extension?

TYPO3 unterstützt `%env(VAR)%` in Site Configuration YAML — aber nur einfache Umgebungsvariablen. In Container- und Kubernetes-Umgebungen werden Secrets oft als Dateien gemountet (`/run/secrets/`) oder über `*_FILE`-Env-Variablen referenziert.

Diese Extension fügt die Syntax `%secret(KEY)%` hinzu, die Secrets aus Dateien auflöst:

1. **`KEY_FILE`-Env** — Liest den Dateipfad aus `${KEY}_FILE` und liest die Datei (Docker Swarm Pattern)
2. **`/run/secrets/`** — Liest `/run/secrets/${key}` (Docker/K8s Secret-Mount)

Für direkte Umgebungsvariablen: TYPO3 Core `%env(KEY)%` verwenden.

## Installation

```bash
composer require moselwal/secret-resolver
```

## Nutzung

```yaml
# config/sites/main/config.yaml
base: 'https://example.com/'

apiKey: '%secret(API_KEY)%'
dbPassword: '%secret(DB_PASSWORD)%'

# Inline in Strings:
dsn: 'mysql://user:%secret(DB_PASSWORD)%@db:3306/app'
```

## Kaskade im Detail

Für `%secret(DB_PASSWORD)%`:

| Schritt | Quelle | Beispiel |
|---|---|---|
| 1 | `DB_PASSWORD_FILE` Env → Datei lesen | `DB_PASSWORD_FILE=/vault/secrets/db-pass` |
| 2 | `/run/secrets/db_password` | Docker/K8s Secret-Mount |

Der erste Treffer gewinnt. Leere Werte und reine Whitespace-Dateien werden übersprungen.

## Caching

Aufgelöste Werte werden von TYPO3 in `cache.core` gecached (identisch zu `%env()%`). Nach Secret-Rotation:

```bash
vendor/bin/typo3 cache:flush
```

## Eigene Provider

Die Extension ist über `SecretProviderInterface` erweiterbar:

```php
use Moselwal\SecretResolver\Domain\Contract\SecretProviderInterface;
use Moselwal\SecretResolver\Domain\ValueObject\SecretKey;

final readonly class VaultSecretProvider implements SecretProviderInterface
{
    public function supports(SecretKey $key): bool
    {
        // Vault-spezifische Logik
        return true;
    }

    public function resolve(SecretKey $key): ?string
    {
        // Vault API aufrufen
        return $vaultClient->getSecret($key->raw);
    }

    public function priority(): int
    {
        return 40; // Vor FileEnv (30)
    }
}
```

Provider werden via `_instanceof`-Tag in `Services.yaml` automatisch registriert.

## Anforderungen

- PHP ^8.3
- TYPO3 ^14.0
