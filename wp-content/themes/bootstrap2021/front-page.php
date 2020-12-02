<?php get_template_part('parts/header'); ?>
<?php $backgroundImg = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'full' );?>
<div class="main-banner"  style="background-image: url('<?php echo $backgroundImg[0]; ?>') ">
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
                                <a class="nav-link active" id="nav-home-tab" data-toggle="tab" href="#nav-local" role="tab" aria-controls="nav-local" aria-selected="true">Local</a>
                                <a class="nav-link" id="nav-toll-free-tab" data-toggle="tab" href="#nav-toll-free" role="tab" aria-controls="nav-profile" aria-selected="false">Toll Free</a>
                            </div>
                        </nav>
                        <div class="tab-content" id="nav-tabContent">
                            <div class="tab-pane fade show active" id="nav-local" role="tabpanel" aria-labelledby="nav-home-tab">
                            <form>
                                <div class="form-row align-items-center">
                                    <div class="form-group col-md-3">
                                        <label class="sr-only" for="localArea">Area Code</label>
                                        <input type="text" class="form-control" id="localArea" placeholder="Area Code">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="sr-only" for="localWord"></label>
                                        <input type="text" class="form-control" id="localWord" placeholder="keyword/number">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <button type="submit" class="btn btn-primary w-100">Search</button>
                                    </div>
                                </div>
                            </form>
                            </div>
                            <div class="tab-pane fade" id="nav-toll-free" role="tabpanel" aria-labelledby="nav-profile-tab">
                            <form>
                                <div class="form-row align-items-center">
                                    <div class="form-group col-md-3">
                                       <span class="tollfree">1-8XX</span>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="sr-only" for="localWord"></label>
                                        <input type="text" class="form-control" id="tollFreeNumber" placeholder="keyword/number">
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
    <div class="mask"></div>
</div>

<section class="home-cards">
    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <div class="card" id="card1" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="50">
                    <div class="card-body">
                    <i class="fal fa-chart-line"></i>
                    <h2>Boost Sales & Get New Leads</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card" id="card2" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="250">
                    <div class="card-body">
                    <i class="fal fa-eye"></i>
                    <h2>Make Ads More Memorable</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card" id="card3" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="450">
                    <div class="card-body">
                    <i class="fal fa-sort-amount-up-alt"></i>
                    <h2>Increase Brand Awareness</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card" id="card4" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="650">
                    <div class="card-body">
                    <i class="fal fa-bullhorn"></i>
                    <h2>Spread Word of Mouth</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="home-text">
    <div class="container">
        <div class="row mt-5">
            <div class="col-md-6" data-aos="fade-right" data-aos-duration="1000">
                <div class="hc-text">
                    <h3>Don't Be Just Another <span>Number</span> </h3>
                    <p>Hi! We're <strong>phoneNumber<sup>+</sup></strong>. We want to help build your business or personal brand by finding a memorable, customized phone number. Let us make your digits count--and stand out from the crowd.</p>
                    <a href="/about-us" class="btn btn-primary">Get Started</a>
                </div>
            </div>
            <div class="col-md-6" data-aos="fade-left" data-aos-duration="1000">
                <img class="crowd" src="<?php bloginfo('template_directory') ?>/img/crowd.jpg" alt="Stand Out In the Crowd">
            </div>
        </div>
    </div>
</section>

<section class="carriers">
    <div class="container">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <h4 data-aos="fade-up" data-aos-duration="1000" data-aos-delay="50">We Work With All the Major Carriers</h4>
                <p data-aos="fade-up" data-aos-duration="1000" data-aos-delay="250">Lorem ipsum dolor sit amet consectetur adipisicing elit. Facilis perspiciatis explicabo magnam quae consectetur blanditiis vero vitae aliquid dolores pariatur. Corporis ipsa, enim numquam temporibus excepturi culpa voluptatibus fuga tempora.</p>
                <a class="btn btn-primary mt-4" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="250" href="/carriers" >See All Carriers</a>
            </div>
        </div>
        <div class="logos" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="350">
            <div class="row">
                <div class="col-md-2 col-6">
                    <img src="<?php bloginfo('template_directory') ?>/img/logos/att-logo.png" alt="AT&T Logo">
                </div>
                <div class="col-md-2 col-6">
                    <img src="<?php bloginfo('template_directory') ?>/img/logos/comcast-logo.png" alt="Comcast Logo">
                </div>
                <div class="col-md-2 col-6">
                    <img src="<?php bloginfo('template_directory') ?>/img/logos/spectrum-logo.png" alt="Spectrum Logo">
                </div>
                <div class="col-md-2 col-6">
                    <img src="<?php bloginfo('template_directory') ?>/img/logos/T-Mobile-logo.png" alt="T-Mobile Logo">
                </div>
                <div class="col-md-2 col-6">
                    <img src="<?php bloginfo('template_directory') ?>/img/logos/verizon-logo.png" alt="Verizon Logo">
                </div>
                <div class="col-md-2 col-6">
                    <img src="<?php bloginfo('template_directory') ?>/img/logos/vonage-logo.png" alt="Vonage Logo">
                </div>
            </div>
        </div>
    </div>
</section>

<section class="industries">
    <div class="container">
        <div class="row">
            <div class="col-md-6 ind-icons" data-aos="fade-right" data-aos-duration="1000">
                <div class="row">
                    <div class="col-md-6 col-6 mb-5">
                        <i class="fal fa-wrench"></i>
                        <h5>Home Services</h5>
                    </div>
                    <div class="col-md-6 col-6 mb-5">
                        <i class="fal fa-hotel"></i>
                        <h5>Real Estate</h5>
                    </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 col-6">
                            <i class="fal fa-stethoscope"></i>
                            <h5>Health & Wellness</h5>
                        </div>
                        <div class="col-md-6 col-6">
                            <i class="fal fa-books"></i>
                            <h5>Law Services</h5>
                        </div>
                    </div>
                </div>
            
            <div class="col-md-6" data-aos="fade-left" data-aos-duration="1000">
                <h4>We Specialize In Multiple <span>Industries</span></h4>
                <p>Lorem, ipsum dolor sit amet consectetur adipisicing elit. Sed explicabo, provident dolorum odio quidem officia incidunt pariatur quae alias debitis.</p>
                <a href="/industries" class="btn btn-primary">Learn More</a>
            </div>
        </div>
    </div>
</section>
	
<?php get_template_part('parts/footer'); ?>