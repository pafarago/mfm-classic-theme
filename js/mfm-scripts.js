/* ==========================================
   mfm-scripts.js
   Full replacement
   Timestamp: 2025-09-11 11:46 AM EDT by ChatGPT
   ========================================== */

// ===========================
// Adjust page content below fixed header
// ===========================
function adjustMainPadding() {
    const header = document.querySelector(".site-header");
    const main = document.querySelector("main");
    if (header && main) {
        const headerHeight = header.offsetHeight;
        main.style.paddingTop = headerHeight + "px";
    }
}

window.addEventListener("resize", adjustMainPadding);
document.addEventListener("DOMContentLoaded", adjustMainPadding);

// ===========================
// Detect mobile device
// ===========================
function isMobileDevice() {
    return /Mobi|Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
}

// ===========================
// Hamburger toggle + submenu logic
// ===========================
document.addEventListener("DOMContentLoaded", function() {
    const toggleButton = document.querySelector(".menu-toggle");
    const navMenu = document.querySelector(".nav-menu");

    if (toggleButton && navMenu) {
        // Set initial state based on screen size
        function handleResize() {
            if (isMobileDevice() || window.innerWidth <= 1024) {
                toggleButton.style.display = "block";
                navMenu.classList.remove("active");
            } else {
                toggleButton.style.display = "none";
                navMenu.classList.remove("active");
            }
        }

        handleResize();
        window.addEventListener("resize", handleResize);

        // Toggle entire nav
        toggleButton.addEventListener("click", function() {
            navMenu.classList.toggle("active");
            const expanded = this.getAttribute("aria-expanded") === "true" || false;
            this.setAttribute("aria-expanded", !expanded);
        });

        // Handle submenu toggling
        const submenuParents = navMenu.querySelectorAll("li.menu-item-has-children > a");

        submenuParents.forEach(link => {
            link.addEventListener("click", function(e) {
                if (isMobileDevice() || window.innerWidth <= 1024) {
                    // On mobile: toggle submenu open/close
                    e.preventDefault();
                    const parentLi = this.parentElement;
                    parentLi.classList.toggle("open");
                }
            });
        });
    }
});

// ===========================
// Add "scrolled" class on header
// ===========================
document.addEventListener("scroll", function() {
    const header = document.querySelector(".site-header");
    if (window.scrollY > 0) {
        header.classList.add("scrolled");
    } else {
        header.classList.remove("scrolled");
    }
});

/* ==========================================
   End of mfm-scripts.js
   Timestamp: 2025-09-11 11:46 AM EDT by ChatGPT
   ========================================== */
