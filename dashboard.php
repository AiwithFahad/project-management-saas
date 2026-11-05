<?php require_once __DIR__ . '/includes/auth.php'; $user=require_role(['tenant_admin','manager','employee']); $pdo=db(); check_tenant_active($pdo); $tid=require_tenant(); $base=''; $active='dashboard'; $pageTitle='Dashboard';
function cnt(PDO $pdo,string $sql,int $tid,array $a=[]){$s=$pdo->prepare($sql);$s->execute([$tid,...$a]);return $s->fetchColumn();}
$totalProjects=cnt($pdo,'SELECT COUNT(*) FROM projects WHERE tenant_id=? AND status!="archived"',$tid);
$activeProjects=cnt($pdo,'SELECT COUNT(*) FROM projects WHERE tenant_id=? AND status="active"',$tid);
$completedProjects=cnt($pdo,'SELECT COUNT(*) FROM projects WHERE tenant_id=? AND status="completed"',$tid);
$pendingTasks=cnt($pdo,'SELECT COUNT(*) FROM tasks WHERE tenant_id=? AND status!="done"',$tid);
$overdueTasks=cnt($pdo,'SELECT COUNT(*) FROM tasks WHERE tenant_id=? AND status!="done" AND due_date<CURDATE()',$tid);
$teamMembers=cnt($pdo,'SELECT COUNT(*) FROM users WHERE tenant_id=? AND status="active"',$tid);
$s=$pdo->prepare('SELECT name,progress FROM projects WHERE tenant_id=? AND status!="archived" ORDER BY id DESC LIMIT 6'); $s->execute([$tid]); $prog=$s->fetchAll();
$s=$pdo->prepare('SELECT * FROM activities WHERE tenant_id=? ORDER BY id DESC LIMIT 8'); $s->execute([$tid]); $acts=$s->fetchAll();
$s=$pdo->prepare('SELECT t.title,t.due_date,t.priority,p.name project_name FROM tasks t LEFT JOIN projects p ON p.id=t.project_id WHERE t.tenant_id=? AND t.status!="done" AND t.due_date IS NOT NULL ORDER BY t.due_date LIMIT 6'); $s->execute([$tid]); $deadlines=$s->fetchAll();
include __DIR__ . '/includes/header.php'; ?>
<div class="row g-3">
<?php foreach ([['Total projects',$totalProjects,'bi-folder'],['Active projects',$activeProjects,'bi-play-circle'],['Completed',$completedProjects,'bi-check-circle'],['Pending tasks',$pendingTasks,'bi-clock'],['Overdue tasks',$overdueTasks,'bi-exclamation-triangle'],['Team members',$teamMembers,'bi-people']] as [$l,$v,$i]): ?>
<div class="col-md-4 col-xl-2"><div class="card stat-card p-3"><i class="bi <?= $i ?> fs-4 text-primary"></i><div class="fs-4 fw-bold"><?= e((string)$v) ?></div><div class="text-muted small"><?= e($l) ?></div></div></div><?php endforeach; ?>
</div>
<div class="row g-3 mt-1"><div class="col-lg-8"><div class="card p-3"><h6>Project progress (%)</h6><canvas id="progChart" height="120"></canvas>
<?php foreach($prog as $p): ?><div class="d-flex align-items-center gap-2 mt-2 small"><span style="width:180px" class="text-truncate"><?= e($p['name']) ?></span><div class="progress flex-grow-1"><div class="progress-bar" style="width:<?= (int)$p['progress'] ?>%"><?= (int)$p['progress'] ?>%</div></div></div><?php endforeach; ?></div>
<div class="card p-3 mt-3"><h6>Upcoming deadlines</h6><ul class="list-unstyled small mb-0"><?php foreach($deadlines as $d): ?><li class="border-bottom py-2"><strong><?= e($d['title']) ?></strong> · <?= e($d['project_name']??'') ?> <span class="badge bg-<?= $d['due_date']<date('Y-m-d')?'danger':'secondary' ?>"><?= e($d['due_date']) ?></span></li><?php endforeach; if(!$deadlines) echo '<li class="py-2 text-muted">No upcoming deadlines.</li>'; ?></ul></div></div>
<div class="col-lg-4"><div class="card p-3"><h6>Recent activities</h6><ul class="list-unstyled small mb-0"><?php foreach($acts as $a): ?><li class="border-bottom py-2"><?= e($a['description']) ?> <span class="text-muted">· <?= e($a['created_at']) ?></span></li><?php endforeach; if(!$acts) echo '<li class="py-2 text-muted">No activity yet.</li>'; ?></ul></div></div></div>
<?php $extraJs='<script>makeChart("progChart","bar",'.json_encode(array_column($prog,'name')).','.json_encode(array_map('intval',array_column($prog,'progress'))).',"Progress %");</script>'; include __DIR__ . '/includes/footer.php'; ?>
