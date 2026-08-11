<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'auth';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['profil'] = 'Profil/index';
$route['profil/edit'] = 'Profil/edit';
$route['profil/update'] = 'Profil/update';
$route['profil/sinkronisasi'] = 'Profil/sinkronisasi';
$route['profil/upload_logo'] = 'Profil/upload_logo';

$route['account'] = 'Account/index';
$route['account/update'] = 'Account/update';
$route['account/photo'] = 'Account/photo';

$route['pertanyaan/download_template/(:num)'] = 'Pertanyaan/download_template/$1';
$route['pertanyaan/import/(:num)'] = 'Pertanyaan/import/$1';
$route['pertanyaan/import_confirm/(:num)'] = 'Pertanyaan/import_confirm/$1';

$route['lpmpi/instrumen/download/(:num)'] = 'lpmpi/Instrumen/download/$1';
$route['lpmpi/penetapan/download/(:num)'] = 'lpmpi/Penetapan/download/$1';
$route['lpmpi/organization'] = 'lpmpi/Organization/index';
$route['lpmpi/organization/unit/create'] = 'lpmpi/Organization/create';
$route['lpmpi/organization/unit/store'] = 'lpmpi/Organization/store_unit';
$route['lpmpi/organization/unit/edit/(:num)'] = 'lpmpi/Organization/edit/$1';
$route['lpmpi/organization/unit/update/(:num)'] = 'lpmpi/Organization/update_unit/$1';
$route['lpmpi/organization/unit/toggle/(:num)'] = 'lpmpi/Organization/toggle/$1';
$route['lpmpi/organization/assignment/create'] = 'lpmpi/Organization/assignment_create';
$route['lpmpi/organization/assignment/store'] = 'lpmpi/Organization/assignment_store';
$route['lpmpi/organization/assignment/end/(:num)'] = 'lpmpi/Organization/assignment_end/$1';
$route['lpmpi/organization/capabilities'] = 'lpmpi/Organization/capabilities';
$route['lpmpi/organization/capabilities/update'] = 'lpmpi/Organization/capabilities_update';
$route['lpmpi/spmi-standards'] = 'lpmpi/Spmi_standards/index';
$route['lpmpi/spmi-standards/version/create'] = 'lpmpi/Spmi_standards/version_create';
$route['lpmpi/spmi-standards/version/store'] = 'lpmpi/Spmi_standards/version_store';
$route['lpmpi/spmi-standards/version/detail/(:num)'] = 'lpmpi/Spmi_standards/version_detail/$1';
$route['lpmpi/spmi-standards/version/edit/(:num)'] = 'lpmpi/Spmi_standards/version_edit/$1';
$route['lpmpi/spmi-standards/version/update/(:num)'] = 'lpmpi/Spmi_standards/version_update/$1';
$route['lpmpi/spmi-standards/version/transition/(:num)'] = 'lpmpi/Spmi_standards/version_transition/$1';
$route['lpmpi/spmi-standards/source/upload/(:num)'] = 'lpmpi/Spmi_standards/source_upload/$1';
$route['lpmpi/spmi-standards/source/download/(:num)'] = 'lpmpi/Spmi_standards/source_download/$1';
$route['lpmpi/spmi-standards/source/delete/(:num)'] = 'lpmpi/Spmi_standards/source_delete/$1';
$route['lpmpi/spmi-standards/standard/create/(:num)'] = 'lpmpi/Spmi_standards/standard_create/$1';
$route['lpmpi/spmi-standards/standard/store/(:num)'] = 'lpmpi/Spmi_standards/standard_store/$1';
$route['lpmpi/spmi-standards/standard/edit/(:num)'] = 'lpmpi/Spmi_standards/standard_edit/$1';
$route['lpmpi/spmi-standards/standard/update/(:num)'] = 'lpmpi/Spmi_standards/standard_update/$1';
$route['lpmpi/spmi-indicators'] = 'lpmpi/Spmi_indicators/index';
$route['lpmpi/spmi-indicators/indicator/create/(:num)'] = 'lpmpi/Spmi_indicators/indicator_create/$1';
$route['lpmpi/spmi-indicators/indicator/store/(:num)'] = 'lpmpi/Spmi_indicators/indicator_store/$1';
$route['lpmpi/spmi-indicators/indicator/detail/(:num)'] = 'lpmpi/Spmi_indicators/indicator_detail/$1';
$route['lpmpi/spmi-indicators/indicator/edit/(:num)'] = 'lpmpi/Spmi_indicators/indicator_edit/$1';
$route['lpmpi/spmi-indicators/indicator/update/(:num)'] = 'lpmpi/Spmi_indicators/indicator_update/$1';
$route['lpmpi/spmi-indicators/target/create/(:num)'] = 'lpmpi/Spmi_indicators/target_create/$1';
$route['lpmpi/spmi-indicators/target/store/(:num)'] = 'lpmpi/Spmi_indicators/target_store/$1';
$route['lpmpi/spmi-indicators/target/edit/(:num)'] = 'lpmpi/Spmi_indicators/target_edit/$1';
$route['lpmpi/spmi-indicators/target/update/(:num)'] = 'lpmpi/Spmi_indicators/target_update/$1';
$route['lpmpi/spmi-master'] = 'lpmpi/Spmi_master/index';
$route['lpmpi/spmi-master/template/(:num)'] = 'lpmpi/Spmi_master/template/$1';
$route['lpmpi/spmi-master/preview/(:num)'] = 'lpmpi/Spmi_master/preview/$1';
$route['lpmpi/spmi-master/confirm/(:num)'] = 'lpmpi/Spmi_master/confirm/$1';
$route['lpmpi/spmi-master/cancel/(:num)'] = 'lpmpi/Spmi_master/cancel/$1';
$route['lpmpi/spmi-master/export/(:num)'] = 'lpmpi/Spmi_master/export/$1';
$route['lpmpi/spmi-instruments'] = 'lpmpi/Spmi_instruments/index';
$route['lpmpi/spmi-instruments/package/create/(:num)'] = 'lpmpi/Spmi_instruments/package_create/$1';
$route['lpmpi/spmi-instruments/package/store/(:num)'] = 'lpmpi/Spmi_instruments/package_store/$1';
$route['lpmpi/spmi-instruments/package/detail/(:num)'] = 'lpmpi/Spmi_instruments/package_detail/$1';
$route['lpmpi/spmi-instruments/package/edit/(:num)'] = 'lpmpi/Spmi_instruments/package_edit/$1';
$route['lpmpi/spmi-instruments/package/update/(:num)'] = 'lpmpi/Spmi_instruments/package_update/$1';
$route['lpmpi/spmi-instruments/package/delete/(:num)'] = 'lpmpi/Spmi_instruments/package_delete/$1';
$route['lpmpi/spmi-instruments/question/create/(:num)'] = 'lpmpi/Spmi_instruments/question_create/$1';
$route['lpmpi/spmi-instruments/question/store/(:num)'] = 'lpmpi/Spmi_instruments/question_store/$1';
$route['lpmpi/spmi-instruments/question/detail/(:num)'] = 'lpmpi/Spmi_instruments/question_detail/$1';
$route['lpmpi/spmi-instruments/question/edit/(:num)'] = 'lpmpi/Spmi_instruments/question_edit/$1';
$route['lpmpi/spmi-instruments/question/update/(:num)'] = 'lpmpi/Spmi_instruments/question_update/$1';
$route['lpmpi/spmi-instruments/question/delete/(:num)'] = 'lpmpi/Spmi_instruments/question_delete/$1';
$route['lpmpi/spmi-instruments/rubric/create/(:num)'] = 'lpmpi/Spmi_instruments/rubric_create/$1';
$route['lpmpi/spmi-instruments/rubric/store/(:num)'] = 'lpmpi/Spmi_instruments/rubric_store/$1';
$route['lpmpi/spmi-instruments/rubric/edit/(:num)'] = 'lpmpi/Spmi_instruments/rubric_edit/$1';
$route['lpmpi/spmi-instruments/rubric/update/(:num)'] = 'lpmpi/Spmi_instruments/rubric_update/$1';
$route['lpmpi/spmi-instruments/rubric/delete/(:num)'] = 'lpmpi/Spmi_instruments/rubric_delete/$1';
$route['lpmpi/spmi-audits'] = 'lpmpi/Spmi_audits/index';
$route['lpmpi/spmi-audits/cycle/create'] = 'lpmpi/Spmi_audits/cycle_create';
$route['lpmpi/spmi-audits/cycle/store'] = 'lpmpi/Spmi_audits/cycle_store';
$route['lpmpi/spmi-audits/cycle/detail/(:num)'] = 'lpmpi/Spmi_audits/cycle_detail/$1';
$route['lpmpi/spmi-audits/cycle/edit/(:num)'] = 'lpmpi/Spmi_audits/cycle_edit/$1';
$route['lpmpi/spmi-audits/cycle/update/(:num)'] = 'lpmpi/Spmi_audits/cycle_update/$1';
$route['lpmpi/spmi-audits/cycle/transition/(:num)'] = 'lpmpi/Spmi_audits/cycle_transition/$1';
$route['lpmpi/spmi-audits/assignment/create/(:num)'] = 'lpmpi/Spmi_audits/assignment_create/$1';
$route['lpmpi/spmi-audits/assignment/store/(:num)'] = 'lpmpi/Spmi_audits/assignment_store/$1';
$route['lpmpi/spmi-audits/assignment/detail/(:num)'] = 'lpmpi/Spmi_audits/assignment_detail/$1';
$route['lpmpi/spmi-audits/assignment/delete/(:num)'] = 'lpmpi/Spmi_audits/assignment_delete/$1';
$route['lpmpi/spmi-reports'] = 'lpmpi/Spmi_reports/index';
$route['lpmpi/spmi-reports/assessment/create/(:num)'] = 'lpmpi/Spmi_reports/create/$1';
$route['lpmpi/spmi-reports/detail/(:num)'] = 'lpmpi/Spmi_reports/detail/$1';
$route['lpmpi/spmi-reports/export/(:num)'] = 'lpmpi/Spmi_reports/export/$1';
$route['lpmpi/spmi-reports/print/(:num)'] = 'lpmpi/Spmi_reports/print_report/$1';
$route['lpmpi/spmi-rtm'] = 'lpmpi/Spmi_rtm/index';
$route['lpmpi/spmi-rtm/create'] = 'lpmpi/Spmi_rtm/create';
$route['lpmpi/spmi-rtm/store'] = 'lpmpi/Spmi_rtm/store';
$route['lpmpi/spmi-rtm/detail/(:num)'] = 'lpmpi/Spmi_rtm/detail/$1';
$route['lpmpi/spmi-rtm/edit/(:num)'] = 'lpmpi/Spmi_rtm/edit/$1';
$route['lpmpi/spmi-rtm/update/(:num)'] = 'lpmpi/Spmi_rtm/update/$1';
$route['lpmpi/spmi-rtm/resolve/(:num)'] = 'lpmpi/Spmi_rtm/resolve/$1';
$route['lpmpi/spmi-rtm/print/(:num)'] = 'lpmpi/Spmi_rtm/print_report/$1';
$route['lpmpi/spmi-follow-ups'] = 'lpmpi/Spmi_follow_ups/index';
$route['lpmpi/spmi-follow-ups/create/(:num)'] = 'lpmpi/Spmi_follow_ups/create/$1';
$route['lpmpi/spmi-follow-ups/store/(:num)'] = 'lpmpi/Spmi_follow_ups/store/$1';
$route['lpmpi/spmi-follow-ups/detail/(:num)'] = 'lpmpi/Spmi_follow_ups/detail/$1';
$route['lpmpi/spmi-follow-ups/edit/(:num)'] = 'lpmpi/Spmi_follow_ups/edit/$1';
$route['lpmpi/spmi-follow-ups/update/(:num)'] = 'lpmpi/Spmi_follow_ups/update/$1';
$route['lpmpi/spmi-follow-ups/transition/(:num)'] = 'lpmpi/Spmi_follow_ups/transition/$1';
$route['lpmpi/spmi-recap'] = 'lpmpi/Spmi_ppepp_recap/index';
$route['lpmpi/spmi-dashboard'] = 'lpmpi/Spmi_management_dashboard/index';
$route['lpmpi/spmi-dashboard/export'] = 'lpmpi/Spmi_management_dashboard/export';
$route['lpmpi/legacy-ami-archive'] = 'lpmpi/Legacy_ami_archive/index';
$route['lpmpi/legacy-ami-archive/preflight'] = 'lpmpi/Legacy_ami_archive/preflight';
$route['lpmpi/legacy-ami-archive/run/(:num)'] = 'lpmpi/Legacy_ami_archive/run/$1';
$route['lpmpi/legacy-ami-archive/task/(:num)'] = 'lpmpi/Legacy_ami_archive/task/$1';
$route['lpmpi/legacy-ami-archive/issues'] = 'lpmpi/Legacy_ami_archive/issues';

$route['auditee'] = 'Auditee/index';
$route['auditee/tugas'] = 'Auditee/tugas';
$route['auditee/form/(:num)'] = 'Auditee/form/$1';
$route['auditee/save/(:num)'] = 'Auditee/save/$1';
$route['auditee/submit/(:num)'] = 'Auditee/submit/$1';
$route['auditee/konfirmasi/(:num)'] = 'Auditee/konfirmasi/$1';
$route['auditee/download_instrumen/(:num)'] = 'Auditee/download_instrumen/$1';
$route['auditee/tugas/form/(:num)'] = 'Auditee/form/$1';
$route['auditee/tugas/save/(:num)'] = 'Auditee/save/$1';
$route['auditee/tugas/submit/(:num)'] = 'Auditee/submit/$1';
$route['auditee/tugas/konfirmasi/(:num)'] = 'Auditee/konfirmasi/$1';
$route['auditee/tugas/download_instrumen/(:num)'] = 'Auditee/download_instrumen/$1';
$route['auditee/isi/(:num)'] = 'Auditee/form/$1';
$route['auditee/simpan_jawaban/(:num)'] = 'Auditee/submit/$1';
$route['auditee/spmi'] = 'spmi_auditee_workspace/index';
$route['auditee/spmi/assignment/(:num)'] = 'spmi_auditee_workspace/assignment/$1';
$route['auditee/spmi/assignment/(:num)/save'] = 'spmi_auditee_workspace/save/$1';
$route['auditee/spmi/assignment/(:num)/submit'] = 'spmi_auditee_workspace/submit/$1';
$route['auditee/spmi/assignment/(:num)/resubmit'] = 'spmi_auditee_workspace/resubmit/$1';
$route['auditee/spmi/assignment/(:num)/final-result'] = 'spmi_auditee_workspace/final_result/$1';
$route['auditee/spmi/item/(:num)/evidence/upload'] = 'spmi_auditee_workspace/upload_evidence/$1';
$route['auditee/spmi/evidence/(:num)/delete'] = 'spmi_auditee_workspace/delete_evidence/$1';
$route['auditee/spmi/evidence/(:num)/download'] = 'spmi_auditee_workspace/download_evidence/$1';
$route['auditee/spmi-dashboard'] = 'Spmi_auditee_dashboard/index';

$route['auditor/penilaian'] = 'Auditor/penilaian';
$route['auditor/penilaian/form/(:num)'] = 'Auditor/form_penilaian/$1';
$route['auditor/penilaian/nilai/(:num)'] = 'Auditor/form_penilaian/$1';
$route['auditor/penilaian/save_item/(:num)'] = 'Auditor/save_penilaian_item/$1';
$route['auditor/penilaian/save/(:num)'] = 'Auditor/save_penilaian_draft/$1';
$route['auditor/penilaian/submit/(:num)'] = 'Auditor/submit_penilaian/$1';
$route['auditor/penilaian/revisi/(:num)'] = 'Auditor/revisi_penilaian/$1';
$route['auditor/penilaian/download_bukti/(:num)'] = 'Auditor/download_bukti_penilaian/$1';
$route['auditor/spmi'] = 'spmi_auditor_workspace/index';
$route['auditor/spmi/assignment/(:num)'] = 'spmi_auditor_workspace/assignment/$1';
$route['auditor/spmi/assignment/(:num)/save'] = 'spmi_auditor_workspace/save/$1';
$route['auditor/spmi/assignment/(:num)/finalize'] = 'spmi_auditor_workspace/finalize/$1';
$route['auditor/spmi/assignment/(:num)/return'] = 'spmi_auditor_workspace/return_for_revision/$1';
$route['auditor/spmi/evidence/(:num)/download'] = 'spmi_auditor_workspace/download_evidence/$1';
$route['auditor/spmi-dashboard'] = 'Spmi_auditor_dashboard/index';
$route['auditor/nilai/(:num)'] = 'Auditor/form_penilaian/$1';
