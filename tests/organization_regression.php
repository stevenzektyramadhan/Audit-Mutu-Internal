<?php
$root = dirname(__DIR__);
function organization_source($path) { $value = file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . $path); if ($value === FALSE) throw new RuntimeException('Tidak dapat membaca ' . $path); return $value; }
function organization_check($condition, $message) { if (!$condition) throw new RuntimeException($message); }

$migration = organization_source('migrations/012_create_organization_structure.sql');
$schema = organization_source('database_schema.sql');
$controller = organization_source('application/controllers/lpmpi/Organization.php');
$service = organization_source('application/services/Organization_service.php');
$routes = organization_source('application/config/routes.php');
$sidebar = organization_source('application/views/layouts/sidebar.php');
$index = organization_source('application/views/lpmpi/organization/index.php');
$unit_form = organization_source('application/views/lpmpi/organization/unit_form.php');
$assignment_form = organization_source('application/views/lpmpi/organization/assignment_form.php');
$capabilities = organization_source('application/views/lpmpi/organization/capabilities.php');

foreach (['organization_units', 'user_unit_assignments', 'capabilities', 'role_capabilities', 'INSERT IGNORE', 'UNIVERSITAS', 'organization.view', 'organization.manage', 'organization.assignment.manage', 'organization.capability.manage', "ENUM('super_admin','admin_lpmpi','auditor','auditee')"] as $literal) organization_check(strpos($migration, $literal) !== FALSE, 'Migration contract missing: ' . $literal);
foreach (['organization_units', 'user_unit_assignments', 'capabilities', 'role_capabilities', 'UNIVERSITAS', 'organization.capability.manage'] as $literal) organization_check(strpos($schema, $literal) !== FALSE, 'Schema parity missing: ' . $literal);
organization_check(strpos($controller, 'extends Admin_Lpmpi_Controller') !== FALSE, 'Organization controller base class changed.');
foreach (['require_post_capability', 'organization.view', 'organization.manage', 'organization.assignment.manage', 'organization.capability.manage', "method(TRUE) !== 'POST'"] as $literal) organization_check(strpos($controller, $literal) !== FALSE, 'Controller security contract missing: ' . $literal);
foreach (['is_descendant', 'has_active_children', 'trans_start', 'valid_until', 'replace_role_capabilities', 'TYPES', 'find_user', 'find_active_unit', 'find_assignment', 'valid_date', 'DateTime::createFromFormat', "Y-m-d", 'is_current_assignment', "'organization.view'", "'organization.capability.manage'"] as $literal) organization_check(strpos($service, $literal) !== FALSE, 'Service invariant missing: ' . $literal);
organization_check(strpos($service, "const ROLES = ['super_admin', 'admin_lpmpi']") !== FALSE, 'Capability role scope must match Admin_Lpmpi_Controller.');
organization_check(strpos($service, 'known_ids') !== FALSE && strpos($service, 'array_values($capability_ids)') !== FALSE, 'Capability IDs must be filtered against known capabilities.');
organization_check(strpos($service, "if (\$role === 'super_admin')") !== FALSE, 'Super admin capability floor missing.');
organization_check(strpos($service, "!empty(\$data['is_primary']) && \$this->is_current_assignment") !== FALSE, 'Historical primary assignment must not clear current primary.');
foreach (['find_user', 'find_active_unit', 'find_assignment'] as $literal) organization_check(strpos($service, $literal . '(') !== FALSE && strpos($routes, 'lpmpi/organization') !== FALSE, 'Organization data lookup contract missing: ' . $literal);
organization_check(strpos($index, "'root'") !== FALSE && strpos($index, "render_organization_units('root'") !== FALSE, 'Organization root rendering contract missing.');
foreach (['lpmpi/organization', 'unit/create', 'unit/store', 'unit/edit', 'unit/update', 'unit/toggle', 'assignment/create', 'assignment/store', 'assignment/end', 'capabilities/update'] as $literal) organization_check(strpos($routes, $literal) !== FALSE, 'Route missing: ' . $literal);
organization_check(substr_count($sidebar, "'key' => 'organization', 'label' => 'Struktur Organisasi', 'icon' => 'fa-sitemap', 'url' => 'lpmpi/organization', 'group' => 'Management'") === 2, 'Organization sidebar entry must exist only for two management roles.');
foreach ([$index, $unit_form, $assignment_form, $capabilities] as $view) organization_check(strpos($view, 'html_escape') !== FALSE, 'Organization view must escape output.');
foreach ([$unit_form, $assignment_form, $capabilities] as $view) organization_check(strpos($view, 'form_open(') !== FALSE, 'Organization mutation view must use form_open().');
organization_check(substr_count($capabilities, 'Organization_service::ROLES') === 3, 'Capability matrix must use scoped management roles only.');
organization_check(strpos($capabilities, 'auditor') === FALSE && strpos($capabilities, 'auditee') === FALSE, 'Capability matrix must not expose auditor/auditee controls.');
organization_check(strpos($sidebar, "'key' => 'dashboard'") !== FALSE && strpos($sidebar, "form_open('auth/logout');") !== FALSE, 'Legacy sidebar hooks changed.');

fwrite(STDOUT, "Organization regression checks passed.\n");
