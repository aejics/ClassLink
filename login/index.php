<?php
    require_once(__DIR__ . '/../vendor/autoload.php');
    require_once(__DIR__ . '/../src/config.php');
    require_once(__DIR__ . '/../src/db.php');
    
    if ($_GET['action'] == "logout"){
        $giae = new \juoum\GiaeConnect\GiaeConnect($info['giae']);
        $giae->session=$_COOKIE["session"];
        $giae->logout();
        setcookie("loggedin", "", time() - 3600, "/");
        die("<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
        <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js'></script>
        <div class='alert alert-success text-center' role='alert'>A sua sessão foi terminada com sucesso.</div>
        <div class='text-center'>
        <a href='/login/'><button type='button' class='btn btn-primary w-100'>Voltar</button></a></div>");
    } else if ($_GET['action'] == "login"){
        $user = filter_input(INPUT_POST, 'username', FILTER_UNSAFE_RAW);
        $pass = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);
        $giae = new \juoum\GiaeConnect\GiaeConnect($info['giae']);
        $session = $giae->getSession($user, $pass);
        $giae->session=$session;
        $config = $giae->getConfInfo();
        $perfil = json_decode($giae->getPerfil(), true);
        if (strpos($config, 'Erro do Servidor') !== false){
            die("<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
                <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js'></script>
                <div class='w-45 alert alert-danger text-center' role='alert'>A sua palavra-passe está errada.</div>
                    <div class='text-center'>
                        <button type='button' class='btn btn-primary w-100' onclick='history.back()'>
                            Voltar
                        </button>
                    </div>
                </div>");
        } else {
            setcookie("token", $session, time() + 3599, "/");
            require '../src/db.php';
            $stmt = $db->prepare("INSERT IGNORE INTO cache_giae(id, nome, nomecompleto, email) VALUES (?, ?, ?, ?);");
            $stmt->bind_param("ssss", $user, json_decode($config, true)['nomeutilizador'], $perfil['perfil']['nome'], $perfil['perfil']['email']);
            $stmt->execute();
            $db->close();
            header('Location: /');
            die();
        }
    } else if ($_SERVER['REQUEST_URI'] == "/login/"){
        echo "<!DOCTYPE html>
            <html lang='pt'>
            <head>
                <meta charset='UTF-8'>
                <meta http-equiv='X-UA-Compatible' content='IE=edge'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>Iniciar Sessão | Reserva Salas</title>
                <link rel='stylesheet' href='/assets/login.css'>
                <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css' />
                <meta name='viewport' content='width=device-width, initial-scale=1'>
                <link rel='icon' href='/assets/logo.png'>
            </head>

            <body>
                <div class='container'>
                    <div class='cardLogin'>
                        <svg xmlns='http://www.w3.org/2000/svg' width='270' height='400' viewBox='0 0 270 387' fill='none'>
                            <path d='M0 20C0 8.9543 8.95431 0 20 0H250C261.046 0 270 8.95431 270 20V366.417C270 381.573 253.799 
                                391.222 240.473 384.002L10.4726 259.388C4.01969 255.892 0 249.143 0 241.803V20Z' fill='white' />
                        </svg>
                            <h2 class='heading'>Iniciar Sessão</h2>
                            <p class='heading-nospacing'>em ReservaSalas, com as suas credenciais GIAE.</p>
                        <form action='/login/?action=login' method='POST'>
                            <div class='input-grup'>
                                <input type='text' name='username' placeholder='Nome de Utilizador' id='username' required>
                                <span class='border'></span>
                                <i class='fa-solid fa-user'></i>
                            </div>
                            <div class='input-grup'>
                                <input type='password' name='password' placeholder='Palavra-Passe' id='password' required>
                                <span class='border'></span>
                                <i class='fa-solid fa-key'></i>
                            </div>
                            <button type='submit'>Iniciar Sessão</button>
                        </form>
                    </div>
                    <div class='sosmed'>
                            <img src='/assets/aejics.png' class='logoaejics'>
                    </div>
                    <div class='cardRegis'>
                        <svg xmlns='http://www.w3.org/2000/svg' width='270' height='447' viewBox='0 0 270 447' fill='none'>
                            <path d='M270 427C270 438.046 261.046 447 250 447L19.9999 447C8.95424 447 -6.02523e-05 438.046
                                 -5.92867e-05 427L-2.37629e-05 20.6546C-2.24466e-05 5.598 16.0091 -4.05922 29.3278 
                                 2.96307L259.328 124.23C265.892 127.691 270 134.501 270 141.922L270 427Z' fill='white' />
                        </svg>
                </div>
            </body>
            </html>";
    } else {
        $session = filter_input(INPUT_COOKIE, 'token', FILTER_UNSAFE_RAW);
        $giae = new \juoum\GiaeConnect\GiaeConnect($info['giae']);
        $giae->session=$session;
        $confinfo = $giae->getConfInfo();
        // Este código funciona especificamente com a maneira de verificação no GIAE AEJICS.
        // Pode não funcionar da mesma maneira nos outros GIAEs. Caso não funcione na mesma maneira, corriga este código e faça um pull request!
        if (str_contains($confinfo, 'Erro do Servidor')){
            setcookie("token", "", time() - 3600, "/");
            die("<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
                <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js'></script>
                <div class='w-45 alert alert-danger text-center' role='alert'>A sua sessão expirou.</div>
                    <div class='text-center'>
                        <button type='button' class='btn btn-primary w-100' onclick='history.back()'>
                            Voltar
                        </button>
                    </div>
                </div>");
        }
        $confinfo = json_decode($confinfo, true);
        $perfil = json_decode($giae->getPerfil(), true);
    }
?>