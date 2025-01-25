
$(document).ready(function() {
    const $themeToggler = $('.theme-toggler');


const $tableBody = $('table tbody');

// Changement de thème
$themeToggler.on('click', function() {
  $('body').toggleClass('dark-theme-variables');
  $(this).find('span:nth-child(1)').toggleClass('active');
  $(this).find('span:nth-child(2)').toggleClass('active');
});




});