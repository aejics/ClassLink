<?php require 'login.php'; ?>
<div class="h-100 d-flex align-items-center justify-content-center flex-column">
    <p class="h2 mb-4">Reservar uma Sala</p>
<form action="reservar.php" method="POST" class="d-flex align-items-center">
    <div class="form-floating me-2">
        <select class="form-select" id="sala" name="sala" required>
            <option value="" selected disabled>Escolha uma sala</option>
            <option value="1">Sala 1</option>
            <option value="2">Sala 2</option>
            <option value="3">Sala 3</option>
            <option value="4">Sala 4</option>
            <option value="5">Sala 5</option>
            <option value="6">Sala 6</option>
            <option value="7">Sala 7</option>
            <option value="8">Sala 8</option>
            <option value="9">Sala 9</option>
            <option value="10">Sala 10</option>
            <option value="11">Sala 11</option>
            <option value="12">Sala 12</option>
        </select>
        <label for="sala" class="form-label">Escolha uma sala</label>
    </div>
    <button type="submit" class="btn btn-primary">Submeter</button>
</form>
</div>
<?php
    if ($_POST['sala']){
        echo(
            "<div class='h-100 d-flex align-items-center justify-content-center flex-column'>
            <table class='table table-bordered'><thead><tr><th scope='col'>Tempos</th>"
        );
        for ($i = 0; $i < 5; $i++){
            $segunda = strtotime("monday this week");
            $dia = date("d-m-Y", strtotime("+{$i} day", $segunda));
            echo "<th scope='col'>{$dia}</th>";
        };
        echo "</tr></thead><tbody>";
        for ($i = 0; $i < 12; $i++){
            $numtempo = $i + 1;
            echo "<tr><th scope='row'>{$numtempo}</th>";
            for ($j = 1; $j <= 5; $j++){
                if ($j == 1 && $i == 2){
                    echo "<td class='bg-warning text-white text-center'>Pendente<br>André Gaspar</td>";
                } else if ($j == 5 && $i == 4){
                    echo "<td class='bg-danger text-white text-center'>Ocupado<br>José Vidigal</td>";
                } else {
                    echo "<td class='bg-success text-white text-center'>Livre</td>";
                }
            }
            echo "</tr>";
        }
        echo "</table></div>";
    }
?>
<?php require 'src/footer.php'; ?>