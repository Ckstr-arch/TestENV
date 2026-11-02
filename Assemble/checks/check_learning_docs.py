#!/usr/bin/env python3
"""Read-only learning package link, inventory and baseline preservation audit.

Does not connect to MySQL, execute learner commands, or access private env files.
Run from any directory: python3 Assemble/checks/check_learning_docs.py
"""
import hashlib
import json
import re
import subprocess
import sys
import unicodedata
from pathlib import Path
from urllib.parse import unquote, urlsplit

ROOT = Path(__file__).resolve().parents[2]
FOLDERS = [
    'Stage-01-Frontend-UX', 'Stage-02-Student-Experience',
    'Stage-03-Admin-Operations', 'Stage-04-Backend-Database-Security', 'Assemble'
]
STAGE_PAGES = [
    'README.md', '00-start-here.md', '01-architecture.md', '02-source-inventory.md',
    '03-learning-roadmap.md', '04-guided-walkthroughs.md', '05-labs-and-exercises.md',
    '06-testing-and-debugging.md', '07-contribution-contract.md',
    '08-knowledge-check.md', '09-completion-evidence.md', 'exercises/README.md'
]
ASSEMBLE_PAGES = [
    'README.md', '00-integration-overview.md', '01-repository-assembly-map.md',
    '02-git-github-team-workflow.md', '03-local-development-macos.md',
    '04-local-development-windows-linux.md', '05-docker-compose-and-data-safety.md',
    '06-php-mysql-deployment-options.md', '07-production-deployment-runbook.md',
    '08-secrets-security-2fa-and-https.md', '09-database-migration-backup-restore.md',
    '10-ci-tests-and-quality-gates.md', '11-host-troubleshooting-and-rollback.md',
    '12-assignment-traceability.md', '13-final-report-and-screenshots.md',
    '14-video-demo-and-team-speaking-plan.md', '15-release-checklist.md',
    '16-evidence-and-artifact-register.md', '17-glossary-and-faq.md',
    'templates/pull-request.md', 'templates/contribution-record.md',
    'templates/review-checklist.md', 'templates/release-notes.md'
]
errors = []
for folder in FOLDERS:
    expected = ASSEMBLE_PAGES if folder == 'Assemble' else STAGE_PAGES
    for name in expected:
        p = ROOT / folder / name
        if not p.is_file() or p.stat().st_size < 80:
            errors.append(f'Missing/empty required document: {folder}/{name}')

manifest = json.loads((ROOT/'Assemble/baseline-source-manifest.json').read_text())
for item in manifest['files']:
    source, guide = ROOT/item['path'], ROOT/item['guide']
    if not source.is_file():
        errors.append(f'Missing baseline source: {item["path"]}')
    elif item['path'] != 'README.md':
        if hashlib.sha256(source.read_bytes()).hexdigest() != item['sha256']:
            errors.append(f'Baseline changed: {item["path"]}')
    if not guide.is_file() or guide.stat().st_size < 1000:
        errors.append(f'Missing/very short primary guide: {item["guide"]}')

# GitHub heading slug approximation: keep Unicode letters/numbers, spaces/hyphens.
def unfence(text):
    return re.sub(r'^\s*(```|~~~).*?^\s*\1\s*$', '', text,
                  flags=re.M | re.S)
def anchors(text):
    result, seen = set(), {}
    for heading in re.findall(r'^#{1,6}\s+(.+?)\s*#*$', unfence(text), re.M):
        heading = re.sub(r'<[^>]*>', '', heading).replace('`', '').strip().lower()
        heading = ''.join(c for c in heading
                          if c in ' -_' or unicodedata.category(c)[0] in 'LN')
        slug = heading.replace(' ', '-')
        n = seen.get(slug, 0)
        seen[slug] = n+1
        result.add(slug if not n else f'{slug}-{n}')
    result.update(re.findall(r'<a\s+(?:name|id)=[\'"]([^\'"]+)', text))
    return result

md_files = [ROOT/'README.md', ROOT/'LEARNING_MAP.md']
for folder in FOLDERS:
    md_files.extend(sorted((ROOT/folder).rglob('*.md')))
links = 0
for p in md_files:
    if not p.exists():
        errors.append(f'Missing navigation: {p.relative_to(ROOT)}')
        continue
    content = unfence(p.read_text())
    for match in re.finditer(r'(?<!!)\[[^\]\n]+\]\(([^)\n]+)\)|!\[[^\]\n]*\]\(([^)\n]+)\)', content):
        target = (match.group(1) or match.group(2)).strip()
        if target.startswith('<') and target.endswith('>'):
            target = target[1:-1]
        # All generated links use no optional title; code spans are labels only.
        target = target.split(' "')[0]
        parts = urlsplit(target)
        if parts.scheme or parts.netloc:
            continue  # external URLs are not network-validated by this checker
        if target.startswith('/'):
            continue  # UI routes, not repository paths
        links += 1
        dest = (p.parent/unquote(parts.path)).resolve() if parts.path else p
        if not dest.exists():
            errors.append(f'{p.relative_to(ROOT)}: missing link {target}')
        elif parts.fragment and dest.suffix == '.md':
            frag = unquote(parts.fragment)
            if frag not in anchors(dest.read_text()):
                errors.append(f'{p.relative_to(ROOT)}: missing anchor {target}')

tracked = subprocess.check_output(['git', 'ls-files'], cwd=ROOT, text=True).splitlines()
known = {x['path'] for x in manifest['files']}
# Learning documents explain the baseline rather than recursively needing a
# guide for every guide. Keep this check valid after they are committed, while
# still rejecting new runtime/config/test files missing a primary explanation.
learning_paths = {str(p.relative_to(ROOT)) for p in md_files}
learning_paths.update({
    'Stage-01-Frontend-UX/exercises/layout-clinic.html',
    'Assemble/baseline-source-manifest.json',
    'Assemble/checks/check_learning_docs.py',
    'Assemble/checks/read_only_smoke.py',
})
for p in tracked:
    if p not in known and p not in learning_paths:
        errors.append(f'Tracked path missing primary inventory: {p}')
print(f'{len(md_files)} learning/navigation Markdown files; {links} local links; '
      f'{len(manifest["files"])} baseline/spec primary guides checked.')
if errors:
    for error in errors:
        print('FAIL', error)
    raise SystemExit(1)
print('PASS required documents, local paths/anchors, inventory and baseline preservation.')
print('External URLs and full Mermaid rendering are not validated by this stdlib checker.')
