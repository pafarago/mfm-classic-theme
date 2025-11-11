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
<button id="menu-toggle" class="menu-toggle">
  <svg class="hamburger-icon" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
<rect class="bar top" y="25" width="100" height="10"></rect>
<rect class="bar middle" y="50" width="100" height="10"></rect>
<rect class="bar bottom" y="75" width="100" height="10"></rect>
  </svg>
 <svg class="close-icon" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <line x1="20" y1="20" x2="80" y2="80" stroke="currentColor" stroke-width="10" />
    <line x1="80" y1="20" x2="20" y2="80" stroke="currentColor" stroke-width="10" />
  </svg>
  <span class="label">Menu</span>
</button>
<nav id="primary-menu" class="main-nav">
  <?php
    wp_nav_menu([
      'theme_location' => 'main-menu',
      'container'      => false,
      'menu_class'     => 'nav-menu',
      'fallback_cb'    => false
    ]);
  ?>
</nav>
</div>
</header>

<!-- Begin M-menu panel container — 2025-09-15 15:41:19 EDT -->
<div id="m-panel" class="m-panel">
  <nav class="m-panel-nav">
    <?php
      wp_nav_menu([
        'theme_location' => 'main-menu',
        'container'      => false,
        'menu_class'     => 'm-panel-menu',
        'fallback_cb'    => false
      ]);
    ?>
  </nav>
</div>
<!-- End M-menu panel container — 2025-09-15 15:41:19 EDT -->
<main>
