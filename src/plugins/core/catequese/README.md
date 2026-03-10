# Plugin Catequese - ChurchCRM

Sistema completo para gestão de catequese paroquial.

## Funcionalidades

### 1. Campos Customizados para Documentos (Etapa 01)
- Campos personalizados no perfil de cada pessoa para documentos obrigatórios
- RG (número, data de emissão, arquivo)
- Comprovante de Residência (arquivo, data - validade 3 meses)
- Certidão de Batismo (arquivo, data)
- Foto 3x4
- Flag de documentação completa

### 2. Grupos de Catequese por Ano Letivo (Etapa 02)
- Tipo de grupo "Catequese"
- Grupos organizados por ano letivo e turma
- Roles: Catequizando, Catequista, Auxiliar
- Group-Specific Properties:
  - Ano letivo
  - Status de matrícula (Inscrito/Confirmado/Pendente Docs/Cancelado)
  - Taxa paga (Sim/Não)
  - Data de pagamento

### 3. Validação de Responsável para Menores (Etapa 03)
- Verificação automática de idade (< 18 anos)
- Alerta visual no perfil se menor sem responsável
- Bloqueio de confirmação de matrícula sem responsável
- API para vincular responsável

### 4. Alertas Visuais de Documentos Pendentes (Etapa 04)
- Badge no perfil da pessoa
- Banner no GroupView
- Widget no dashboard
- Alertas em tempo real (sem necessidade de cron)

### 5. Fluxo de Rematrícula Anual (Etapa 05)
- Wizard em 4 passos
- Seleção de grupo de origem e destino
- Revisão de lista de membros
- Migração automática com reset de propriedades
- Mantém documentos pessoais

### 6. Registro de Pagamento de Taxa (Etapa 06)
- Integração com módulo Finance nativo
- Fundo "Taxa de Matrícula Catequese"
- Consulta de status de pagamento
- Alertas de inadimplência

### 7. Tô Aqui Jesus - Presença com Justificativa (Etapa 07)
- Sistema de pontuação:
  - Presente via kiosk: 1.0 ponto (imutável)
  - Falta justificada: 0.5 pontos
  - Falta sem justificativa: 0 pontos
- Registro de justificativas pela secretaria
- Ranking acumulado por ano letivo
- Exportação CSV para sorteios
- Integração com Kiosk Manager existente

## Instalação

### Pré-requisitos
- ChurchCRM 7.0.0 ou superior
- PHP 8.4+
- MySQL 8.0+

### Passos

1. **Copiar plugin para o diretório correto**
   ```
   src/plugins/core/catequese/
   ```

2. **Executar migration SQL**
   ```sql
   mysql -u usuario -p churchcrm_db < src/mysql/upgrade/catequese-1.0.0.sql
   ```

3. **Ativar o plugin**
   - Acessar: Admin → Plugin Management
   - Localizar "Módulo de Catequese"
   - Clicar em "Ativar"

4. **Configurar campos customizados (Etapa 01)**
   - People → Admin → Person Custom Fields
   - Criar campos conforme documentação

5. **Criar tipo de grupo Catequese (Etapa 02)**
   - Groups → Edit Group Types
   - Adicionar tipo "Catequese"
   - Criar grupos por turma

6. **Configurar Finance (Etapa 06)**
   - Admin → Donation Funds
   - Criar fundo "Taxa de Matrícula Catequese"

## Uso

### Menu Catequese
Após ativação, um novo menu "Catequese" aparecerá na navegação com:
- Rematrícula
- Documentos Pendentes
- Tô Aqui Jesus - Presença
- Ranking

### Rematrícula Anual
1. Acessar: Catequese → Rematrícula
2. Selecionar grupo de origem (ano anterior)
3. Selecionar grupo de destino (novo ano)
4. Revisar lista de membros
5. Executar migração

### Gestão de Presença
1. Acessar: Catequese → Tô Aqui Jesus - Presença
2. Selecionar grupo e evento
3. Registrar justificativas de falta
4. Sistema atualiza ranking automaticamente

### Ranking e Sorteios
1. Acessar: Catequese → Ranking
2. Selecionar grupo e ano letivo
3. Visualizar classificação
4. Exportar CSV para sorteio

## Estrutura de Arquivos

```
src/plugins/core/catequese/
├── plugin.json                          # Manifest do plugin
├── README.md                            # Esta documentação
├── src/
│   ├── CatequesePlugin.php             # Classe principal
│   └── Service/
│       ├── DocumentService.php         # Lógica de documentos
│       ├── MatriculaService.php        # Lógica de rematrícula
│       └── PresencaService.php         # Lógica de presença e ranking
├── routes/
│   └── routes.php                      # Rotas MVC e API
└── views/
    ├── rematricula.php                 # Wizard de rematrícula
    ├── documentos.php                  # Lista de docs pendentes
    ├── presenca.php                    # Gestão de presença
    └── ranking.php                     # Ranking e exportação
```

## API Endpoints

### Rematrícula
- `POST /plugins/catequese/api/rematricula` - Executar migração de membros
- `GET /plugins/catequese/api/grupos/{year}` - Listar grupos por ano

### Presença
- `POST /plugins/catequese/api/attendance/{eventId}/{personId}/justify` - Registrar justificativa
- `GET /plugins/catequese/api/ranking/{groupId}/{year}/export` - Exportar ranking CSV

## Hooks Utilizados

- `PERSON_VIEW_TABS` - Adiciona tab de documentos no perfil
- `DASHBOARD_WIDGETS` - Widget de documentos pendentes
- `EVENT_CHECKIN` - Atualiza ranking ao registrar presença
- `PERSON_UPDATED` - Valida responsável de menores
- `MENU_BUILDING` - Adiciona menu Catequese

## Banco de Dados

### Tabelas Criadas
- `catequese_ranking` - Ranking acumulado por pessoa/grupo/ano

### Colunas Adicionadas
- `event_attend.justification_text` - Texto da justificativa
- `event_attend.justification_date` - Data do registro
- `event_attend.justification_by` - Quem registrou
- `event_attend.attendance_score` - Pontuação (0, 0.5 ou 1.0)

## Suporte

Para dúvidas ou problemas:
- Documentação ChurchCRM: https://docs.churchcrm.io
- GitHub Issues: https://github.com/ChurchCRM/CRM/issues
- Wiki: https://github.com/ChurchCRM/CRM/wiki

## Licença

Este plugin segue a mesma licença do ChurchCRM (MIT License).

## Changelog

### v1.0.0 (2026-03-10)
- Versão inicial
- Todas as 7 etapas implementadas
- Sistema completo de rematrícula
- Gestão de documentos
- Tô Aqui Jesus com ranking
