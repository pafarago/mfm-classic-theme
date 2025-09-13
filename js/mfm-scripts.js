/* ============================================================
   mfm-scripts.js
   Full Replacement - 2025-09-13 01:00 AM EDT
   ============================================================ */

document.addEventListener("DOMContentLoaded", function () {
  const toggleButton = document.querySelector(".menu-toggle");
  const navMenu = document.querySelector(".nav-menu");
  const header = document.querySelector(".site-header");

  if (toggleButton && navMenu) {
    // --- Resize handler: show/hide button ---
    function handleResize() {
      if (window.innerWidth <= 999) {
        toggleButton.style.display = "block";
        toggleButton.style.position = "absolute";
        toggleButton.style.top = "10px";
        toggleButton.style.right = "15px";
        navMenu.classList.remove("active");
        toggleButton.setAttribute("aria-expanded", "false");
        toggleButton.textContent = "☰ Open Menu";
      } else {
        toggleButton.style.display = "none";
        navMenu.classList.remove("active");
      }
    }

    handleResize();
    window.addEventListener("resize", handleResize);

    // --- Toggle menu open/close ---
    toggleButton.addEventListener("click", function () {
      const isOpen = navMenu.classList.toggle("active");
      this.setAttribute("aria-expanded", isOpen);
      this.textContent = isOpen ? "✖ Close Menu" : "☰ Open Menu";
    });
  }

  // --- Adjust page padding under fixed header ---
  const main = document.querySelector("main");
  if (header && main) {
    const headerHeight = header.offsetHeight;
    main.style.paddingTop = headerHeight + "px";
  }
});

/* ============================================================
   End of mfm-scripts.js
   ============================================================ */

