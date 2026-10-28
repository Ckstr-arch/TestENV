# 🔍 Container build: PHP Apache image

## 1 · File card

| Field | Inspected value |
|---|---|
| Canonical file | [Dockerfile](../../Dockerfile) |
| Guide owner | Assemble · team jointly; stage specialists review related behavior |
| Language / size | configuration/SQL/shell · 25 physical lines · 1,113 bytes |
| Runtime baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |
| Specification | `096258d302367aba198a83147c5f985fa74477b7`; fetched/read without merging |
| Authorship | Existing Codex-assisted baseline; no student contribution assigned by this guide |

## 2 · Why it exists

Builds the single PHP application image with required extensions, Apache routing and a startup entrypoint.

## 3 · Prerequisites

Read [integration overview](../00-integration-overview.md), then follow the dependencies below. Distinguish a source file, a running process and persistent state: a document describes behavior, configuration controls a tool, and stored database/key and session state survives separately from replaceable source.

## 4 · Dependencies and consumers

[Context exclusions](_dockerignore.md), [Apache config](docker__apache_conf.md), [entrypoint](docker__entrypoint_sh.md), [Compose](compose_yaml.md).

## 5 · Source-order coverage tour

1. `FROM php:8.4-apache-bookworm` selects PHP/Apache on Debian; this mutable tag is not a locked digest. Reproducibility/security-update policy must be chosen deliberately for a release.
2. The RUN secret mount is optional, read-only to this build step and mode 0444 so apt can read it. If present, an apt HTTPS CA setting points to the mounted trusted certificate file. It does not disable TLS or signatures.
3. Debian sources are changed from HTTP to HTTPS; apt update and no-recommends installation bring libonig-dev and Python 3. Python is for tests, not a runtime framework dependency.
4. `docker-php-ext-install -j2 pdo_mysql mbstring` compiles required PHP extensions with modest parallelism. Sodium/Argon2id availability still needs doctor verification.
5. `a2enmod rewrite` enables clean API routing. Temporary CA configuration and apt lists are removed; the private CA secret is not copied into a layer.
6. WORKDIR fixes the source root; COPY installs the virtual host config and entire filtered repository.
7. Normalize copied directory/file modes 755 / 644, make entrypoint executable, create session path and assign var to www-data. These modes are inside the image, not a command to chmod an entire host checkout.
8. EXPOSE 80 documents the container port; it does not publish it on the host. Compose controls loopback binding.
9. ENTRYPOINT invokes the app startup script; CMD supplies apache2-foreground. Exec-array form avoids an extra shell around these top-level commands.

No multi-runtime stage copies are built. Learning folders remain private source material if copied, and are never alternate document roots.

## 6 · Real excerpt and annotation

```text
WORKDIR /var/www/html
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY . /var/www/html
```

WORKDIR sets relative command context. The first COPY installs webserver configuration, the second uses the filtered context. That makes .dockerignore a security boundary worth reviewing.

## 7 · Input → transformation → output

**Input:** Verified upstream PHP 8.4 Apache Bookworm image, repository build context and optional BuildKit trusted-CA secret.

**Output:** An image exposing internal HTTP 80 and starting container-init before Apache.

The tour above identifies the intermediate steps and their authority; copying a document or template is not evidence those steps executed.

## 8 · Connections

Use the linked consumers above and the [complete assembly inventory](../01-repository-assembly-map.md). Public traffic enters `public/`; services/configuration/learning material stay outside its document root. Canonical implementation explanations live in their owning stage, while this guide owns operational/documentation interpretation.

## 9 · Why this implementation?

A single image keeps extension/server setup consistent. Current image includes testing tools for educational verification; a future slim production target would need its own reviewed/tested build plan.

## 10 · Security, correctness and accessibility

Do not pass actual credentials as build args or COPY files. Keep package signatures/TLS enabled. Exposing public/ via Apache is essential even though private files exist inside the image.

For documentation, keep supported Markdown, meaningful link labels and diagrams whose meaning does not depend on color alone. For configuration, preserve the application trust boundaries rather than hiding failures in UI.

## 11 · Common mistakes and diagnosis

Missing rewrite produces API 404. Unreadable copied modes produce Apache 403. Missing mbstring/PDO causes 500/doctor failure. A cached image does not include source edits until rebuilt. Missing BuildKit support rejects secret mount syntax; use a compatible Docker builder, not an insecure workaround.

## 12 · Read or run safely

Read build dependencies before a build. Use the isolated project launch in the Docker chapter; current-session syntax/doctor evidence does not claim a new image build ran.

These are teaching instructions. Actual outcomes, including skipped suites, are centralized in [the current evidence register](../16-evidence-and-artifact-register.md). Do not relabel a legacy or suggested command as freshly passed.

## 13 · Debugging lesson

Trace the consumer named in section 4 and verify one boundary at a time. Record filenames, versions, route/status, rule names or synthetic counts. Do not dump resolved configuration, private env files, request cookies, factor values or identity-bearing SQL exports. Diagnose the first failed prerequisite before retrying.

## 14 · Guided experiment

Trace the path of app/bootstrap.php and public/index.php inside the image, then explain why only the latter is web-addressable. No new build or source change required.

Keep an observation worksheet outside the public webroot. If you later make a temporary source edit, save your own small patch first, then manually undo only your lines; never overwrite another contributor's shared work or reset a real database for a lesson.

## 15 · Challenge ladder

- **Understand:** explain section 6 using the source and identify its actual consumer.
- **Modify:** propose one narrowly scoped clarification or correction only after reproducing a misunderstanding; coordinate with the source owner and verify its linked contract.
- **Engineer:** evaluate one failure in section 11, describe its evidence and trade-off, and submit genuine reviewed work through the human workflow after authorization.

Expected evidence is a correct source-to-behavior explanation and any real test result, not a cosmetic commit or copied screenshot. The experiment in section 14 supplies the safe setup; future changes must have their own focused validation.

## 16 · Knowledge check

1. Does EXPOSE publish 80 on every host interface?
2. Why can a secret deleted in a later COPY layer remain unsafe?
3. Why does the CA mount keep verification meaningful?

<details>
<summary>Answer key · reason first</summary>

1. No; Compose ports controls publication.
2. Earlier layers/context can retain it.
3. It supplies trusted issuer information rather than bypassing certificate checks.

</details>

## 17 · Contribution boundaries

Assemble coordinates integration; Stage 01 reviews presentation/accessibility, Stage 02 student behavior, Stage 03 admin operations and Stage 04 API/data/security. Identify the exact changed section before editing a shared source. Do not add frameworks, duplicate runtime copies, untested CI or public setup scripts for documentation convenience. Manual Git/PR/release actions require human authority and truthful identities; no such actions were performed for this guide.

## 18 · Status and limitations

**VERIFIED BY INSPECTION:** source-order sections and quoted text above. Current root-run safe checks include security/PHP / JS / shell and startup/doctor; see the central register for precise commands/outcomes. Database-reset suites were not rerun for this documentation task. Host/provider/MySQL 8 production compatibility, public deployment and native Windows/macOS execution remain unverified unless separately evidenced. **PLANNED:** learner experiments/contributions and production release; these are not completed merely because instructions exist.

## 19 · Glossary

**BuildKit:** Docker build engine supporting secret mounts. **Entrypoint:** executable run first. **Image:** immutable filesystem/config template.

## 20 · One-minute explanation to personalize

Explain base image→extensions→COPY→permissions→entrypoint, and why context privacy and public document root solve different exposure problems.

Explain only what you actually studied or changed. Generated reference material is not proof of personal implementation, testing or authorship. Use the [contribution record](../templates/contribution-record.md) for your own evidence.
