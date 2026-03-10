<?php

namespace ChurchCRM\Plugins\Catequese\Service;

use ChurchCRM\dto\SystemURLs;
use ChurchCRM\model\ChurchCRM\PersonCustom;
use ChurchCRM\model\ChurchCRM\PersonCustomQuery;
use ChurchCRM\model\ChurchCRM\PersonQuery;
use DateTime;

class DocumentService
{
    private const COMPROVANTE_VALIDADE_MESES = 3;

    /**
     * Check if person has all required documents
     */
    public function hasAllDocuments(int $personId): bool
    {
        $custom = PersonCustomQuery::create()->findPk($personId);
        if ($custom === null) {
            return false;
        }

        return !empty($custom->getCustom('rg_numero'))
            && !empty($custom->getCustom('rg_arquivo'))
            && !empty($custom->getCustom('comprovante_residencia'))
            && !empty($custom->getCustom('certidao_batismo'))
            && !empty($custom->getCustom('foto_3x4'))
            && $this->isComprovanteValid($custom);
    }

    /**
     * Check if comprovante de residência is still valid (3 months)
     */
    public function isComprovanteValid(?PersonCustom $custom): bool
    {
        if ($custom === null) {
            return false;
        }

        $dataComprovante = $custom->getCustom('comprovante_residencia_data');
        if (empty($dataComprovante)) {
            return false;
        }

        try {
            $date = new DateTime($dataComprovante);
            $now = new DateTime();
            $diff = $now->diff($date);
            $monthsDiff = ($diff->y * 12) + $diff->m;

            return $monthsDiff <= self::COMPROVANTE_VALIDADE_MESES;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get list of pending documents for a person
     */
    public function getPendingDocuments(int $personId): array
    {
        $custom = PersonCustomQuery::create()->findPk($personId);
        $pending = [];

        if ($custom === null) {
            return [
                'rg_numero' => gettext('RG - Número'),
                'rg_arquivo' => gettext('RG - Arquivo'),
                'comprovante_residencia' => gettext('Comprovante de Residência'),
                'certidao_batismo' => gettext('Certidão de Batismo'),
                'foto_3x4' => gettext('Foto 3x4'),
            ];
        }

        if (empty($custom->getCustom('rg_numero'))) {
            $pending['rg_numero'] = gettext('RG - Número');
        }
        if (empty($custom->getCustom('rg_arquivo'))) {
            $pending['rg_arquivo'] = gettext('RG - Arquivo');
        }
        if (empty($custom->getCustom('comprovante_residencia')) || !$this->isComprovanteValid($custom)) {
            $pending['comprovante_residencia'] = gettext('Comprovante de Residência (válido por 3 meses)');
        }
        if (empty($custom->getCustom('certidao_batismo'))) {
            $pending['certidao_batismo'] = gettext('Certidão de Batismo');
        }
        if (empty($custom->getCustom('foto_3x4'))) {
            $pending['foto_3x4'] = gettext('Foto 3x4');
        }

        return $pending;
    }

    /**
     * Get all catequizandos with pending documents
     */
    public function getAllPendingDocuments(): array
    {
        $persons = PersonQuery::create()
            ->usePerson2group2roleP2g2rQuery()
                ->useGroupQuery()
                    ->filterByType(3) // Assuming 3 is Catequese type - will need to be configured
                ->endUse()
            ->endUse()
            ->find();

        $pending = [];
        foreach ($persons as $person) {
            $personPending = $this->getPendingDocuments($person->getId());
            if (!empty($personPending)) {
                $pending[] = [
                    'person_id' => $person->getId(),
                    'person_name' => $person->getFullName(),
                    'pending_docs' => $personPending,
                ];
            }
        }

        return $pending;
    }

    /**
     * Render person document tab content
     */
    public function renderPersonDocumentTab(int $personId): string
    {
        $pending = $this->getPendingDocuments($personId);
        $hasAll = empty($pending);

        $html = '<div class="card">';
        $html .= '<div class="card-header">';
        $html .= '<h3 class="card-title">' . gettext('Status de Documentação') . '</h3>';
        $html .= '</div>';
        $html .= '<div class="card-body">';

        if ($hasAll) {
            $html .= '<div class="alert alert-success">';
            $html .= '<i class="fa fa-check-circle"></i> ';
            $html .= gettext('Todos os documentos estão completos!');
            $html .= '</div>';
        } else {
            $html .= '<div class="alert alert-warning">';
            $html .= '<i class="fa fa-exclamation-triangle"></i> ';
            $html .= gettext('Documentos pendentes:');
            $html .= '<ul class="mt-2">';
            foreach ($pending as $doc) {
                $html .= '<li>' . htmlspecialchars($doc) . '</li>';
            }
            $html .= '</ul>';
            $html .= '</div>';
        }

        $html .= '<p class="text-muted">';
        $html .= gettext('Configure os documentos na aba "Campos Personalizados" do perfil.');
        $html .= '</p>';

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Render dashboard widget
     */
    public function renderDashboardWidget(): string
    {
        $pending = $this->getAllPendingDocuments();
        $count = count($pending);

        $html = '<div class="info-box bg-warning">';
        $html .= '<span class="info-box-icon"><i class="fa fa-file-alt"></i></span>';
        $html .= '<div class="info-box-content">';
        $html .= '<span class="info-box-text">' . gettext('Documentos Pendentes') . '</span>';
        $html .= '<span class="info-box-number">' . $count . '</span>';
        $html .= '<a href="' . SystemURLs::getRootPath() . '/plugins/catequese/documentos" class="small-box-footer">';
        $html .= gettext('Ver detalhes') . ' <i class="fa fa-arrow-circle-right"></i>';
        $html .= '</a>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
}
