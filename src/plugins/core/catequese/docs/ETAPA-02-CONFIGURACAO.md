# Etapa 02 - Configuração de Grupos de Catequese por Ano Letivo

## Objetivo
Estruturar grupos anuais para catequese, permitindo organização por turmas e anos letivos, com controle de matrícula e pagamento de taxa.

## Tipo de Implementação
**Configuração nativa** - Sem necessidade de código PHP. Usa a interface administrativa do ChurchCRM.

## Passo a Passo

### Parte 1: Criar Tipo de Grupo "Catequese"

#### 1. Acessar Editor de Tipos de Grupo

1. Faça login como administrador
2. Navegue para: **Groups → Edit Group Types**
3. Você verá a lista de tipos de grupos existentes

#### 2. Adicionar Novo Tipo

1. Clique em **"Add New Group Type"**
2. Preencha:
   - **Type Name**: `Catequese`
   - **Description**: `Grupos de catequese organizados por ano letivo e turma`
3. Clique em **Save**

#### 3. Configurar Roles (Papéis) do Grupo

Após criar o tipo, configure os papéis disponíveis:

1. Localize o tipo "Catequese" na lista
2. Clique em **"Manage Roles"**
3. Adicione os seguintes roles:

**Role 1: Catequizando**
- **Role Name**: `Catequizando`
- **Order**: 1
- **Default**: Yes (marcar como padrão)

**Role 2: Catequista**
- **Role Name**: `Catequista`
- **Order**: 2

**Role 3: Auxiliar**
- **Role Name**: `Auxiliar`
- **Order**: 3

### Parte 2: Criar Grupos por Ano Letivo

#### 1. Criar Primeiro Grupo

1. Navegue para: **Groups → Add New Group**
2. Preencha:
   - **Group Name**: `Catequese 1ª Eucaristia - 2025 - Turma A`
   - **Group Type**: `Catequese`
   - **Description**: `Turma A da primeira eucaristia para o ano letivo de 2025`
3. Clique em **Save**

#### 2. Padrão de Nomenclatura Recomendado

Use este formato para facilitar a organização:

```
Catequese [Etapa] - [Ano] - Turma [Letra]
```

**Exemplos:**
- `Catequese 1ª Eucaristia - 2025 - Turma A`
- `Catequese 1ª Eucaristia - 2025 - Turma B`
- `Catequese Crisma - 2025 - Turma A`
- `Catequese Batismo Adultos - 2025 - Turma Única`

#### 3. Criar Múltiplas Turmas

Repita o processo para cada turma que sua paróquia oferece:

- Primeira Eucaristia (geralmente 2-3 turmas)
- Crisma (1-2 turmas)
- Batismo de Adultos (1 turma)
- Perseverança (opcional)

### Parte 3: Habilitar Group-Specific Properties

Para cada grupo criado, habilite propriedades específicas:

#### 1. Acessar Configurações do Grupo

1. Vá para **Groups → View All Groups**
2. Clique no grupo desejado
3. Clique em **"Group Settings"**

#### 2. Ativar Propriedades Específicas

1. Localize a opção **"Enable Group-Specific Properties"**
2. Marque como **Yes**
3. Clique em **Save**

#### 3. Definir Campos Específicos do Grupo

Após ativar, configure os campos customizados do grupo:

1. Clique em **"Manage Group Properties"**
2. Adicione os seguintes campos:

**Campo 1: Ano Letivo**
- **Property Name**: `ano_letivo`
- **Display Name**: `Ano Letivo`
- **Type**: Number (Year)
- **Required**: Yes
- **Order**: 1

**Campo 2: Status de Matrícula**
- **Property Name**: `status_matricula`
- **Display Name**: `Status de Matrícula`
- **Type**: Dropdown
- **Options**: 
  - `Inscrito`
  - `Confirmado`
  - `Pendente Docs`
  - `Cancelado`
- **Default**: `Inscrito`
- **Order**: 2

**Campo 3: Taxa Paga**
- **Property Name**: `taxa_paga`
- **Display Name**: `Taxa Paga`
- **Type**: Boolean (Yes/No)
- **Default**: No
- **Order**: 3

**Campo 4: Data de Pagamento**
- **Property Name**: `data_pagamento`
- **Display Name**: `Data de Pagamento`
- **Type**: Date
- **Order**: 4

### Parte 4: Adicionar Membros aos Grupos

#### 1. Adicionar Catequizandos

**Método 1: Individual**
1. Acesse o grupo: **Groups → [Nome do Grupo]**
2. Clique em **"Add Member"**
3. Busque a pessoa
4. Selecione o role: **Catequizando**
5. Preencha as propriedades específicas:
   - Ano Letivo: 2025
   - Status: Inscrito
   - Taxa Paga: No
6. Clique em **Save**

**Método 2: Em Lote (usando Cart)**
1. Vá para **People → Person List**
2. Filtre os catequizandos desejados
3. Adicione-os ao **Cart** (carrinho)
4. Vá para **Cart → Add to Group**
5. Selecione o grupo de catequese
6. Escolha o role padrão: **Catequizando**
7. Confirme a adição

#### 2. Adicionar Catequistas e Auxiliares

1. Acesse o grupo
2. Clique em **"Add Member"**
3. Busque o catequista
4. Selecione o role: **Catequista** ou **Auxiliar**
5. Salve

### Parte 5: Organização por Ano Letivo

#### Estrutura Recomendada

```
Ano Letivo 2024 (concluído)
├── Catequese 1ª Eucaristia - 2024 - Turma A
├── Catequese 1ª Eucaristia - 2024 - Turma B
└── Catequese Crisma - 2024 - Turma A

Ano Letivo 2025 (atual)
├── Catequese 1ª Eucaristia - 2025 - Turma A
├── Catequese 1ª Eucaristia - 2025 - Turma B
├── Catequese 1ª Eucaristia - 2025 - Turma C
└── Catequese Crisma - 2025 - Turma A

Ano Letivo 2026 (futuro - para rematrícula)
├── Catequese 1ª Eucaristia - 2026 - Turma A
└── (grupos criados conforme necessário)
```

#### Desativar Grupos Antigos

Ao final do ano letivo:

1. Acesse o grupo do ano anterior
2. Vá para **"Group Settings"**
3. Marque **"Inactive"**
4. Salve

Isso mantém o histórico mas remove o grupo das listas ativas.

### Parte 6: Integração com o Plugin Catequese

Após configurar os grupos, o **Plugin Catequese** automaticamente:

✅ Listará os grupos por ano no wizard de rematrícula  
✅ Permitirá migrar membros de um ano para outro  
✅ Resetará status de matrícula e taxa ao rematricular  
✅ Manterá histórico de participação por grupo  

## Fluxo de Trabalho Anual

### Início do Ano Letivo (Janeiro/Fevereiro)

1. **Criar grupos do novo ano**
   - Ex: Catequese 1ª Eucaristia - 2026 - Turma A, B, C

2. **Executar rematrícula** (via plugin)
   - Migrar catequizandos dos grupos de 2025 para 2026
   - Status volta para "Inscrito"
   - Taxa Paga volta para "No"

3. **Coletar documentação**
   - Verificar campos customizados (Etapa 01)
   - Atualizar comprovantes vencidos

4. **Confirmar matrículas**
   - Após pagamento de taxa e documentos OK
   - Mudar status para "Confirmado"

### Durante o Ano Letivo

1. **Registrar presenças**
   - Usar Kiosk Manager ou plugin Tô Aqui Jesus
   - Sistema acumula pontuação automaticamente

2. **Gerenciar faltas**
   - Registrar justificativas via plugin
   - Pontuar 0.5 para faltas justificadas

3. **Acompanhar ranking**
   - Visualizar classificação em tempo real
   - Exportar CSV para sorteios

### Final do Ano Letivo (Novembro/Dezembro)

1. **Desativar grupos do ano corrente**
   - Marcar como "Inactive"
   - Preserva histórico

2. **Preparar próximo ano**
   - Criar grupos do ano seguinte
   - Planejar turmas conforme demanda

## Troubleshooting

### Problema: Não consigo criar tipo "Catequese"
**Solução**: Verifique se você está logado como administrador. Apenas admins podem criar tipos de grupo.

### Problema: Propriedades específicas não aparecem
**Solução**: Certifique-se de ter ativado "Enable Group-Specific Properties" nas configurações do grupo.

### Problema: Membros não aparecem no grupo
**Solução**: Verifique se o role foi atribuído corretamente ao adicionar o membro.

### Problema: Quero renomear um grupo
**Solução**: Acesse o grupo → "Edit Group" → altere o nome → Save.

## Próximos Passos

Após concluir esta etapa, prossiga para:
- **Etapa 03**: Validação de Responsável para Menores (código PHP)
- **Etapa 05**: Usar o wizard de rematrícula do plugin
