# Gestão de Materiais

## Visão Geral
O sistema de gestão de materiais permite associar equipamentos e recursos a salas específicas, e os utilizadores podem reservar esses materiais quando fazem uma reserva de sala.

## Funcionalidades

### 1. Gestão de Materiais no Backoffice
Aceda ao painel administrativo e selecione **"Gestão de Materiais"** no menu lateral.

#### Adicionar um Material
1. Preencha o campo "Nome do Material"
2. Clique em "Submeter"
3. Complete a descrição (opcional) e selecione a sala associada
4. Clique em "Criar Material"

#### Editar ou Apagar Materiais
- Utilize os links "EDITAR" ou "APAGAR" na tabela de materiais existentes

### 2. Importação via CSV

#### Formato do Ficheiro CSV
O ficheiro CSV deve seguir este formato:
```
MaterialName,MaterialDescription,RoomID
```

**Exemplo:**
```csv
Projetor HD,Projetor Full HD 1080p com cabo HDMI,sala-uuid-123
Computador Portátil,Dell Latitude 15" com Windows 11,sala-uuid-123
Quadro Interativo,Smart Board 75" com canetas digitais,sala-uuid-456
```

#### Como Obter o RoomID
1. Aceda à página de "Gestão de Materiais"
2. Na parte inferior da página, existe uma tabela de referência com os IDs de todas as salas
3. Copie o ID (UUID) da sala desejada para usar no CSV

#### Passos para Importar
1. Prepare o ficheiro CSV com o formato correto
2. Na página "Gestão de Materiais", localize a secção "Importar Materiais via CSV"
3. Clique em "Escolher ficheiro" e selecione o seu ficheiro CSV
4. Clique em "Importar CSV"
5. O sistema apresentará um relatório com o número de materiais importados com sucesso e eventuais erros

### 3. Reservar Materiais

#### Durante a Reserva de Sala
Quando um utilizador reserva uma sala:
1. Após selecionar a sala, tempo e data
2. Preencher o motivo e informação extra
3. **Uma secção "Materiais Disponíveis" aparecerá se a sala tiver materiais associados**
4. Selecione os materiais desejados marcando as caixas de verificação
5. Clique em "Reservar"

Os materiais selecionados serão reservados juntamente com a sala.

#### Reservas em Massa
A funcionalidade também está disponível para reservas em massa:
1. Selecione múltiplos tempos marcando as caixas de verificação
2. Preencha o motivo e informação extra
3. Selecione os materiais desejados
4. Clique em "Reservar Selecionados"

Os materiais selecionados serão aplicados a todas as reservas criadas.

### 4. Visualizar Materiais Reservados
Na página de detalhes de uma reserva, os materiais reservados são apresentados numa lista com:
- Nome do material
- Descrição (se disponível)

## Notas Importantes

- Cada material está associado a uma sala específica
- Os materiais só aparecem quando se está a reservar a sala correspondente
- A seleção de materiais é opcional
- Ao apagar uma sala, os materiais associados serão também apagados
- Ao apagar uma reserva, as associações de materiais são também apagadas

## Estrutura da Base de Dados

### Tabela `materiais`
- `id` (VARCHAR(99)) - UUID único do material
- `nome` (VARCHAR(255)) - Nome do material
- `descricao` (TEXT) - Descrição opcional do material
- `sala_id` (VARCHAR(99)) - ID da sala associada (Foreign Key)

### Tabela `reservas_materiais`
- `reserva_sala` (VARCHAR(99)) - ID da sala reservada
- `reserva_tempo` (VARCHAR(99)) - ID do tempo reservado
- `reserva_data` (DATE) - Data da reserva
- `material_id` (VARCHAR(99)) - ID do material reservado

A chave primária composta garante que cada material pode ser reservado apenas uma vez por slot de reserva.
