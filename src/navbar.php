<?php
  echo "<nav class='navbar navbar-expand-lg navbar-light bg-light justify-content-center'>
  <a class='navbar-brand' href='/'>Reserva Salas</a>
  <div class='dropdown'>";
  if (isset($_COOKIE["loggedin"])){
    require_once(__DIR__ . "/../vendor/autoload.php");
    $giae = new \juoum\GiaeConnect\GiaeConnect("giae.aejics.org");
    $giae->session=$_COOKIE["session"];
    $config = json_decode($giae->getConfInfo(), true);
    $fotoutente = json_decode('"' . $config['fotoutente'] . '"'); // Decode Unicode da Foto do Utente
    echo "<button class='btn btn-secondary dropdown-toggle' type='button' id='areaMenuButton' data-bs-toggle='dropdown' aria-expanded='false'>
      <img class='fotoutente' src='https://giae.aejics.org/" . $fotoutente . "'>  A Minha Área
      </button>
      <ul class='dropdown-menu' aria-labelledby='dropdownMenuButton'>
      <li><a class='dropdown-item' href='/'>As minhas reservas</a></li>
      <li><a class='dropdown-item' href='/reservar.php'>Reservar uma sala</a></li>
      <li><a class='dropdown-item' href='/logout.php'>Terminar sessão</a></li>
    </ul>
    </div>";
  }
  echo "</nav>";
?>