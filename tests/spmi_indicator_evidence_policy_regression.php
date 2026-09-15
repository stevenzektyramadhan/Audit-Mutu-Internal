<?php

function indicator_evidence_source($path)
{
    $source = file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . $path);
    if ($source === FALSE) throw new RuntimeException('Tidak dapat membaca ' . $path);
    return $source;
}

function indicator_evidence_check($condition, $message)
{
    if (!$condition) throw new RuntimeException($message);
}

$migration = indicator_evidence_source('migrations/035_add_indicator_evidence_policy.sql');
$schema = indicator_evidence_source('database_schema.sql');
$service = indicator_evidence_source('application/services/Spmi_indicators_service.php');
$controller = indicator_evidence_source('application/controllers/lpmpi/Spmi_indicators.php');
$form = indicator_evidence_source('application/views/lpmpi/spmi_indicators/indicator_form.php');
$detail = indicator_evidence_source('application/views/lpmpi/spmi_indicators/indicator_detail.php');
$audits_service = indicator_evidence_source('application/services/Spmi_audits_service.php');

$policy = "ENUM('none','file','url','either','both') NOT NULL DEFAULT 'none'";
indicator_evidence_check(strpos($migration, 'INFORMATION_SCHEMA.COLUMNS') !== FALSE && strpos($migration, "TABLE_NAME = 'spmi_indicators'") !== FALSE && strpos($migration, 'COLUMN_NAME = \'evidence_policy\'') !== FALSE, 'Migration 035 must guard spmi_indicators.evidence_policy idempotently.');
indicator_evidence_check(strpos($migration, $policy) !== FALSE && strpos($migration, 'AFTER `evidence_requirement`') !== FALSE, 'Migration 035 must add the exact safe evidence_policy column after evidence_requirement.');
indicator_evidence_check(!preg_match('/\b(INSERT|UPDATE|DELETE)\b/i', $migration) && stripos($migration, 'foreign_key_checks') === FALSE, 'Migration 035 must not run DML or disable foreign keys.');
indicator_evidence_check(strpos($schema, "`evidence_policy` ENUM('none','file','url','either','both') NOT NULL DEFAULT 'none'") !== FALSE && strpos($schema, 'Current parity migration 001-035') !== FALSE, 'Bootstrap schema must include the source indicator evidence policy and parity marker.');
indicator_evidence_check(strpos($controller, "set_rules('evidence_policy', 'Kebijakan bukti', 'required|in_list[none,file,url,either,both]')") !== FALSE, 'Indicator controller must validate the submitted evidence policy.');
indicator_evidence_check(strpos($service, "'evidence_policy' => trim") !== FALSE && strpos($service, "in_array(\$payload['evidence_policy'], ['none', 'file', 'url', 'either', 'both'], TRUE)") !== FALSE, 'Indicator service must normalize and allowlist evidence policy.');
foreach (['name="evidence_policy"', 'value="none"', 'value="file"', 'value="url"', 'value="either"', 'value="both"'] as $literal) indicator_evidence_check(strpos($form, $literal) !== FALSE, 'Indicator form policy control missing: ' . $literal);
indicator_evidence_check(strpos($detail, "isset(\$indicator->evidence_policy) ? \$indicator->evidence_policy : 'none'") !== FALSE && strpos($detail, 'html_escape($evidence_policy)') !== FALSE, 'Indicator detail must safely render an absent evidence policy as none during schema rollout.');
indicator_evidence_check(strpos($audits_service, "'evidence_policy' => \$indicator->evidence_policy") !== FALSE && strpos($audits_service, "'evidence_policy' => 'none'") === FALSE, 'New assignment items must snapshot the source indicator policy instead of a static policy.');

fwrite(STDOUT, "SPMI indicator evidence policy regression checks passed.\n");
