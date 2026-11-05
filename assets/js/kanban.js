document.querySelectorAll('.task-card').forEach(card=>{
 card.addEventListener('dragstart',e=>e.dataTransfer.setData('text/plain',card.dataset.id));
});
document.querySelectorAll('.kanban-col').forEach(col=>{
 col.addEventListener('dragover',e=>e.preventDefault());
 col.addEventListener('drop',async e=>{e.preventDefault();const id=e.dataTransfer.getData('text/plain');const card=document.querySelector(`.task-card[data-id="${id}"]`);if(!card)return;col.querySelector('.col-body').appendChild(card);
  const fd=new FormData();fd.append('task_id',id);fd.append('status',col.dataset.status);fd.append('_csrf',document.querySelector('meta[name=csrf]')?.content||'');
  const r=await fetch('api/task_status_update.php',{method:'POST',body:fd}); if(!r.ok) alert('Task status update failed — reload to see the saved state.');});
});
