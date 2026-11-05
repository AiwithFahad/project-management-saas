function applyTheme(dark){document.body.classList.toggle('dark',dark);if(dark){document.documentElement.dataset.theme='dark';}else{delete document.documentElement.dataset.theme;}}
document.getElementById('themeToggle')?.addEventListener('click',()=>{const dark=!document.body.classList.contains('dark');applyTheme(dark);localStorage.setItem('pm-theme',dark?'dark':'light');});
if(localStorage.getItem('pm-theme')==='dark')applyTheme(true);
function applySidebar(collapsed){document.body.classList.toggle('sidebar-collapsed',collapsed);}
document.getElementById('sidebarToggle')?.addEventListener('click',()=>{const c=!document.body.classList.contains('sidebar-collapsed');applySidebar(c);localStorage.setItem('pm-sidebar',c?'collapsed':'open');});
if(localStorage.getItem('pm-sidebar')==='collapsed'||document.documentElement.dataset.sidebar==='collapsed')applySidebar(true);
document.querySelectorAll('[data-confirm]')?.forEach(el=>el.addEventListener('click',ev=>{if(!confirm(el.dataset.confirm))ev.preventDefault();}));
