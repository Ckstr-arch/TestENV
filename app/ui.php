<?php
function icon(string $name): string {
 $paths=[
 'home'=>'<path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z"/>',
 'clubs'=>'<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m20 0v-2a4 4 0 0 0-3-3.87M16 3a4 4 0 0 1 0 8"/><circle cx="9" cy="7" r="4"/>',
 'calendar'=>'<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18m-14 4h3m4 0h3"/>',
 'trophy'=>'<path d="M8 3h8v6a4 4 0 0 1-8 0zM8 5H4v2a4 4 0 0 0 4 4m8-6h4v2a4 4 0 0 1-4 4m-4 2v5m-4 3h8m-10 0h12"/>',
 'booking'=>'<rect x="4" y="5" width="16" height="16" rx="2"/><path d="M8 3v4m8-4v4M4 11h16m-12 5 2 2 5-5"/>',
 'chart'=>'<path d="M4 3v18h17M8 16v-4m5 4V8m5 8V5"/>',
 'shield'=>'<path d="M12 3 3 7v5c0 5 9 9 9 9s9-4 9-9V7zM8 12l3 3 5-6"/>',
 'arrow'=>'<path d="M5 12h14m-5-5 5 5-5 5"/>',
 'sun'=>'<circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M2 12h2m16 0h2M5 5l1.5 1.5m11 11L19 19M5 19l1.5-1.5m11-11L19 5"/>',
 'moon'=>'<path d="M20.9 13A9 9 0 0 1 11 3.1 9 9 0 1 0 20.9 13z"/>',
 'menu'=>'<path d="M3 6h18M3 12h18M3 18h18"/>',
 'search'=>'<circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/>',
 'chat'=>'<path d="M21 11a8 8 0 0 1-8 8H6l-4 3V11a9.5 9.5 0 0 1 19 0zM7 10h9m-9 4h6"/>',
 'heart'=>'<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8z"/>',
 'box'=>'<path d="m12 3 9 5v9l-9 5-9-5V8zm0 10 9-5M3 8l9 5m0 0v9M7 5.8l10 5.5"/>',
 'chevron'=>'<path d="m6 9 6 6 6-6"/>',
 'clock'=>'<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
 'pin'=>'<path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="2.5"/>',
 ];
 return '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($paths[$name]??$paths['home']).'</svg>';
}
function initials(string $name): string { $parts=preg_split('/\s+/u',trim($name)); return mb_strtoupper(mb_substr($parts[0]??'',0,1).mb_substr($parts[1]??'',0,1)); }
function club_url(int $id,string $tab='community'): string { return '/?page=club&club_id='.$id.'&tab='.rawurlencode($tab); }
function community_form(string $action,int $clubId,int $postId=0,array $extra=[]): void {
 echo '<form method="post" class="community-form">'.csrf_field().'<input type="hidden" name="action" value="'.e($action).'"><input type="hidden" name="club_id" value="'.$clubId.'"><input type="hidden" name="post_id" value="'.$postId.'">';
 foreach ($extra as $key=>$value) echo '<input type="hidden" name="'.e($key).'" value="'.e($value).'">';
}
function metric(string $label,int $value,string $glyph,string $url): void { echo '<a class="metric card" href="'.e($url).'"><span class="metric-icon">'.icon($glyph).'</span><span><strong>'.$value.'</strong><small>'.e($label).'</small></span>'.icon('arrow').'</a>'; }
