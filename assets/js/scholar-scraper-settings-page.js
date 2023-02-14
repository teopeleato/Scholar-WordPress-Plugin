let body = document.querySelector('body');
let form = document.getElementById('scholar-scraper-settings-form');
let submitContainer = form.querySelector("p.submit");
let tabLinks = form.querySelectorAll(".nav-tab-wrapper > .nav-tab");
let lastTabClicked = form.querySelector('.nav-tab-wrapper > .nav-tab.nav-tab-active');
let activeSectionContent = form.querySelector('.section-content.section-content-active');
let tallestSectionContent;

let formData;
let waitForClick = false;
let tallestOffsetHeight = 0;
let formHeight;

let lastCheckSize = 0;
let isBodyScrollable = false;


// Wait for the DOM to be loaded
document.addEventListener('DOMContentLoaded', function () {

    // When loading the page, store the form data in a variable
    formData = new FormData(form);


    // Add an event listener to the form submit button
    form.querySelector('input[type="submit"]').addEventListener('click', onFormSubmit);

    form.addEventListener('submit', onFormSubmit);
});


// Detect width changes
window.addEventListener('resize', function () {
    changeSectionContainerHeight(false)
});


// Wait until the page is fully loaded (stylesheets, images, etc.)
window.addEventListener('load', function () {
    computeFormHeight();

    // Entrée : L'élément affiché n'est pas celui qui a la plus grande hauteur
    //       => On met à jour la hauteur du conteneur des sections (on évite ainsi le décalage
    //          du bouton de soumission du formulaire de quelques dixièmes de pixels)
    if (tallestSectionContent !== form.querySelector('.section-content.section-content-active')) {
        changeSectionContainerHeight(false);
    }
});


// Check if the form data has changed and if so, show a warning message before leaving the page
window.addEventListener('beforeunload', onBeforeUnload);


// Add an event listener to the tab links
tabLinks.forEach((tabLink) => {

    tabLink.addEventListener('click', function (event) {

        if (lastTabClicked === this) {
            event.preventDefault();
            return;
        }
        lastTabClicked = this;

        changeActiveSection(this.dataset.section);
        changeSectionContainerHeight(true);
    });
});


// Detect DOM changes inside the form and any of its children, sub-children, etc.
const observer = new MutationObserver(function (mutations) {
    console.log('DOM changed', mutations);
    mutations.forEach(function (mutation) {
        if (mutation.type === 'childList') {
            computeFormHeight();
            changeSectionContainerHeight(true);
        }
    });
});

observer.observe(form, {childList: true, subtree: true});


/**
 * Fonction permettant de changer la section active dans la page des paramètres.
 * @param sectionId L'identifiant de la section à activer.
 * @since 1.0.0
 */
function changeActiveSection(sectionId) {
    // Find the active section and remove the active class
    let activeSection = form.querySelector('.nav-tab.nav-tab-active');
    activeSection.classList.remove('nav-tab-active');

    // Find the section to activate and add the active class
    let sectionToActivate = form.querySelector('.nav-tab[data-section="' + sectionId + '"]');
    sectionToActivate.classList.add('nav-tab-active');

    // Find the active section content and remove the active class
    activeSectionContent = form.querySelector('.section-content.section-content-active');
    activeSectionContent.classList.remove('section-content-active');

    // Find the section content to activate and add the active class
    let sectionContentToActivate = form.querySelector('.section-content[data-section="' + sectionId + '"]');
    sectionContentToActivate.classList.add('section-content-active');
    activeSectionContent = sectionToActivate;
}


/**
 * Fonction qui gère les actions à effectuer pour remettre le style du formulaire à son état initial.
 * @since 1.0.0
 */
function resetFormStyle() {
    // Reset the height of the section content container
    form.style.height = 'auto';
    form.classList.remove('relative');
    submitContainer.classList.remove('absolute');
}


/**
 * Fonctiojn qui permet de déterminer si le corps de la page a un contenu défilant.
 * @since 1.0.0
 */
function isScrollable() {
    resetFormStyle();

    activeSectionContent = form.querySelector('.section-content.section-content-active');

    // Hide the active section content
    activeSectionContent.classList.remove('section-content-active');

    // Check if with the biggest section content displayed, the section content container has scrollable content
    tallestSectionContent.classList.add('section-content-active');

    // Compare the height to see if the element has scrollable content
    let hasScrollableContent = body.scrollHeight > body.clientHeight;

    // It's not enough because the element's `overflow-y` style can be set as
    // * `hidden`
    // * `hidden !important`
    // In those cases, the scrollbar isn't shown
    let overflowYStyle = window.getComputedStyle(body).overflowY;
    let isOverflowHidden = overflowYStyle.indexOf('hidden') !== -1;

    // Unset the tallest section and re-enable the real active section content
    tallestSectionContent.classList.remove('section-content-active');
    activeSectionContent.classList.add('section-content-active');

    isBodyScrollable = hasScrollableContent && !isOverflowHidden;
}


/**
 * Fonction qui permet de trouver la section la plus haute.
 * @since 1.0.0
 */
function determineTallestSectionContent() {
    resetFormStyle();

    activeSectionContent = form.querySelector('.section-content.section-content-active');
    let sectionContents = document.querySelectorAll('.section-content');
    tallestOffsetHeight = 0;

    sectionContents.forEach((sectionContent) => {

        sectionContent.classList.add('section-content-active');

        if (sectionContent.offsetHeight > tallestOffsetHeight) {
            tallestOffsetHeight = sectionContent.offsetHeight;
            tallestSectionContent = sectionContent;
        }

        sectionContent.classList.remove('section-content-active');
    });

    activeSectionContent.classList.add('section-content-active');
}


/**
 * Fonction qui permet de calculer la hauteur du conteneur de contenu de section en fonction du contenu de la plus haute section.
 * @since 1.0.0
 */
function computeFormHeight() {
    determineTallestSectionContent();

    activeSectionContent = form.querySelector('.section-content.section-content-active');

    // Get the height of the section content container without the height of the active section content
    let newContainerHeight = (form.offsetHeight - activeSectionContent.offsetHeight);

    // Add the height of the tallest section content to the height of the section content container
    formHeight = (newContainerHeight + tallestOffsetHeight) + 'px';
}


/**
 * Fonction qui gère les actions à effectuer lors du changement de taille de la fenêtre.
 * @param manuallyTriggered Si la fonction a été appelée manuellement ou non.
 * @returns {boolean} True si la hauteur du conteneur de contenu de section a été modifiée, false sinon.
 * @since 1.0.0
 */
function changeSectionContainerHeight(manuallyTriggered = false) {
    //Check that there is at least 10px between lastCheckSize and the current window size
    if (!manuallyTriggered && Math.abs(lastCheckSize - window.innerWidth) <= 10) {
        return false;
    }

    lastCheckSize = window.innerWidth;
    isScrollable();

    // Entrée : Avec la plus grande section d'affichée, le contenu de la section a du contenu défilant
    //          ET Le dernier onglet cliqué n'est pas celui de la plus grande section
    //       => La prochaine fois qu'on redimensionera la fenêtre, on ne changera pas la hauteur du conteneur de contenu de section
    //          pour éviter que le contenu de la section ne se déplace (pas très agréable pour l'utilisateur)
    if (isBodyScrollable && lastTabClicked.dataset.section !== tallestSectionContent.dataset.section) {
        waitForClick = true;
    } else if (manuallyTriggered) {
        waitForClick = false;
    }

    // Check if with the biggest section content displayed, the section content container has scrollable content
    if (isBodyScrollable) {
        return false;
    }


    // Entrée : on ne souhaite attendre un clic sur un autre onglet avant de changer la hauteur du conteneur de contenu de section
    if (waitForClick) {
        return false;
    }

    // On définit la taille du formulaire en fonction de la taille de la plus grande section
    form.style.height = formHeight;

    // Add the relative to the section content container
    form.classList.add('relative');
    submitContainer.classList.add('absolute');

    return true;
}


/**
 * Fonction appelée lors de la soumission du formulaire.
 * @param event L'événement de soumission du formulaire.
 * @since 1.0.0
 */
function onFormSubmit(event) {
    formData = new FormData(form);
}


/**
 * Fonction appelée avant de quitter la page.
 * @param event L'événement de fermeture de la page.
 * @since 1.0.0
 */
function onBeforeUnload(event) {

    event = event || window.event;

    // Check if the form data has changed
    if (!arrayEquals([...formData], [...new FormData(form)])) {

        // Cancel the event
        event.preventDefault();

        // Chrome requires returnValue to be set
        event.returnValue = '';

    }
}


/**
 * Fonction permettant de comparer deux tableaux.
 * @param a Le premier tableau.
 * @param b Le second tableau.
 * @returns {boolean} True si les tableaux sont égaux, false sinon.
 * @since 1.0.0
 */
function arrayEquals(a, b) {

    if (!Array.isArray(a) || !Array.isArray(b)) {
        return false;
    }

    if (a.length !== b.length) {
        return false;
    }

    // Sort the arrays
    a.sort();
    b.sort();

    // Compare the arrays
    return a.every((val, index) => {
        // Check if the values are array, is so, compare them recursively
        if (Array.isArray(val) || Array.isArray(b[index])) {
            return arrayEquals(val, b[index]);
        }

        return val === b[index];
    });
}