# Etapa 01 - Configuração de Campos Customizados para Documentos

## Objetivo
Criar campos personalizados no perfil de cada pessoa para registrar status dos documentos obrigatórios da catequese.

## Tipo de Implementação
**Configuração nativa** - Sem necessidade de código PHP. Usa a interface administrativa do ChurchCRM.

## Passo a Passo

### 1. Acessar o Editor de Campos Personalizados

1. Faça login como administrador
2. Navegue para: **People → Admin → Person Custom Fields**
3. Você verá a interface de gerenciamento de campos customizados

### 2. Criar os Campos de Documentos

Crie cada um dos seguintes campos clicando em "Add New Custom Field":

#### Campo 1: RG - Número
- **Field Name**: `rg_numero`
- **Display Name**: `RG - Número`
- **Type**: Text
- **Max Length**: 50
- **Security**: Restrito à secretaria (se disponível)
- **Order**: 1

#### Campo 2: RG - Data de Emissão
- **Field Name**: `rg_data_emissao`
- **Display Name**: `RG - Data de Emissão`
- **Type**: Date
- **Order**: 2

#### Campo 3: RG - Arquivo Digitalizado
- **Field Name**: `rg_arquivo`
- **Display Name**: `RG - Arquivo`
- **Type**: Text
- **Max Length**: 200
- **Help Text**: `URL ou caminho do arquivo digitalizado do RG`
- **Security**: Restrito à secretaria
- **Order**: 3

#### Campo 4: Comprovante de Residência - Arquivo
- **Field Name**: `comprovante_residencia`
- **Display Name**: `Comprovante de Residência`
- **Type**: Text
- **Max Length**: 200
- **Help Text**: `Arquivo PDF ou foto do comprovante`
- **Security**: Restrito à secretaria
- **Order**: 4

#### Campo 5: Comprovante de Residência - Data
- **Field Name**: `comprovante_residencia_data`
- **Display Name**: `Comprovante - Data`
- **Type**: Date
- **Help Text**: `Data do comprovante (validade de 3 meses)`
- **Order**: 5

#### Campo 6: Certidão de Batismo - Arquivo
- **Field Name**: `certidao_batismo`
- **Display Name**: `Certidão de Batismo`
- **Type**: Text
- **Max Length**: 200
- **Help Text**: `Arquivo digitalizado da certidão de batismo`
- **Security**: Restrito à secretaria
- **Order**: 6

#### Campo 7: Certidão de Batismo - Data
- **Field Name**: `certidao_batismo_data`
- **Display Name**: `Certidão - Data`
- **Type**: Date
- **Order**: 7

#### Campo 8: Foto 3x4
- **Field Name**: `foto_3x4`
- **Display Name**: `Foto 3x4`
- **Type**: Text
- **Max Length**: 200
- **Help Text**: `Arquivo da foto 3x4 do catequizando`
- **Security**: Restrito à secretaria
- **Order**: 8

#### Campo 9: Documentação Completa (Flag)
- **Field Name**: `documentacao_completa`
- **Display Name**: `Documentação Completa`
- **Type**: Boolean (Yes/No)
- **Help Text**: `Marcar quando todos os documentos foram verificados pelo secretário`
- **Security**: Restrito à secretaria
- **Order**: 9

### 3. Configurar Permissões de Segurança

Para campos que contêm documentos sensíveis (arquivos), configure:

1. Clique no ícone de edição do campo
2. Na seção "Security", selecione: **Admin Only** ou **Secretary**
3. Isso garante que apenas usuários autorizados possam visualizar/editar esses campos

### 4. Verificar a Configuração

1. Acesse o perfil de qualquer pessoa: **People → View Person**
2. Clique na aba **Custom Fields**
3. Você deve ver todos os 9 campos criados
4. Teste preenchendo alguns campos

### 5. Orientações de Uso

#### Para a Secretaria:

**Ao cadastrar um novo catequizando:**
1. Preencha os dados básicos da pessoa
2. Vá para a aba "Custom Fields"
3. Registre os dados dos documentos:
   - Digite o número do RG
   - Faça upload dos arquivos (RG, comprovante, certidão, foto) para um servidor/pasta
   - Insira o caminho ou URL de cada arquivo nos campos correspondentes
   - Registre as datas dos documentos
4. **Importante**: Só marque "Documentação Completa" após verificar fisicamente todos os documentos

**Validação de Comprovante de Residência:**
- O comprovante tem validade de **3 meses**
- O plugin Catequese alertará automaticamente quando estiver vencido
- Lembre-se de atualizar a data sempre que receber um novo comprovante

#### Upload de Arquivos:

O ChurchCRM não possui sistema nativo de upload no perfil da pessoa. Recomendações:

**Opção 1: Pasta no servidor**
```
/uploads/catequese/documentos/{person_id}/
  - rg.pdf
  - comprovante.pdf
  - certidao.pdf
  - foto.jpg
```
Depois insira o caminho relativo no campo: `/uploads/catequese/documentos/123/rg.pdf`

**Opção 2: Google Drive / OneDrive**
- Faça upload para uma pasta compartilhada
- Gere link de compartilhamento
- Insira o link no campo correspondente

**Opção 3: Sistema de arquivos da família**
- Use o sistema nativo de upload de arquivos do ChurchCRM para famílias
- Vincule os arquivos à família do catequizando
- Referencie no campo customizado

### 6. Integração com o Plugin

Após criar estes campos, o **Plugin Catequese** automaticamente:

✅ Exibirá alertas visuais quando documentos estiverem pendentes  
✅ Verificará a validade do comprovante de residência (3 meses)  
✅ Mostrará um widget no dashboard com contadores de docs pendentes  
✅ Adicionará uma aba "Documentos" no perfil da pessoa com status visual  

## Troubleshooting

### Problema: Campos não aparecem no perfil
**Solução**: Verifique se você está logado com permissões de admin ou secretaria.

### Problema: Não consigo fazer upload de arquivos
**Solução**: O ChurchCRM não tem upload direto em custom fields. Use uma das opções descritas acima (pasta no servidor, Google Drive, ou sistema de arquivos de família).

### Problema: Quero adicionar mais campos
**Solução**: Você pode criar quantos campos customizados quiser. O plugin funcionará com os campos padrão listados acima, mas você pode adicionar outros conforme necessário.

## Próximos Passos

Após concluir esta etapa, prossiga para:
- **Etapa 02**: Configuração de Grupos de Catequese por Ano Letivo
