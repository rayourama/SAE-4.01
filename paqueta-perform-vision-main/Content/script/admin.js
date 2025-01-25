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
  }, 1000); // Adjust the delay (in milliseconds) as needed
});


// Fonction pour ouvrir un onglet

function openCity(evt, cityName) {
  $('.tabcontent').hide(); 
  $('.tablinks').removeClass('active'); 

  $(`#${cityName}`).show();
  $(evt.currentTarget).addClass('active');

}
