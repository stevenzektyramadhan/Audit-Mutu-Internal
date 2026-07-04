<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php if (!empty($auditee_list)): ?>
    <h3 class="ami-section-title mt-4 mb-3" style="font-size: 18px;">Auditee</h3>
    <div class="table-responsive">
        <table class="table ami-table">
            <thead>
            <tr>
                <th>#</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Unit</th>
                <th>Jenis Unit</th>
                <th>Aksi</th>
            </tr>
            </thead>
            <tbody>
            <?php $no = 1; foreach ($auditee_list as $row): ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo html_escape($row->nama); ?></td>
                    <td><?php echo html_escape($row->email); ?></td>
                    <td><?php echo html_escape($row->nama_unit ?: '-'); ?></td>
                    <td><?php echo html_escape($row->jenis_unit ?: '-'); ?></td>
                    <td>
                        <div class="ami-row-actions">
                            <a href="<?php echo site_url('lpmpi/akun/edit/' . (int) $row->id); ?>" class="ami-action-btn" title="Edit akun">
                                <i class="fas fa-edit" aria-hidden="true"></i><span>Edit</span>
                            </a>
                            <?php echo form_open('lpmpi/akun/delete/' . (int) $row->id, ['class' => 'd-inline', 'onsubmit' => "return confirm('Hapus akun auditee ini?');"]); ?>
                                <button type="submit" class="ami-action-btn danger" title="Hapus akun">
                                    <i class="fas fa-trash-alt" aria-hidden="true"></i><span>Hapus</span>
                                </button>
                            <?php echo form_close(); ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
