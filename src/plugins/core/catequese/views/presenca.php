<?php

use ChurchCRM\dto\SystemURLs;

require $sRootPath . '/Include/Header.php';
?>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa fa-check-circle"></i> <?= gettext('Tô Aqui Jesus - Gestão de Presença') ?>
                </h3>
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
                            <label><?= gettext('Evento/Missa') ?></label>
                            <select class="form-control" id="evento-select">
                                <option value=""><?= gettext('Primeiro selecione um grupo...') ?></option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info">
                    <h5><i class="fa fa-info-circle"></i> <?= gettext('Regras de Pontuação') ?></h5>
                    <ul>
                        <li><strong><?= gettext('Presente via Kiosk') ?>:</strong> 1.0 ponto (imutável)</li>
                        <li><strong><?= gettext('Falta Justificada') ?>:</strong> 0.5 pontos</li>
                        <li><strong><?= gettext('Falta sem Justificativa') ?>:</strong> 0 pontos</li>
                    </ul>
                    <p class="mb-0">
                        <i class="fa fa-exclamation-triangle text-warning"></i>
                        <?= gettext('Atenção: Não é possível rebaixar uma presença registrada via kiosk para falta justificada.') ?>
                    </p>
                </div>

                <div id="attendance-container" class="d-none">
                    <h4><?= gettext('Lista de Presença') ?></h4>
                    <div class="table-responsive">
                        <table class="table table-striped" id="attendance-table">
                            <thead>
                                <tr>
                                    <th><?= gettext('Nome') ?></th>
                                    <th><?= gettext('Status') ?></th>
                                    <th><?= gettext('Pontuação') ?></th>
                                    <th><?= gettext('Ações') ?></th>
                                </tr>
                            </thead>
                            <tbody id="attendance-list">
                                <!-- Populated via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for justification -->
<div class="modal fade" id="justification-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= gettext('Registrar Justificativa de Falta') ?></h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="justify-person-id">
                <input type="hidden" id="justify-event-id">
                <div class="form-group">
                    <label><?= gettext('Nome') ?></label>
                    <input type="text" class="form-control" id="justify-person-name" readonly>
                </div>
                <div class="form-group">
                    <label><?= gettext('Motivo da Falta') ?></label>
                    <textarea class="form-control" id="justify-text" rows="3" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <?= gettext('Cancelar') ?>
                </button>
                <button type="button" class="btn btn-primary" id="btn-save-justification">
                    <i class="fa fa-save"></i> <?= gettext('Salvar') ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
window.CRM.presenca = {
    currentGroupId: null,
    currentEventId: null,
    
    init: function() {
        this.loadGroups();
        this.bindEvents();
    },
    
    bindEvents: function() {
        $('#grupo-select').on('change', () => this.onGroupChange());
        $('#evento-select').on('change', () => this.onEventChange());
        $('#btn-save-justification').on('click', () => this.saveJustification());
    },
    
    loadGroups: function() {
        const currentYear = new Date().getFullYear();
        $.getJSON(`<?= SystemURLs::getRootPath() ?>/plugins/catequese/api/grupos/${currentYear}`, (data) => {
            const select = $('#grupo-select');
            select.empty().append('<option value=""><?= gettext('Selecione um grupo...') ?></option>');
            data.grupos.forEach(g => {
                select.append(`<option value="${g.id}">${g.name}</option>`);
            });
        });
    },
    
    onGroupChange: function() {
        this.currentGroupId = $('#grupo-select').val();
        if (!this.currentGroupId) {
            $('#attendance-container').addClass('d-none');
            return;
        }
        this.loadEvents();
    },
    
    loadEvents: function() {
        $.getJSON(`<?= SystemURLs::getRootPath() ?>/api/groups/${this.currentGroupId}/events`, (data) => {
            const select = $('#evento-select');
            select.empty().append('<option value=""><?= gettext('Selecione um evento...') ?></option>');
            data.forEach(event => {
                select.append(`<option value="${event.event_id}">${event.event_title} - ${event.event_start}</option>`);
            });
        });
    },
    
    onEventChange: function() {
        this.currentEventId = $('#evento-select').val();
        if (!this.currentEventId) {
            $('#attendance-container').addClass('d-none');
            return;
        }
        this.loadAttendance();
    },
    
    loadAttendance: function() {
        // This would load attendance data via API
        // For now, showing placeholder
        $('#attendance-container').removeClass('d-none');
        window.CRM.DisplayAlert('<?= gettext('Info') ?>', '<?= gettext('Funcionalidade em desenvolvimento') ?>', 'info');
    },
    
    showJustificationModal: function(personId, personName, eventId) {
        $('#justify-person-id').val(personId);
        $('#justify-person-name').val(personName);
        $('#justify-event-id').val(eventId);
        $('#justify-text').val('');
        $('#justification-modal').modal('show');
    },
    
    saveJustification: function() {
        const personId = $('#justify-person-id').val();
        const eventId = $('#justify-event-id').val();
        const justificativa = $('#justify-text').val().trim();
        
        if (!justificativa) {
            window.CRM.DisplayAlert('<?= gettext('Erro') ?>', '<?= gettext('Digite o motivo da falta') ?>', 'error');
            return;
        }
        
        $.ajax({
            url: `<?= SystemURLs::getRootPath() ?>/plugins/catequese/api/attendance/${eventId}/${personId}/justify`,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                justificativa: justificativa,
                registrado_por: <?= $_SESSION['user']->getId() ?? 0 ?>
            }),
            success: () => {
                window.CRM.DisplayAlert('<?= gettext('Sucesso') ?>', '<?= gettext('Justificativa registrada') ?>', 'success');
                $('#justification-modal').modal('hide');
                this.loadAttendance();
            },
            error: (xhr) => {
                const msg = xhr.responseJSON?.message || '<?= gettext('Erro ao salvar justificativa') ?>';
                window.CRM.DisplayAlert('<?= gettext('Erro') ?>', msg, 'error');
            }
        });
    }
};

$(document).ready(() => window.CRM.presenca.init());
</script>

<?php require $sRootPath . '/Include/Footer.php'; ?>
