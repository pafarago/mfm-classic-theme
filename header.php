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
	<button class="menu-toggle" aria-expanded="false" aria-controls="primary-menu" aria-label="Open Menu">
	  <svg class="hamburger" viewBox="0 0 100 80" width="30" height="30" aria-hidden="true">
	    <rect width="100" height="10" fill="currentColor"></rect>
	    <rect y="30" width="100" height="10" fill="currentColor"></rect>
	    <rect y="60" width="100" height="10" fill="currentColor"></rect>
	  </svg>
	  <span class="menu-label">Open Menu</span>
	</button>

	<nav id="primary-menu" class="main-nav">
	  <?php
	    wp_nav_menu(array(
	      'theme_location' => 'main-menu',
	      'menu_class'     => 'nav-menu',
	      'container'      => false,
	    ));
	  ?>
	</nav>
  </div>
</header>
<main>
