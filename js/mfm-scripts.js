/* Begin ChatGPT code block 2025-09-13 03:16 PM EDT */
document.addEventListener("DOMContentLoaded", function () {
  const toggleButton = document.querySelector(".menu-toggle");
  const navMenu = document.querySelector(".nav-menu");
  const header = document.querySelector(".site-header");

  if (toggleButton && navMenu) {
    const label = toggleButton.querySelector(".label");
    toggleButton.addEventListener("click", function () {
      const isOpen = navMenu.classList.toggle("active");
      this.classList.toggle("open", isOpen);
      this.setAttribute("aria-expanded", isOpen);
      if (label) label.textContent = isOpen ? "CLOSE" : "MENU";
    });
  }

  // keep your header gap correction
  if (header) {
    document.documentElement.style.setProperty("--header-offset", header.offsetHeight + "px");
  }
});
