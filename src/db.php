<?php
    require 'config.php';
    $db = new mysqli($db['servidor'], $db['user'], $db['password'], $db['db'], $db['porta']);
    if ($db->connect_error) {
        die("Ligação ao servidor falhou: " . $db->connect_error);
    }
    $db->set_charset("utf8");

    // Criar bases de dados. Todas.
    $db->query("CREATE TABLE IF NOT EXISTS cache_giae (id VARCHAR(99) UNIQUE, nome VARCHAR(99), nomecompleto VARCHAR(99), email VARCHAR(99), PRIMARY KEY (id));");
    $db->query("CREATE TABLE IF NOT EXISTS admins (id VARCHAR(99) UNIQUE, permitido BOOLEAN, PRIMARY KEY (id));");
    $db->query("CREATE TABLE IF NOT EXISTS salas (id VARCHAR(99) UNIQUE, nome VARCHAR(99), PRIMARY KEY (id));");
    $db->query("CREATE TABLE IF NOT EXISTS tempos (id INTEGER UNIQUE, horashumanos VARCHAR(99), PRIMARY KEY (id));");
    $db->query("CREATE TABLE IF NOT EXISTS reservas (sala VARCHAR(99), tempo INTEGER, requisitor VARCHAR(99), data DATE, aprovado BOOLEAN, FOREIGN KEY (tempo) REFERENCES tempos(id), FOREIGN KEY (sala) REFERENCES salas(id), FOREIGN KEY (requisitor) REFERENCES cache_giae(id));");

    // Forçar a criação de um administrador.
    $db->query("INSERT IGNORE INTO admins (id, permitido) VALUES ('{$info['adminforcado']}', 1);");
?>