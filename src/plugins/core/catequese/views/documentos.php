<?php

use ChurchCRM\dto\SystemURLs;

require $sRootPath . '/Include/Header.php';
?>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa fa-file-alt"></i> <?= gettext('Documentos Pendentes - Catequese') ?>
                </h3>
            </div>
            <div class="card-body">
                <?php if (empty($pending)): ?>
                    <div class="alert alert-success">
                        <i class="fa fa-check-circle"></i>
                        <?= gettext('Todos os catequizandos estão com a documentação completa!') ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i>
                        <?= sprintf(gettext('%d catequizandos com documentação pendente'), count($pending)) ?>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" id="pending-docs-table">
                            <thead>
                                <tr>
                                    <th><?= gettext('Nome') ?></th>
                                    <th><?= gettext('Documentos Pendentes') ?></th>
                                    <th><?= gettext('Ações') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pending as $item): ?>
                                    <tr>
                                        <td>
                                            <a href="<?= SystemURLs::getRootPath() ?>/PersonView.php?PersonID=<?= $item['person_id'] ?>">
                                                <?= htmlspecialchars($item['person_name']) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <ul class="list-unstyled mb-0">
                                                <?php foreach ($item['pending_docs'] as $doc): ?>
                                                    <li>
                                                        <span class="badge badge-warning">
                                                            <i class="fa fa-exclamation-triangle"></i>
                                                            <?= htmlspecialchars($doc) ?>
                                                        </span>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </td>
                                        <td>
                                            <a href="<?= SystemURLs::getRootPath() ?>/PersonView.php?PersonID=<?= $item['person_id'] ?>" 
                                               class="btn btn-sm btn-primary">
                                                <i class="fa fa-edit"></i> <?= gettext('Editar') ?>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#pending-docs-table').DataTable({
        language: {
            url: '<?= SystemURLs::getRootPath() ?>/skin/js/DataTables/i18n/<?= $_SESSION['sLanguage'] ?? 'pt_BR' ?>.json'
        },
        order: [[0, 'asc']],
        pageLength: 25
    });
});
</script>

<?php require $sRootPath . '/Include/Footer.php'; ?>
