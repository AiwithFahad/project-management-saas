<?php require_once __DIR__ . '/includes/auth.php'; $user=require_role(['tenant_admin','manager','employee']); $pdo=db(); check_tenant_active($pdo); $tid=require_tenant(); $base=''; $active='teams'; $pageTitle='Teams';
if (is_post()) { verify_csrf(); $act=$_POST['action']??'';
  if($act==='delete') $pdo->prepare('DELETE FROM teams WHERE id=? AND tenant_id=?')->execute([(int)$_POST['id'],$tid]);
  elseif($act==='add_member') { $teamId=(int)$_POST['team_id']; $s=$pdo->prepare('SELECT id FROM teams WHERE id=? AND tenant_id=?'); $s->execute([$teamId,$tid]); if($s->fetch()) $pdo->prepare('INSERT IGNORE INTO team_members(tenant_id,team_id,user_id,role_in_team) VALUES(?,?,?,?)')->execute([$tid,$teamId,(int)$_POST['user_id'],$_POST['role_in_team']??'member']); flash('success','Member added.'); }
  elseif($act==='remove_member') $pdo->prepare('DELETE FROM team_members WHERE id=? AND tenant_id=?')->execute([(int)$_POST['id'],$tid]);
  elseif($act==='invite') { $teamId=(int)($_POST['team_id']??0)?:null; $token=bin2hex(random_bytes(16)); $pdo->prepare('INSERT INTO invites(tenant_id,team_id,email,token,status,invited_by,expires_at,created_at) VALUES(?,?,?,?, "pending",?,DATE_ADD(NOW(),INTERVAL 7 DAY),NOW())')->execute([$tid,$teamId,trim($_POST['email']??''),$token,$user['id']]); flash('success','Invite recorded (token '.$token.'). In production this invite link is emailed.'); }
  else { $data=[trim($_POST['name']??''),$_POST['description']??null,(int)($_POST['leader_id']??0)?:null];
    if(!empty($_POST['id'])) $pdo->prepare('UPDATE teams SET name=?,description=?,leader_id=? WHERE id=? AND tenant_id=?')->execute([...$data,(int)$_POST['id'],$tid]);
    else { $pdo->prepare('INSERT INTO teams(tenant_id,name,description,leader_id,created_at) VALUES(?,?,?,?,NOW())')->execute([$tid,...$data]); $newTeam=(int)$pdo->lastInsertId(); if($data[2]) $pdo->prepare('INSERT IGNORE INTO team_members(tenant_id,team_id,user_id,role_in_team) VALUES(?,?,?,?)')->execute([$tid,$newTeam,$data[2],'leader']); }
    flash('success','Team saved.'); }
  redirect('teams.php'); }
$s=$pdo->prepare('SELECT t.*,u.name leader_name FROM teams t LEFT JOIN users u ON u.id=t.leader_id WHERE t.tenant_id=? ORDER BY t.id DESC'); $s->execute([$tid]); $rows=$s->fetchAll();
$s=$pdo->prepare('SELECT id,name,email FROM users WHERE tenant_id=? ORDER BY name'); $s->execute([$tid]); $users=$s->fetchAll();
$s=$pdo->prepare('SELECT m.*,u.name member_name,u.email,tm.name team_name FROM team_members m JOIN users u ON u.id=m.user_id JOIN teams tm ON tm.id=m.team_id WHERE m.tenant_id=? ORDER BY tm.name,u.name'); $s->execute([$tid]); $members=$s->fetchAll();
$s=$pdo->prepare('SELECT i.*,t.name team_name FROM invites i LEFT JOIN teams t ON t.id=i.team_id WHERE i.tenant_id=? ORDER BY i.id DESC LIMIT 10'); $s->execute([$tid]); $invites=$s->fetchAll();
$edit=null; if(!empty($_GET['edit'])){$s=$pdo->prepare('SELECT * FROM teams WHERE id=? AND tenant_id=?');$s->execute([(int)$_GET['edit'],$tid]);$edit=$s->fetch();}
include __DIR__ . '/includes/header.php'; ?>
<div class="row g-3"><div class="col-lg-5"><div class="card p-3"><h6><?= $edit?'Edit':'New' ?> team</h6><form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= e($edit['id']??'') ?>">
<div class="mb-2"><input class="form-control" name="name" placeholder="Team name" required value="<?= e($edit['name']??'') ?>"></div>
<div class="mb-2"><input class="form-control" name="description" placeholder="Description" value="<?= e($edit['description']??'') ?>"></div>
<div class="mb-2"><select class="form-select" name="leader_id"><option value="">Team leader…</option><?php foreach($users as $u): ?><option value="<?= $u['id'] ?>" <?= ($edit['leader_id']??'')==$u['id']?'selected':'' ?>><?= e($u['name']) ?></option><?php endforeach; ?></select></div>
<button class="btn btn-primary w-100">Save team</button></form></div>
<div class="card p-3 mt-3"><h6>Invite member by email</h6><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="invite">
<div class="mb-2"><select class="form-select" name="team_id"><option value="">Any team</option><?php foreach($rows as $t): ?><option value="<?= $t['id'] ?>"><?= e($t['name']) ?></option><?php endforeach; ?></select></div>
<div class="mb-2"><input class="form-control" type="email" name="email" placeholder="person@example.com" required></div><button class="btn btn-outline-primary w-100">Send invite (records invite)</button></form>
<?php foreach($invites as $i): ?><div class="small border-bottom py-1"><?= e($i['email']) ?> · <?= e($i['team_name']??'—') ?> · <?= e($i['status']) ?></div><?php endforeach; ?></div></div>
<div class="col-lg-7"><div class="card p-3"><h6>Teams</h6><table class="table"><tr><th>Team</th><th>Leader</th><th></th></tr><?php foreach($rows as $r): ?><tr><td><strong><?= e($r['name']) ?></strong><div class="small text-muted"><?= e($r['description']??'') ?></div></td><td><?= e($r['leader_name']??'—') ?></td>
<td class="text-end text-nowrap"><a class="btn btn-sm btn-outline-primary" href="?edit=<?= $r['id'] ?>">Edit</a> <form method="post" class="d-inline"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-sm btn-outline-danger" data-confirm="Delete team?">Delete</button></form></td></tr><?php endforeach; ?></table>
<h6 class="mt-3">Members</h6><form class="row g-2 mb-2" method="post"><?= csrf_field() ?><input type="hidden" name="action" value="add_member">
<div class="col-md-4"><select class="form-select" name="team_id" required><?php foreach($rows as $t): ?><option value="<?= $t['id'] ?>"><?= e($t['name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-4"><select class="form-select" name="user_id" required><?php foreach($users as $u): ?><option value="<?= $u['id'] ?>"><?= e($u['name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-2"><input class="form-control" name="role_in_team" value="member"></div><div class="col-md-2"><button class="btn btn-primary w-100">Add</button></div></form>
<table class="table"><tr><th>Member</th><th>Team</th><th>Role</th><th></th></tr><?php foreach($members as $m): ?><tr><td><?= e($m['member_name']) ?> <span class="text-muted small"><?= e($m['email']) ?></span></td><td><?= e($m['team_name']) ?></td><td><?= e($m['role_in_team']) ?></td>
<td><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="remove_member"><input type="hidden" name="id" value="<?= $m['id'] ?>"><button class="btn btn-sm btn-outline-danger">Remove</button></form></td></tr><?php endforeach; ?></table></div></div></div>
<?php include __DIR__ . '/includes/footer.php'; ?>
