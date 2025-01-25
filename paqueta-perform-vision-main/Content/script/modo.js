$(document).ready(function() {
  const $allLinks = $('.tabs a');
  const $allTabs = $('.tab-content');

  // Gère les clics sur les liens
  $allLinks.on('click', function(event) {
    event.preventDefault(); // Empêche le comportement par défaut du lien

    const linkId = this.id; 
    const hrefLinkClick = this.href;

    // Met à jour les classes "active" des liens
    $allLinks.removeClass('active');
    $(this).addClass('active');

    // Met à jour les classes des onglets et génère du contenu
    $allTabs.removeClass('tab-content--active');
    $allTabs.filter(`[id*="${linkId}"]`).addClass('tab-content--active').each(function() {
      generateTabItems(this, $(this)); // Appelle votre fonction avec le contexte jQuery
    });
  });
});

// Données des onglets

const tabRecords = [
  {
    src: 'assets/slack.png',
    name: 'Slack',
    site: 'https://slack.com',
    description: 'Slack is a messaging app for business.',
    type: ['communication', 'productivity'],
  },
  {
    src: 'assets/sendgrid.png',
    name: 'SendGrid',
    site: 'https://sendgrid.com',
    description: 'Email API service for developers',
    type: ['developer'],
  },
  {
    src: 'assets/asana.png',
    name: 'Asana',
    site: 'https://asana.com',
    description: 'Track, manage and control your projects across any team, right',
    type: ['productivity'],
  },
  {
    src: 'assets/yahoo.png',
    name: 'Yahoo Finance',
    site: 'https://finance.yahoo.com',
    description: 'Get quotes of stocks, funds, ETFs, currencies',
    type: ['external'],
  },
];

//? predefined filter functions
const filter = {
  ['all']: () => true,
  ['developer']: (record) => record.type.includes('developer'),
  ['productivity']: (record) => record.type.includes('productivity'),
  ['external']: (record) => record.type.includes('external'),
  ['communication']: (record) => record.type.includes('communication'),
}

// Génère les éléments de l'onglet

const generateTabItems = (elem, tabContent) => {
  const filterName = elem.name;

  const filterFunction = filter[filterName];

  const mappedRecords = tabRecords
    .filter(filterFunction)
    .map(
      (record) => {
      return DOMPurify.sanitize(
      `<div class="record">
        <div class="avatar__wrapper">
          <img
             src="${record.src}"
             class="avatar avatar--${record.type}"
             alt="Profile"
          >
        </div>
        <div class="content">
          <div class="title-description">
            <div class="title">
              <div class="title-name">
                ${record.name}
              </div>
              <a
                href="${record.site}"
                target="_blank"
                rel="noopener noreferrer"
              >
                ${record.site}
                <img src="assets/link.svg" alt="External">
              </a>
            </div>
            <div class="description">
              ${record.description}
            </div>
          </div>
          <label class="switch-wrapper">
              <span class="switch">
                  <input type="checkbox">
                  <span class="slider round"></span>
              </span>
          </label>
        </div>
      </div>`);
  });

  tabContent.innerHTML = mappedRecords.join('');
}


  // Gère la sélection initiale basée sur le hash
  let $activeLink = $('.tabs a');
  const currentHash = window.location.hash;

  if (currentHash) {
    const $visibleHash=$(`#${currentHash}`);

    if ($visibleHash) {
      $activeLink = $visibleHash;
    }

  // Active le lien et l'onglet correspondant
  $activeLink.addClass('active');
  const $activeTab=$(`#${$activeLink.attr('id')}-content`);
  $activeTab.addClass('tab-content--active');
  generateTabItems($activeLink.get(0), $activeTab);
  }


document.addEventListener("DOMContentLoaded", function() {
  var element = document.querySelector('.recent-orders');
  setTimeout(function() {
    element.classList.add('show');
  }, 1000); 
});

// Fonction pour ouvrir un onglet

function openCity(evt, cityName) {
  $('.tabcontent').hide(); 
  $('.tablinks').removeClass('active'); 

  $(`#${cityName}`).show();
  $(evt.currentTarget).addClass('active');
}

$(document).ready(function() {
  // Récupération du modal
  var $modal = $("#modal");

  // Récupération du bouton qui ouvre le modal
  var $buttons = $(".button");

  // Récupération du span qui ferme le modal
  var $span = $(".close");

  // Fonction pour ouvrir le modal
  function openModal(id) {
    // Chargez les informations du formateur dans le modal en utilisant AJAX ou tout autre moyen
    var $modalContent = $("#modal-content");

    // Ajoutez le code pour récupérer les compétences du formateur par son ID ici
    $.ajax({
      url: '?controller=panel&action=getCompetences&id=' + id,
      method: 'GET',
      success: function(response) {
        $modalContent.html(response);
        $modal.show();
      },
      error: function() {
        console.error('Erreur lors du chargement des compétences.');
      }
    });
  }

  // Fonction pour fermer le modal
  $span.on("click", function() {
    $modal.hide();
  });

  // Fermer le modal lorsque l'utilisateur clique en dehors du modal
  $(window).on("click", function(event) {
    if ($(event.target).is($modal)) {
      $modal.hide();
    }
  });

  // Ajouter un écouteur d'événement pour chaque bouton
  $buttons.on("click", function(e) {
    e.preventDefault(); // Empêche le comportement par défaut du bouton
    var userId = $(this).data("id");
    openModal(userId);
  });
});