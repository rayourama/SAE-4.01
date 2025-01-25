$(document).ready(function() {

  // Afficher ou non les FAQS
  $('.faq').on('click', function() {
    $(this).toggleClass('open');

    const $icon = $(this).find('.faq__icon i');
    $icon.toggleClass('uil-plus uil-minus'); 
  });

  $(window).on('scroll', function() {
    $('nav').toggleClass('window-scroll', $(window).scrollTop() > 0);
  });

  // Ajouter une nouvelle classe à la navbar
  $(window).on('scroll',()=>{
    $('nav').toggleClass('window-scroll',$(this).scrollTop()>0);
  });
});

