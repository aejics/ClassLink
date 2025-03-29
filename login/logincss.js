$(document).ready(function () {
    $('.btnCardRegis ,.btnCardLogin').click(function() {
        $('.cardLogin').toggleClass('off');
        $('.cardRegis').toggleClass('on');
        $('.btnIcon').toggleClass('rotate');
        $('.sosmed').toggleClass('top');
      })
});