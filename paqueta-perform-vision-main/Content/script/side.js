$(document).ready(function() { 
  let $sidebar = $('.sidebar');
  let $closeBtn = $('#btn');
  let $log = $('.profile');
  let $log_name = $('.logo_name');
  let $leftColumn = $('.container');

  // Faire en sorte que la sidebar se ferme ou s'ouvre
  $closeBtn.on("click", function() {
    $sidebar.toggleClass("open");
    menuBtnChange();
    $sidebar.css('borderRadius', '0');
    toggleSidebar();
  });

  // Fonction qui change l'icone du menu
  function menuBtnChange() {
    $closeBtn.toggleClass("bx-menu bx-menu-alt-right");
  }

  // Fonction ajustant la sidebar
  function toggleSidebar() {
    if ($sidebar.hasClass('open')) {
      $log_name.html(`<nobr><a href="?controller=activity" style="color:white;">Perform Vision</a></nobr>`);
      $sidebar.css('borderRadius', '0');
      $log.css('borderRadius', '0');
      $leftColumn.css('gridTemplateColumns', '15rem auto 15rem');
    } else {
      $sidebar.css({
        'borderTopRightRadius': '22px',
        'borderBottomRightRadius': '22px'
      });
      $log.css('borderBottomRightRadius', '22px');
      $leftColumn.css('gridTemplateColumns', '5rem auto 21rem');
    }
  }
});