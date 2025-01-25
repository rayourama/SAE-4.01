
$(document).ready(function() {
  const $sideMenu = $('aside');
  const $menuBtn = $('#menu-btn');
  const $closeBtn = $('#close-btn');
  const $themeToggler = $('.theme-toggler');



// close sidebar
$closeBtn.on('click', () => {
  $sideMenu.css('display', 'none');
})

const $tableBody = $('table tbody');

// Changement de thème
$themeToggler.on('click', function() {
$('body').toggleClass('dark-theme-variables');
$(this).find('span:nth-child(1)').toggleClass('active');
$(this).find('span:nth-child(2)').toggleClass('active');
});




});