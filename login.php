<?php
    if (!$loggedin && $action !== "loginform" && $action !== "login"){
        header('Location: /login.php?action=loginform');
    }
    if ($action == "loginform"){
        print("");
        include 'src/footer.php';
    }
    if ($action == "login"){
        $user = filter_input(INPUT_POST, 'user', FILTER_UNSAFE_RAW);
        $pass = filter_input(INPUT_POST, 'pass', FILTER_UNSAFE_RAW);
        $giae = new \juoum\GiaeConnect\GiaeConnect("giae.aejics.org", $user, $pass);
        $config = json_decode($giae->getConfInfo(), true);
        $perfil = json_decode($giae->getPerfil(), true);
        if (strpos($giae->getConfInfo(), 'Erro do Servidor') !== false){
            echo("<div class='alert alert-danger text-center' role='alert'>A sua palavra-passe está errada.</div>
            <div class='text-center'>
            <button type='button' class='btn btn-primary w-100' onclick='history.back()'>Voltar</button></div>");
            include 'src/footer.php';
        }
        else {
            setcookie("loggedin", "true", time() + 3599, "/");
            setcookie("session", $giae->session, time() + 3599, "/");
            setcookie("user", $_POST["user"], time() + 3599, "/");
            $db = new SQLite3('db.sqlite3');
            $db->exec("CREATE TABLE cache_giae (id VARCHAR(99), nome VARCHAR(99), nomecompleto VARCHAR(99), email VARCHAR(99), PRIMARY KEY (id));");
            $db->exec("CREATE TABLE admins (id VARCHAR(99), atividade BOOLEAN, PRIMARY KEY (id));");
            $valordb = $db->prepare("INSERT INTO cache_giae(id, nome, nomecompleto, email) VALUES (:1, :2, :3, :4);");
            $valordb->bindValue(':1', mb_convert_encoding($_POST["user"], 'ISO-8859-1', 'auto'), SQLITE3_TEXT);
            $valordb->bindValue(':2', mb_convert_encoding($config['nomeutilizador'], 'ISO-8859-1', 'auto'), SQLITE3_TEXT);
            $valordb->bindValue(':3', mb_convert_encoding($perfil['perfil']['nome'], 'ISO-8859-1', 'auto'), SQLITE3_TEXT);
            $valordb->bindValue(':4', mb_convert_encoding($perfil['perfil']['email'], 'ISO-8859-1', 'auto'), SQLITE3_TEXT);
            $valordb->execute();
            $db->close();
            header('Location: /');
        }
    };
    if ($loggedin){
        $session = filter_input(INPUT_COOKIE, 'session', FILTER_UNSAFE_RAW);
        $giae = new \juoum\GiaeConnect\GiaeConnect("giae.aejics.org");
        $giae->session=$session;
        // Este código funciona especificamente com a maneira de verificação no GIAE AEJICS.
        // Pode não funcionar da mesma maneira nos outros GIAEs. Caso não funcione na mesma maneira, corriga este código e faça um pull request!
        if (str_contains($giae->getConfInfo(), 'Erro do Servidor')){
            setcookie("loggedin", "", time() - 3600, "/");
            echo("<div class='alert alert-danger text-center' role='alert'>A sua sessão expirou.</div>
            <div class='text-center'>
            <button type='button' class='btn btn-primary w-100' onclick='history.back()'>Voltar</button></div>");
        }
    }
    if ($action == "logout"){
        $giae = new \juoum\GiaeConnect\GiaeConnect("giae.aejics.org");
        $giae->session=$_COOKIE["session"];
        $giae->logout();
        setcookie("loggedin", "", time() - 3600, "/");
        echo("<div class='alert alert-success text-center' role='alert'>A sua sessão foi terminada com sucesso.</div>
        <div class='text-center'>
        <button type='button' class='btn btn-primary w-100' onclick='history.back()'>Voltar</button></div>");
        include 'src/footer.php';
    };
?>