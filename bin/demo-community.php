<?php
declare(strict_types=1);
require_once __DIR__.'/../app/bootstrap.php';
if (PHP_SAPI!=='cli') exit;
if (config('APP_ENV')!=='development' || !in_array('--confirm-demo',$argv,true)) { fwrite(STDERR,"Community demo data requires development mode and --confirm-demo.\n"); exit(1); }
$admin=one('SELECT id FROM users WHERE email="admin@sportshub.test" AND role="admin"');
if (!$admin) { echo "No demo administrator found; community seed skipped.\n"; return; }
foreach (['Green Warriors Cricket','NSBM Football Club','Campus Basketball','Athletics Collective'] as $name) {
 $club=one('SELECT id FROM clubs WHERE name=?',[$name]);
 if (!$club || one('SELECT id FROM club_posts WHERE club_id=? AND user_id=?',[$club['id'],$admin['id']])) continue;
 query('INSERT INTO club_posts(club_id,user_id,body) VALUES(?,?,?)',[$club['id'],$admin['id'],'Welcome to your club community! Share training updates, celebrate progress, and help your teammates feel at home. Check the Schedule tab for upcoming sessions.']);
}
$athlete=one('SELECT id FROM users WHERE email="athlete@sportshub.test"');
if ($athlete) {
 $membership=one('SELECT club_id FROM memberships WHERE user_id=? AND status="approved" ORDER BY id LIMIT 1',[$athlete['id']]);
 if ($membership && !one('SELECT id FROM club_posts WHERE club_id=? AND user_id=?',[$membership['club_id'],$athlete['id']])) query('INSERT INTO club_posts(club_id,user_id,body) VALUES(?,?,?)',[$membership['club_id'],$athlete['id'],'Looking forward to training with the team. What is one skill you want to improve this season? Let’s keep each other motivated.']);
}
echo "Development community welcome posts prepared; existing conversations preserved.\n";
