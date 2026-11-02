# 🔍 Apache document root and logging

## 1 · File card

| Field | Inspected value |
|---|---|
| Canonical file | [docker/apache.conf](../../docker/apache.conf) |
| Guide owner | Assemble · team jointly; stage specialists review related behavior |
| Language / size | configuration/SQL/shell · 10 physical lines · 314 bytes |
| Runtime baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |
| Specification | `096258d302367aba198a83147c5f985fa74477b7`; fetched/read without merging |
| Authorship | Existing Codex-assisted baseline; no student contribution assigned by this guide |

## 2 · Why it exists

Exposes only public/ while keeping application services/configuration outside HTTP-addressable storage.

## 3 · Prerequisites

Read [integration overview](../00-integration-overview.md), then follow the dependencies below. Distinguish a source file, a running process and persistent state: a document describes behavior, configuration controls a tool, and stored database/key and session state survives separately from replaceable source.

## 4 · Dependencies and consumers

[Dockerfile](Dockerfile.md), [public rewrite rules](public___htaccess.md), [production layout](../07-production-deployment-runbook.md).

## 5 · Source-order coverage tour

| Directive, source order | Effect |
|---|---|
| `<VirtualHost *:80>` | Handles HTTP 80 inside container; TLS must be added by a reviewed host/proxy plan |
| `DocumentRoot /var/www/html/public` | Maps URL `/` to public source only |
| `<Directory /var/www/html/public>` | Applies explicit access policy to this filesystem directory |
| `Options -Indexes +FollowSymLinks` | No directory listing; allow filesystem symlink following under server policy |
| `AllowOverride All` | Permits public/.htaccess rewrite directives |
| `Require all granted` | Allows requests to public files; application authorization remains PHP responsibility |
| `ErrorLog` | Apache runtime errors at configured private log directory |
| `CustomLog ... combined` | Access metadata using combined format; review retention/privacy |

The closing Directory/VirtualHost directives end their scopes. `Require all granted` must not be moved to the repository root. Directory-list prevention alone cannot protect `.env` if the wrong webroot is used.

## 6 · Real excerpt and annotation

```text
    DocumentRoot /var/www/html/public
    <Directory /var/www/html/public>
        Options -Indexes +FollowSymLinks
```

DocumentRoot is filesystem mapping; Directory is a filesystem policy scope. They are not PHP include paths. URL paths never need to contain /public when the root is configured correctly.

## 7 · Input → transformation → output

**Input:** Apache virtual-host directives loaded by Dockerfile.

**Output:** Port 80 virtual host, directory policy and protected server-side logs.

The tour above identifies the intermediate steps and their authority; copying a document or template is not evidence those steps executed.

## 8 · Connections

Use the linked consumers above and the [complete assembly inventory](../01-repository-assembly-map.md). Public traffic enters `public/`; services/configuration/learning material stay outside its document root. Canonical implementation explanations live in their owning stage, while this guide owns operational/documentation interpretation.

## 9 · Why this implementation?

A private project root plus public document root is stronger than hoping users never request private filenames. `.htaccess` support is suitable for this Apache image; other servers need equivalent routing.

## 10 · Security, correctness and accessibility

This file alone does not terminate TLS, configure a production domain or secure an exposed database. Keep private logs restricted; access logs may include query strings/IP metadata, so never put secrets in URLs.

For documentation, keep supported Markdown, meaningful link labels and diagrams whose meaning does not depend on color alone. For configuration, preserve the application trust boundaries rather than hiding failures in UI.

## 11 · Common mistakes and diagnosis

Serving /var/www/html exposes private source/config. AllowOverrideNone prevents API rewrite. Wrong asset root gives 404. Changing permissions recursively to 777 is unsafe; match service-user access minimally.

## 12 · Read or run safely

In an authorized existing container, Apache config syntax can be checked with `apache2ctl -t`. Current PHP lint results do not validate an unavailable Apache runtime; report this distinction.

These are teaching instructions. Actual outcomes, including skipped suites, are centralized in [the current evidence register](../16-evidence-and-artifact-register.md). Do not relabel a legacy or suggested command as freshly passed.

## 13 · Debugging lesson

Trace the consumer named in section 4 and verify one boundary at a time. Record filenames, versions, route/status, rule names or synthetic counts. Do not dump resolved configuration, private env files, request cookies, factor values or identity-bearing SQL exports. Diagnose the first failed prerequisite before retrying.

## 14 · Guided experiment

Map /.env, /assets/app.css and /api/v1/health to their intended handling. Explain why app/bootstrap.php must not be directly served. No edits required.

Keep an observation worksheet outside the public webroot. If you later make a temporary source edit, save your own small patch first, then manually undo only your lines; never overwrite another contributor's shared work or reset a real database for a lesson.

## 15 · Challenge ladder

- **Understand:** explain section 6 using the source and identify its actual consumer.
- **Modify:** propose one narrowly scoped clarification or correction only after reproducing a misunderstanding; coordinate with the source owner and verify its linked contract.
- **Engineer:** evaluate one failure in section 11, describe its evidence and trade-off, and submit genuine reviewed work through the human workflow after authorization.

Expected evidence is a correct source-to-behavior explanation and any real test result, not a cosmetic commit or copied screenshot. The experiment in section 14 supplies the safe setup; future changes must have their own focused validation.

## 16 · Knowledge check

1. Does disabling indexes prevent requesting a known secret file?
2. Does Require all granted grant admin role?
3. Why does API rewriting depend on override support?

<details>
<summary>Answer key · reason first</summary>

1. No; correct document root is essential.
2. No; PHP checks sessions/roles separately.
3. The rewrite rule lives in public/.htaccess.

</details>

## 17 · Contribution boundaries

Assemble coordinates integration; Stage 01 reviews presentation/accessibility, Stage 02 student behavior, Stage 03 admin operations and Stage 04 API/data/security. Identify the exact changed section before editing a shared source. Do not add frameworks, duplicate runtime copies, untested CI or public setup scripts for documentation convenience. Manual Git/PR/release actions require human authority and truthful identities; no such actions were performed for this guide.

## 18 · Status and limitations

**VERIFIED BY INSPECTION:** source-order sections and quoted text above. Current root-run safe checks include security/PHP / JS / shell and startup/doctor; see the central register for precise commands/outcomes. Database-reset suites were not rerun for this documentation task. Host/provider/MySQL 8 production compatibility, public deployment and native Windows/macOS execution remain unverified unless separately evidenced. **PLANNED:** learner experiments/contributions and production release; these are not completed merely because instructions exist.

## 19 · Glossary

**Virtual host:** server configuration for requests. **Document root:** filesystem base exposed to HTTP. **Directive scope:** where a server rule applies.

## 20 · One-minute explanation to personalize

Trace a browser URL to public/ and explain why private includes remain available to PHP but unavailable as downloads.

Explain only what you actually studied or changed. Generated reference material is not proof of personal implementation, testing or authorship. Use the [contribution record](../templates/contribution-record.md) for your own evidence.
