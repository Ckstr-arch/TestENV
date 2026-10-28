# 🌐 PHP/MySQL hosting decision

No provider capability/pricing was checked live for this task. Every named provider/free-tier promise is therefore **UNVERIFIED**. The assignment lists examples, not a guarantee that those products still exist or support this PHP app. Choose only after written capability confirmation and a deployment rehearsal.

| Method | Suitability if verified | Must verify | Maintenance/cost |
|---|---|---|---|
| Shared PHP hosting | Small course submission | PHP≥8.2, PDO MySQL/mbstring/sodium/Argon2id, MySQL 8, public-only root, private vars, CLI migration/admin provisioning, HTTPS, backups | Provider-specific; UNVERIFIED |
| Controlled Linux VPS | Full control | Supported OS, Apache/PHP-FPM, MySQL 8, TLS/firewall, private storage, patching/monitoring/restore | Human operations responsibility and possible recurring cost |
| Managed container platform | Existing image can be evaluated | Real PHP container support, durable private sessions/key, managed MySQL 8 connectivity/TLS, release jobs, persistent DB, ingress HTTPS/forwarded scheme | Platform-specific; UNVERIFIED |
| Free host | Only after capability answers | All above plus sleep/quota/cron/CLI limits, terms/retention/reliability | Free is not proof of suitability |

**Static GitHub Pages and Cloudflare Pages alone cannot execute PHP/MySQL.** A local Mac Docker stack and PHP development server do not supply a reliable public submission URL. Do not expose your laptop as a shortcut.

## Ask a candidate host

- Which exact PHP and MySQL server versions are available? Is it really MySQL, not a differently labeled engine?
- Can CLI and HTTP PHP both load pdo_mysql/mbstring/sodium and support Argon2id?
- Can document root be `public/`, or can private code live above public_html with tested path mapping?
- Are secrets/private sessions durable, protected and writable by only the service identity?
- Can we run CLI additive migrations, `doctor`, and private administrator creation? What if CLI is unavailable? Reject the host until a reviewed private alternative is demonstrated; never publish setup endpoints.
- Does HTTPS work with exact APP_URL/Origin handling? How are proxies and DB TLS configured?
- Are logs private, backups encrypted, restore possible, and app/DB storage retained through redeploy/sleep?
- What current quotas/pricing/support/expiry apply? Can lecturer access without your private network?

Record answers/evidence in the [artifact register](16-evidence-and-artifact-register.md). Baseline PDO currently constructs a DSN without explicit database TLS options; a remote DB requiring verified TLS needs a reviewed configuration/code adaptation and tests, **PLANNED**, not a claim that the current DSN enables TLS. Prefer a verified private DB network and get requirements before selecting a host.

Proceed only after the [production checklist](07-production-deployment-runbook.md) is feasible. MySQL 8 compatibility testing and image/brand rights confirmation remain open.
