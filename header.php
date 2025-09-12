<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="<?php bloginfo('charset'); ?>">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<header class="site-header">
  <div class="container">
    <div class="header-inner">
      <div class="logo-title">
        <img src="<?php echo get_template_directory_uri(); ?>/images/logo.png"
             alt="Midlothian Friends Meeting" class="site-logo">
      </div>
<button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false" aria-label="Menu">
  <!-- Inline SVG hamburger icon -->
<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28"
     viewBox="0 0 24 24" aria-hidden="true">
  <rect x="3" y="6"  width="18" height="2" rx="1" ry="1" fill="currentColor"></rect>
  <rect x="3" y="11" width="18" height="2" rx="1" ry="1" fill="currentColor"></rect>
  <rect x="3" y="16" width="18" height="2" rx="1" ry="1" fill="currentColor"></rect>
</svg>
</button>
    </div>

    <nav id="main-menu" class="main-nav">
      <?php wp_nav_menu(array(
          'theme_location' => 'main-menu',
          'menu_class'     => 'nav-menu',
          'container'      => false,
      )); ?>
    </nav>
  </div>
</header>
<main>
