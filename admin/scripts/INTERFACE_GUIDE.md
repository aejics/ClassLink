# Interface Guide - Semanas Repetidas Script

## User Interface Layout

The enhanced script presents a clean, intuitive form with the following sections:

### 1. Header Section
```
┌─────────────────────────────────────────────────┐
│ Semanas repetidas                               │
│                                                 │
│ Este script permite criar reservas repetidas   │
│ de salas ao longo de várias semanas.           │
└─────────────────────────────────────────────────┘
```

### 2. Room and User Selection (Row 1)
```
┌────────────────────────────┬────────────────────────────┐
│ 🏫 Sala                    │ 👤 Utilizador (requisitor) │
│ ┌────────────────────────┐ │ ┌────────────────────────┐ │
│ │ Escolha uma sala     ▼ │ │ │ Escolha um utilizador▼ │ │
│ │                        │ │ │                        │ │
│ │ - Sala 101             │ │ │ - Prof. João Silva     │ │
│ │ - Sala 102             │ │ │ - Prof. Maria Costa    │ │
│ │ - Laboratório          │ │ │ - ...                  │ │
│ └────────────────────────┘ │ └────────────────────────┘ │
└────────────────────────────┴────────────────────────────┘
```

### 3. Time Selection (Row 2)
```
┌──────────────────────────────────────────────────────────┐
│ Tempos (selecione os horários para reservar):           │
│ ┌──────────────────────────────────────────────────────┐ │
│ │ ☐ 08:00-08:50                                        │ │
│ │ ☐ 09:00-09:50                                        │ │
│ │ ☐ 10:00-10:50                                        │ │
│ │ ☐ 11:00-11:50                                        │ │
│ │ ☐ 12:00-12:50                                        │ │
│ │ ... (scrollable)                                     │ │
│ └──────────────────────────────────────────────────────┘ │
│ Selecione um ou mais tempos para reservar em cada semana│
└──────────────────────────────────────────────────────────┘
```

### 4. Schedule Configuration (Row 3)
```
┌──────────────────┬──────────────────────┬──────────────────┐
│ 📅 Dia da semana │ 📆 Data de início    │ 🔢 Número de     │
│                  │   (primeira semana)  │    semanas       │
│ ┌──────────────┐ │ ┌──────────────────┐ │ ┌──────────────┐ │
│ │Segunda-feira▼│ │ │  [Date Picker]   │ │ │      12      │ │
│ │              │ │ │  2024-01-08      │ │ │  (min: 1,    │ │
│ └──────────────┘ │ └──────────────────┘ │ │   max: 52)   │ │
│                  │                      │ └──────────────┘ │
└──────────────────┴──────────────────────┴──────────────────┘
```

### 5. Submit Button
```
┌──────────────────────────────────────────────────────────┐
│            [ Criar Reservas ]                            │
└──────────────────────────────────────────────────────────┘
```

## Feedback Messages

After submission, the user receives detailed feedback:

### Success Message
```
┌──────────────────────────────────────────────────────────┐
│ ✓ Sucesso! 36 reserva(s) criada(s) com sucesso.        │
└──────────────────────────────────────────────────────────┘
```

### Warning Message (if duplicates found)
```
┌──────────────────────────────────────────────────────────┐
│ ⚠ Atenção: 3 reserva(s) já existia(m) e não foi/foram  │
│   criada(s).                                            │
└──────────────────────────────────────────────────────────┘
```

### Error Message (if errors occurred)
```
┌──────────────────────────────────────────────────────────┐
│ ✗ Erros encontrados:                                    │
│   • Tempo inválido: xyz123                              │
│   • Erro ao criar reserva para 2024-01-15 - 08:00-08:50│
└──────────────────────────────────────────────────────────┘
```

### Summary Panel
```
┌──────────────────────────────────────────────────────────┐
│ ℹ Resumo:                                                │
│   - Sala: Sala 101                                      │
│   - Utilizador: Prof. João Silva                        │
│   - Tempos selecionados: 3                              │
│   - Semanas: 12                                         │
│   - Total de reservas esperadas: 36                     │
│   - Reservas criadas: 36                                │
└──────────────────────────────────────────────────────────┘
```

## Visual Flow

```
User Opens Script
       ↓
Selects Room (dropdown)
       ↓
Selects User (dropdown)
       ↓
Checks Multiple Time Slots (checkboxes)
       ↓
Selects Day of Week (dropdown)
       ↓
Enters Start Date (date picker)
       ↓
Enters Number of Weeks (number input)
       ↓
Clicks "Criar Reservas"
       ↓
System Validates All Inputs
       ↓
System Calculates Dates
       ↓
System Creates Reservations
       ↓
System Displays Detailed Feedback
       ↓
Done ✓
```

## Key Features

### ✅ User-Friendly
- No need to remember IDs
- Clear labels and placeholders
- Visual feedback on selections
- Descriptive error messages

### ✅ Flexible
- Select multiple time slots at once
- Choose any user as requisitor
- Schedule up to 52 weeks ahead
- Automatically handles date calculations

### ✅ Safe
- Validates all inputs
- Prevents duplicate reservations
- Shows warnings for existing bookings
- Provides detailed operation summary

### ✅ Efficient
- Bulk reservation creation
- One form submission for multiple reservations
- Clear confirmation of what was created
- Immediate feedback on success or errors

## Comparison: Old vs New

### Old Interface
```
ID Sala: [____]  (need to know the ID)
Tempo (número): [____]  (need to know the ID)
Primeiro Dia: [____]  (specific date)
Semanas a repetir: [____]

→ Only 1 time slot per submission
→ Manual ID entry required
→ Always creates in admin's name
```

### New Interface
```
Sala: [Dropdown with names ▼]
Utilizador: [Dropdown with names ▼]
Tempos:
  ☐ 08:00-08:50
  ☐ 09:00-09:50
  ☐ 10:00-10:50
  ... (select multiple)
Dia da semana: [Dropdown ▼]
Data de início: [Date picker 📅]
Semanas: [12]

→ Multiple time slots in one go
→ User-friendly dropdowns
→ Choose who owns the reservation
→ Flexible day-of-week scheduling
```

## Tips for Use

1. **Select Room First** - This helps you focus on which classroom you're scheduling

2. **Choose Requisitor Carefully** - This is who will be shown as the owner of the reservation

3. **Check Multiple Times** - You can select as many time slots as needed for the recurring schedule

4. **Use Day of Week** - This is more flexible than specific dates, automatically adjusting for each week

5. **Review the Summary** - Always check the summary to confirm the correct number of reservations were created
