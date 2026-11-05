<?php require_once __DIR__ . '/../includes/auth.php'; header('Content-Type: application/json');
$user = require_login(); if (!is_post()) { http_response_code(405); exit(json_encode(['error'=>'POST required'])); }
verify_csrf(); $pdo=db(); $tid=require_tenant();
$taskId=(int)($_POST['task_id']??0); $status=$_POST['status']??'';
$allowed=['todo','in_progress','review','done'];
if (!in_array($status,$allowed,true)) { http_response_code(422); exit(json_encode(['error'=>'Invalid status'])); }
$s=$pdo->prepare('UPDATE tasks SET status=? WHERE id=? AND tenant_id=?'); $s->execute([$status,$taskId,$tid]);
if($s->rowCount()>=0 && $taskId){ $chk=$pdo->prepare('SELECT id FROM tasks WHERE id=? AND tenant_id=?'); $chk->execute([$taskId,$tid]); if($chk->fetch()){ audit_log($pdo,'task_status_update','tasks',$taskId); echo json_encode(['ok'=>true,'status'=>$status]); exit; } }
http_response_code(404); echo json_encode(['error'=>'Task not found']);
