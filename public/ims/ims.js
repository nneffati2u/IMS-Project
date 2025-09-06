
(function(){
  const sidebar = document.querySelector('.ims-sidebar');
  const overlay = document.querySelector('.ims-overlay');
  const btn = document.querySelector('[data-drawer-toggle]');
  const body = document.body;

  function openDrawer(){
    if(!sidebar) return;
    sidebar.classList.add('is-open');
    if(overlay){ overlay.classList.add('active'); }
    body.style.overflow = 'hidden';
    sidebar.setAttribute('aria-hidden','false');
  }
  function closeDrawer(){
    if(!sidebar) return;
    sidebar.classList.remove('is-open');
    if(overlay){ overlay.classList.remove('active'); }
    body.style.overflow = '';
    sidebar.setAttribute('aria-hidden','true');
  }

  if(btn){
    btn.addEventListener('click', (e)=>{
      e.preventDefault();
      if(sidebar.classList.contains('is-open')) closeDrawer(); else openDrawer();
    });
  }
  if(overlay){
    overlay.addEventListener('click', closeDrawer);
  }
  window.addEventListener('keydown', (e)=>{
    if(e.key==='Escape') closeDrawer();
  });

  // Apply sticky table header enhancement
  document.querySelectorAll('table.table, table.ims-table').forEach(tbl=>{
    tbl.parentElement?.classList.add('table-responsive');
  });

  // Export CSV helper for nearest table when clicking .ims-export-csv
  function tableToCSV(table){
    const rows = Array.from(table.querySelectorAll('tr'));
    return rows.map(tr => Array.from(tr.cells).map(td => {
      const t = (td.innerText || "").replace(/\n/g, " ").trim();
      const needsQuotes = /[",;\n]/.test(t);
      const escaped = t.replace(/"/g,'""');
      return needsQuotes ? `"${escaped}"` : escaped;
    }).join(";")).join("\n");
  }
  document.addEventListener('click', (e)=>{
    const btn = e.target.closest('.ims-export-csv');
    if(!btn) return;
    const table = btn.closest('.card, .container, body').querySelector('table');
    if(!table) return;
    const csv = tableToCSV(table);
    const blob = new Blob([csv], {type:'text/csv;charset=utf-8;'});
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = (btn.dataset.filename || 'export') + '.csv';
    document.body.appendChild(a);
    a.click();
    setTimeout(()=>{URL.revokeObjectURL(url); a.remove();}, 0);
  });

  // Ensure only main scrolls in desktop test (helper no-op here)
})();
