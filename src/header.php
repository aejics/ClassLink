<?php

  $db->close();
  echo "<nav class='navbar navbar-expand-lg navbar-light bg-light justify-content-center'>
  <a class='navbar-brand' href='/'>ReservaSalas</a>
  <div class='dropdown'>";
  if (isset($_COOKIE["loggedin"])){
    require_once(__DIR__ . "/../vendor/autoload.php");
    $giae = new \juoum\GiaeConnect\GiaeConnect("giae.aejics.org");
    $giae->session=$_COOKIE["session"];
    $config = json_decode($giae->getConfInfo(), true);

    echo "<button class='btn btn-secondary dropdown-toggle' type='button' id='areaMenuButton' data-bs-toggle='dropdown' aria-expanded='false'>
      <img class='fotoutente' src='https://giae.aejics.org/" . $config['fotoutente'] . "'>  A Minha Área
      </button>
      <ul class='dropdown-menu' aria-labelledby='dropdownMenuButton'>
      <li><a class='dropdown-item' href='/'>As Minhas Reservas</a></li>
      <li><a class='dropdown-item' href='/reservar.php'>Reservar uma Sala</a></li>";
    if ($isAdmin) {
      echo "<li><a class='dropdown-item' href='/admin'>Painel Administrativo</a></li>";
    }
    echo "<li><a class='dropdown-item' href='/login.php?action=logout'>Terminar sessão</a></li>";
    echo "</ul>
    </div>";
  }
  echo "</nav>";
?>