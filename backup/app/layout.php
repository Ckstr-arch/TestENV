<?php
require_once __DIR__.'/ui.php';
$isAdmin=$current && $current['role']==='admin';
$groups=$isAdmin ? [
 'Workspace'=>['admin'=>['Overview','home']],
 'Community'=>['manage_clubs'=>['Clubs','clubs'],'approvals'=>['Membership & booking approvals','shield'],'moderation'=>['Community moderation','chat']],
 'Competitions'=>['manage_events'=>['Training & matches','calendar'],'manage_tournaments'=>['Tournaments','trophy'],'results'=>['Results','chart']],
 'Resources'=>['manage_resources'=>['Facilities & equipment','box']],
 'Insights'=>['reports'=>['Reports & attendance','chart'],'audit'=>['Audit history','clock']],
] : [
 'Your workspace'=>['dashboard'=>['Overview','home'],'my_clubs'=>['My clubs','clubs'],'clubs'=>['Discover clubs','search'],'schedule'=>['Schedule','calendar'],'tournaments'=>['Tournaments','trophy'],'facilities'=>['Bookings','booking'],'activity'=>['Participation','chart'],'results'=>['Results','trophy']],
];
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="description" content="NSBM SportsHub: your team, your community, your next breakthrough."><meta name="color-scheme" content="dark light"><title>NSBM SportsHub · <?=e(ucwords(str_replace('_',' ',$page)))?></title><script src="/assets/theme-init.js"></script><link rel="stylesheet" href="/assets/app.css"><script src="/assets/app.js" defer></script></head>
<body class="<?=$current?'portal':'public-site'?>">
<a class="skip-link" href="#main">Skip to content</a>
<?php if ($current):?>
<aside class="sidebar" id="navigation" aria-label="<?=$isAdmin?'Administrator':'Student'?> navigation">
 <a class="brand" href="/?page=<?=$isAdmin?'admin':'dashboard'?>"><img src="/assets/nsbm.png" alt=""><span>NSBM <b>SportsHub</b><small><?=$isAdmin?'ADMIN WORKSPACE':'STUDENT PORTAL'?></small></span></a>
 <nav aria-label="Portal navigation"><?php foreach($groups as $group=>$items):?><div class="nav-group"><span class="nav-caption"><?=e($group)?></span><?php foreach($items as $key=>[$label,$glyph]): $active=$page===$key || ($key==='my_clubs' && $page==='club');?><a class="nav-item <?=$active?'active':''?>" href="/?page=<?=$key?>" <?=$active?'aria-current="page"':''?>><?=icon($glyph)?><span><?=e($label)?></span></a><?php endforeach;?></div><?php endforeach;?></nav>
 <div class="sidebar-footer"><span class="campus-line">NSBM GREEN UNIVERSITY</span><small>One campus. Every game.</small><a href="/?page=home">Explore SportsHub <?=icon('arrow')?></a></div>
</aside><button class="sidebar-scrim" aria-label="Close navigation" tabindex="-1" hidden></button>
<?php endif;?>
<div class="app-frame">
<header class="workspace-header">
 <?php if ($current):?><button type="button" class="menu-toggle icon-button" aria-expanded="false" aria-controls="navigation" aria-label="Toggle navigation"><?=icon('menu')?></button><div class="workspace-identity"><strong><?=e($isAdmin?'Sports office':'Your sporting journey')?></strong><small><?=e($page==='club' && $clubView?$clubView['name']:'NSBM Green University')?></small></div><form method="get" class="header-search"><input type="hidden" name="page" value="clubs"><label class="sr-only" for="global-search">Search clubs</label><?=icon('search')?><input id="global-search" name="q" placeholder="Search clubs and find your team…"></form>
 <?php else:?><a class="brand" href="/?page=home"><img src="/assets/nsbm.png" alt=""><span>NSBM <b>SportsHub</b></span></a><button type="button" class="menu-toggle icon-button" aria-expanded="false" aria-controls="navigation" aria-label="Toggle navigation"><?=icon('menu')?></button><nav class="public-nav" id="navigation" aria-label="Main navigation"><?php foreach(['home'=>'Home','clubs'=>'Clubs','schedule'=>'Schedule','tournaments'=>'Tournaments','results'=>'Results','facilities'=>'Facilities'] as $key=>$label):?><a href="/?page=<?=$key?>" <?=$page===$key?'aria-current="page"':''?>><?=$label?></a><?php endforeach;?></nav><?php endif;?>
 <div class="header-actions"><button class="theme-toggle" type="button" aria-label="Switch to light mode" aria-pressed="false"><span class="theme-sun"><?=icon('sun')?></span><span class="theme-moon"><?=icon('moon')?></span><span class="sr-only">Toggle color theme</span></button>
 <?php if($current):?><details class="profile-menu"><summary><span class="avatar"><?=e(initials($current['name']))?></span><span class="profile-name"><strong><?=e($current['name'])?></strong><small><?=$isAdmin?'Administrator':'Student athlete'?></small></span><?=icon('chevron')?></summary><div class="profile-dropdown"><p><?=e($current['email'])?></p><a href="/?page=security"><?=icon('shield')?> Account security</a><a href="/?page=activity"><?=icon('chart')?> My participation</a><form method="post"><?=csrf_field()?><input type="hidden" name="action" value="logout"><button class="text-button">Sign out</button></form></div></details>
 <?php else:?><a class="sign-in" href="/?page=login">Sign in</a><a class="button small" href="/?page=register">Join SportsHub <?=icon('arrow')?></a><?php endif;?></div>
</header>
<main id="main" tabindex="-1"><?php if($notice):?><div class="alert success" role="status"><?=e($notice)?></div><?php endif;?><?php if($error):?><div class="alert error" role="alert"><?=e($error)?></div><?php endif;?><?php require __DIR__.'/views.php';?></main>
<footer class="app-footer"><span>NSBM SportsHub <span class="footer-dot">·</span> Play with purpose.</span><span>All times in Sri Lanka · <?=date('Y')?></span></footer>
</div></body></html>
