# Guia de Instalação - Plugin Catequese

## Pré-requisitos

- ChurchCRM 7.0.0 ou superior
- PHP 8.4+
- MySQL 8.0+
- Acesso administrativo ao ChurchCRM
- Acesso ao servidor (cPanel, SSH, ou File Manager)

## Instalação Passo a Passo

### 1. Upload dos Arquivos

#### Via cPanel File Manager

1. Acesse o cPanel da sua hospedagem
2. Abra o **File Manager**
3. Navegue até a pasta do ChurchCRM: `public_html/crm/` (ou onde está instalado)
4. Vá para: `src/plugins/core/`
5. Faça upload da pasta `catequese/` completa
6. Verifique se a estrutura está correta:
   ```
   src/plugins/core/catequese/
   ├── plugin.json
   ├── README.md
   ├── INSTALL.md
   ├── src/
   ├── routes/
   ├── views/
   └── docs/
   ```

#### Via FTP/SFTP

1. Conecte-se ao servidor via FileZilla ou similar
2. Navegue até `[raiz-churchcrm]/src/plugins/core/`
3. Faça upload da pasta `catequese/`

#### Via SSH (se disponível)

```bash
cd /caminho/para/churchcrm/src/plugins/core/
git clone [url-do-repositorio] catequese
# ou
unzip catequese.zip
```

### 2. Executar Migration SQL

#### Via cPanel phpMyAdmin

1. Acesse o cPanel → **phpMyAdmin**
2. Selecione o banco de dados do ChurchCRM (ex: `churchcrm_db`)
3. Clique na aba **SQL**
4. Abra o arquivo `src/mysql/upgrade/catequese-1.0.0.sql` em um editor de texto
5. Copie todo o conteúdo
6. Cole na área de texto do phpMyAdmin
7. Clique em **Executar** (Go)
8. Verifique se aparece mensagem de sucesso

#### Via linha de comando (SSH)

```bash
mysql -u usuario -p churchcrm_db < src/mysql/upgrade/catequese-1.0.0.sql
```

#### Verificar se a migration foi aplicada

Execute esta query no phpMyAdmin:

```sql
SELECT * FROM config WHERE cfg_name = 'plugin.catequese.migration_version';
```

Deve retornar uma linha com `cfg_value = '1.0.0'`.

### 3. Ativar o Plugin

1. Faça login no ChurchCRM como **administrador**
2. Navegue para: **Admin → Plugin Management** (ou **Plugins → Management**)
3. Localize **"Módulo de Catequese"** na lista
4. Clique no botão **"Enable"** ou **"Ativar"**
5. Aguarde a confirmação de ativação
6. Recarregue a página

### 4. Verificar Instalação

#### Verificar Menu

Após ativar, você deve ver um novo menu **"Catequese"** na barra de navegação com os itens:
- Rematrícula
- Documentos Pendentes
- Tô Aqui Jesus - Presença
- Ranking

#### Verificar Hooks

Acesse o perfil de qualquer pessoa e verifique se aparece uma nova aba **"Documentos"**.

#### Verificar Dashboard

No dashboard principal, deve aparecer um widget **"Documentos Pendentes - Catequese"** (se houver catequizandos cadastrados).

### 5. Configuração Inicial

Agora execute as etapas de configuração na ordem:

#### Etapa 01: Campos Customizados de Documentos
📄 Consulte: `docs/ETAPA-01-CONFIGURACAO.md`

1. Acesse: **People → Admin → Person Custom Fields**
2. Crie os 9 campos listados no guia
3. Configure permissões de segurança

#### Etapa 02: Grupos de Catequese
📄 Consulte: `docs/ETAPA-02-CONFIGURACAO.md`

1. Acesse: **Groups → Edit Group Types**
2. Crie o tipo "Catequese"
3. Configure roles: Catequizando, Catequista, Auxiliar
4. Crie grupos por ano letivo e turma
5. Habilite Group-Specific Properties em cada grupo

#### Etapa 06: Finance - Taxa de Matrícula
📄 Consulte: `docs/ETAPA-06-CONFIGURACAO.md`

1. Acesse: **Admin → Donation Funds**
2. Crie o fundo "Taxa de Matrícula Catequese"
3. Configure valores e prazos

### 6. Testar Funcionalidades

#### Teste 1: Documentos Pendentes

1. Cadastre uma pessoa
2. Adicione-a a um grupo de catequese
3. Não preencha os campos de documentos
4. Acesse o dashboard → deve aparecer alerta
5. Acesse **Catequese → Documentos Pendentes** → deve listar a pessoa

#### Teste 2: Rematrícula

1. Crie um grupo do ano anterior (ex: 2024)
2. Adicione alguns membros
3. Crie um grupo do ano atual (ex: 2025)
4. Acesse **Catequese → Rematrícula**
5. Siga o wizard e migre os membros
6. Verifique se foram movidos para o novo grupo

#### Teste 3: Ranking

1. Crie um evento vinculado a um grupo de catequese
2. Use o Kiosk Manager para registrar presenças
3. Acesse **Catequese → Ranking**
4. Selecione o grupo e ano
5. Verifique se as presenças aparecem com pontuação 1.0

## Troubleshooting

### Plugin não aparece na lista

**Causa**: Arquivo `plugin.json` não foi encontrado ou está com erro de sintaxe.

**Solução**:
1. Verifique se o arquivo existe em `src/plugins/core/catequese/plugin.json`
2. Valide o JSON em https://jsonlint.com
3. Verifique permissões do arquivo (644)

### Erro ao ativar plugin

**Causa**: Classe principal não foi encontrada ou tem erro de sintaxe PHP.

**Solução**:
1. Verifique se existe `src/plugins/core/catequese/src/CatequesePlugin.php`
2. Verifique logs de erro do PHP no cPanel: **Logs → Error Log**
3. Procure por erros de namespace ou sintaxe

### Menu Catequese não aparece

**Causa**: Plugin ativado mas hook não está funcionando.

**Solução**:
1. Limpe o cache do navegador (Ctrl+F5)
2. Faça logout e login novamente
3. Verifique se seu usuário tem permissão "Edit Records"

### Migration SQL falhou

**Causa**: Tabelas já existem ou erro de sintaxe SQL.

**Solução**:
1. Verifique a mensagem de erro no phpMyAdmin
2. Se tabela já existe, use `DROP TABLE IF EXISTS catequese_ranking;` antes de executar
3. Para `ALTER TABLE`, verifique se as colunas já existem: `SHOW COLUMNS FROM event_attend;`

### Erro 500 ao acessar rotas do plugin

**Causa**: Erro PHP nas rotas ou services.

**Solução**:
1. Ative debug no ChurchCRM: `Admin → System Settings → Debug Mode: On`
2. Acesse a rota novamente
3. Leia a mensagem de erro completa
4. Verifique logs: cPanel → **Error Logs**

### Widget não aparece no dashboard

**Causa**: Hook `DASHBOARD_WIDGETS` não está registrado ou há erro no service.

**Solução**:
1. Verifique se o plugin está ativado
2. Verifique se há catequizandos cadastrados
3. Inspecione o console do navegador (F12) para erros JavaScript

## Desinstalação

Se precisar remover o plugin:

### 1. Desativar Plugin

1. **Admin → Plugin Management**
2. Localize "Módulo de Catequese"
3. Clique em **"Disable"**

### 2. Remover Dados (Opcional)

Se quiser remover completamente os dados:

```sql
-- Remover tabela de ranking
DROP TABLE IF EXISTS catequese_ranking;

-- Remover colunas adicionadas
ALTER TABLE event_attend
  DROP COLUMN IF EXISTS justification_text,
  DROP COLUMN IF EXISTS justification_date,
  DROP COLUMN IF EXISTS justification_by,
  DROP COLUMN IF EXISTS attendance_score;

-- Remover configuração
DELETE FROM config WHERE cfg_name = 'plugin.catequese.migration_version';
```

**⚠️ ATENÇÃO**: Isso apagará permanentemente o histórico de ranking e justificativas!

### 3. Remover Arquivos

Via cPanel File Manager ou FTP:
1. Navegue até `src/plugins/core/`
2. Delete a pasta `catequese/`

## Atualização

Para atualizar o plugin para uma versão futura:

1. **Backup**: Faça backup do banco de dados e da pasta do plugin
2. **Desativar**: Desative o plugin temporariamente
3. **Substituir**: Substitua os arquivos pela nova versão
4. **Migration**: Execute novas migrations SQL (se houver)
5. **Reativar**: Ative o plugin novamente
6. **Testar**: Verifique se tudo funciona corretamente

## Suporte

- **Documentação**: Consulte os arquivos em `docs/`
- **README**: Leia `README.md` para visão geral
- **GitHub Issues**: https://github.com/ChurchCRM/CRM/issues
- **Comunidade**: Fórum do ChurchCRM

## Checklist de Instalação

Use este checklist para garantir que tudo foi configurado:

- [ ] Arquivos do plugin copiados para `src/plugins/core/catequese/`
- [ ] Migration SQL executada com sucesso
- [ ] Plugin ativado em Plugin Management
- [ ] Menu "Catequese" aparece na navegação
- [ ] Campos customizados de documentos criados (Etapa 01)
- [ ] Tipo de grupo "Catequese" criado (Etapa 02)
- [ ] Grupos de catequese criados por ano/turma
- [ ] Group-Specific Properties habilitadas
- [ ] Fundo "Taxa de Matrícula Catequese" criado (Etapa 06)
- [ ] Testes de rematrícula realizados
- [ ] Testes de ranking realizados
- [ ] Widget de documentos pendentes aparece no dashboard

---

**Parabéns! O Plugin Catequese está instalado e pronto para uso! 🎉**
