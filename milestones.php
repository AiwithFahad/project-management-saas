<?php require_once __DIR__ . '/includes/auth.php'; $user=require_role(['tenant_admin','manager','employee']); $pdo=db(); check_tenant_active($pdo); $tid=require_tenant(); $base=''; $active='milestones'; $pageTitle='Milestones';
if (is_post()) { verify_csrf(); $act=$_POST['action']??'';
  if($act==='delete') $pdo->prepare('DELETE FROM milestones WHERE id=? AND tenant_id=?')->execute([(int)$_POST['id'],$tid]);
  elseif($act==='complete') $pdo->prepare('UPDATE milestones SET is_completed=1,completed_at=NOW() WHERE id=? AND tenant_id=?')->execute([(int)$_POST['id'],$tid]);
  else { $data=[(int)$_POST['project_id'],trim($_POST['title']??''),$_POST['description']??null,$_POST['due_date']?:null,!empty($_POST['is_completed'])?1:0];
    if(!empty($_POST['id'])) $pdo->prepare('UPDATE milestones SET project_id=?,title=?,description=?,due_date=?,is_completed=? WHERE id=? AND tenant_id=?')->execute([...$data,(int)$_POST['id'],$tid]);
    else $pdo->prepare('INSERT INTO milestones(tenant_id,project_id,title,description,due_date,is_completed,created_at) VALUES(?,?,?,?,?,?,NOW())')->execute([$tid,...$data]);
    flash('success','Milestone saved.'); }
  redirect('milestones.php'); }
$s=$pdo->prepare('SELECT m.*,p.name project_name FROM milestones m JOIN projects p ON p.id=m.project_id WHERE m.tenant_id=? ORDER BY m.due_date IS NULL,m.due_date'); $s->execute([$tid]); $rows=$s->fetchAll();
$s=$pdo->prepare('SELECT id,name FROM projects WHERE tenant_id=? AND status!="archived" ORDER BY name'); $s->execute([$tid]); $projects=$s->fetchAll();
$edit=null; if(!empty($_GET['edit'])){$s=$pdo->prepare('SELECT * FROM milestones WHERE id=? AND tenant_id=?');$s->execute([(int)$_GET['edit'],$tid]);$edit=$s->fetch();}
include __DIR__ . '/includes/header.php'; ?>
<div class="card p-3 mb-3"><form class="row g-2" method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= e($edit['id']??'') ?>">
<div class="col-md-3"><select class="form-select" name="project_id" required><?php foreach($projects as $p): ?><option value="<?= $p['id'] ?>" <?= ($edit['project_id']??'')==$p['id']?'selected':'' ?>><?= e($p['name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-3"><input class="form-control" name="title" placeholder="Milestone title" required value="<?= e($edit['title']??'') ?>"></div>
<div class="col-md-2"><input class="form-control" type="date" name="due_date" value="<?= e($edit['due_date']??'') ?>"></div>
<div class="col-md-2"><input class="form-control" name="description" placeholder="Description" value="<?= e($edit['description']??'') ?>"></div>
<div class="col-md-1 form-check pt-2"><input class="form-check-input" type="checkbox" name="is_completed" id="mc" <?= !empty($edit['is_completed'])?'checked':'' ?>><label for="mc">Done</label></div>
<div class="col-md-1"><button class="btn btn-primary w-100"><?= $edit?'Update':'Add' ?></button></div></form></div>
<div class="card p-0 overflow-auto"><table class="table table-hover mb-0"><tr><th>Milestone</th><th>Project</th><th>Due</th><th>Status</th><th></th></tr>
<?php foreach($rows as $r): ?><tr><td><?= e($r['title']) ?><div class="small text-muted"><?= e($r['description']??'') ?></div></td><td><?= e($r['project_name']) ?></td><td><?= e($r['due_date']??'') ?></td><td><?= $r['is_completed']?'<span class="badge bg-success">Completed</span>':'<span class="badge bg-secondary">Open</span>' ?></td>
<td class="text-end text-nowrap"><?php if(!$r['is_completed']): ?><form method="post" class="d-inline"><?= csrf_field() ?><input type="hidden" name="action" value="complete"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-sm btn-outline-success">Complete</button></form><?php endif; ?> <a class="btn btn-sm btn-outline-primary" href="?edit=<?= $r['id'] ?>">Edit</a> <form method="post" class="d-inline"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-sm btn-outline-danger" data-confirm="Delete milestone?">Delete</button></form></td></tr><?php endforeach; ?></table></div>
<?php include __DIR__ . '/includes/footer.php'; ?>
