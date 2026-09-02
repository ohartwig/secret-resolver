## [0.8.6](https://git.ole-hartwig.eu/development/moselwal/secret-resolver/compare/v0.8.5...v0.8.6) (2026-09-02)

### :bug: Fixes

* **deps:** update dependency php to ^8.5.10 ([1508e53](https://git.ole-hartwig.eu/development/moselwal/secret-resolver/commit/1508e53351606919d35a5dfc5bf5d28b9b95f0e9))

## [0.8.5](https://git.ole-hartwig.eu/development/moselwal/secret-resolver/compare/v0.8.4...v0.8.5) (2026-08-22)

### :repeat: Chores

* **deps:** update dependency devops/ci-cd-components/extension-clean-export to v1.3.29 ([64851bf](https://git.ole-hartwig.eu/development/moselwal/secret-resolver/commit/64851bf1c74a10f962807789364f0ae36bdf7774))
* **repo-templates:** sync ([a88f3a5](https://git.ole-hartwig.eu/development/moselwal/secret-resolver/commit/a88f3a5e5cec5bb67d31bfbe28b39606e16d64fe))

## [0.8.4](https://git.ole-hartwig.eu/development/moselwal/secret-resolver/compare/v0.8.3...v0.8.4) (2026-08-15)

### :repeat: Chores

* **ci:** drop the local .releaserc.yml, which was overriding the preset ([8d16598](https://git.ole-hartwig.eu/development/moselwal/secret-resolver/commit/8d165989b6444144962752c61acac5b177ce51c5))

## [0.8.3](https://git.ole-hartwig.eu/development/moselwal/secret-resolver/compare/v0.8.2...v0.8.3) (2026-08-15)


### Bug Fixes

* **tests:** let Infection run the suite to completion ([5c94fdc](https://git.ole-hartwig.eu/development/moselwal/secret-resolver/commit/5c94fdc8a9c56153f68fd928dfc43b7d511d090d))

## [0.8.2](https://git.ole-hartwig.eu/development/moselwal/secret-resolver/compare/v0.8.1...v0.8.2) (2026-08-14)


### Bug Fixes

* allow the infection extension installer plugin ([34631d7](https://git.ole-hartwig.eu/development/moselwal/secret-resolver/commit/34631d77647c50495a677ebc43534686f9197b62))
* **ci:** point at the current hosts ([c630681](https://git.ole-hartwig.eu/development/moselwal/secret-resolver/commit/c630681c12e1d6dc49e8e9187014fd4acc668f28))

## [0.8.1](https://git.ole-hartwig.eu/development/moselwal/secret-resolver/compare/v0.8.0...v0.8.1) (2026-08-12)


### Bug Fixes

* **deps:** update dependency php to ^8.5.9 ([13457df](https://git.ole-hartwig.eu/development/moselwal/secret-resolver/commit/13457df4cbb57284541518baa5f7f7146a3f6999))

# [0.8.0](https://git.ole-hartwig.eu/development/moselwal/secret-resolver/compare/v0.7.0...v0.8.0) (2026-08-12)


### Features

* **commit-signing:** populate both trust anchors ([e19a5ef](https://git.ole-hartwig.eu/development/moselwal/secret-resolver/commit/e19a5efb608690ae40545f62a0be87adc2a4244f))

# [0.7.0](https://git.ole-hartwig.eu/development/moselwal/secret-resolver/compare/v0.6.5...v0.7.0) (2026-08-07)


### Features

* **commit-signing:** add .gitsigners + lefthook hint (G-SDLC-002 step 3) ([bc391df](https://git.ole-hartwig.eu/development/moselwal/secret-resolver/commit/bc391dfec86df48800bb731411527d6ad38f95b9))

## [0.6.5](https://git.ole-hartwig.eu/development/moselwal/secret-resolver/compare/v0.6.4...v0.6.5) (2026-07-29)


### Bug Fixes

* **docs:** point at the handbook repository, not an unreachable domain ([4cf17b8](https://git.ole-hartwig.eu/development/moselwal/secret-resolver/commit/4cf17b85590bac439c8892040eaf1ecc506418e3))

## [0.6.4](https://git.ole-hartwig.eu/development/moselwal/secret-resolver/compare/v0.6.3...v0.6.4) (2026-07-28)


### Bug Fixes

* **processor:** say so when a secret placeholder resolves to nothing ([3573dfc](https://git.ole-hartwig.eu/development/moselwal/secret-resolver/commit/3573dfcc3ecff576ca97c6c60a8c70be8709a936))

## [0.6.3](https://git.ole-hartwig.eu/development/moselwal/secret-resolver/compare/v0.6.2...v0.6.3) (2026-07-24)


### Bug Fixes

* **ci:** drop github-mirror (no public mirroring for now) ([c3ff8aa](https://git.ole-hartwig.eu/development/moselwal/secret-resolver/commit/c3ff8aae594557e8c59cd29841fec413a5033c9b))

## [0.6.2](https://git.ole-hartwig.eu/development/moselwal/secret-resolver/compare/v0.6.1...v0.6.2) (2026-07-24)


### Bug Fixes

* **ci:** drop ter-publish (no TER publishing for now) ([0044005](https://git.ole-hartwig.eu/development/moselwal/secret-resolver/commit/00440053a64205abb32b8ec4de769f9d417eb72d))
* **ci:** ter-publish 1.2.12 (az1a IPv4 for tailor install) ([f9af4b1](https://git.ole-hartwig.eu/development/moselwal/secret-resolver/commit/f9af4b1d54af06ec47d90d07845bfa5bc23b7fcd))

## [0.6.1](https://git.ole-hartwig.eu/development/moselwal/secret-resolver/compare/v0.6.0...v0.6.1) (2026-07-24)


### Bug Fixes

* **ci:** adopt github-mirror 1.2.10 (skip mirror when no token) ([f4df864](https://git.ole-hartwig.eu/development/moselwal/secret-resolver/commit/f4df864ee6170abf5e6e0e6fb906a727795fc4d8))
* **ci:** github-mirror 1.2.11 (contains skip-if-no-token) ([40531e7](https://git.ole-hartwig.eu/development/moselwal/secret-resolver/commit/40531e74029008326d4dd8baf055db500186bbd0))

# [0.6.0](https://git.ole-hartwig.eu/development/moselwal/secret-resolver/compare/v0.5.1...v0.6.0) (2026-06-10)


### Bug Fixes

* **deps:** bump extension-clean-export to 1.2.2 (TER tailor fix) ([8b6a488](https://git.ole-hartwig.eu/development/moselwal/secret-resolver/commit/8b6a4880a26ed0ca0ad546b7d5094ed347b61077))
* **security:** allowlist extended secret-key path segments (M9) ([4bee8f5](https://git.ole-hartwig.eu/development/moselwal/secret-resolver/commit/4bee8f564c748fc22db00d21020568f3bb8db8a4))


### Features

* **release:** add develop branch as rc-prerelease channel ([2068d7a](https://git.ole-hartwig.eu/development/moselwal/secret-resolver/commit/2068d7a93603688c0cac583bf94c75a8516284db))

## [0.5.1](https://git.ole-hartwig.eu/development/moselwal/secret-resolver/compare/v0.5.0...v0.5.1) (2026-06-07)


### Bug Fixes

* **release:** drop [skip ci] from semantic-release commit ([3a14066](https://git.ole-hartwig.eu/development/moselwal/secret-resolver/commit/3a14066659690f9c3a6574ac69362378ea75cea7))

# [0.5.0](https://git.ole-hartwig.eu/development/moselwal/secret-resolver/compare/v0.4.1...v0.5.0) (2026-06-07)


### Bug Fixes

* **ci:** allow_failure on 9 known-broken component jobs ([aa96c66](https://git.ole-hartwig.eu/development/moselwal/secret-resolver/commit/aa96c6623c0ae88dd6ef4fbd86b2bee3b59b523c))
* composer normalize + require-checker whitelist for transitive symbols ([699da7c](https://git.ole-hartwig.eu/development/moselwal/secret-resolver/commit/699da7c68f8abcf1e77b250e4a5d45dba395e5fc))
* **composer:** add homepage field ([a14bfcf](https://git.ole-hartwig.eu/development/moselwal/secret-resolver/commit/a14bfcf5522b39425e0f11e06e760ae66c2e0a63))
* **deptrac:** use 'value' instead of 'regex' for DirectoryCollector ([83301a0](https://git.ole-hartwig.eu/development/moselwal/secret-resolver/commit/83301a0aac34e9bb9027e8f3c4d43ec69a97e7ca))
* **md:** replace bare fenced codeblocks with ```text ([d801a8d](https://git.ole-hartwig.eu/development/moselwal/secret-resolver/commit/d801a8d8426d33504930f84b00e7cbd514aa897a))
* **md:** scope markdownlint to public surfaces ([527de05](https://git.ole-hartwig.eu/development/moselwal/secret-resolver/commit/527de05e4f3eb94053c8f1c5ff53a9d330d7f56b))


### Features

* **ci:** add TER publish to release stage ([674641c](https://git.ole-hartwig.eu/development/moselwal/secret-resolver/commit/674641c15f457027b4897054a62bea0b1d2f01ae))
