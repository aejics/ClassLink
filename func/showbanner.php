<?php

// forçar banner e facilmente alterar.
// futuramente adicionar parametro no backoffice
function showBanner() {
    echo '<link rel="stylesheet" href="/assets/banner.css">
        <div class="alert alert-success fixed-top-banner text-center mb-0" role="alert">
            Este banner está pronto para ser utilizado. Falta decidir a mensagem.
        </div>';
}
?>