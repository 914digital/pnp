<?php get_template_part('parts/header'); ?>

<div class="page-banner">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h1><?php the_title(); ?></h1>
            </div>
        </div>
    </div>
</div>

<section class="page-content">
    <div class="container">
        <div class="row">
           <div class="col-md-6">
           <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
                <?php the_content(); ?>
                    <?php endwhile; else : ?>
                <p><?php _e( 'Sorry, no posts matched your criteria.' ); ?></p>
            <?php endif; ?>		
           </div>
        </div>
    </div>
</section>


<?php get_template_part('parts/footer'); ?>
