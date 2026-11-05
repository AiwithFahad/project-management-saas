# Project Management SaaS (ProjectPro)

Production-oriented, multi-tenant Project Management SaaS built exactly to the specified stack:
**HTML5, CSS3, Bootstrap 5, JavaScript (AJAX/Fetch), Core PHP 8+ (no Laravel/React/Vue/Node), MySQL via PDO, Chart.js, Bootstrap Icons.**

The secure multi-tenant foundation (auth, subscriptions, Super Admin) is reused from the CRM Management SaaS product and adapted for projects, tasks and teams.

## Features
- **Auth:** register (creates tenant + tenant-admin + 14-day trial on the Free plan flow, a default "General" team and a welcome project), login/logout, remember-me (hashed token cookie), forgot/reset password (hashed, expiring tokens), suspended account & suspended tenant checks, `password_hash`, secure sessions, CSRF on every POST, XSS escaping via `e()`.
- **Tenant isolation:** every tenant query is scoped by `tenant_id` from the session; the AJAX endpoint and comment/member actions verify related rows belong to the tenant.
- **Dashboard:** total / active / completed projects, pending + overdue tasks, team members, Chart.js project-progress chart, recent activities, upcoming deadlines — all from real PHP queries.
- **Projects:** CRUD with description, start/end dates, budget, category, priority, status, progress %, archive action, search/status filter, plan project-limit enforcement (from the subscription's plan).
- **Tasks & Kanban:** task CRUD with project, assignee, priority, deadline, labels (comma-separated, synced to `task_labels`), dependencies (`depends_on`), recurring flag, and task comments (`task_comments`). Drag-and-drop Kanban board (`board.php`) with columns **To Do / In Progress / Review / Done**; drops save instantly via Fetch AJAX to `api/task_status_update.php` (CSRF + tenant-verified).
- **Teams:** teams CRUD with team leader, member add/remove, and invite-by-email (writes an `invites` record with a token and 7-day expiry).
- **Calendar:** month view that renders task due dates, project end dates, milestones and events from the database; add/delete events and meetings.
- **Time tracking:** start/stop timer (starting creates an open `time_logs` row — any other open timer for the user is closed first; stopping computes elapsed minutes with `TIMESTAMPDIFF`), manual entries, billable flag, per-project time totals.
- **Milestones:** CRUD tied to projects, due dates, one-click complete.
- **Reports:** project performance, task completion by status (chart), time by project, team productivity, overdue task list, CSV export.
- **Settings:** workspace settings (per-tenant), profile update, password change, support-ticket view.
- **Subscriptions:** Free / Starter / **Business** / Enterprise, monthly & yearly, upgrade/downgrade writes a new subscription + payment + invoice in a transaction, trial handling, coupon check + coupon application on plan change (e.g. `WELCOME10`), invoices list.
- **Super Admin (`super-admin/`):** platform dashboard & stats, tenants list with suspend/reactivate, plans CRUD (users/projects/tasks limits), subscriptions + payments management, coupons CRUD, support tickets, announcements, platform settings, audit-log view.
- **Audit & activity logs** on key actions (logins, plan changes, tenant toggles, task status moves).

## Installation
1. Requirements: PHP 8.1+, MySQL 8+ (or MariaDB 10.4+), a web server (Apache/Nginx) or PHP built-in server.
2. Create the database and import the schema + demo data:
   ```bash
   mysql -u root -p < database.sql
   ```
   This creates database `project_saas` with all tables, foreign keys, indexes and demo seed data.
3. Configure the DB connection via environment variables (or edit `config/config.php` defaults):
   `DB_HOST`, `DB_NAME` (default `project_saas`), `DB_USER`, `DB_PASS`.
4. Serve the folder:
   ```bash
   php -S localhost:8000 -t .
   ```
5. Open `http://localhost:8000/auth/login.php`.

## Demo logins (password for all: `password`)
| Role | Email | URL after login |
|---|---|---|
| Super Admin | `super@projectpro.test` | `super-admin/index.php` |
| Tenant Admin (Demo Company) | `admin@demo.com` | `dashboard.php` |
| Manager | `manager@demo.com` | `dashboard.php` |

Demo coupon: `WELCOME10` (10% off). Demo data: 3 projects (Website Redesign, Mobile App MVP, Internal Docs Portal), 8 tasks across To Do / In Progress / Review / Done, 2 teams, 3 milestones, time logs and events. One seeded task ("Fix login redirect bug") is intentionally overdue so the dashboard/reports overdue views are visible.

## Project structure
```
config/        DB + app config (PDO, env-based)
includes/      functions (CSRF, escaping, audit, tenant helpers, plan limits), auth guards, header/footer/sidebar
auth/          login, register, logout, forgot/reset password
api/           AJAX endpoints (task status update for Kanban drag-and-drop)
super-admin/   platform dashboard, tenants, plans, subscriptions, coupons, tickets, announcements, settings
assets/        CSS + JS (theme, kanban drag-drop, charts)
database.sql   complete schema + demo seed data
vercel-demo/   static in-browser demo (see below)
dashboard.php  projects.php  board.php  tasks.php  teams.php  calendar.php
milestones.php timelogs.php  reports.php  subscription.php  settings.php
```

## Deployment notes
- **PHP/MySQL hosting (production app):** deploy to any PHP 8 + MySQL host / VPS (Apache/Nginx + PHP-FPM). Set env vars, import `database.sql`, point the web root at this folder, enable HTTPS (session cookie Secure flag activates automatically), and disable display_errors in production.
- **Payment integration:** payments/invoices are recorded in DB with a gateway-ready architecture — plug Stripe/PayPal credentials into the subscription change flow in `subscription.php` where the payment row is created, and mark payments `paid` only after gateway confirmation/webhook.
- **Vercel:** Vercel cannot run Core PHP/MySQL, so `vercel-demo/index.html` is a single self-contained static demo (Bootstrap 5 + Chart.js via CDN, data in localStorage) that mirrors ProjectPro — dashboard/stats/charts, projects CRUD, drag-drop Kanban, tasks CRUD, team view, and plans/billing. Deploy that folder to Vercel for a live demo link; keep the full PHP app on PHP hosting / GitHub.

## Security notes
PDO prepared statements everywhere, `password_hash`/`password_verify`, CSRF tokens, output escaping, secure + HttpOnly + SameSite session cookies, tenant-scoped queries, audit logging. Change demo passwords immediately in production and remove/rotate seeded accounts.
