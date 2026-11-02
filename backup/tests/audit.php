<?php
declare(strict_types=1);
/** Read-only stack and Markdown-link audit. Does not connect to or change a database. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root=realpath(__DIR__.'/..');
$failures=[];$checks=0;$files=[];
function audit_check(bool $ok,string $label): void {
    global $checks,$failures;
    ++$checks;
    if (!$ok) $failures[]=$label;
}
$iterator=new RecursiveIteratorIterator(new RecursiveCallbackFilterIterator(
    new RecursiveDirectoryIterator($root,FilesystemIterator::SKIP_DOTS),
    function(SplFileInfo $entry): bool {
        // Private local state is never read or included in the audit's source inventory.
        if ($entry->isDir() && in_array($entry->getFilename(),['var','.git'],true)) return false;
        if ($entry->isFile() && $entry->getFilename()==='.env') return false;
        return true;
    }
));
foreach ($iterator as $file) {
    if (!$file->isFile()) continue;
    $path=$file->getPathname();$relative=substr($path,strlen($root)+1);$files[$relative]=$path;
    $extension=strtolower($file->getExtension());
    audit_check(!in_array($extension,['py','pyc','ts','tsx','jsx','rb','go','java','jar'],true),'Unsupported runtime source: '.$relative);
    audit_check(!preg_match('~(^|/)(node_modules|vendor)(/|$)~',$relative),'Dependency directory must not ship: '.$relative);
    audit_check(!in_array($file->getFilename(),['package.json','package-lock.json','yarn.lock','pnpm-lock.yaml','composer.json','composer.lock','Dockerfile','compose.yaml','docker-compose.yml'],true),'Unneeded framework/container manifest: '.$relative);
}
audit_check(isset($files['public/index.php'],$files['public/api.php'],$files['public/router.php']),'Canonical PHP entrypoints present');
audit_check(isset($files['database/schema.sql'],$files['database/migrations/20261017_club_communities.sql']),'Canonical relational schema and additive migration present');
audit_check(isset($files['.env.example'],$files['.gitignore']),'Private configuration template and ignore policy present');
if (isset($files['.gitignore'])) audit_check(str_contains(file_get_contents($files['.gitignore']),'.env'),'Local secrets ignored');
$sourceCount=0;
foreach ($files as $relative=>$path) {
    if (!preg_match('~^(app/|public/)~',$relative) || !in_array(strtolower(pathinfo($path,PATHINFO_EXTENSION)),['php','js','css','html'],true)) continue;
    ++$sourceCount;$content=file_get_contents($path);
    audit_check(!preg_match('~(?:<script[^>]+src|<link[^>]+href)\s*=\s*[\'"]https?://~i',$content),'No remote script/style dependency: '.$relative);
    audit_check(!preg_match('~(?:ReactDOM\.|Vue\.createApp\(|from\s+[\'"](?:react|vue|next|@angular)/?)~',$content),'No foreign frontend framework: '.$relative);
}
$linkCount=0;
foreach ($files as $relative=>$path) {
    if (strtolower(pathinfo($path,PATHINFO_EXTENSION))!=='md') continue;
    // Fenced examples are code, not rendered Markdown hyperlinks.
    $text=preg_replace('/^(`{3,}|~{3,})[^\n]*\n.*?^\1\s*$/ms','',file_get_contents($path));
    preg_match_all('/!?\[[^\]\n]*\]\(([^\s)]+)(?:\s+[\'"][^\n]*[\'"])?\)/',$text,$matches);
    foreach ($matches[1] as $target) {
        $target=trim($target,'<>');
        if (preg_match('~^(?:https?://|mailto:|#)~i',$target)) continue;
        // Explicit application routes are browser destinations, not repository files.
        if (str_starts_with($target,'/?page=') || str_starts_with($target,'/api/')) continue;
        $target=rawurldecode(explode('#',explode('?',$target,2)[0],2)[0]);
        if ($target==='') continue;
        ++$linkCount;
        $resolved=realpath(dirname($path).'/'.$target);
        audit_check($resolved!==false && str_starts_with($resolved,$root.DIRECTORY_SEPARATOR),'Local Markdown target exists within standalone backup: '.$relative.' → '.$target);
    }
}
if ($failures) {
    foreach ($failures as $failure) fwrite(STDERR,'FAIL '.$failure.PHP_EOL);
    fwrite(STDERR,count($failures).' audit failures; '.$checks.' checks evaluated.'.PHP_EOL);
    exit(1);
}
echo $checks.' read-only stack/link checks passed across '.count($files).' files, '.$sourceCount.' runtime text files and '.$linkCount.' local Markdown links.'.PHP_EOL;
echo "No database access, framework dependency, browser rendering, external-link availability or Markdown-anchor validation is claimed by this audit.\n";
