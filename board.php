<?php require_once __DIR__ . '/includes/auth.php'; $user=require_role(['tenant_admin','manager','employee']); $pdo=db(); check_tenant_active($pdo); $tid=require_tenant(); $base=''; $active='board'; $pageTitle='Kanban Board';
$projFilter=(int)($_GET['project_id']??0);
$sql='SELECT t.*,u.name assignee,p.name project_name FROM tasks t LEFT JOIN users u ON u.id=t.assigned_to LEFT JOIN projects p ON p.id=t.project_id WHERE t.tenant_id=?'; $args=[$tid]; if($projFilter){$sql.=' AND t.project_id=?';$args[]=$projFilter;} $sql.=' ORDER BY t.due_date IS NULL,t.due_date';
$s=$pdo->prepare($sql); $s->execute($args); $tasks=$s->fetchAll();
$s=$pdo->prepare('SELECT id,name FROM projects WHERE tenant_id=? AND status!="archived" ORDER BY name'); $s->execute([$tid]); $projects=$s->fetchAll();
$cols=['todo'=>'To Do','in_progress'=>'In Progress','review'=>'Review','done'=>'Done'];
include __DIR__ . '/includes/header.php'; ?><meta name="csrf" content="<?= e(csrf_token()) ?>">
<form class="row g-2 mb-3" method="get"><div class="col-md-4"><select class="form-select" name="project_id"><option value="">All projects</option><?php foreach($projects as $p): ?><option value="<?= $p['id'] ?>" <?= $projFilter==$p['id']?'selected':'' ?>><?= e($p['name']) ?></option><?php endforeach; ?></select></div><div class="col-md-2"><button class="btn btn-outline-primary w-100">Filter</button></div><div class="col-md-2"><a class="btn btn-primary w-100" href="tasks.php">+ New task</a></div></form>
<div class="kanban"><?php foreach($cols as $key=>$label): ?><div class="kanban-col" data-status="<?= $key ?>"><h6><?= $label ?> <span class="badge bg-primary"><?= count(array_filter($tasks,fn($t)=>$t['status']===$key)) ?></span></h6><div class="col-body">
<?php foreach($tasks as $t): if($t['status']!==$key) continue; ?><div class="deal-card task-card" draggable="true" data-id="<?= $t['id'] ?>"><strong><?= e($t['title']) ?></strong><div class="small text-muted"><?= e($t['project_name']??'') ?></div>
<div class="small"><span class="badge bg-<?= $t['priority']==='urgent'?'danger':($t['priority']==='high'?'warning':'secondary') ?>"><?= e($t['priority']) ?></span> <?= e($t['assignee']??'Unassigned') ?><?php if($t['due_date']): ?> · <?= e($t['due_date']) ?><?php endif; ?></div>
<?php if($t['labels']): ?><div class="small text-primary"><?= e($t['labels']) ?></div><?php endif; ?></div><?php endforeach; ?>
</div></div><?php endforeach; ?></div>
<p class="small text-muted mt-3">Drag a card to another column — the new status is saved instantly via Fetch AJAX to <code>api/task_status_update.php</code>.</p>
<?php $extraJs='<script src="assets/js/kanban.js"></script>'; include __DIR__ . '/includes/footer.php'; ?>
