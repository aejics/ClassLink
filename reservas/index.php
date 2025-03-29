<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserva Salas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
        }
        .left-icons {
            position: fixed;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            flex-direction: column;
            gap: 15px;
            background-color: #f0f0f0;
            padding: 15px 8px;
            border-top-right-radius: 15px;
            border-bottom-right-radius: 15px;
        }
        .left-icons .icon {
            font-size: 30px;
            width: 70px;
            height: 70px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 50%;
            background-color: white;
            transition: transform 0.3s ease;
            box-shadow: 0 3px 5px rgba(0,0,0,0.1);
        }
        .left-icons .icon:hover {
            transform: scale(1.1);
        }
        .left-icons .icon i {
            color: #333;
        }
        .main-content {
            margin-left: 90px;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="left-icons">
        <div class="icon icon-fill">
        <a href="/">
            <i class="fa fa-home"></i>
    </a>
        </div>
        <div class="icon icon-enter">
        <a href="/perfil">
            <i class="fa fa-user"></i>
    </a>
        </div>
        <div class="icon icon-collapse">
        <a href="/sign_in">
            <i class="fa fa-sign-in"></i>
    </a>
        </div>
        <div class="icon icon-rotate">
        <a href="/suporte">
            <i class="fa fa-phone"></i>
    </a>
        </div>
    </div>

    <div class="main-content">
        <h1 class="text-center mt-4 mb-4">Reserva Salas</h1>
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="content">
                        <h2>Formulário de Reserva</h2>
                        <form action="" method="POST" class="mb-4">
                            <div class="mb-3">
                                <label for="tempo" class="form-label">Tempo</label>
                                <input type="time" id="tempo" name="tempo" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="dia" class="form-label">Dia</label>
                                <input type="date" id="dia" name="dia" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="sala" class="form-label">Sala</label>
                                <input type="text" id="sala" name="sala" class="form-control" placeholder="Ex: Sala 46" required>
                            </div>
                            <div class="mb-3">
                                <label for="turma" class="form-label">Turma</label>
                                <input type="text" id="turma" name="turma" class="form-control" placeholder="Ex: Turma 2ºE" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Reservar</button>
                            <button type="submit" class="btn btn-primary">Deletar</button>
                        </form>

                        <h2>Reservas Existentes</h2>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Tempo</th>
                                    <th>Dia</th>
                                    <th>Sala</th>
                                    <th>Turma</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Verifica se o formulário foi enviado
                                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                    // Recebe os dados do formulário
                                    $tempo = htmlspecialchars($_POST['tempo']);
                                    $dia = htmlspecialchars($_POST['dia']);
                                    $sala = htmlspecialchars($_POST['sala']);
                                    $turma = htmlspecialchars($_POST['turma']);

                                    // Exibe os dados na tabela
                                    echo "<tr>";
                                    echo "<td>$tempo</td>";
                                    echo "<td>$dia</td>";
                                    echo "<td>$sala</td>";
                                    echo "<td>$turma</td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>