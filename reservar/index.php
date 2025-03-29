<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservar uma Sala</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</head>
<style>
.reserva{
    display:block;
    width:100%;
    height:100%;
    color:white;
    text-decoration:none;
    text-align:center;
}

th .center {
    text-align: center;
}
</style>
<body>
    <div class="h-100 d-flex align-items-center justify-content-center flex-column">
        <h1 class="mt-2">Reservar uma Sala</h1>
        <form action="/reservar/" method="POST" class="d-flex align-items-center">
            <div class="form-floating me-2">
                <select class="form-select" id="sala" name="sala" required onchange="this.form.submit();">
                    <option value='0' selected disabled>Escolha uma sala</option>
                    <option value='multimedia'>Multimédia</option>
                </select>
                <label for="sala" class="form-label">Escolha uma sala</label>
            </div>
        </form>
    </div>
    <?php
    if ($_POST['sala']){
        echo(
            "<div class='mt-3 h-100 d-flex align-items-center justify-content-center flex-column'>
            <table class='table table-bordered'><thead><tr><th scope='col'>Tempos</th>"
        );
        for ($i = 0; $i < 7; $i++){
            $segunda = strtotime("monday this week");
            $dia = date("d-m-Y", strtotime("+{$i} day", $segunda));
            echo "<th scope='col'>{$dia}</th>";
        };
        echo "</tr></thead><tbody>";
        // por cada tempo:
        for ($i = 1; $i <= 7; $i++){
            $horainicio = strtotime("08:05" . "+{$i} hour");
            $horainiciohumana = date("H:i", $horainicio);
            $horafinal = strtotime($horainiciohumana . "+50 minutes");
            $horafinalhumana = date("H:i", $horafinal);
            echo "<tr><th scope='row'>{$horainiciohumana}-{$horafinalhumana}</td>";
            // por cada dia da semana:
            for ($j = 0; $j < 7; $j++){
                    echo "<td class='bg-success text-white text-center'>
                    <a class='reserva' href='/reservar/'>
                    Livre
                    </a></td>";
                    // link para ver a reserva ainda não está definido
            }
            echo "</tr>";
            }
            echo "</table></div>";
        }
    ?>
</body>
</html>