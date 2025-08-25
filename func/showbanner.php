<?php

// forçar banner e facilmente alterar.
// futuramente adicionar parametro no backoffice
function showBanner() {
    echo '<div class="alert alert-success fixed-top-banner text-center mb-0" role="alert" id="alert">
            Este banner está pronto para ser utilizado. Falta decidir a mensagem.
            <button type="button" class="close" data-dismiss="alert" aria-label="Fechar" style="position:absolute;right:10px;top:10px;background:none;border:none;font-size:1.5rem;line-height:1;color:#000;opacity:0.7;cursor:pointer;">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <script>
            document.querySelectorAll(\'.alert .close\').forEach(function(btn) {
                btn.addEventListener(\'click\', function() {
                    btn.closest(\'.alert\').style.display = \'none\';
                    btn.closest(\'.alert\').remove();
                });
            });
            document.addEventListener(\'DOMContentLoaded\', function() {
                if (document.cookie.indexOf(\'bannerClosed=true\') !== -1) {
                    document.querySelector(\'.alert\').style.display = \'none\';
                }
            });
            document.querySelectorAll(\'.alert .close\').forEach(function(btn) {
                btn.addEventListener(\'click\', function() {
                    let date = new Date();
                    date.setTime(date.getTime() + (24 * 60 * 60 * 1000));
                    document.cookie = "bannerClosed=true; expires=\" + date.toUTCString() + \"; path=/";
                });
            });
        </script>';
}
?>