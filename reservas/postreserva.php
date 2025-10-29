<?php
require_once(__DIR__ . '/../src/db.php');
session_start();
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserva Aprovada | ClassLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link href="/assets/index.css" rel="stylesheet">
    <link rel='icon' href='/assets/logo.png'>
</head>

<body>
    <nav>
        <a href="/"><img src="/assets/logo.png" class="logo"></a>
        <div class="list">
            <ul>
                <li><a href="/reservas">As minhas reservas</a></li>
                <li><a href="/reservar">Reservar sala</a></li>
                <?php
                if ($_SESSION['admin']) {
                    echo "<li><a href='/admin'>Painel Administrativo</a></li>";
                }
                ?>
                <li><a href="/login/?action=logout">Terminar sessão</a></li>
            </ul>
        </div>
    </nav>
    <div class="container mt-5">
        <?php
        if (!isset($_GET['sala']) || !isset($_GET['tempo']) || !isset($_GET['data'])) {
            echo "<div class='alert alert-danger'>Parâmetros inválidos.</div>";
            echo "<a href='/reservas' class='btn btn-primary'>Voltar para as minhas reservas</a>";
        } else {
            $sala = $_GET['sala'];
            $tempo = $_GET['tempo'];
            $data = $_GET['data'];
            
            // Verify the reservation exists and belongs to the user
            $stmt = $db->prepare("SELECT * FROM reservas WHERE sala=? AND tempo=? AND data=? AND requisitor=? AND aprovado=1");
            $stmt->bind_param("ssss", $sala, $tempo, $data, $_SESSION['id']);
            $stmt->execute();
            $reserva = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            if (!$reserva) {
                echo "<div class='alert alert-danger'>Reserva não encontrada ou não aprovada.</div>";
                echo "<a href='/reservas' class='btn btn-primary'>Voltar para as minhas reservas</a>";
            } else {
                // Get sala details including post-reservation content
                $stmt = $db->prepare("SELECT nome, post_reservation_content FROM salas WHERE id=?");
                $stmt->bind_param("s", $sala);
                $stmt->execute();
                $salaData = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                
                // Get tempo details
                $stmt = $db->prepare("SELECT horashumanos FROM tempos WHERE id=?");
                $stmt->bind_param("s", $tempo);
                $stmt->execute();
                $tempoData = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                
                echo "<div class='alert alert-success'><h4>Reserva Aprovada!</h4></div>";
                echo "<div class='card mb-4'>";
                echo "<div class='card-body'>";
                echo "<h5 class='card-title'>Detalhes da Reserva</h5>";
                echo "<p><strong>Sala:</strong> " . htmlspecialchars($salaData['nome'], ENT_QUOTES, 'UTF-8') . "</p>";
                echo "<p><strong>Data:</strong> " . htmlspecialchars($data, ENT_QUOTES, 'UTF-8') . "</p>";
                echo "<p><strong>Tempo:</strong> " . htmlspecialchars($tempoData['horashumanos'], ENT_QUOTES, 'UTF-8') . "</p>";
                echo "<p><strong>Motivo:</strong> " . htmlspecialchars($reserva['motivo'], ENT_QUOTES, 'UTF-8') . "</p>";
                echo "</div>";
                echo "</div>";
                
                // Display post-reservation content if available
                if (!empty($salaData['post_reservation_content'])) {
                    echo "<div class='card mb-4'>";
                    echo "<div class='card-body'>";
                    echo "<h5 class='card-title'>Informações Importantes</h5>";
                    echo "<div class='post-reservation-content'>";
                    echo $salaData['post_reservation_content']; // Content is already HTML from CKEditor
                    echo "</div>";
                    echo "</div>";
                    echo "</div>";
                }
                
                echo "<a href='/reservas' class='btn btn-primary'>Ver todas as minhas reservas</a> ";
                echo "<a href='/reservar' class='btn btn-success'>Fazer nova reserva</a>";
            }
        }
        $db->close();
        ?>
    </div>
</body>

</html>
