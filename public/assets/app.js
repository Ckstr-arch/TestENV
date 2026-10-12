'use strict';
const menu = document.querySelector('.menu-toggle');
menu?.addEventListener('click', () => {
  const expanded = menu.getAttribute('aria-expanded') === 'true';
  menu.setAttribute('aria-expanded', String(!expanded));
  document.getElementById('navigation')?.classList.toggle('open', !expanded);
});
document.getElementById('print-report')?.addEventListener('click', () => window.print());

function endpoint(action, fields) {
  const routes = {
    register: ['POST', '/auth/register'], login: ['POST', '/auth/login'],
    verify: ['POST', '/auth/2fa'], logout: ['POST', '/auth/logout'],
    setup_2fa: ['POST', '/me/2fa/setup'], enable_2fa: ['POST', '/me/2fa'],
    disable_2fa: ['DELETE', '/me/2fa'], book: ['POST', '/bookings'],
    join: ['POST', `/clubs/${fields.id}/memberships`],
    register_tournament: ['POST', `/tournaments/${fields.id}/registrations`],
    attend: ['POST', `/events/${fields.id}/attendance`],
    cancel: ['DELETE', `/me/${fields.entity}/${fields.id}`],
    review: ['PATCH', `/admin/${fields.entity}/${fields.id}`],
    attendance: ['PATCH', `/admin/attendance/${fields.id}`],
    save: [Number(fields.id) ? 'PUT' : 'POST', `/admin/${fields.entity}${Number(fields.id) ? `/${fields.id}` : ''}`],
    delete: ['DELETE', `/admin/${fields.entity}/${fields.id}`]
  };
  return routes[action];
}
function showRecoveryCodes(codes) {
  return new Promise(resolve => {
    const dialog = document.createElement('dialog');
    dialog.className = 'recovery-dialog';
    const title = document.createElement('h2'); title.textContent = 'Save your recovery codes';
    const note = document.createElement('p');
    note.textContent = 'Each code works once. Store these securely outside SportsHub. They will not be shown again.';
    const grid = document.createElement('div'); grid.className = 'recovery-grid';
    codes.forEach(value => { const code = document.createElement('code'); code.textContent = value; grid.append(code); });
    const button = document.createElement('button'); button.className = 'button full'; button.textContent = 'I have saved my codes';
    dialog.append(title, note, grid, button); document.body.append(dialog);
    dialog.addEventListener('cancel', event => event.preventDefault());
    button.addEventListener('click', () => { dialog.close(); dialog.remove(); resolve(); }, {once: true});
    dialog.showModal(); button.focus();
  });
}
document.querySelectorAll('form[method="post"]').forEach(form => {
  form.addEventListener('submit', async event => {
    const fields = Object.fromEntries(new FormData(form));
    const route = endpoint(fields.action, fields);
    if (!route) return; // Existing native form behavior remains for any unmapped extension.
    event.preventDefault();
    if (form.dataset.confirm && !window.confirm(form.dataset.confirm)) return;
    if (form.dataset.busy) return;
    const [method, path] = route;
    const action = fields.action;
    const token = fields.csrf;
    delete fields.action; delete fields.csrf; delete fields.id; delete fields.entity;
    if (action === 'attendance') fields.status = 'attended';
    for (const name of ['capacity', 'club_id', 'tournament_id', 'resource_id', 'home_score', 'away_score']) {
      if (Object.hasOwn(fields, name)) fields[name] = fields[name] === '' ? null : Number(fields[name]);
    }
    const buttons = [...form.querySelectorAll('button[type="submit"],button:not([type])')];
    buttons.forEach(button => { button.disabled = true; }); form.dataset.busy = 'true';
    let alert = form.querySelector('.api-alert');
    if (!alert) { alert = document.createElement('div'); alert.className = 'alert error api-alert'; alert.setAttribute('role', 'alert'); form.prepend(alert); }
    alert.hidden = true;
    try {
      const response = await fetch('/api/v1' + path, {
        method, credentials: 'same-origin',
        headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-Token': token},
        body: JSON.stringify(fields)
      });
      const result = await response.json();
      if (result.meta?.csrf_token) document.querySelectorAll('input[name="csrf"]').forEach(input => { input.value = result.meta.csrf_token; });
      if (!response.ok) {
        alert.textContent = result.error?.message || 'The request could not be completed.';
        alert.hidden = false;
        if (response.status === 401) {
          const link = document.createElement('a'); link.href = '/?page=login'; link.textContent = ' Sign in again.'; alert.append(link);
        }
        return;
      }
      // Codes stay in memory only; never localStorage, browser logs or query strings.
      if (result.data?.recovery_codes) await showRecoveryCodes(result.data.recovery_codes);
      const redirect = result.meta?.redirect;
      if (redirect && redirect.startsWith('/?page=')) window.location.assign(redirect);
      else window.location.reload();
    } catch {
      // Do not replay failed mutations automatically: the server may have accepted them.
      alert.textContent = 'The response could not be received. Check your activity before retrying.';
      alert.hidden = false;
    } finally {
      buttons.forEach(button => { button.disabled = false; }); delete form.dataset.busy;
    }
  });
});
