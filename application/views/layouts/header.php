<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$app_short_name = isset($app_short_name) && trim((string) $app_short_name) !== '' ? trim((string) $app_short_name) : 'AMI';
$app_name = isset($app_name) && trim((string) $app_name) !== '' ? trim((string) $app_name) : 'Audit Mutu Internal';
$title = isset($title) ? $title : ('Dashboard ' . $app_short_name);
$page_title = isset($page_title) ? $page_title : 'Dashboard';
$page_subtitle = isset($page_subtitle) ? $page_subtitle : ($app_name . ' Perguruan Tinggi');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo html_escape($title); ?></title>
    <link rel="icon" href="<?php echo html_escape(base_url('favicon.ico')); ?>" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <?php if (isset($active_menu) && $active_menu === 'spmi_dashboard'): ?>
        <link rel="stylesheet" href="<?php echo html_escape(base_url('assets/css/spmi-dashboard.css')); ?>">
    <?php endif; ?>
    <?php if (isset($active_menu) && $active_menu === 'users'): ?>
        <link rel="stylesheet" href="<?php echo html_escape(base_url('assets/css/users-management.css')); ?>">
    <?php endif; ?>
    <?php if (isset($active_menu) && in_array($active_menu, ['organization', 'master_data_prodi_staf'], TRUE)): ?>
        <link rel="stylesheet" href="<?php echo html_escape(base_url('assets/css/organization.css')); ?>">
    <?php endif; ?>
    <?php if (isset($active_menu) && $active_menu === 'spmi_standards'): ?>
        <link rel="stylesheet" href="<?php echo html_escape(base_url('assets/css/standards-management.css')); ?>">
    <?php endif; ?>
    <?php if (isset($active_menu) && $active_menu === 'spmi_audits'): ?>
        <link rel="stylesheet" href="<?php echo html_escape(base_url('assets/css/spmi-audits.css') . '?v=' . (int) (file_exists(FCPATH . 'assets/css/spmi-audits.css') ? filemtime(FCPATH . 'assets/css/spmi-audits.css') : time())); ?>">
    <?php endif; ?>
    <?php if (isset($active_menu) && $active_menu === 'pertanyaan'): ?>
        <link rel="stylesheet" href="<?php echo html_escape(base_url('assets/css/pertanyaan.css') . '?v=' . (int) (file_exists(FCPATH . 'assets/css/pertanyaan.css') ? filemtime(FCPATH . 'assets/css/pertanyaan.css') : time())); ?>">
    <?php endif; ?>
    <?php if (isset($active_menu) && $active_menu === 'spmi_reports'): ?>
        <link rel="stylesheet" href="<?php echo html_escape(base_url('assets/css/spmi-reports.css')); ?>">
    <?php endif; ?>
    <?php if (isset($active_menu) && $active_menu === 'spmi_rtm'): ?>
        <link rel="stylesheet" href="<?php echo html_escape(base_url('assets/css/spmi-rtm.css')); ?>">
    <?php endif; ?>
    <?php if (isset($active_menu) && $active_menu === 'account'): ?>
        <link rel="stylesheet" href="<?php echo html_escape(base_url('assets/css/account.css')); ?>">
    <?php endif; ?>
    <?php if (isset($active_menu) && $active_menu === 'profil'): ?>
        <link rel="stylesheet" href="<?php echo html_escape(base_url('assets/css/institution-profile.css')); ?>">
    <?php endif; ?>
    <?php if (isset($active_menu) && in_array($active_menu, ['spmi_auditor_dashboard', 'spmi_assessment'], TRUE)): ?>
        <link rel="stylesheet" href="<?php echo html_escape(base_url('assets/css/spmi-auditor-workspace.css')); ?>">
    <?php endif; ?>
    <?php if (isset($active_menu) && in_array($active_menu, ['spmi_auditee_dashboard', 'spmi_workspace'], TRUE)): ?>
        <link rel="stylesheet" href="<?php echo html_escape(base_url('assets/css/spmi-auditee-workspace.css')); ?>">
    <?php endif; ?>
    <?php if (isset($active_menu) && $active_menu === 'spmi_ppepp_documents'): ?>
        <link rel="stylesheet" href="<?php echo html_escape(base_url('assets/css/spmi-ppepp-documents.css')); ?>">
    <?php endif; ?>
    <?php if (isset($active_menu) && $active_menu === 'upload_size_settings'): ?>
        <link rel="stylesheet" href="<?php echo html_escape(base_url('assets/css/upload-size-settings.css')); ?>">
    <?php endif; ?>
    <?php if (isset($active_menu) && $active_menu === 'system_settings'): ?>
        <link rel="stylesheet" href="<?php echo html_escape(base_url('assets/css/system-settings.css')); ?>">
    <?php endif; ?>
    <style>
        :root {
            color-scheme: light;
            --ami-bg: #f3f5f8;
            --ami-panel: #ffffff;
            --ami-sidebar: #172033;
            --ami-sidebar-soft: rgba(255, 255, 255, 0.075);
            --ami-text: #202733;
            --ami-muted: #667085;
            --ami-border: #d7dde6;
            --ami-blue: #185fa5;
            --ami-green: #3b6d11;
            --ami-amber: #854f0b;
            --ami-rose: #993556;
            --ami-teal: #0f6e56;
            --ami-link: #185fa5;
            --ami-link-soft: rgba(24, 95, 165, 0.1);
            --ami-radius-sm: 7px;
            --ami-space-sm: 8px;
            --ami-space-md: 16px;
            --ami-space-lg: 24px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--ami-bg);
            color: var(--ami-text);
            color-scheme: light;
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
            justify-content: space-between;
            gap: 10px;
        }

        .ami-brand-link {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
            flex: 1 1 auto;
            text-decoration: none !important;
        }

        .ami-brand-text {
            min-width: 0;
            flex: 1 1 auto;
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

        .ami-brand-subtitle {
            color: rgba(255, 255, 255, 0.65);
            font-size: 11px;
            line-height: 1.3;
            margin-top: 1px;
            word-break: break-word;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
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
            padding: var(--ami-space-xs) 0 var(--ami-space-sm);
            flex: 1;
        }

        .ami-nav-label {
            color: rgba(255, 255, 255, 0.40);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            padding: 12px 18px 4px;
        }

        .ami-nav-label:first-child {
            padding-top: 6px;
        }

        .ami-nav-subgroup-label {
            color: rgba(255, 255, 255, 0.32);
            font-size: 9px;
            font-weight: 700;
            letter-spacing: .09em;
            text-transform: uppercase;
            padding: 10px 18px 2px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .ami-nav-subgroup-label::after {
            content: "";
            flex: 1;
            height: 1px;
            background: rgba(255, 255, 255, 0.08);
        }

        .ami-nav-link {
            min-height: 44px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 18px;
            color: #94a3b8;
            font-size: 13px;
            font-weight: 500;
            border-left: 3px solid transparent;
            transition: background .12s ease, color .12s ease, border-color .12s ease;
        }

        .ami-nav-link:hover,
        .ami-nav-link:focus {
            background: rgba(255, 255, 255, 0.05);
            color: #f1f5f9;
            outline: 0;
            box-shadow: none;
        }

        .ami-nav-link.active {
            background: rgba(30, 58, 138, 0.45);
            color: #ffffff;
            font-weight: 600;
            border-left-color: #3b82f6;
            box-shadow: none;
        }

        .ami-nav-link.active:focus {
            box-shadow: inset 0 0 0 1px rgba(59, 130, 246, 0.5);
        }

        .ami-nav-link i {
            width: 18px;
            text-align: center;
            font-size: 13px;
            color: #94a3b8;
            transition: color .12s ease;
        }

        .ami-nav-link:hover i,
        .ami-nav-link:focus i,
        .ami-nav-link.active i {
            color: #ffffff;
        }

        .ami-nav-step {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            border-radius: 4px;
            background: rgba(255, 255, 255, 0.06);
            color: #94a3b8;
            font-size: 10px;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-weight: 600;
            letter-spacing: -0.02em;
            flex: 0 0 20px;
            transition: all .12s ease;
        }

        .ami-nav-link:hover .ami-nav-step,
        .ami-nav-link:focus .ami-nav-step {
            background: rgba(255, 255, 255, 0.12);
            color: #f8fafc;
        }

        .ami-nav-link.active .ami-nav-step,
        .ami-nav-disclosure.has-active-child .ami-nav-step {
            background: #2563eb;
            color: #ffffff;
            font-weight: 700;
        }

        .ami-nav-disclosure {
            width: 100%;
            background: transparent;
            background-color: transparent;
            border-top: 0;
            border-right: 0;
            border-bottom: 0;
            border-left: 3px solid transparent;
            text-align: left;
            cursor: pointer;
        }

        .ami-nav-disclosure.active {
            background: transparent;
            color: #cbd5e1;
            border-left-color: transparent;
            box-shadow: none;
        }

        .ami-nav-disclosure.has-active-child {
            color: #f8fafc;
            background: transparent;
            border-left-color: transparent;
            box-shadow: none;
        }

        .ami-nav-disclosure.has-active-child:hover,
        .ami-nav-disclosure.has-active-child:focus,
        .ami-nav-disclosure.active:hover,
        .ami-nav-disclosure.active:focus {
            background: rgba(255, 255, 255, 0.05);
            color: #ffffff;
            box-shadow: none;
        }

        .ami-nav-disclosure[aria-expanded="true"] {
            color: #f8fafc;
        }

        .ami-nav-disclosure .ami-nav-chevron {
            width: auto;
            margin-left: auto;
            font-size: 11px;
            color: #94a3b8;
            transition: transform .15s ease, color .12s ease;
        }

        .ami-nav-disclosure[aria-expanded="true"] .ami-nav-chevron {
            transform: rotate(180deg);
            color: #cbd5e1;
        }

        .ami-nav-disclosure:focus-visible,
        .ami-nav-sub-link:focus-visible {
            outline: 2px solid #3b82f6;
            outline-offset: -2px;
        }

        .ami-nav-submenu {
            list-style: none;
            margin: 0;
            padding: 2px 0 4px;
            position: relative;
        }

        .ami-nav-submenu::before {
            content: "";
            position: absolute;
            top: 4px;
            bottom: 6px;
            left: 27px;
            width: 1px;
            background: rgba(255, 255, 255, 0.1);
        }

        .ami-nav-sub-link {
            min-height: 40px;
            padding: 6px 18px 6px 42px;
            font-size: 12px;
            color: #94a3b8;
            border-left: 3px solid transparent;
            position: relative;
        }

        .ami-nav-sub-link[aria-current="page"] {
            color: #ffffff;
            font-weight: 600;
            background: rgba(30, 58, 138, 0.45);
            border-left-color: #3b82f6;
        }

        .ami-nav-sub-link:hover,
        .ami-nav-sub-link:focus {
            color: #f8fafc;
            background: rgba(255, 255, 255, 0.05);
            border-left-color: transparent;
        }

        .ami-nav-sub-link[aria-current="page"]:hover,
        .ami-nav-sub-link[aria-current="page"]:focus {
            border-left-color: #3b82f6;
        }

        .ami-nav-sub-link span {
            min-width: 0;
            overflow-wrap: anywhere;
        }

        .ppepp-heading {
            display: flex;
            align-items: flex-start;
            gap: var(--ami-space-md);
        }

        .ppepp-heading-icon {
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            border-radius: var(--ami-radius-sm);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--ami-link-soft);
            color: var(--ami-blue);
        }

        .ppepp-breadcrumb {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 6px;
            margin-bottom: 6px;
            color: var(--ami-muted);
            font-size: 12px;
        }

        .ppepp-breadcrumb a {
            color: var(--ami-blue) !important;
        }

        .ppepp-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            justify-content: space-between;
            gap: var(--ami-space-md);
            margin-bottom: var(--ami-space-lg);
        }

        .ppepp-search {
            flex: 1 1 260px;
            max-width: 360px;
        }

        .ppepp-search label {
            display: block;
            margin-bottom: 5px;
            font-size: 12px;
            font-weight: 700;
        }

        .ppepp-search-control {
            position: relative;
            display: flex;
            align-items: center;
        }

        .ppepp-search-control > i {
            position: absolute;
            left: 12px;
            color: var(--ami-muted);
            pointer-events: none;
        }

        .ppepp-search-control .form-control {
            padding-left: 34px;
            padding-right: 62px;
        }

        .ppepp-search-clear {
            position: absolute;
            right: 6px;
            min-height: 30px;
            padding: 4px 8px;
            border: 0;
            border-radius: 5px;
            background: var(--ami-link-soft);
            color: var(--ami-blue);
            font-size: 12px;
            font-weight: 700;
        }

        .ppepp-checklist-grid,
        .ppepp-document-grid {
            display: grid;
            gap: var(--ami-space-md);
        }

        .ppepp-checklist-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .ppepp-checklist-card,
        .ppepp-document-card {
            min-width: 0;
            border: 1px solid var(--ami-border);
            border-radius: var(--ami-radius-sm);
            background: var(--ami-panel);
        }

        .ppepp-checklist-card-inner {
            min-height: 78px;
            padding: var(--ami-space-md);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--ami-space-sm);
        }

        .ppepp-document-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .ppepp-document-card {
            padding: var(--ami-space-md);
            display: flex;
            flex-direction: column;
            gap: 10px;
            box-shadow: 0 8px 22px rgba(32, 39, 51, 0.07);
        }

        .ppepp-document-card-top,
        .ppepp-document-meta,
        .ppepp-document-types {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 7px;
        }

        .ppepp-document-card-top {
            justify-content: space-between;
            color: var(--ami-muted);
            font-size: 12px;
        }

        .ppepp-document-category {
            color: var(--ami-blue);
            font-weight: 700;
        }

        .ppepp-document-card h3 {
            margin: 0;
            font-size: 15px;
            line-height: 1.35;
            overflow-wrap: anywhere;
        }

        .ppepp-document-description {
            overflow-wrap: anywhere;
            line-height: 1.5;
            overflow: hidden;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 3;
            line-clamp: 3;
        }

        .ppepp-document-meta {
            margin: 0;
            gap: var(--ami-space-md);
        }

        .ppepp-document-meta div {
            min-width: 0;
        }

        .ppepp-document-meta dt {
            color: var(--ami-muted);
            font-size: 11px;
            font-weight: 700;
        }

        .ppepp-document-meta dd {
            margin: 2px 0 0;
            font-size: 12px;
            overflow-wrap: anywhere;
        }

        .ppepp-document-actions {
            margin-top: auto;
            padding-top: 4px;
        }

        @media (max-width: 1199.98px) {
            .ppepp-checklist-grid,
            .ppepp-document-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 767.98px) {
            .ppepp-checklist-grid,
            .ppepp-document-grid {
                grid-template-columns: 1fr;
            }

            .ppepp-toolbar,
            .ppepp-search {
                max-width: none;
                width: 100%;
            }

            .ppepp-toolbar form,
            .ppepp-toolbar form .form-control,
            .ppepp-toolbar form .btn-ami {
                width: 100%;
            }

            .ppepp-toolbar form {
                gap: 8px;
            }
        }

        .ami-nav-badge {
            margin-left: auto;
            min-width: 22px;
            height: 22px;
            border-radius: 999px;
            background: var(--ami-link);
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 7px;
            font-size: 11px;
            font-weight: 700;
        }

        .ami-logout {
            padding: var(--ami-space-md) 18px var(--ami-space-lg);
            border-top: 1px solid var(--ami-sidebar-soft);
        }

        .ami-logout .ami-nav-link {
            padding-left: 0;
            padding-right: 0;
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

        .ami-desktop-toggle {
            border: 1px solid var(--ami-border);
            background: rgba(255, 255, 255, 0.04);
            color: var(--ami-text);
            border-radius: 7px;
            width: 38px;
            height: 38px;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .ami-desktop-toggle:hover {
            background: var(--ami-link-soft);
            color: var(--ami-blue);
            border-color: var(--ami-link);
        }

        /* Desktop Sidebar Collapse (72px) */
        @media (min-width: 992px) {
            .ami-sidebar {
                transition: width 0.2s cubic-bezier(0.4, 0, 0.2, 1), flex-basis 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            }

            .ami-app.sidebar-collapsed .ami-sidebar {
                width: 72px;
                flex: 0 0 72px;
            }

            .ami-app.sidebar-collapsed .ami-brand-text,
            .ami-app.sidebar-collapsed .ami-user-name,
            .ami-app.sidebar-collapsed .ami-user-role,
            .ami-app.sidebar-collapsed .ami-nav-label,
            .ami-app.sidebar-collapsed .ami-nav-subgroup-label,
            .ami-app.sidebar-collapsed .ami-nav-link > span:not(.ami-nav-step),
            .ami-app.sidebar-collapsed .ami-nav-chevron,
            .ami-app.sidebar-collapsed .ami-nav-badge,
            .ami-app.sidebar-collapsed .ami-logout span {
                display: none !important;
            }

            .ami-app.sidebar-collapsed .ami-brand {
                padding: 16px 14px;
                justify-content: center;
            }

            .ami-app.sidebar-collapsed .ami-user {
                padding: 14px 10px;
                justify-content: center;
            }

            .ami-app.sidebar-collapsed .ami-nav-link {
                justify-content: center;
                padding-left: 0;
                padding-right: 0;
            }

            .ami-app.sidebar-collapsed .ami-nav-link i {
                margin: 0;
                font-size: 16px;
            }

            .ami-app.sidebar-collapsed .ami-nav-step {
                font-size: 11px;
            }

            .ami-app.sidebar-collapsed .ami-nav-submenu {
                padding-left: 0;
                border-left: none;
            }

            .ami-app.sidebar-collapsed .ami-nav-sub-link {
                padding-left: 0;
                justify-content: center;
            }

            .ami-app.sidebar-collapsed .ami-logout {
                padding: 16px 10px;
                display: flex;
                justify-content: center;
            }

            .ami-app.sidebar-collapsed .ami-logout button {
                text-align: center;
                justify-content: center;
            }

            .ami-app.sidebar-collapsed .ami-icon-panel-close {
                display: none !important;
            }

            .ami-app.sidebar-collapsed .ami-icon-panel-open {
                display: inline-block !important;
            }
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

        .ami-topbar-actions {
            display: flex;
            align-items: center;
            gap: var(--ami-space-sm);
        }

        .ami-account-dropdown {
            position: relative;
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

        .dropdown {
            position: relative;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            z-index: 1000;
            display: none;
            float: left;
            min-width: 10rem;
            padding: 0.5rem 0;
            margin: 0.125rem 0 0;
            font-size: 0.875rem;
            color: #1e293b;
            text-align: left;
            list-style: none;
            background-color: #ffffff;
            background-clip: padding-box;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
        }

        .dropdown-menu.show {
            display: block;
        }

        .dropdown-menu-right {
            right: 0;
            left: auto;
        }

        .dropdown-divider {
            height: 0;
            margin: 0.5rem 0;
            overflow: hidden;
            border-top: 1px solid #e2e8f0;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            padding: 0.5rem 0.75rem;
            clear: both;
            font-weight: 500;
            color: #334155;
            text-align: inherit;
            white-space: nowrap;
            background-color: transparent;
            border: 0;
            cursor: pointer;
            text-decoration: none;
        }

        .dropdown-item:hover,
        .dropdown-item:focus {
            color: #1e40af;
            background-color: #eff6ff;
            outline: none;
        }

        .ami-account-menu {
            position: absolute;
            top: 100%;
            right: 0;
            z-index: 1000;
            display: none;
            min-width: 170px;
            padding: 6px;
            margin: 0.125rem 0 0;
            border-color: #e2e8f0;
            border-radius: 8px;
            background: #ffffff;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.1), 0 8px 10px -6px rgba(15, 23, 42, 0.05);
        }

        .ami-account-dropdown.ami-open .ami-account-menu {
            display: block;
        }

        .ami-account-item {
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 0;
            border-radius: 6px;
            background: transparent;
            color: #334155;
            font-size: 13px;
            font-weight: 500;
            text-align: left;
            white-space: nowrap;
            cursor: pointer;
            text-decoration: none;
        }

        .ami-account-item:hover,
        .ami-account-item:focus {
            background: #eff6ff;
            color: #1d4ed8;
            outline: none;
        }

        .ami-account-divider {
            height: 0;
            margin: 0.5rem 0;
            overflow: hidden;
            border-top: 1px solid #e2e8f0;
        }

        .ami-account-menu form {
            margin: 0;
        }

        .ami-account-menu .dropdown-item {
            border-radius: 6px;
            color: #334155;
            font-size: 13px;
        }

        .ami-account-menu .dropdown-item:hover,
        .ami-account-menu .dropdown-item:focus {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .ami-account-menu .dropdown-item i {
            width: 20px;
        }

        .ami-account-menu .dropdown-divider {
            border-top-color: #e2e8f0;
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

        .ami-content a:not(.btn):not([class*="btn"]):not([class*="button"]):not(.ami-action-btn):not(.ppepp-primary-action) {
            color: #4da3ff;
        }

        .ami-content a:not(.btn):not([class*="btn"]):not([class*="button"]):not(.ami-action-btn):not(.ppepp-primary-action):hover {
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

        .ami-dashboard-logo-banner {
            display: flex;
            align-items: center;
            gap: 16px;
            background: var(--ami-panel);
            border: 1px solid var(--ami-border);
            border-radius: 10px;
            padding: 18px 22px;
            margin-bottom: 18px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
        }

        .ami-dashboard-logo {
            height: 56px;
            width: auto;
            object-fit: contain;
            flex-shrink: 0;
        }

        .ami-dashboard-logo-title {
            font-size: 17px;
            font-weight: 700;
            color: var(--ami-text);
            line-height: 1.3;
        }

        .ami-dashboard-logo-subtitle {
            font-size: 13px;
            color: var(--ami-muted);
            margin-top: 2px;
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

        .ami-alert {
            display: flex;
            align-items: center;
            gap: 8px;
            border-radius: 8px;
            margin-bottom: 18px;
            padding: 12px 14px;
            font-size: 13px;
        }

        .ami-alert-danger {
            background: rgba(153, 53, 86, 0.2);
            color: #ffc0d5;
        }

        .ami-alert-info {
            background: #e6f1fb;
            color: #185fa5;
        }

        .ami-alert-warning {
            background: #faeeda;
            color: #854f0b;
        }

        .ami-alert-success {
            background: #eaf3de;
            color: #315d13;
        }

        .ami-table-wrap {
            overflow-x: auto;
        }

        .ami-muted {
            color: var(--ami-muted);
        }

        .ami-font-bold {
            font-weight: 700;
        }

        .ami-form-group {
            margin-bottom: 16px;
        }

        .ami-form-group:last-child {
            margin-bottom: 0;
        }

        .ami-form-control {
            display: block;
            width: 100%;
            min-height: 38px;
            padding: 8px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            background-color: #ffffff;
            color: #1e293b;
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

        .ami-content .form-control,
        .ami-content .ami-form-control {
            background-color: #ffffff;
            border-color: #cbd5e1;
            color: #1e293b;
        }

        .ami-content .form-control:focus,
        .ami-content .ami-form-control:focus {
            background-color: #ffffff;
            border-color: #3b82f6;
            color: #1e293b;
            box-shadow: 0 0 0 .2rem rgba(59, 130, 246, 0.15);
        }

        .ami-content .form-control::placeholder,
        .ami-content .ami-form-control::placeholder {
            color: #94a3b8;
            opacity: 1;
        }

        .ami-content .form-control:disabled,
        .ami-content .form-control[readonly],
        .ami-content .ami-form-control:disabled,
        .ami-content .ami-form-control[readonly] {
            background-color: #f1f5f9;
            color: #64748b;
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

        .ami-content select.form-control option,
        .ami-content select.ami-form-control option {
            background: #ffffff;
            color: #1e293b;
        }

        .ami-content .btn-primary,
        .ami-content .btn-primary:hover,
        .ami-content .btn-primary:focus,
        .ami-content .btn-primary:visited {
            color: #ffffff !important;
        }

        .ami-content .btn-primary svg {
            color: #ffffff !important;
            stroke: currentColor;
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

        .ami-flash-warning {
            background: rgba(133, 79, 11, 0.16);
            border: 1px solid rgba(133, 79, 11, 0.30);
            color: var(--ami-amber);
        }

        .ami-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .ami-grid-4,
        .ami-grid-2 {
            display: grid;
            gap: 16px;
        }

        .ami-grid-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .ami-grid-2 { grid-template-columns: minmax(0, 7fr) minmax(0, 5fr); }
        .ami-grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .ami-grid-3-9 { grid-template-columns: minmax(0, 1fr) minmax(0, 3fr); }

        .ami-hidden { display: none !important; }
        .ami-mb-0 { margin-bottom: 0 !important; }
        .ami-mb-1 { margin-bottom: 4px !important; }
        .ami-mb-2 { margin-bottom: 8px !important; }
        .ami-mb-3 { margin-bottom: 16px !important; }
        .ami-mt-2 { margin-top: 8px !important; }
        .ami-mt-3 { margin-top: 16px !important; }
        .ami-pt-3 { padding-top: 16px !important; }
        .ami-mr-2 { margin-right: 8px !important; }
        .ami-flex { display: flex; }
        .ami-flex-wrap { flex-wrap: wrap; }
        .ami-align-start { align-items: flex-start; }
        .ami-align-end { align-items: flex-end; }
        .ami-justify-between { justify-content: space-between; }
        .ami-justify-end { justify-content: flex-end; }
        .ami-inline-block { display: inline-block; }

        @media (max-width: 767px) {
            .ami-grid-4,
            .ami-grid-2,
            .ami-grid-3,
            .ami-grid-3-9 { grid-template-columns: 1fr; }
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
            display: inline-flex;
            align-items: center;
            flex-wrap: nowrap;
            gap: 6px;
            white-space: nowrap;
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

        .ami-stat-card,
        .ami-panel,
        .ami-task-card,
        .admin-summary-card {
            box-shadow: 0 8px 22px rgba(32, 39, 51, 0.07);
        }

        .ami-content a:not(.btn):not([class*="btn"]):not([class*="button"]):not(.ami-action-btn):not(.ppepp-primary-action) {
            color: var(--ami-blue);
        }

        .ami-content a:not(.btn):not([class*="btn"]):not([class*="button"]):not(.ami-action-btn):not(.ppepp-primary-action):hover {
            color: #0e4a80;
        }

        .ami-content .text-primary {
            color: var(--ami-blue) !important;
        }

        .ami-content .text-success {
            color: var(--ami-green) !important;
        }

        .ami-content .text-warning {
            color: #995c08 !important;
        }

        .ami-table th {
            background: #f7f8fa;
        }

        .ami-table tbody tr:hover {
            background: #f6f9fc;
        }

        .ami-table tbody tr:hover td {
            color: var(--ami-text);
        }

        .ami-content .form-control,
        .ami-content .bg-dark {
            background-color: #ffffff !important;
            border-color: #bdc6d2 !important;
            color: var(--ami-text) !important;
        }

        .ami-content .form-control:focus {
            background-color: #ffffff !important;
            border-color: #4da3ff !important;
            color: var(--ami-text) !important;
        }

        .ami-content .form-control:disabled,
        .ami-content .form-control[readonly] {
            background-color: #eef1f5 !important;
            color: #596273 !important;
        }

        .ami-content select.form-control option {
            background: #ffffff;
            color: var(--ami-text);
        }

        .ami-content .text-light,
        .ami-content h1,
        .ami-content h2,
        .ami-content h3,
        .ami-content h4,
        .ami-content h5,
        .ami-content h6 {
            color: var(--ami-text) !important;
        }

        .ami-content .btn-secondary.text-light {
            color: var(--ami-text) !important;
        }

        .btn-outline-ami,
        .ami-action-btn {
            background: #ffffff;
            color: #344054;
        }

        .btn-outline-ami:hover,
        .ami-action-btn:hover {
            background: #edf5fd;
            color: var(--ami-blue);
        }

        .ami-action-btn.danger {
            color: var(--ami-rose);
        }

        .ami-password-toggle:hover,
        .ami-password-toggle:focus {
            color: var(--ami-blue);
            background: #edf5fd;
        }

        .ami-timeline-marker {
            background: #f0f2f5;
        }

        .ami-timeline-step.is-current {
            color: var(--ami-blue);
        }

        .ami-timeline-step.is-complete {
            color: #397fbf;
        }

        .ami-flash-success {
            background: #edf8e8;
            color: #315d13;
        }

        .ami-flash-error {
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

            .ami-sidebar .ami-nav {
                padding-top: var(--ami-space-md);
            }

            .ami-sidebar .ami-nav-label {
                padding-top: var(--ami-space-lg);
            }

            .ami-sidebar .ami-nav-label:first-child {
                padding-top: var(--ami-space-sm);
            }

            .ami-sidebar .ami-logout {
                padding-bottom: calc(var(--ami-space-lg) + env(safe-area-inset-bottom));
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

        /* Global Toast Notifications */
        .ami-toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 99999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-width: 420px;
            width: calc(100vw - 32px);
            pointer-events: none;
        }

        .ami-toast {
            pointer-events: auto;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px 16px;
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.1), 0 8px 10px -6px rgba(15, 23, 42, 0.08);
            transform: translateY(0);
            opacity: 1;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            animation: amiToastSlideIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes amiToastSlideIn {
            from {
                opacity: 0;
                transform: translateX(30px) scale(0.96);
            }
            to {
                opacity: 1;
                transform: translateX(0) scale(1);
            }
        }

        .ami-toast.ami-toast-hiding {
            opacity: 0;
            transform: translateX(30px);
            max-height: 0;
            padding-top: 0;
            padding-bottom: 0;
            margin-top: 0;
            margin-bottom: 0;
            overflow: hidden;
            border: none;
        }

        .ami-toast-icon {
            flex-shrink: 0;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: -2px;
        }

        .ami-toast-success { border-left: 4px solid #10b981; }
        .ami-toast-success .ami-toast-icon { background: #ecfdf5; color: #059669; }

        .ami-toast-error { border-left: 4px solid #ef4444; }
        .ami-toast-error .ami-toast-icon { background: #fef2f2; color: #dc2626; }

        .ami-toast-warning { border-left: 4px solid #f59e0b; }
        .ami-toast-warning .ami-toast-icon { background: #fffbeb; color: #d97706; }

        .ami-toast-info { border-left: 4px solid #3b82f6; }
        .ami-toast-info .ami-toast-icon { background: #eff6ff; color: #2563eb; }

        .ami-toast-body {
            flex: 1;
            min-width: 0;
        }

        .ami-toast-title {
            font-size: 13px;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 2px;
            line-height: 1.3;
        }

        .ami-toast-message {
            font-size: 13px;
            color: #475569;
            line-height: 1.45;
            word-break: break-word;
        }

        .ami-toast-close {
            flex-shrink: 0;
            width: 24px;
            height: 24px;
            padding: 0;
            border: none;
            background: transparent;
            color: #94a3b8;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.15s, background-color 0.15s;
        }

        .ami-toast-close:hover {
            color: #334155;
            background: #f1f5f9;
        }

        /* Global Confirmation Modal */
        .ami-confirm-modal {
            position: fixed;
            inset: 0;
            z-index: 100000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .ami-confirm-modal[hidden] {
            display: none !important;
        }

        .ami-confirm-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            animation: amiFadeIn 0.2s ease-out;
        }

        @keyframes amiFadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .ami-confirm-card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 420px;
            background: #ffffff;
            border-radius: 16px;
            padding: 28px 24px 24px 24px;
            text-align: center;
            box-shadow: 0 20px 25px -5px rgba(15, 23, 42, 0.2), 0 8px 10px -6px rgba(15, 23, 42, 0.1);
            animation: amiModalScaleIn 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes amiModalScaleIn {
            from {
                opacity: 0;
                transform: scale(0.95) translateY(8px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .ami-confirm-badge {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            margin: 0 auto 16px auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .ami-confirm-badge.ami-badge-danger {
            background: #fee2e2;
            color: #dc2626;
        }

        .ami-confirm-badge.ami-badge-warning {
            background: #fef3c7;
            color: #d97706;
        }

        .ami-confirm-badge.ami-badge-info {
            background: #eff6ff;
            color: #2563eb;
        }

        .ami-confirm-title {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 8px 0;
            line-height: 1.3;
        }

        .ami-confirm-desc {
            font-size: 14px;
            color: #64748b;
            line-height: 1.5;
            margin: 0 0 24px 0;
            word-break: break-word;
        }

        .ami-confirm-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .ami-confirm-btn-cancel,
        .ami-confirm-btn-submit {
            flex: 1;
            height: 44px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .ami-confirm-btn-cancel {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            color: #334155;
        }

        .ami-confirm-btn-cancel:hover {
            background: #f8fafc;
            border-color: #94a3b8;
            color: #0f172a;
        }

        .ami-confirm-btn-submit.ami-btn-danger {
            background: #dc2626;
            border: 1px solid #dc2626;
            color: #ffffff;
        }

        .ami-confirm-btn-submit.ami-btn-danger:hover {
            background: #b91c1c;
            border-color: #b91c1c;
        }

        .ami-confirm-btn-submit.ami-btn-warning {
            background: #d97706;
            border: 1px solid #d97706;
            color: #ffffff;
        }

        .ami-confirm-btn-submit.ami-btn-warning:hover {
            background: #b45309;
            border-color: #b45309;
        }

        .ami-confirm-btn-submit.ami-btn-primary {
            background: #1e3a8a;
            border: 1px solid #1e3a8a;
            color: #ffffff;
        }

        .ami-confirm-btn-submit.ami-btn-primary:hover {
            background: #172554;
            border-color: #172554;
        }

        body.ami-modal-lock {
            overflow: hidden !important;
        }

        @media (max-width: 480px) {
            .ami-toast-container {
                top: 12px;
                right: 16px;
                left: 16px;
                width: auto;
            }

            .ami-confirm-card {
                padding: 20px 16px 16px 16px;
            }

            .ami-confirm-actions {
                flex-direction: column-reverse;
                gap: 8px;
            }

            .ami-confirm-btn-cancel,
            .ami-confirm-btn-submit {
                width: 100%;
                flex: none;
            }
        }
    </style>
</head>
<body>
<div class="ami-app">
