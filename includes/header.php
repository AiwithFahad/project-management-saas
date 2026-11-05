<?php $b = $base ?? ''; ?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($pageTitle ?? APP_NAME) ?> | <?= e(APP_NAME) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="<?= $b ?>assets/css/style.css" rel="stylesheet">
<script>try{var t=localStorage.getItem('pm-theme');if(t==='dark'){document.documentElement.dataset.theme='dark';}var sc=localStorage.getItem('pm-sidebar');if(sc==='collapsed'){document.documentElement.dataset.sidebar='collapsed';}}catch(e){}</script></head>
<body><div class="layout"><?php include __DIR__ . '/sidebar.php'; ?><main class="main p-4">
<?php $cu = function_exists('current_user') ? (current_user() ?? []) : []; $cuName = trim((string)($cu['name'] ?? 'U')); $ini = ''; foreach (preg_split('/\s+/', $cuName) as $w) { if ($w !== '') $ini .= strtoupper($w[0]); if (strlen($ini) >= 2) break; } ?>
<header class="topbar">
<button class="icon-btn" id="sidebarToggle" type="button" title="Collapse sidebar" aria-label="Collapse sidebar"><i class="bi bi-layout-sidebar-inset"></i></button>
<h1 class="page-title"><?= e($pageTitle ?? '') ?></h1>
<form class="search-wrap" method="get" action="<?= $b ?>projects.php" role="search"><i class="bi bi-search"></i><input class="form-control" type="search" name="q" placeholder="Search projects…" aria-label="Search projects"></form>
<div class="dropdown"><button class="icon-btn" type="button" data-bs-toggle="dropdown" aria-label="Notifications"><i class="bi bi-bell"></i><span class="dot"></span></button>
<ul class="dropdown-menu dropdown-menu-end"><li><span class="dropdown-item-text small text-muted">You're all caught up.<br>New activity will appear here.</span></li></ul></div>
<button class="icon-btn" id="themeToggle" type="button" title="Toggle theme" aria-label="Toggle theme"><i class="bi bi-moon"></i></button>
<span class="avatar" title="<?= e($cuName) ?>"><?= e($ini ?: 'U') ?></span>
</header>
<?php if ($m = flash('success')): ?><div class="alert alert-success"><?= e($m) ?></div><?php endif; ?>
<?php if ($m = flash('error')): ?><div class="alert alert-danger"><?= e($m) ?></div><?php endif; ?>
