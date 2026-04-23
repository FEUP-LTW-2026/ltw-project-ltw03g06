const nav = document.querySelector('.main-nav');
const menuToggle = document.querySelector('.menu-toggle');

if (nav && menuToggle) {
    menuToggle.addEventListener('click', () => {
        const isOpen = nav.classList.toggle('mobile-open');
        menuToggle.setAttribute('aria-expanded', String(isOpen));
    });
}
