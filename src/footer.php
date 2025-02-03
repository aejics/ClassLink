<?php 
function getLatestCommit() {
    return substr(file_get_contents(sprintf( '.git/refs/heads/%s', 'dev' )),0,8);
}

echo "<hr><div class='h-100 d-flex align-items-center justify-content-center flex-column'>
<p class='font-weight-light'><small>(C) " . date('Y') . " AEJICS (2ºEF)- <a href='https://github.com/aejics/reservasalas/commit/" . getLatestCommit() ."'>Versão " . getLatestCommit() . "</a></i></small></p>" ?>