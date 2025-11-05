# Semanas Repetidas - Guia de Utilização

## Descrição
O script **Semanas Repetidas** permite criar múltiplas reservas de salas de forma automática ao longo de várias semanas, facilitando o agendamento de eventos recorrentes.

## Funcionalidades

### 1. Seleção de Sala
- Interface intuitiva com dropdown
- Lista todas as salas disponíveis ordenadas alfabeticamente
- Não é necessário saber o ID da sala

### 2. Seleção de Utilizador (Requisitor)
- Permite escolher o utilizador que aparecerá como requisitor das reservas
- Útil para criar reservas em nome de outros utilizadores
- Lista todos os utilizadores do sistema

### 3. Seleção de Tempos (Horários)
- Interface com checkboxes para seleção múltipla
- Permite selecionar um ou vários tempos simultaneamente
- Todos os tempos selecionados serão reservados em cada semana
- Área com scroll para facilitar a visualização de muitos horários

### 4. Dia da Semana
- Escolha o dia da semana para as reservas
- Opções: Segunda a Domingo
- O sistema calcula automaticamente as datas corretas

### 5. Data de Início
- Campo de data com calendário
- Define a semana inicial para começar as reservas
- O sistema ajusta para o próximo dia da semana selecionado se necessário

### 6. Número de Semanas
- Define quantas semanas consecutivas terão reservas
- Máximo de 52 semanas (1 ano)
- Mínimo de 1 semana

## Exemplo de Uso

**Cenário:** Criar reservas para aulas de matemática às segundas-feiras

1. **Sala:** Sala 101
2. **Utilizador:** Prof. João Silva
3. **Tempos:** 
   - 08:00-08:50
   - 09:00-09:50
   - 10:00-10:50
4. **Dia da Semana:** Segunda-feira
5. **Data de Início:** 2024-01-08
6. **Semanas:** 12

**Resultado:** O sistema criará 36 reservas (3 tempos × 12 semanas) para as segundas-feiras, começando em 08/01/2024 e terminando após 12 semanas.

## Validações e Segurança

### Validações Implementadas
- Verifica se a sala existe
- Verifica se o utilizador existe
- Verifica se os tempos existem
- Detecta reservas duplicadas (não sobrescreve)
- Valida entrada de dados com prepared statements (SQL injection prevention)

### Feedback ao Utilizador
- **Sucesso:** Mostra quantas reservas foram criadas
- **Avisos:** Informa sobre reservas duplicadas que não foram criadas
- **Erros:** Lista todos os erros encontrados durante o processo
- **Resumo:** Apresenta um resumo completo da operação

## Mensagens de Feedback

### Mensagem de Sucesso
```
✓ Sucesso! X reserva(s) criada(s) com sucesso.
```

### Mensagem de Aviso (Duplicadas)
```
⚠ Atenção: X reserva(s) já existia(m) e não foi/foram criada(s).
```

### Resumo Detalhado
```
ℹ Resumo:
- Sala: [Nome da Sala]
- Utilizador: [Nome do Utilizador]
- Tempos selecionados: X
- Semanas: Y
- Total de reservas esperadas: X × Y
- Reservas criadas: Z
```

## Detalhes Técnicos

### Campos da Reserva
- **sala:** ID da sala selecionada
- **tempo:** ID do tempo selecionado
- **data:** Data calculada automaticamente
- **requisitor:** ID do utilizador selecionado
- **aprovado:** Sempre 1 (aprovado automaticamente)
- **motivo:** "Horário adicionado por um administrador através do script de semanas repetidas."
- **extra:** Inclui nome do administrador que criou as reservas

### Algoritmo de Cálculo de Datas
1. Recebe a data de início
2. Identifica o dia da semana da data de início
3. Se necessário, ajusta para o próximo dia da semana selecionado
4. Para cada semana subsequente:
   - Adiciona 7 dias à data anterior
   - Cria reservas para todos os tempos selecionados

## Melhorias em Relação à Versão Anterior

### Versão Antiga
- ❌ Entrada manual de IDs (difícil de usar)
- ❌ Apenas 1 tempo por execução
- ❌ Apenas utilizador logado como requisitor
- ❌ Data específica em vez de dia da semana

### Versão Nova
- ✅ Dropdowns intuitivos com nomes
- ✅ Múltiplos tempos numa única operação
- ✅ Escolha livre do requisitor
- ✅ Seleção por dia da semana (mais flexível)
- ✅ Validação completa com feedback detalhado
- ✅ Prevenção de duplicatas
- ✅ Interface moderna e responsiva

## Acesso

O script está disponível no painel administrativo em:
**Administração → Extensibilidade → Semanasrepetidas**

## Permissões

Apenas administradores têm acesso a este script.
