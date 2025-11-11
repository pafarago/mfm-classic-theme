/* Begin ChatGPT code block 2025-09-13 04:07 PM EDT */
document.addEventListener("DOMContentLoaded", function () {
  const toggleButton = document.querySelector(".menu-toggle");
  const navMenu = document.querySelector(".nav-menu");
  const header = document.querySelector(".site-header");

  if (toggleButton && navMenu) {
    const label = toggleButton.querySelector(".label");

    // Toggle main menu
    toggleButton.addEventListener("click", function () {
      const isOpen = navMenu.classList.toggle("active");
      this.classList.toggle("open", isOpen);
      this.setAttribute("aria-expanded", isOpen);
      if (label) label.textContent = isOpen ? "CLOSE" : "MENU";

      // lock/unlock body scroll
      document.body.classList.toggle("menu-open", isOpen);
    });

    // Submenu panel handling
    navMenu.querySelectorAll("li.menu-item-has-children > a").forEach(link => {
      link.addEventListener("click", function (e) {
        e.preventDefault();
        const parentLi = this.parentElement;
        const submenu = parentLi.querySelector(".sub-menu");
        if (!submenu) return;

        // hide main menu items
        navMenu.querySelectorAll("> li").forEach(li => li.style.display = "none");

        // show submenu as panel
        submenu.style.display = "block";
        submenu.classList.add("submenu-panel");

        // add back button
        const backItem = document.createElement("li");
        backItem.classList.add("back-button");
        backItem.innerHTML = `<a href="#">&#8592; Back to main menu</a>`;
        submenu.prepend(backItem);

        backItem.querySelector("a").addEventListener("click", function (e) {
          e.preventDefault();
          // hide submenu
          submenu.style.display = "none";
          submenu.classList.remove("submenu-panel");
          backItem.remove();
          // show main items
          navMenu.querySelectorAll("> li").forEach(li => li.style.display = "");
        });
      });
    });
  }

  // maintain header padding
  if (header) {
    document.documentElement.style.setProperty("--header-offset", header.offsetHeight + "px");
  }
});
/* End ChatGPT code block 2025-09-13 04:07 PM EDT */

