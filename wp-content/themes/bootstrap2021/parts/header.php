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

<?php

    if ( isset( $_POST['pt_wc_rt_search_tollfree'] ) ) {
        $local_aria    = 'true';
        $local_pane    = '';
        $tollfree_aria = 'false';
        $tollfree_pane = 'show active';
    } else {
        $local_aria    = 'false';
        $local_pane    = 'show active';
        $tollfree_aria = 'true';
        $tollfree_pane = '';
    }

?>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="main-banner-text">
                    <h1 data-aos="fade-in" data-aos-duration="1000"><?php the_field('main_banner_text'); ?></h1>
                    <div class="mb-search mt-5" data-aos="fade-in" data-aos-duration="1000">
                        <div class="row">
                            <div class="col-md-8 offset-md-2">
                            <nav>
                            <div class="nav nav-tabs justify-content-center mb-3" id="nav-tab" role="tablist">
                                <a class="nav-link active" id="nav-home-tab" data-toggle="tab" href="#nav-local" role="tab" aria-controls="nav-local" aria-selected="<?php echo $local_aria;?>">Local</a>
                                <a class="nav-link" id="nav-toll-free-tab" data-toggle="tab" href="#nav-toll-free" role="tab" aria-controls="nav-profile" aria-selected="<?php echo $tollfree_aria;?>">Toll Free</a>
                            </div>
                        </nav>
                        <div class="tab-content" id="nav-tabContent">
                            <div class="tab-pane fade <?php echo $local_pane; ?>" id="nav-local" role="tabpanel" aria-labelledby="nav-home-tab">
                            <form method="post">
                                <input type="hidden" name="pt_wc_rb_search_local" value="1">
                                <div class="form-row align-items-center">
                                    <div class="form-group col-md-3">
                                        <label class="sr-only" for="localArea">Area Code</label>
                                        <input type="text" name="pt_wc_rb_area" class="form-control" id="localArea" placeholder="Area Code">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="sr-only" for="localWord"></label>
                                        <input type="text" class="form-control" id="localWord" placeholder="keyword/number" name="pt_wc_rb_vanity" data-swplive="true" /> <!-- data-swplive="true" enables SearchWP Live Search -->
                                    </div>
                                    <div class="form-group col-md-3">
                                        <button type="submit" class="btn btn-primary w-100">Search</button>
                                    </div>
                                </div>
                            </form>
                            </div>
                            <div class="tab-pane fade <?php echo $tollfree_pane; ?>" id="nav-toll-free" role="tabpanel" aria-labelledby="nav-profile-tab">
                            <form method="post">
                                <input type="hidden" name="pt_wc_rt_search_tollfree" value="1">
                                <div class="form-row align-items-center">
                                    <div class="form-group col-md-3">
                                       <span class="tollfree">1-8XX</span>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="sr-only" for="tollFreeNumber"></label>
                                        <input type="text" name="pt_wc_rt_vanity" class="form-control" id="tollFreeNumber" placeholder="keyword/number">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <button type="submit" class="btn btn-primary w-100">Search</button>
                                    </div>
                                </div>
                            </form>
                            </div>
                        </div>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

   
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