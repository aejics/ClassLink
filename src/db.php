<?php
    require_once(__DIR__ . '/../vendor/autoload.php');
    require_once(__DIR__ . '/config.php');
    $db = new mysqli($db['servidor'], $db['user'], $db['password'], $db['db'], $db['porta']);
    if ($db->connect_error) {
        die("Ligação ao servidor falhou: " . $db->connect_error);
    }
    $db->set_charset("utf8");

    // Criar bases de dados. Todas.
    $db->query("CREATE TABLE IF NOT EXISTS cache (id VARCHAR(99) UNIQUE, nome VARCHAR(99), email VARCHAR(99), admin BOOL, PRIMARY KEY (id));");
    $db->query("CREATE TABLE IF NOT EXISTS salas (id VARCHAR(99) UNIQUE, nome VARCHAR(99), post_reservation_content TEXT, PRIMARY KEY (id));");
    // Add column to existing tables if not present
    $db->query("ALTER TABLE salas ADD COLUMN IF NOT EXISTS post_reservation_content TEXT;");
    $db->query("CREATE TABLE IF NOT EXISTS tempos (id VARCHAR(99) UNIQUE, horashumanos VARCHAR(99), PRIMARY KEY (id));");
    $db->query("CREATE TABLE IF NOT EXISTS reservas (sala VARCHAR(99) NOT NULL, tempo VARCHAR(99) NOT NULL, requisitor VARCHAR(99) NOT NULL, data DATE NOT NULL, aprovado BOOLEAN, motivo VARCHAR(99), extra VARCHAR(9999), UNIQUE (sala, tempo, data), FOREIGN KEY (tempo) REFERENCES tempos(id), FOREIGN KEY (sala) REFERENCES salas(id), FOREIGN KEY (requisitor) REFERENCES cache(id));");
    $db->query("CREATE TABLE IF NOT EXISTS logs (id VARCHAR(99), loginfo VARCHAR(9999), userid VARCHAR(99), timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (id), FOREIGN KEY (userid) REFERENCES cache(id));");
?>