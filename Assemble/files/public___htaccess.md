# 🔍 Apache directory and API routing rules

## 1 · File card

| Field | Inspected value |
|---|---|
| Canonical file | [public/.htaccess](../../public/.htaccess) |
| Guide owner | Assemble · team jointly; stage specialists review related behavior |
| Language / size | configuration/SQL/shell · 4 physical lines · 93 bytes |
| Runtime baseline | `f81eab54ebe7179e1763f364d6b8d4f564c36541` |
| Specification | `096258d302367aba198a83147c5f985fa74477b7`; fetched/read without merging |
| Authorship | Existing Codex-assisted baseline; no student contribution assigned by this guide |

## 2 · Why it exists

Disables directory listing, selects index.php and routes API URLs to the JSON entrypoint under Apache.

## 3 · Prerequisites

Read [integration overview](../00-integration-overview.md), then follow the dependencies below. Distinguish a source file, a running process and persistent state: a document describes behavior, configuration controls a tool, and stored database/key and session state survives separately from replaceable source.

## 4 · Dependencies and consumers

[Apache virtual host](docker__apache_conf.md), [API entry](../../Stage-04-Backend-Database-Security/files/public__api_php.md), [development router](../../Stage-04-Backend-Database-Security/files/public__router_php.md).

## 5 · Source-order coverage tour

1. `Options -Indexes`: do not generate file listings for requested directories lacking an index.
2. `DirectoryIndex index.php`: use the PHP website entrypoint for `/`.
3. `RewriteEngine On`: enable mod_rewrite in this directory context.
4. `RewriteRule ^api/ api.php [QSA,L]`: paths beginning api/ internally use api.php; QSA preserves query-string arguments and L stops this rule pass.

This directory-context pattern has no leading slash. It routes both valid and invalid API suffixes; PHP's allowlist returns the correct 404 / 405. It is not an authentication or SQL-validation rule. Native page routing still uses query parameter page. PHP's development server ignores .htaccess and therefore needs public/router.php.

## 6 · Real excerpt and annotation

```text
RewriteEngine On
RewriteRule ^api/ api.php [QSA,L]
```

The regex recognizes the URL family; replacement is relative entrypoint. Flags handle query preservation and rule flow; no external redirect is issued here.

## 7 · Input → transformation → output

**Input:** Requests reaching Apache public document root with mod_rewrite and AllowOverride enabled.

**Output:** Normal page entry or internal API rewrite retaining query parameters.

The tour above identifies the intermediate steps and their authority; copying a document or template is not evidence those steps executed.

## 8 · Connections

Use the linked consumers above and the [complete assembly inventory](../01-repository-assembly-map.md). Public traffic enters `public/`; services/configuration/learning material stay outside its document root. Canonical implementation explanations live in their owning stage, while this guide owns operational/documentation interpretation.

## 9 · Why this implementation?

Clean API paths can use one controlled PHP dispatcher without creating many public PHP endpoints. Server-specific equivalents are needed for Nginx rather than copying .htaccess blindly.

## 10 · Security, correctness and accessibility

A rewrite should not expose private directories or strip CSRF/auth checks. Verify actual server configuration; copying this file to a static host cannot execute PHP.

For documentation, keep supported Markdown, meaningful link labels and diagrams whose meaning does not depend on color alone. For configuration, preserve the application trust boundaries rather than hiding failures in UI.

## 11 · Common mistakes and diagnosis

PHP -S without router yieldsAPI 404. Missing rewrite module/AllowOverride gives 404 or 500. Adding a leading slash to directory-context pattern prevents match. Removing QSA can lose filters/pagination.

## 12 · Read or run safely

Request `/api/v1/health` through a configured local Apache instance, not by opening files. Readiness requires database available. Use current execution register for whether Apache itself was tested.

These are teaching instructions. Actual outcomes, including skipped suites, are centralized in [the current evidence register](../16-evidence-and-artifact-register.md). Do not relabel a legacy or suggested command as freshly passed.

## 13 · Debugging lesson

Trace the consumer named in section 4 and verify one boundary at a time. Record filenames, versions, route/status, rule names or synthetic counts. Do not dump resolved configuration, private env files, request cookies, factor values or identity-bearing SQL exports. Diagnose the first failed prerequisite before retrying.

## 14 · Guided experiment

Predict handling of `/`, `/api/v1/clubs?page=2`, `/assets/app.css` and `/api/v1/unknown`. Inspect server/PHP boundary; no source changes needed.

Keep an observation worksheet outside the public webroot. If you later make a temporary source edit, save your own small patch first, then manually undo only your lines; never overwrite another contributor's shared work or reset a real database for a lesson.

## 15 · Challenge ladder

- **Understand:** explain section 6 using the source and identify its actual consumer.
- **Modify:** propose one narrowly scoped clarification or correction only after reproducing a misunderstanding; coordinate with the source owner and verify its linked contract.
- **Engineer:** evaluate one failure in section 11, describe its evidence and trade-off, and submit genuine reviewed work through the human workflow after authorization.

Expected evidence is a correct source-to-behavior explanation and any real test result, not a cosmetic commit or copied screenshot. The experiment in section 14 supplies the safe setup; future changes must have their own focused validation.

## 16 · Knowledge check

1. Is this read by PHP -S?
2. Who rejects an unknown API route?
3. Does an internal rewrite change browser URL?

<details>
<summary>Answer key · reason first</summary>

1. No; use public/router.php.
2. The PHP dispatcher after rewrite.
3. No; it is server-side routing.

</details>

## 17 · Contribution boundaries

Assemble coordinates integration; Stage 01 reviews presentation/accessibility, Stage 02 student behavior, Stage 03 admin operations and Stage 04 API/data/security. Identify the exact changed section before editing a shared source. Do not add frameworks, duplicate runtime copies, untested CI or public setup scripts for documentation convenience. Manual Git/PR/release actions require human authority and truthful identities; no such actions were performed for this guide.

## 18 · Status and limitations

**VERIFIED BY INSPECTION:** source-order sections and quoted text above. Current root-run safe checks include security/PHP / JS / shell and startup/doctor; see the central register for precise commands/outcomes. Database-reset suites were not rerun for this documentation task. Host/provider/MySQL 8 production compatibility, public deployment and native Windows/macOS execution remain unverified unless separately evidenced. **PLANNED:** learner experiments/contributions and production release; these are not completed merely because instructions exist.

## 19 · Glossary

**Rewrite:** internal request routing. **QSA:** query-string append. **Directory context:** path matching below document root.

## 20 · One-minute explanation to personalize

Explain why an API 404 can be a webserver routing problem or an application route problem, and how to tell which layer answered.

Explain only what you actually studied or changed. Generated reference material is not proof of personal implementation, testing or authorship. Use the [contribution record](../templates/contribution-record.md) for your own evidence.
