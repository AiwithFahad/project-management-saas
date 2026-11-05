<?php $b = $base ?? ''; ?>
<nav class="sidebar d-flex flex-column p-3">
  <a class="brand text-white text-decoration-none fs-5 fw-bold mb-4" href="<?= $b ?>dashboard.php"><i class="bi bi-kanban"></i> ProjectPro</a>
  <?php foreach([['dashboard','dashboard.php','bi-speedometer2','Dashboard'],['projects','projects.php','bi-folder','Projects'],['board','board.php','bi-kanban','Kanban Board'],['tasks','tasks.php','bi-check2-square','Tasks'],['teams','teams.php','bi-people','Teams'],['calendar','calendar.php','bi-calendar3','Calendar'],['milestones','milestones.php','bi-flag','Milestones'],['timelogs','timelogs.php','bi-stopwatch','Time Tracking'],['reports','reports.php','bi-bar-chart','Reports'],['subscription','subscription.php','bi-credit-card','Subscription'],['settings','settings.php','bi-gear','Settings']] as [$k,$f,$i,$l]): ?>
  <a class="nav-link <?= ($active??'')===$k?'active':'' ?>" href="<?= $b ?><?= $f ?>"><i class="bi <?= $i ?>"></i> <?= $l ?></a><?php endforeach; ?>
  <div class="mt-auto small text-white-50"><?= e(current_user()['name'] ?? '') ?><br><?= e(current_user()['role'] ?? '') ?></div>
  <a class="nav-link" href="<?= $b ?>auth/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
</nav>
