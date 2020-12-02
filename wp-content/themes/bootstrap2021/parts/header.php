<!DOCTYPE html>
<html>
<head>
	<title><?php wp_title(''); ?></title>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
  	<meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="shortcut icon" href="<?php bloginfo('template_directory') ?>/img/favicon.png" />
  <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<div id="myNav" class="overlay">

  <!-- Button to close the overlay navigation -->
  <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>

  <!-- Overlay content -->
  <div class="overlay-content">
   
  </div>

</div>

  <nav class="navbar navbar-expand-lg">
		<div class="container">
        <a class="navbar-brand" href="<?php echo site_url(); ?>">
          <img src="<?php bloginfo('template_directory') ?>/img/logo_v2.png" class="header-logo" alt="">
				</a>
				<button class="navbar-toggler collapsed" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
					<span class="icon-bar top-bar"></span>
					<span class="icon-bar middle-bar"></span>
					<span class="icon-bar bottom-bar"></span>
        </button>
       
				<div class="collapse navbar-collapse ml-5" id="navbarNavDropdown">
          <?php				
              $args = array(
                'theme_location' => 'upper-bar',
                'depth' => 0,
                'container'	=> false,
                'fallback_cb' => false,
                'menu_class' => 'nav navbar-nav',
                'walker' => new WP_Bootstrap_Navwalker()
              );
              wp_nav_menu($args);
          ?>
       
          </div>
      </div>
      <div class="menu-right">
        <ul class="nav navbar-nav">
          <li><a class="nav-link" href=""><i class="fas fa-phone-alt mr-2"></i> 888.888.8888</a></li>
          <li onclick="openNav();"><i class="fas fa-search"></i></li>
          <li><a class="nav-link" href=""><i class="fas fa-shopping-cart"></i></a></li>
          <li><a class="nav-link btn btn-primary si-btn" href="/contact-us"><i class="fal fa-envelope"></i> Contact Us</a></li>
        </ul>
      </div>
  </nav>