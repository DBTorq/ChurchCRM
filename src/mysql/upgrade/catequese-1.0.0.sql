-- Migration for Catequese Plugin v1.0.0
-- Adds support for attendance justifications and ranking system

-- Extend event_attend table for justifications
ALTER TABLE event_attend
  ADD COLUMN IF NOT EXISTS justification_text VARCHAR(500) NULL COMMENT 'Motivo da falta justificada',
  ADD COLUMN IF NOT EXISTS justification_date DATETIME NULL COMMENT 'Data do registro da justificativa',
  ADD COLUMN IF NOT EXISTS justification_by INT NULL COMMENT 'ID do usuário que registrou a justificativa',
  ADD COLUMN IF NOT EXISTS attendance_score DECIMAL(3,1) DEFAULT 0 COMMENT 'Pontuação: 1.0 = presente, 0.5 = justificado, 0 = falta';

-- Create catequese_ranking table for accumulated scores
CREATE TABLE IF NOT EXISTS catequese_ranking (
  id INT AUTO_INCREMENT PRIMARY KEY,
  person_id INT NOT NULL COMMENT 'ID da pessoa (catequizando)',
  group_id INT NOT NULL COMMENT 'ID do grupo de catequese',
  year INT NOT NULL COMMENT 'Ano letivo',
  total_score DECIMAL(8,1) DEFAULT 0 COMMENT 'Pontuação total acumulada',
  present_count INT DEFAULT 0 COMMENT 'Total de presenças',
  justified_count INT DEFAULT 0 COMMENT 'Total de faltas justificadas',
  unjustified_count INT DEFAULT 0 COMMENT 'Total de faltas sem justificativa',
  last_updated DATETIME COMMENT 'Última atualização do registro',
  UNIQUE KEY unique_person_group_year (person_id, group_id, year),
  KEY idx_group_year (group_id, year),
  KEY idx_person (person_id),
  CONSTRAINT fk_ranking_person FOREIGN KEY (person_id) REFERENCES person_per(per_ID) ON DELETE CASCADE,
  CONSTRAINT fk_ranking_group FOREIGN KEY (group_id) REFERENCES group_grp(grp_ID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Ranking de presença para Tô Aqui Jesus';

-- Add index to event_attend for better query performance
ALTER TABLE event_attend
  ADD INDEX IF NOT EXISTS idx_event_person (event_id, person_id);

-- Insert version record
INSERT INTO config (cfg_name, cfg_value, cfg_type, cfg_default, cfg_tooltip, cfg_section, cfg_category)
VALUES (
  'plugin.catequese.migration_version',
  '1.0.0',
  'text',
  '1.0.0',
  'Versão da migration do plugin Catequese',
  'Plugins',
  'Catequese'
) ON DUPLICATE KEY UPDATE cfg_value = '1.0.0';
