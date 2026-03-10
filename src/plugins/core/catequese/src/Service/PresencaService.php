<?php

namespace ChurchCRM\Plugins\Catequese\Service;

use ChurchCRM\model\ChurchCRM\EventAttend;
use ChurchCRM\model\ChurchCRM\EventAttendQuery;
use ChurchCRM\model\ChurchCRM\EventQuery;
use Propel\Runtime\Propel;

class PresencaService
{
    private const SCORE_PRESENTE = 1.0;
    private const SCORE_JUSTIFICADO = 0.5;
    private const SCORE_FALTA = 0.0;

    /**
     * Register presence when check-in occurs
     */
    public function registrarPresenca(int $eventId, int $personId): void
    {
        $attendance = EventAttendQuery::create()
            ->filterByEventId($eventId)
            ->filterByPersonId($personId)
            ->findOne();

        if ($attendance !== null) {
            // Update attendance score to full point (present via kiosk)
            $this->updateAttendanceScore($attendance, self::SCORE_PRESENTE);
            $this->updateRanking($eventId, $personId, self::SCORE_PRESENTE);
        }
    }

    /**
     * Register justified absence
     */
    public function registrarJustificativa(
        int $eventId,
        int $personId,
        string $justificativa,
        int $registradoPor
    ): bool {
        try {
            $attendance = EventAttendQuery::create()
                ->filterByEventId($eventId)
                ->filterByPersonId($personId)
                ->findOne();

            if ($attendance === null) {
                // Create attendance record for justified absence
                $attendance = new EventAttend();
                $attendance->setEventId($eventId);
                $attendance->setPersonId($personId);
            }

            // Check if already checked in - cannot downgrade from present to justified
            if ($attendance->getCheckinDate() !== null) {
                return false; // Already present - cannot justify
            }

            // Set justification fields (will be added in migration)
            // $attendance->setJustificationText($justificativa);
            // $attendance->setJustificationDate(new \DateTime());
            // $attendance->setJustificationBy($registradoPor);
            
            $this->updateAttendanceScore($attendance, self::SCORE_JUSTIFICADO);
            $attendance->save();

            $this->updateRanking($eventId, $personId, self::SCORE_JUSTIFICADO);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Update attendance score
     */
    private function updateAttendanceScore(EventAttend $attendance, float $score): void
    {
        // Will be implemented after migration adds attendance_score column
        // $attendance->setAttendanceScore($score);
        $attendance->save();
    }

    /**
     * Update ranking table
     */
    private function updateRanking(int $eventId, int $personId, float $score): void
    {
        // Get event to find group and year
        $event = EventQuery::create()->findPk($eventId);
        if ($event === null) {
            return;
        }

        $year = (int)date('Y');
        
        // This will use the catequese_ranking table created in migration
        // For now, this is a placeholder
        $con = Propel::getWriteConnection('default');
        
        $sql = "INSERT INTO catequese_ranking 
                (person_id, group_id, year, total_score, present_count, justified_count, unjustified_count, last_updated)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE
                total_score = total_score + ?,
                present_count = present_count + IF(? = 1.0, 1, 0),
                justified_count = justified_count + IF(? = 0.5, 1, 0),
                last_updated = NOW()";
        
        // Will be properly implemented after migration
    }

    /**
     * Get ranking for a group and year
     */
    public function getRanking(int $groupId, int $year): array
    {
        // Query catequese_ranking table
        $con = Propel::getReadConnection('default');
        
        $sql = "SELECT 
                    r.person_id,
                    r.total_score,
                    r.present_count,
                    r.justified_count,
                    r.unjustified_count,
                    p.per_FirstName,
                    p.per_LastName
                FROM catequese_ranking r
                JOIN person_per p ON r.person_id = p.per_ID
                WHERE r.group_id = ? AND r.year = ?
                ORDER BY r.total_score DESC, p.per_LastName ASC";
        
        // Will be properly implemented after migration
        return [];
    }

    /**
     * Export ranking to CSV
     */
    public function exportRankingCSV(int $groupId, int $year): string
    {
        $ranking = $this->getRanking($groupId, $year);
        
        $csv = "Posição,Nome,Pontuação Total,Presenças,Faltas Justificadas,Faltas\n";
        
        $position = 1;
        foreach ($ranking as $entry) {
            $csv .= sprintf(
                "%d,%s,%.1f,%d,%d,%d\n",
                $position++,
                $entry['per_FirstName'] . ' ' . $entry['per_LastName'],
                $entry['total_score'],
                $entry['present_count'],
                $entry['justified_count'],
                $entry['unjustified_count']
            );
        }
        
        return $csv;
    }
}
