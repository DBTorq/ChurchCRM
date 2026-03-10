# Etapa 06 - Configuração de Registro de Pagamento de Taxa

## Objetivo
Configurar o módulo Finance nativo do ChurchCRM para controlar o pagamento da taxa de matrícula da catequese, integrando com o sistema de alertas do plugin.

## Tipo de Implementação
**Configuração nativa + Integração com plugin** - Usa o módulo Finance existente do ChurchCRM.

## Passo a Passo

### Parte 1: Criar Fundo de Doação para Taxa de Matrícula

#### 1. Acessar Gerenciamento de Fundos

1. Faça login como administrador
2. Navegue para: **Admin → Donation Funds** (ou **Finance → Funds**)
3. Você verá a lista de fundos existentes

#### 2. Criar Novo Fundo

1. Clique em **"Add New Fund"**
2. Preencha:
   - **Fund Name**: `Taxa de Matrícula Catequese`
   - **Description**: `Taxa anual de matrícula para grupos de catequese`
   - **Active**: Yes
3. Clique em **Save**
4. **Anote o ID do fundo** (aparecerá na URL ou na lista)

### Parte 2: Configurar Valores de Taxa

#### Definir Valor Padrão

Você pode definir valores diferentes por etapa da catequese:

**Exemplo de estrutura:**
- 1ª Eucaristia: R$ 50,00
- Crisma: R$ 60,00
- Batismo Adultos: R$ 40,00

**Opção 1: Usar um único fundo com valores variáveis**
- Vantagem: Mais simples de gerenciar
- Desvantagem: Não diferencia automaticamente por etapa

**Opção 2: Criar fundos separados por etapa**
- `Taxa Matrícula - 1ª Eucaristia`
- `Taxa Matrícula - Crisma`
- `Taxa Matrícula - Batismo Adultos`
- Vantagem: Relatórios mais detalhados
- Desvantagem: Mais fundos para gerenciar

**Recomendação**: Use Opção 1 para simplicidade.

### Parte 3: Registrar Pledges (Compromissos de Pagamento)

#### 1. Criar Pledge para uma Família

Quando um catequizando se inscreve:

1. Vá para **Finance → Pledges** ou acesse o perfil da família
2. Clique em **"Add New Pledge"**
3. Preencha:
   - **Family**: Selecione a família do catequizando
   - **Fund**: `Taxa de Matrícula Catequese`
   - **Fiscal Year**: Ano letivo corrente (ex: 2025)
   - **Amount**: Valor da taxa (ex: R$ 50,00)
   - **Schedule**: One-time (único)
   - **Due Date**: Data limite de pagamento
4. Clique em **Save**

#### 2. Criar Pledges em Lote (Opcional)

Para criar pledges para todas as famílias de um grupo:

1. Acesse o grupo de catequese
2. Exporte a lista de membros
3. Use a ferramenta de importação CSV do Finance (se disponível)
4. Ou crie manualmente para cada família

### Parte 4: Registrar Pagamentos

#### Método 1: Pagamento Individual

Quando uma família paga a taxa:

1. Vá para **Finance → Deposits** ou **Finance → Payments**
2. Clique em **"Add New Payment"**
3. Preencha:
   - **Family**: Família que pagou
   - **Fund**: `Taxa de Matrícula Catequese`
   - **Amount**: Valor pago
   - **Payment Method**: Dinheiro / Cartão / PIX / Transferência
   - **Date**: Data do pagamento
   - **Check/Reference Number**: (se aplicável)
4. Clique em **Save**

#### Método 2: Via Deposit Slip (Recomendado)

Para registrar múltiplos pagamentos de uma vez:

1. Vá para **Finance → Deposits**
2. Clique em **"Create New Deposit"**
3. Preencha:
   - **Deposit Type**: Bank / Cash
   - **Date**: Data do depósito
   - **Comment**: "Taxas de matrícula catequese - [data]"
4. Adicione os pagamentos:
   - Clique em **"Add Payment"**
   - Selecione a família
   - Selecione o fundo `Taxa de Matrícula Catequese`
   - Insira o valor
   - Repita para cada família
5. Clique em **"Close Deposit"**

### Parte 5: Atualizar Status no Grupo

Após registrar o pagamento, atualize o status no grupo:

1. Acesse o grupo de catequese
2. Localize o membro que pagou
3. Clique em **"Edit Properties"**
4. Atualize:
   - **Taxa Paga**: Yes
   - **Data de Pagamento**: [data do pagamento]
5. Se documentos também estão OK:
   - **Status de Matrícula**: `Confirmado`
6. Salve

### Parte 6: Consultar Status de Pagamento

#### Via Finance Dashboard

1. Vá para **Finance → Pledge Dashboard**
2. Filtre por fundo: `Taxa de Matrícula Catequese`
3. Você verá:
   - Famílias com pledge criado
   - Valor prometido vs. pago
   - Saldo devedor

#### Via Relatórios

1. Vá para **Finance → Reports**
2. Selecione: **"Pledge vs. Payment Report"**
3. Filtre:
   - Fund: `Taxa de Matrícula Catequese`
   - Fiscal Year: Ano corrente
4. Exporte para CSV ou PDF

### Parte 7: Integração com Plugin Catequese

O plugin automaticamente:

✅ **Consulta status de pagamento** ao exibir alertas de documentos  
✅ **Inclui taxa não paga** nos alertas do dashboard  
✅ **Bloqueia confirmação de matrícula** se taxa não foi paga  
✅ **Exibe status visual** no perfil do catequizando  

#### Como o Plugin Verifica Pagamento

O plugin consulta a tabela `pledge_plg` do ChurchCRM:

```sql
SELECT SUM(plg_amount) as total_pago
FROM pledge_plg
WHERE plg_FamID = [family_id]
  AND plg_fundID = [fund_id_taxa_catequese]
  AND plg_PledgeOrPayment = 'Payment'
  AND plg_FYID = [current_fiscal_year]
```

Se `total_pago >= valor_esperado`, considera como pago.

### Parte 8: Fluxo de Trabalho Completo

#### Início do Ano Letivo

1. **Criar pledges para todas as famílias**
   - Valor: R$ 50,00 (ou conforme definido)
   - Due Date: 30 dias após início das aulas

2. **Comunicar às famílias**
   - Enviar e-mail/SMS com boleto ou dados para pagamento
   - Usar sistema de e-mail do ChurchCRM

#### Durante Período de Matrícula

1. **Receber pagamentos**
   - Registrar via deposit slip ou pagamento individual
   - Atualizar status no grupo

2. **Monitorar inadimplência**
   - Usar dashboard do plugin Catequese
   - Enviar lembretes para famílias pendentes

3. **Confirmar matrículas**
   - Após pagamento + documentos OK
   - Mudar status para "Confirmado"

#### Relatórios Mensais

1. **Total arrecadado**
   - Finance → Reports → Fund Summary
   - Filtrar por `Taxa de Matrícula Catequese`

2. **Inadimplentes**
   - Finance → Pledge Dashboard
   - Exportar lista de famílias com saldo devedor

3. **Exportar para contabilidade**
   - Finance → Reports → Detailed Financial Report
   - Período: mês corrente
   - Formato: CSV ou PDF

### Parte 9: Configurações Avançadas

#### Parcelamento de Taxa

Se permitir parcelamento (ex: 3x R$ 17,00):

1. Crie 3 pledges separados:
   - Pledge 1: R$ 17,00 - Vencimento: Mês 1
   - Pledge 2: R$ 17,00 - Vencimento: Mês 2
   - Pledge 3: R$ 16,00 - Vencimento: Mês 3

2. Registre cada pagamento conforme recebido

#### Bolsas / Isenções

Para famílias com isenção:

1. **Opção 1**: Não criar pledge
   - Marcar "Taxa Paga: Yes" manualmente no grupo
   - Adicionar nota no perfil da família

2. **Opção 2**: Criar pledge com valor R$ 0,00
   - Registrar pagamento de R$ 0,00
   - Adicionar comentário: "Bolsa integral"

#### Integração com PIX

Se usar PIX:

1. Gere QR Code PIX para cada família
2. Insira o código PIX no campo de referência do pledge
3. Ao receber, registre o pagamento com a chave PIX como referência

## Troubleshooting

### Problema: Não encontro o menu Finance
**Solução**: Verifique se seu usuário tem permissão "Finance" ativada. Vá para Admin → Users → [seu usuário] → ative "Finance Enabled".

### Problema: Plugin não detecta pagamento
**Solução**: 
1. Verifique se o pagamento foi registrado no fundo correto
2. Confirme que o fiscal year está correto
3. Certifique-se de que o pagamento está marcado como `PledgeOrPayment = 'Payment'` (não 'Pledge')

### Problema: Quero gerar boletos automaticamente
**Solução**: O ChurchCRM não tem geração nativa de boletos. Opções:
- Usar gateway de pagamento externo (PagSeguro, Mercado Pago)
- Integrar via plugin customizado
- Gerar boletos manualmente via banco e registrar pagamentos no sistema

### Problema: Família pagou mas status não atualiza no grupo
**Solução**: O status no grupo é manual. Após registrar o pagamento no Finance, você deve atualizar manualmente o campo "Taxa Paga" nas propriedades do membro do grupo.

## Próximos Passos

Após concluir esta etapa:
- ✅ Sistema completo de catequese está funcional
- ✅ Use o plugin para gerenciar rematrícula, documentos e presença
- ✅ Monitore o dashboard para alertas em tempo real
- ✅ Exporte relatórios mensais para prestação de contas

## Recursos Adicionais

- **Documentação Finance ChurchCRM**: https://docs.churchcrm.io/finance
- **Vídeo tutorial**: (se disponível na comunidade)
- **Suporte**: GitHub Issues do ChurchCRM
