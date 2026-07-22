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
    <script>
        (function () {
            try {
                var theme = localStorage.getItem('ami-theme');
                if (theme === 'light' || theme === 'dark') {
                    document.documentElement.setAttribute('data-theme', theme);
                }
            } catch (error) {}
        })();
    </script>
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
            --ami-link: #4da3ff;
            --ami-link-soft: rgba(77, 163, 255, 0.15);
            --ami-radius-sm: 7px;
            --ami-space-sm: 8px;
            --ami-space-md: 16px;
            --ami-space-lg: 24px;
        }

        html[data-theme="light"] {
            color-scheme: light;
            --ami-bg: #f3f5f8;
            --ami-panel: #ffffff;
            --ami-text: #202733;
            --ami-muted: #667085;
            --ami-border: #d7dde6;
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
            color-scheme: dark;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            font-size: 14px;
            letter-spacing: 0;
            transition: background-color .18s ease, color .18s ease;
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

        .ami-sidebar-close,
        .ami-menu-toggle,
        .ami-sidebar-overlay {
            display: none;
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

        .ami-logo-img {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            object-fit: contain;
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
            overflow: hidden;
            background: #dbeafe;
            color: var(--ami-blue);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 12px;
            flex: 0 0 34px;
        }

        .ami-avatar-image {
            display: block;
            width: 100%;
            height: 100%;
            max-width: 100%;
            object-fit: cover;
            border-radius: inherit;
        }

        .ami-avatar.avatar-auditor {
            background: #3b6d11;
            color: #ffffff;
        }

        .ami-avatar.avatar-auditee {
            background: #a15c05;
            color: #ffffff;
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

        .ami-topbar-heading {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .ami-menu-toggle,
        .ami-sidebar-close {
            border: 1px solid var(--ami-border);
            background: rgba(255, 255, 255, 0.04);
            color: var(--ami-text);
            border-radius: 7px;
            width: 38px;
            height: 38px;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .ami-theme-toggle {
            min-height: 38px;
            padding: 7px 11px;
            border: 1px solid var(--ami-border);
            border-radius: 7px;
            background: rgba(255, 255, 255, 0.04);
            color: var(--ami-text);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
        }

        .ami-theme-toggle:hover,
        .ami-theme-toggle:focus {
            border-color: var(--ami-link);
            color: var(--ami-link);
            outline: 0;
        }

        .ami-topbar-actions {
            display: flex;
            align-items: center;
            gap: var(--ami-space-sm);
        }

        .ami-account-toggle {
            min-height: 38px;
            padding: 4px var(--ami-space-sm) 4px 4px;
            border: 1px solid var(--ami-border);
            border-radius: var(--ami-radius-sm);
            background: rgba(255, 255, 255, 0.04);
            color: var(--ami-text);
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
        }

        .ami-account-toggle:hover,
        .ami-account-toggle:focus {
            border-color: var(--ami-link);
            color: var(--ami-link);
            outline: 0;
        }

        .ami-topbar-avatar {
            width: 28px;
            height: 28px;
            flex-basis: 28px;
            font-size: 10px;
        }

        .ami-account-menu {
            min-width: 170px;
            padding: 6px;
            border-color: var(--ami-border);
            border-radius: var(--ami-radius-sm);
            background: var(--ami-panel);
        }

        .ami-account-menu .dropdown-item {
            border-radius: var(--ami-radius-sm);
            color: var(--ami-text);
            font-size: 13px;
        }

        .ami-account-menu .dropdown-item:hover,
        .ami-account-menu .dropdown-item:focus {
            background: var(--ami-link-soft);
            color: var(--ami-link);
        }

        .ami-account-menu .dropdown-item i {
            width: 20px;
        }

        .ami-account-menu .dropdown-divider {
            border-top-color: var(--ami-border);
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
            color: var(--ami-text);
        }

        .ami-content a:not(.btn) {
            color: #4da3ff;
        }

        .ami-content a:not(.btn):hover {
            color: #82c0ff;
        }

        .ami-content .text-muted {
            color: var(--ami-muted) !important;
        }

        .ami-content .text-primary {
            color: #4da3ff !important;
        }

        .ami-content .text-success {
            color: #75c94b !important;
        }

        .ami-content .text-warning {
            color: #f0ad4e !important;
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
            color: var(--ami-text);
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
            color: var(--ami-text);
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
            color: var(--ami-text);
        }

        .ami-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.035);
            color: #ffffff;
        }

        .ami-table tbody tr:hover td {
            color: #ffffff;
        }

        .ami-content label,
        .ami-content .form-label {
            color: var(--ami-text);
        }

        .ami-content .form-control {
            background-color: #191b1f;
            border-color: #4a4f57;
            color: var(--ami-text);
        }

        .ami-content .form-control:focus {
            background-color: #191b1f;
            border-color: #4da3ff;
            color: #ffffff;
            box-shadow: 0 0 0 .2rem rgba(77, 163, 255, 0.18);
        }

        .ami-content .form-control::placeholder {
            color: #858b94;
            opacity: 1;
        }

        .ami-content .form-control:disabled,
        .ami-content .form-control[readonly] {
            background-color: #25282d;
            color: #c7cbd1;
            opacity: 1;
        }

        .ami-account-panel {
            max-width: 640px;
        }

        .ami-account-heading {
            display: flex;
            align-items: center;
            gap: var(--ami-space-md);
            margin-bottom: var(--ami-space-lg);
        }

        .ami-account-photo {
            width: 72px;
            height: 72px;
            flex: 0 0 72px;
            border: 1px solid var(--ami-border);
            border-radius: 50%;
            object-fit: cover;
        }

        .ami-account-initials {
            background: var(--ami-link-soft);
            color: var(--ami-blue);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            font-weight: 700;
        }

        .ami-content select.form-control option {
            background: #191b1f;
            color: var(--ami-text);
        }

        .ami-content .btn-primary,
        .ami-content .btn-primary:hover,
        .ami-content .btn-primary:focus {
            color: #ffffff;
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

        .status-draft {
            background: #e1f5ee;
            color: var(--ami-teal);
        }

        .status-submitted {
            background: #e6f1fb;
            color: var(--ami-blue);
        }

        .status-revisi {
            background: #faeeda;
            color: var(--ami-amber);
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

        .ami-empty-icon {
            width: 46px;
            height: 46px;
            margin: 0 auto 12px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(77, 163, 255, 0.12);
            color: #6bb5ff;
            font-size: 18px;
        }

        .ami-empty-title {
            color: var(--ami-text);
            font-weight: 700;
            margin-bottom: 4px;
        }

        .ami-empty .btn {
            margin-top: 14px;
        }

        .ami-flash {
            display: flex;
            align-items: center;
            gap: 9px;
            border-radius: 8px;
            margin-bottom: 18px;
            padding: 12px 14px;
            font-size: 13px;
        }

        .ami-flash-success {
            background: rgba(59, 109, 17, 0.2);
            border: 1px solid rgba(117, 201, 75, 0.38);
            color: #b9ea9f;
        }

        .ami-flash-error {
            background: rgba(153, 53, 86, 0.2);
            border: 1px solid rgba(235, 108, 150, 0.38);
            color: #ffc0d5;
        }

        .ami-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .ami-filter-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 10px;
            margin-bottom: 16px;
        }

        .ami-filter-grow {
            flex: 1 1 260px;
            min-width: 220px;
        }

        .ami-filter-select {
            flex: 0 1 190px;
            min-width: 170px;
        }

        .ami-filter-bar .ami-stat-label {
            display: block;
            margin-bottom: 5px;
        }

        .ami-loading-spinner {
            width: 14px;
            height: 14px;
            border: 2px solid rgba(255, 255, 255, 0.45);
            border-top-color: #ffffff;
            border-radius: 50%;
            animation: ami-spin .65s linear infinite;
        }

        @keyframes ami-spin {
            to { transform: rotate(360deg); }
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

        .ami-row-actions {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
        }

        .ami-action-btn {
            min-height: 32px;
            padding: 5px 9px;
            border-radius: 6px;
            border: 1px solid var(--ami-border);
            background: rgba(255, 255, 255, 0.025);
            color: var(--ami-text);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 700;
            line-height: 1;
        }

        .ami-action-btn:hover,
        .ami-action-btn:focus {
            background: rgba(77, 163, 255, 0.12);
            border-color: rgba(77, 163, 255, 0.55);
            color: #8bc7ff;
        }

        .ami-action-btn.danger {
            color: #ff8aae;
            border-color: rgba(255, 138, 174, 0.3);
        }

        .ami-action-btn.danger:hover,
        .ami-action-btn.danger:focus {
            background: rgba(153, 53, 86, 0.2);
            border-color: rgba(255, 138, 174, 0.55);
            color: #ffc0d5;
        }

        .ami-action-btn:disabled {
            opacity: .65;
            cursor: wait;
        }

        .ami-password-wrap {
            position: relative;
        }

        .ami-password-wrap .form-control {
            padding-right: 44px;
        }

        .ami-password-toggle {
            position: absolute;
            top: 50%;
            right: 5px;
            transform: translateY(-50%);
            width: 34px;
            height: 34px;
            border: 0;
            border-radius: 6px;
            background: transparent;
            color: var(--ami-muted);
            cursor: pointer;
        }

        .ami-password-toggle:hover,
        .ami-password-toggle:focus {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.07);
            outline: 0;
        }

        .ami-timeline {
            display: flex;
            padding: 8px 0 2px;
        }

        .ami-timeline-step {
            position: relative;
            flex: 1;
            text-align: center;
            color: var(--ami-muted);
            font-size: 12px;
        }

        .ami-timeline-step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 15px;
            left: calc(50% + 18px);
            right: calc(-50% + 18px);
            height: 2px;
            background: var(--ami-border);
        }

        .ami-timeline-step.is-complete:not(:last-child)::after {
            background: #4da3ff;
        }

        .ami-timeline-marker {
            position: relative;
            z-index: 1;
            width: 32px;
            height: 32px;
            margin: 0 auto 8px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #292c31;
            border: 2px solid var(--ami-border);
        }

        .ami-timeline-step.is-complete .ami-timeline-marker,
        .ami-timeline-step.is-current .ami-timeline-marker {
            background: var(--ami-blue);
            border-color: #64b5ff;
            color: #ffffff;
        }

        .ami-timeline-step.is-current {
            color: #ffffff;
            font-weight: 700;
        }

        .ami-timeline-step.is-complete {
            color: #a9d4ff;
        }

        html[data-theme="light"] .ami-stat-card,
        html[data-theme="light"] .ami-panel,
        html[data-theme="light"] .ami-task-card,
        html[data-theme="light"] .admin-summary-card {
            box-shadow: 0 8px 22px rgba(32, 39, 51, 0.07);
        }

        html[data-theme="light"] body {
            color-scheme: light;
        }

        html[data-theme="light"] .ami-content a:not(.btn) {
            color: var(--ami-blue);
        }

        html[data-theme="light"] .ami-content a:not(.btn):hover {
            color: #0e4a80;
        }

        html[data-theme="light"] .ami-content .text-primary {
            color: var(--ami-blue) !important;
        }

        html[data-theme="light"] .ami-content .text-success {
            color: var(--ami-green) !important;
        }

        html[data-theme="light"] .ami-content .text-warning {
            color: #995c08 !important;
        }

        html[data-theme="light"] .ami-table th {
            background: #f7f8fa;
        }

        html[data-theme="light"] .ami-table tbody tr:hover {
            background: #f6f9fc;
        }

        html[data-theme="light"] .ami-table tbody tr:hover td {
            color: var(--ami-text);
        }

        html[data-theme="light"] .ami-content .form-control,
        html[data-theme="light"] .ami-content .bg-dark {
            background-color: #ffffff !important;
            border-color: #bdc6d2 !important;
            color: var(--ami-text) !important;
        }

        html[data-theme="light"] .ami-content .form-control:focus {
            background-color: #ffffff !important;
            border-color: #4da3ff !important;
            color: var(--ami-text) !important;
        }

        html[data-theme="light"] .ami-content .form-control:disabled,
        html[data-theme="light"] .ami-content .form-control[readonly] {
            background-color: #eef1f5 !important;
            color: #596273 !important;
        }

        html[data-theme="light"] .ami-content select.form-control option {
            background: #ffffff;
            color: var(--ami-text);
        }

        html[data-theme="light"] .ami-content .text-light,
        html[data-theme="light"] .ami-content h1,
        html[data-theme="light"] .ami-content h2,
        html[data-theme="light"] .ami-content h3,
        html[data-theme="light"] .ami-content h4,
        html[data-theme="light"] .ami-content h5,
        html[data-theme="light"] .ami-content h6 {
            color: var(--ami-text) !important;
        }

        html[data-theme="light"] .ami-content .btn-secondary.text-light {
            color: var(--ami-text) !important;
        }

        html[data-theme="light"] .btn-outline-ami,
        html[data-theme="light"] .ami-action-btn,
        html[data-theme="light"] .ami-theme-toggle {
            background: #ffffff;
            color: #344054;
        }

        html[data-theme="light"] .btn-outline-ami:hover,
        html[data-theme="light"] .ami-action-btn:hover {
            background: #edf5fd;
            color: var(--ami-blue);
        }

        html[data-theme="light"] .ami-action-btn.danger {
            color: var(--ami-rose);
        }

        html[data-theme="light"] .ami-password-toggle:hover,
        html[data-theme="light"] .ami-password-toggle:focus {
            color: var(--ami-blue);
            background: #edf5fd;
        }

        html[data-theme="light"] .ami-timeline-marker {
            background: #f0f2f5;
        }

        html[data-theme="light"] .ami-timeline-step.is-current {
            color: var(--ami-blue);
        }

        html[data-theme="light"] .ami-timeline-step.is-complete {
            color: #397fbf;
        }

        html[data-theme="light"] .ami-flash-success {
            background: #edf8e8;
            color: #315d13;
        }

        html[data-theme="light"] .ami-flash-error {
            background: #fff0f4;
            color: #8c294b;
        }

        @media (max-width: 991.98px) {
            .ami-app {
                display: block;
            }

            .ami-sidebar {
                position: fixed;
                inset: 0 auto 0 0;
                z-index: 1040;
                width: 280px;
                max-width: 86vw;
                min-height: 100vh;
                overflow-y: auto;
                transform: translateX(-100%);
                transition: transform .22s ease;
                box-shadow: 14px 0 32px rgba(0, 0, 0, 0.35);
            }

            .ami-app.sidebar-open .ami-sidebar {
                transform: translateX(0);
            }

            .ami-sidebar-close,
            .ami-menu-toggle {
                display: inline-flex;
            }

            .ami-sidebar-close {
                margin-left: auto;
                border-color: var(--ami-sidebar-soft);
                color: #ffffff;
            }

            .ami-sidebar-overlay {
                position: fixed;
                inset: 0;
                z-index: 1030;
                width: 100%;
                height: 100%;
                border: 0;
                padding: 0;
                background: rgba(0, 0, 0, 0.58);
                opacity: 0;
                visibility: hidden;
                transition: opacity .2s ease, visibility .2s ease;
                cursor: default;
            }

            .ami-app.sidebar-open .ami-sidebar-overlay {
                display: block;
                opacity: 1;
                visibility: visible;
            }

            body.ami-sidebar-lock {
                overflow: hidden;
            }

            .ami-content,
            .ami-topbar {
                padding-left: 16px;
                padding-right: 16px;
            }
        }

        @media (max-width: 575.98px) {
            .ami-topbar {
                min-height: 64px;
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

            .ami-filter-grow,
            .ami-filter-select,
            .ami-filter-bar .btn {
                flex: 1 1 100%;
                width: 100%;
            }

            .ami-filter-bar .btn {
                justify-content: center;
            }

            .ami-timeline-label {
                font-size: 11px;
            }
        }
    </style>
</head>
<body>
<div class="ami-app">
