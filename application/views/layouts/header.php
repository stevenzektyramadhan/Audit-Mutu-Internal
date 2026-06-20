<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$title = isset($title) ? $title : 'Dashboard AMI';
$page_title = isset($page_title) ? $page_title : 'Dashboard';
$page_subtitle = isset($page_subtitle) ? $page_subtitle : 'Audit Mutu Internal Perguruan Tinggi';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo html_escape($title); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --ami-bg: #161616;
            --ami-panel: #222222;
            --ami-sidebar: #172033;
            --ami-sidebar-soft: rgba(255, 255, 255, 0.075);
            --ami-text: #e1e3e6;
            --ami-muted: #a1a5ab;
            --ami-border: #33373e;
            --ami-blue: #185fa5;
            --ami-green: #3b6d11;
            --ami-amber: #854f0b;
            --ami-rose: #993556;
            --ami-teal: #0f6e56;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--ami-bg);
            color: var(--ami-text);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            font-size: 14px;
            letter-spacing: 0;
        }

        a,
        a:hover {
            text-decoration: none;
        }

        .ami-app {
            min-height: 100vh;
            display: flex;
        }

        .ami-sidebar {
            width: 248px;
            flex: 0 0 248px;
            background: var(--ami-sidebar);
            color: #ffffff;
            display: flex;
            flex-direction: column;
        }

        .ami-brand {
            min-height: 72px;
            padding: 16px 18px;
            border-bottom: 1px solid var(--ami-sidebar-soft);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .ami-logo {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: var(--ami-blue);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 36px;
        }

        .ami-brand-title {
            color: #ffffff;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.22;
        }

        .ami-user {
            padding: 14px 18px;
            border-bottom: 1px solid var(--ami-sidebar-soft);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .ami-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: #dbeafe;
            color: var(--ami-blue);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 12px;
            flex: 0 0 34px;
        }

        .ami-user-name {
            color: #eef4ff;
            font-weight: 600;
            font-size: 13px;
            line-height: 1.2;
            max-width: 154px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .ami-user-role {
            color: rgba(255, 255, 255, 0.55);
            font-size: 11px;
            margin-top: 2px;
        }

        .ami-nav {
            padding: 12px 0;
            flex: 1;
        }

        .ami-nav-label {
            color: rgba(255, 255, 255, 0.36);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            padding: 8px 18px 6px;
        }

        .ami-nav-link {
            min-height: 40px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 18px;
            color: rgba(255, 255, 255, 0.68);
            border-left: 3px solid transparent;
            transition: background .12s ease, color .12s ease, border-color .12s ease;
        }

        .ami-nav-link:hover {
            background: rgba(255, 255, 255, 0.055);
            color: #ffffff;
        }

        .ami-nav-link.active {
            background: rgba(24, 95, 165, 0.33);
            color: #ffffff;
            border-left-color: #4da3ff;
        }

        .ami-nav-link i {
            width: 18px;
            text-align: center;
            font-size: 14px;
        }

        .ami-nav-badge {
            margin-left: auto;
            min-width: 22px;
            height: 20px;
            border-radius: 999px;
            background: var(--ami-blue);
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 7px;
            font-size: 11px;
            font-weight: 700;
        }

        .ami-logout {
            padding: 12px 18px;
            border-top: 1px solid var(--ami-sidebar-soft);
        }

        .ami-main {
            flex: 1;
            min-width: 0;
        }

        .ami-topbar {
            min-height: 72px;
            background: var(--ami-panel);
            border-bottom: 1px solid var(--ami-border);
            padding: 15px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .ami-page-title {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            color: var(--ami-text);
        }

        .ami-page-subtitle {
            color: var(--ami-muted);
            font-size: 12px;
            margin-top: 2px;
        }

        .ami-content {
            padding: 24px;
        }

        .ami-stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            gap: 14px;
            margin-bottom: 20px;
        }

        .ami-stat-card,
        .ami-panel,
        .ami-task-card {
            background: var(--ami-panel);
            border: 1px solid var(--ami-border);
            border-radius: 8px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
        }

        .ami-stat-card {
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 13px;
            min-height: 86px;
        }

        .ami-stat-icon,
        .ami-task-icon {
            width: 42px;
            height: 42px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 42px;
        }

        .tone-blue {
            background: #e6f1fb;
            color: var(--ami-blue);
        }

        .tone-green {
            background: #eaf3de;
            color: var(--ami-green);
        }

        .tone-amber {
            background: #faeeda;
            color: var(--ami-amber);
        }

        .tone-rose {
            background: #fbeaf0;
            color: var(--ami-rose);
        }

        .tone-teal {
            background: #e1f5ee;
            color: var(--ami-teal);
        }

        .tone-violet {
            background: #eeedfe;
            color: #534ab7;
        }

        .ami-stat-label {
            color: var(--ami-muted);
            font-size: 12px;
            margin-bottom: 2px;
        }

        .ami-stat-value {
            color: var(--ami-text);
            font-size: 25px;
            font-weight: 700;
            line-height: 1;
        }

        .ami-section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin: 22px 0 10px;
        }

        .ami-section-title {
            margin: 0;
            font-size: 15px;
            font-weight: 700;
            color: var(--ami-text);
        }

        .ami-panel {
            overflow: hidden;
        }

        .ami-panel-body {
            padding: 16px;
        }

        .ami-table {
            margin: 0;
            font-size: 13px;
        }

        .ami-table th {
            border-top: 0;
            border-bottom: 1px solid var(--ami-border);
            background: rgba(255, 255, 255, 0.03);
            color: var(--ami-muted);
            font-size: 12px;
            font-weight: 700;
        }

        .ami-table td {
            vertical-align: middle;
            border-top: 1px solid var(--ami-border);
        }

        .ami-status {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            border-radius: 999px;
            padding: 4px 9px;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .status-belum_diisi {
            background: #faeeda;
            color: var(--ami-amber);
        }

        .status-diisi {
            background: #e6f1fb;
            color: var(--ami-blue);
        }

        .status-dinilai {
            background: #eaf3de;
            color: var(--ami-green);
        }

        .ami-task-card {
            padding: 15px;
            display: flex;
            align-items: flex-start;
            gap: 13px;
            margin-bottom: 10px;
        }

        .ami-task-main {
            min-width: 0;
            flex: 1;
        }

        .ami-task-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--ami-text);
            margin-bottom: 3px;
        }

        .ami-task-meta {
            color: var(--ami-muted);
            font-size: 12px;
            line-height: 1.4;
        }

        .ami-empty {
            padding: 26px 18px;
            color: var(--ami-muted);
            text-align: center;
            font-size: 13px;
        }

        .ami-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .btn-ami {
            border-radius: 7px;
            font-weight: 700;
            font-size: 13px;
            padding: 8px 12px;
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }

        .btn-outline-ami {
            background: transparent;
            border: 1px solid var(--ami-border);
            color: var(--ami-text);
            transition: all 0.2s;
        }

        .btn-outline-ami:hover {
            background: rgba(255, 255, 255, 0.05);
            color: #ffffff;
        }

        .action-link {
            font-size: 13px;
            text-decoration: none;
        }
        
        .action-link.edit {
            color: var(--ami-blue);
        }
        
        .action-link.delete {
            color: var(--ami-rose);
        }

        @media (max-width: 991.98px) {
            .ami-app {
                display: block;
            }

            .ami-sidebar {
                width: 100%;
                flex: none;
            }

            .ami-nav {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
                padding-bottom: 8px;
            }

            .ami-nav-label,
            .ami-logout {
                grid-column: 1 / -1;
            }

            .ami-content,
            .ami-topbar {
                padding-left: 16px;
                padding-right: 16px;
            }
        }

        @media (max-width: 575.98px) {
            .ami-topbar {
                align-items: flex-start;
                flex-direction: column;
            }

            .ami-content {
                padding-top: 16px;
            }

            .ami-stat-grid {
                grid-template-columns: 1fr;
            }

            .ami-task-card {
                flex-direction: column;
            }

            .ami-section-head {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
<div class="ami-app">
