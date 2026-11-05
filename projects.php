<?php require_once __DIR__ . '/includes/auth.php'; $user=require_role(['tenant_admin','manager','employee']); $pdo=db(); check_tenant_active($pdo); $tid=require_tenant(); $base=''; $active='projects'; $pageTitle='Projects';
if (is_post()) { verify_csrf(); $act=$_POST['action']??'';
  if($act==='delete') $pdo->prepare('DELETE FROM projects WHERE id=? AND tenant_id=?')->execute([(int)$_POST['id'],$tid]);
  elseif($act==='archive') { $pdo->prepare('UPDATE projects SET status="archived" WHERE id=? AND tenant_id=?')->execute([(int)$_POST['id'],$tid]); flash('success','Project archived.'); }
  else { $data=[trim($_POST['name']??''),$_POST['description']??null,$_POST['start_date']?:null,$_POST['end_date']?:null,(float)($_POST['budget']??0),$_POST['category']??'general',$_POST['priority']??'medium',$_POST['status']??'active',max(0,min(100,(int)($_POST['progress']??0)))];
    if(!empty($_POST['id'])) { $pdo->prepare('UPDATE projects SET name=?,description=?,start_date=?,end_date=?,budget=?,category=?,priority=?,status=?,progress=? WHERE id=? AND tenant_id=?')->execute([...$data,(int)$_POST['id'],$tid]); flash('success','Project updated.'); }
    else { $limits=subscription_limits($pdo); $c=$pdo->prepare('SELECT COUNT(*) FROM projects WHERE tenant_id=? AND status!="archived"'); $c->execute([$tid]); if($c->fetchColumn()>=(int)($limits['max_projects']??3)){flash('error','Project limit reached for your plan ('.($limits['max_projects']??3).'). Upgrade to add more.'); redirect('projects.php');}
      $pdo->prepare('INSERT INTO projects(tenant_id,name,description,start_date,end_date,budget,category,priority,status,progress,created_by,created_at) VALUES(?,?,?,?,?,?,?,?,?,?,?,NOW())')->execute([$tid,...$data,$user['id']]); $pid=(int)$pdo->lastInsertId();
      $pdo->prepare('INSERT IGNORE INTO project_members(tenant_id,project_id,user_id,role_in_project) VALUES(?,?,?,?)')->execute([$tid,$pid,$user['id'],'lead']);
      flash('success','Project created.'); log_activity($pdo,'Created project '.$data[0]); } }
  redirect('projects.php'); }
$q=trim($_GET['q']??''); $status=$_GET['status']??'';
$sql='SELECT p.*,(SELECT COUNT(*) FROM tasks t WHERE t.project_id=p.id) task_count,(SELECT COUNT(*) FROM tasks t WHERE t.project_id=p.id AND t.status="done") done_count FROM projects p WHERE p.tenant_id=?'; $args=[$tid];
if($q!==''){$sql.=' AND p.name LIKE ?'; $args[]="%$q%";} if($status!==''){$sql.=' AND p.status=?';$args[]=$status;} $sql.=' ORDER BY p.id DESC';
$s=$pdo->prepare($sql); $s->execute($args); $rows=$s->fetchAll(); $edit=null; if(!empty($_GET['edit'])){$s=$pdo->prepare('SELECT * FROM projects WHERE id=? AND tenant_id=?');$s->execute([(int)$_GET['edit'],$tid]);$edit=$s->fetch();}
include __DIR__ . '/includes/header.php'; ?>
<div class="card p-3 mb-3"><form class="row g-2" method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= e($edit['id']??'') ?>">
<div class="col-md-3"><input class="form-control" name="name" placeholder="Project name" required value="<?= e($edit['name']??'') ?>"></div>
<div class="col-md-3"><input class="form-control" name="category" placeholder="Category" value="<?= e($edit['category']??'') ?>"></div>
<div class="col-md-2"><select class="form-select" name="priority"><?php foreach(['low','medium','high','urgent'] as $c): ?><option <?= ($edit['priority']??'medium')===$c?'selected':'' ?>><?= $c ?></option><?php endforeach; ?></select></div>
<div class="col-md-2"><select class="form-select" name="status"><?php foreach(['planning','active','on_hold','completed','archived'] as $c): ?><option <?= ($edit['status']??'active')===$c?'selected':'' ?>><?= $c ?></option><?php endforeach; ?></select></div>
<div class="col-md-2"><input class="form-control" type="number" name="progress" min="0" max="100" placeholder="Progress %" value="<?= e($edit['progress']??'0') ?>"></div>
<div class="col-md-2"><label class="form-label small mb-0">Start</label><input class="form-control" type="date" name="start_date" value="<?= e($edit['start_date']??'') ?>"></div>
<div class="col-md-2"><label class="form-label small mb-0">End</label><input class="form-control" type="date" name="end_date" value="<?= e($edit['end_date']??'') ?>"></div>
<div class="col-md-2"><label class="form-label small mb-0">Budget</label><input class="form-control" type="number" step="0.01" name="budget" value="<?= e($edit['budget']??'') ?>"></div>
<div class="col-md-4"><input class="form-control mt-4" name="description" placeholder="Description" value="<?= e($edit['description']??'') ?>"></div>
<div class="col-md-2"><button class="btn btn-primary w-100 mt-4"><?= $edit?'Update':'Add' ?> project</button></div></form></div>
<form class="row g-2 mb-3" method="get"><div class="col-md-5"><input class="form-control" name="q" value="<?= e($q) ?>" placeholder="Search projects"></div>
<div class="col-md-2"><select class="form-select" name="status"><option value="">All status</option><?php foreach(['planning','active','on_hold','completed','archived'] as $c): ?><option <?= $status===$c?'selected':'' ?>><?= $c ?></option><?php endforeach; ?></select></div>
<div class="col-md-2"><button class="btn btn-outline-primary w-100">Filter</button></div></form>
<div class="row g-3"><?php foreach($rows as $r): ?><div class="col-md-6 col-xl-4"><div class="card p-3 h-100"><div class="d-flex justify-content-between"><strong><?= e($r['name']) ?></strong><span class="badge bg-<?= $r['status']==='completed'?'success':($r['status']==='archived'?'dark':'primary') ?>"><?= e($r['status']) ?></span></div>
<p class="small text-muted mb-1"><?= e($r['description']??'') ?></p><div class="small"><?= e($r['category']) ?> · <?= e($r['priority']) ?> · <?= money($r['budget']) ?><br><?= e($r['start_date']??'') ?> → <?= e($r['end_date']??'') ?><br><?= $r['done_count'] ?>/<?= $r['task_count'] ?> tasks done</div>
<div class="progress mt-2"><div class="progress-bar" style="width:<?= (int)$r['progress'] ?>%"><?= (int)$r['progress'] ?>%</div></div>
<div class="mt-2 d-flex gap-1"><a class="btn btn-sm btn-outline-primary" href="tasks.php?project_id=<?= $r['id'] ?>">Tasks</a><a class="btn btn-sm btn-outline-secondary" href="?edit=<?= $r['id'] ?>">Edit</a>
<form method="post" class="d-inline"><?= csrf_field() ?><input type="hidden" name="action" value="archive"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-sm btn-outline-warning">Archive</button></form>
<form method="post" class="d-inline"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-sm btn-outline-danger" data-confirm="Delete project and its tasks?">Delete</button></form></div></div></div><?php endforeach; if(!$rows) echo '<p class="text-muted">No projects yet.</p>'; ?></div>
<?php include __DIR__ . '/includes/footer.php'; ?>
