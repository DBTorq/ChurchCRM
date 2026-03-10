<?php

use ChurchCRM\dto\SystemURLs;

require $sRootPath . '/Include/Header.php';
?>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa fa-trophy"></i> <?= gettext('Ranking - Tô Aqui Jesus') ?>
                </h3>
                <div class="card-tools">
                    <button class="btn btn-sm btn-success" id="btn-export-csv">
                        <i class="fa fa-download"></i> <?= gettext('Exportar CSV') ?>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><?= gettext('Grupo de Catequese') ?></label>
                            <select class="form-control" id="grupo-select">
                                <option value=""><?= gettext('Selecione um grupo...') ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><?= gettext('Ano Letivo') ?></label>
                            <select class="form-control" id="year-select">
                                <?php
                                $currentYear = (int)date('Y');
                                for ($i = $currentYear; $i >= $currentYear - 5; $i--): ?>
                                    <option value="<?= $i ?>" <?= $i === $year ? 'selected' : '' ?>><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info">
                    <h5><i class="fa fa-info-circle"></i> <?= gettext('Como funciona o ranking?') ?></h5>
                    <p><?= gettext('O ranking é calculado com base na pontuação acumulada ao longo do ano letivo:') ?></p>
                    <ul>
                        <li><?= gettext('Cada presença registrada via kiosk vale 1.0 ponto') ?></li>
                        <li><?= gettext('Cada falta justificada vale 0.5 pontos') ?></li>
                        <li><?= gettext('Faltas sem justificativa não pontuam') ?></li>
                    </ul>
                    <p class="mb-0">
                        <strong><?= gettext('Use este ranking para realizar sorteios de prêmios ou reconhecimentos!') ?></strong>
                    </p>
                </div>

                <?php if (empty($ranking)): ?>
                    <div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle"></i>
                        <?= gettext('Selecione um grupo para visualizar o ranking.') ?>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" id="ranking-table">
                            <thead>
                                <tr>
                                    <th><?= gettext('Posição') ?></th>
                                    <th><?= gettext('Nome') ?></th>
                                    <th><?= gettext('Pontuação Total') ?></th>
                                    <th><?= gettext('Presenças') ?></th>
                                    <th><?= gettext('Faltas Justificadas') ?></th>
                                    <th><?= gettext('Faltas') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $position = 1;
                                foreach ($ranking as $entry): ?>
                                    <tr>
                                        <td>
                                            <?php if ($position <= 3): ?>
                                                <span class="badge badge-<?= $position === 1 ? 'warning' : ($position === 2 ? 'secondary' : 'info') ?>">
                                                    <?= $position ?>º
                                                </span>
                                            <?php else: ?>
                                                <?= $position ?>º
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?= SystemURLs::getRootPath() ?>/PersonView.php?PersonID=<?= $entry['person_id'] ?>">
                                                <?= htmlspecialchars($entry['per_FirstName'] . ' ' . $entry['per_LastName']) ?>
                                            </a>
                                        </td>
                                        <td><strong><?= number_format($entry['total_score'], 1) ?></strong></td>
                                        <td><?= $entry['present_count'] ?></td>
                                        <td><?= $entry['justified_count'] ?></td>
                                        <td><?= $entry['unjustified_count'] ?></td>
                                    </tr>
                                <?php
                                    $position++;
                                endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
window.CRM.ranking = {
    currentGroupId: <?= $groupId ?>,
    currentYear: <?= $year ?>,
    
    init: function() {
        this.loadGroups();
        this.bindEvents();
        this.initDataTable();
    },
    
    bindEvents: function() {
        $('#grupo-select').on('change', () => this.onGroupChange());
        $('#year-select').on('change', () => this.onYearChange());
        $('#btn-export-csv').on('click', () => this.exportCSV());
    },
    
    loadGroups: function() {
        $.getJSON(`<?= SystemURLs::getRootPath() ?>/plugins/catequese/api/grupos/${this.currentYear}`, (data) => {
            const select = $('#grupo-select');
            select.empty().append('<option value=""><?= gettext('Selecione um grupo...') ?></option>');
            data.grupos.forEach(g => {
                const selected = g.id == this.currentGroupId ? 'selected' : '';
                select.append(`<option value="${g.id}" ${selected}>${g.name}</option>`);
            });
        });
    },
    
    onGroupChange: function() {
        this.currentGroupId = $('#grupo-select').val();
        this.reload();
    },
    
    onYearChange: function() {
        this.currentYear = $('#year-select').val();
        this.loadGroups();
        this.reload();
    },
    
    reload: function() {
        if (this.currentGroupId && this.currentYear) {
            window.location.href = `<?= SystemURLs::getRootPath() ?>/plugins/catequese/ranking?group_id=${this.currentGroupId}&year=${this.currentYear}`;
        }
    },
    
    exportCSV: function() {
        if (!this.currentGroupId || !this.currentYear) {
            window.CRM.DisplayAlert('<?= gettext('Erro') ?>', '<?= gettext('Selecione um grupo e ano') ?>', 'error');
            return;
        }
        
        window.location.href = `<?= SystemURLs::getRootPath() ?>/plugins/catequese/api/ranking/${this.currentGroupId}/${this.currentYear}/export`;
    },
    
    initDataTable: function() {
        if ($('#ranking-table').length) {
            $('#ranking-table').DataTable({
                language: {
                    url: '<?= SystemURLs::getRootPath() ?>/skin/js/DataTables/i18n/<?= $_SESSION['sLanguage'] ?? 'pt_BR' ?>.json'
                },
                order: [[2, 'desc']],
                pageLength: 50,
                paging: false,
                searching: false,
                info: false
            });
        }
    }
};

$(document).ready(() => window.CRM.ranking.init());
</script>

<?php require $sRootPath . '/Include/Footer.php'; ?>
