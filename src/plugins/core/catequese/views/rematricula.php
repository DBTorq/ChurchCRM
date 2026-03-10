<?php

use ChurchCRM\dto\SystemURLs;

require $sRootPath . '/Include/Header.php';
?>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa fa-user-graduate"></i> <?= gettext('Rematrícula Anual - Catequese') ?>
                </h3>
            </div>
            <div class="card-body">
                <div id="rematricula-wizard">
                    <!-- Step 1: Select source group -->
                    <div class="step" id="step-1">
                        <h4><?= gettext('Passo 1: Selecione o grupo de origem (ano anterior)') ?></h4>
                        <div class="form-group">
                            <label><?= gettext('Ano Letivo de Origem') ?></label>
                            <select class="form-control" id="ano-origem">
                                <option value=""><?= gettext('Selecione...') ?></option>
                                <?php
                                $currentYear = (int)date('Y');
                                for ($i = $currentYear - 1; $i >= $currentYear - 5; $i--): ?>
                                    <option value="<?= $i ?>"><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><?= gettext('Grupo de Origem') ?></label>
                            <select class="form-control" id="grupo-origem">
                                <option value=""><?= gettext('Primeiro selecione o ano...') ?></option>
                            </select>
                        </div>
                        <button class="btn btn-primary" id="btn-step-1" disabled>
                            <?= gettext('Próximo') ?> <i class="fa fa-arrow-right"></i>
                        </button>
                    </div>

                    <!-- Step 2: Select destination group -->
                    <div class="step d-none" id="step-2">
                        <h4><?= gettext('Passo 2: Selecione o grupo de destino (novo ano)') ?></h4>
                        <div class="form-group">
                            <label><?= gettext('Ano Letivo de Destino') ?></label>
                            <select class="form-control" id="ano-destino">
                                <option value=""><?= gettext('Selecione...') ?></option>
                                <?php
                                for ($i = $currentYear; $i <= $currentYear + 2; $i++): ?>
                                    <option value="<?= $i ?>"><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><?= gettext('Grupo de Destino') ?></label>
                            <select class="form-control" id="grupo-destino">
                                <option value=""><?= gettext('Primeiro selecione o ano...') ?></option>
                            </select>
                        </div>
                        <button class="btn btn-secondary" id="btn-back-1">
                            <i class="fa fa-arrow-left"></i> <?= gettext('Voltar') ?>
                        </button>
                        <button class="btn btn-primary" id="btn-step-2" disabled>
                            <?= gettext('Próximo') ?> <i class="fa fa-arrow-right"></i>
                        </button>
                    </div>

                    <!-- Step 3: Review and select members -->
                    <div class="step d-none" id="step-3">
                        <h4><?= gettext('Passo 3: Revisar e selecionar membros') ?></h4>
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i>
                            <?= gettext('Selecione os catequizandos que serão rematriculados no novo grupo.') ?>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped" id="members-table">
                                <thead>
                                    <tr>
                                        <th>
                                            <input type="checkbox" id="select-all">
                                        </th>
                                        <th><?= gettext('Nome') ?></th>
                                        <th><?= gettext('Papel') ?></th>
                                    </tr>
                                </thead>
                                <tbody id="members-list">
                                    <!-- Populated via JS -->
                                </tbody>
                            </table>
                        </div>
                        <button class="btn btn-secondary" id="btn-back-2">
                            <i class="fa fa-arrow-left"></i> <?= gettext('Voltar') ?>
                        </button>
                        <button class="btn btn-success" id="btn-execute">
                            <i class="fa fa-check"></i> <?= gettext('Executar Rematrícula') ?>
                        </button>
                    </div>

                    <!-- Step 4: Results -->
                    <div class="step d-none" id="step-4">
                        <h4><?= gettext('Resultado da Rematrícula') ?></h4>
                        <div id="result-container"></div>
                        <a href="<?= SystemURLs::getRootPath() ?>/plugins/catequese/rematricula" class="btn btn-primary">
                            <i class="fa fa-redo"></i> <?= gettext('Nova Rematrícula') ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window.CRM.rematricula = {
    grupoOrigemId: null,
    grupoDestinoId: null,
    
    init: function() {
        this.bindEvents();
    },
    
    bindEvents: function() {
        $('#ano-origem').on('change', () => this.loadGruposOrigem());
        $('#grupo-origem').on('change', () => this.validateStep1());
        $('#ano-destino').on('change', () => this.loadGruposDestino());
        $('#grupo-destino').on('change', () => this.validateStep2());
        $('#btn-step-1').on('click', () => this.goToStep2());
        $('#btn-step-2').on('click', () => this.goToStep3());
        $('#btn-back-1').on('click', () => this.goToStep1());
        $('#btn-back-2').on('click', () => this.goToStep2());
        $('#btn-execute').on('click', () => this.executeRematricula());
        $('#select-all').on('change', (e) => {
            $('.member-checkbox').prop('checked', e.target.checked);
        });
    },
    
    loadGruposOrigem: function() {
        const year = $('#ano-origem').val();
        if (!year) return;
        
        $.getJSON(`<?= SystemURLs::getRootPath() ?>/plugins/catequese/api/grupos/${year}`, (data) => {
            const select = $('#grupo-origem');
            select.empty().append('<option value=""><?= gettext('Selecione...') ?></option>');
            data.grupos.forEach(g => {
                select.append(`<option value="${g.id}">${g.name} (${g.member_count} membros)</option>`);
            });
        });
    },
    
    loadGruposDestino: function() {
        const year = $('#ano-destino').val();
        if (!year) return;
        
        $.getJSON(`<?= SystemURLs::getRootPath() ?>/plugins/catequese/api/grupos/${year}`, (data) => {
            const select = $('#grupo-destino');
            select.empty().append('<option value=""><?= gettext('Selecione...') ?></option>');
            data.grupos.forEach(g => {
                select.append(`<option value="${g.id}">${g.name}</option>`);
            });
        });
    },
    
    validateStep1: function() {
        const valid = $('#grupo-origem').val() !== '';
        $('#btn-step-1').prop('disabled', !valid);
    },
    
    validateStep2: function() {
        const valid = $('#grupo-destino').val() !== '';
        $('#btn-step-2').prop('disabled', !valid);
    },
    
    goToStep1: function() {
        $('.step').addClass('d-none');
        $('#step-1').removeClass('d-none');
    },
    
    goToStep2: function() {
        this.grupoOrigemId = $('#grupo-origem').val();
        $('.step').addClass('d-none');
        $('#step-2').removeClass('d-none');
    },
    
    goToStep3: function() {
        this.grupoDestinoId = $('#grupo-destino').val();
        this.loadMembers();
        $('.step').addClass('d-none');
        $('#step-3').removeClass('d-none');
    },
    
    loadMembers: function() {
        $.getJSON(`<?= SystemURLs::getRootPath() ?>/api/groups/${this.grupoOrigemId}/members`, (data) => {
            const tbody = $('#members-list');
            tbody.empty();
            data.forEach(member => {
                tbody.append(`
                    <tr>
                        <td><input type="checkbox" class="member-checkbox" value="${member.person_id}" checked></td>
                        <td>${member.name}</td>
                        <td>${member.role || 'Catequizando'}</td>
                    </tr>
                `);
            });
        });
    },
    
    executeRematricula: function() {
        const personIds = $('.member-checkbox:checked').map(function() {
            return $(this).val();
        }).get();
        
        if (personIds.length === 0) {
            window.CRM.DisplayAlert('<?= gettext('Erro') ?>', '<?= gettext('Selecione pelo menos um membro') ?>', 'error');
            return;
        }
        
        $.ajax({
            url: '<?= SystemURLs::getRootPath() ?>/plugins/catequese/api/rematricula',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                grupo_origem_id: this.grupoOrigemId,
                grupo_destino_id: this.grupoDestinoId,
                person_ids: personIds
            }),
            success: (result) => {
                this.showResults(result);
            },
            error: () => {
                window.CRM.DisplayAlert('<?= gettext('Erro') ?>', '<?= gettext('Erro ao executar rematrícula') ?>', 'error');
            }
        });
    },
    
    showResults: function(result) {
        const container = $('#result-container');
        container.empty();
        
        if (result.migrated.length > 0) {
            container.append(`
                <div class="alert alert-success">
                    <i class="fa fa-check-circle"></i>
                    <strong>${result.migrated.length}</strong> <?= gettext('membros rematriculados com sucesso!') ?>
                </div>
            `);
        }
        
        if (result.errors.length > 0) {
            container.append(`
                <div class="alert alert-warning">
                    <i class="fa fa-exclamation-triangle"></i>
                    <strong>${result.errors.length}</strong> <?= gettext('erros encontrados') ?>
                </div>
            `);
        }
        
        $('.step').addClass('d-none');
        $('#step-4').removeClass('d-none');
    }
};

$(document).ready(() => window.CRM.rematricula.init());
</script>

<?php require $sRootPath . '/Include/Footer.php'; ?>
