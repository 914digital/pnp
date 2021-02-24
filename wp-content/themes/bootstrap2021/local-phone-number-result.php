<?php
        
    $result = pt_wc_rb_search_number();

?>

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
           <div class="col-md-8 offset-md-2">

                <h2><?php esc_html_e( 'Search results' ); ?></h2>

                <?php wc_print_notices(); ?>

                <?php  //echo 'Result: <pre>'. print_r( $result,1  ) . '</pre>'; ?>

                <?php if ( ! $result || ! $result->items ) { 

                    $message = esc_html_e( 'Sorry, an error occurred while searching phone numbers. Please check the API results.' );
                    $message .= print_r( $result, 1);

                    $admin_email = array( get_option('admin_email') );
                    $subject     = esc_html_( 'API error reporting for ' . site_url() );
                    wp_mail( $admin_email, $subject, $message );

                    ?>

                    <p><?php esc_html_e( 'There are no matching numbers from your query. Please try again.' ); ?></p>


                <?php } elseif ( 0 == count( $result->items ) ) { ?>

                    <p><?php esc_html_e( 'There are no matching numbers from your query. Please try again.' ); ?></p>

                <?php } else { ?>

                    <table class="table table-striped">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col"><?php esc_html_e( 'Phone number' ); ?></th>
                                <th scope="col"><?php esc_html_e( 'City' ); ?></th>
                                <th scope="col"><?php esc_html_e( 'State' ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Price' ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Buy' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>

                    <?php

                        foreach ( $result->items as $item ) {

                            //echo '<pre>' . print_r( $item, 1 ) . '</pre>';

                            /*
                                [phone]  => 7245974663
                                [vanity] => 724597home

                                [phone] => 3055214982
                                [area_code] => 305
                                [city] => MIAMI
                                [state] => FL
                                [categories] => Array
                                    (
                                        [0] => Name
                                    )

                                [call_for_price] => 
                                [price] => 99
                            */
                            if ( ! $item->vanity ) {

                                $number = substr( $item->phone, 3 );

                            } else {
                                
                                $number = strtoupper( substr( $item->vanity, 3 ) );

                                $position = strspn( $item->vanity, $item->phone ) - 3;

                                if ( $position ) { 

                                    $number = substr_replace( $number, ' - ', $position, 0 );
                                }


                            }                             

                            ?>

                            <tr>                          
                                <th scope="row"> (<?php echo $item->area_code;?>) <?php echo $number ?></th>
                                <td><?php echo $item->city; ?></td>
                                <td><?php echo $item->state; ?></td>
                                    <?php 
                                    
                                        if ( 0 == $item->price ) {

                                            ?>

                                                <td colspan="2"><a href="/contact" class="button"><?php esc_html_e( 'Please contact us' ); ?></a></td>

                                            <?php 

                                        } else {

                                            ?>
                                                <td><?php echo wc_price( $item->price ); ?></td>
                                                <td><a href="?pt_wc_rb_phone=<?php echo $item->phone;?>" class="button"><?php esc_html_e( 'Buy' ); ?></a></td>

                                            <?php

                                        }
                                    ?>
                            </tr>

                            <?php

                        } 

                        ?>

                        </tbody>
                    </table>
                        <?php

                            /*
                              [page] => 1
                              [pages] => 17
                              [per_page] => 100
                            */

                            if ( $result->pages > 1 ) {

                                $link = '&pt_wc_rb_paged_search=1&pt_wc_rb_area=' . sanitize_text_field( $_REQUEST['pt_wc_rb_area'] ) .'&pt_wc_rb_vanity=' . sanitize_text_field( $_REQUEST['pt_wc_rb_vanity'] );

                                $i     = 0;
                                $links = array();

                                if ( $result->page != 1 ) {

                                    $first_link = '<li class="page-item"><a class="page-link" href="?pt_wc_rb_page=' . ($result->page - 1) . $link . '">' . esc_html__('Previous') . '</a></li>';

                                } else {

                                    $first_link = '<li class="page-item disabled"><span class="page-link">' . esc_html__('Previous') . '</span></li>';

                                }

                                if ( $result->page == $result->pages ) {

                                    $last_link = '<li class="page-item disabled"><span class="page-link">' . esc_html__('Next') . '</span></li>';

                                } else {

                                    $last_link = '<li class="page-item"><a class="page-link" href="?pt_wc_rb_page=' . ($result->page + 1) . $link . '">' . esc_html__('Next') . '</a></li>';

                                }


                                $limit   = 12;
                                $counter = 1;
                                $i       = $result->page;

                                if ( $result->page > 1 && $result->page > ( $limit / 2 ) ) {

                                    $links[] = '<li class="page-item"><a class="page-link" href="?pt_wc_rb_page=1' . $link . '">1</a></li>';
                                    $links[] = '<li class="page-item disabled"><span class="page-link">' . esc_html__('...') . '</span></li>';

                                    $i       = $result->page - $limit / 2;


                                }


                                while ( $i <= $result->pages ) {

                                    if ( $counter++ < $limit ) {

                                        if ( $i == $result->page ) {

                                            $links[] = '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';

                                        } else {

                                            $links[] = '<li class="page-item"><a class="page-link" href="?pt_wc_rb_page=' . $i . $link . '">' . $i . '</a></li>';

                                        }    
                                    }
                                    $i++;
                                 }

                                 if ( $result->page < $result->pages - ( $limit / 2 ) ) {

                                    $links[] = '<li class="page-item disabled"><span class="page-link">' . esc_html__('...') . '</span></li>';

                                    $links[] = '<li class="page-item"><a class="page-link" href="?pt_wc_rb_page=' . ( $result->pages ) . $link . '">' . ( $result->pages ) . '</a></li>';

                                }

                                 //echo " $i <pre>" . print_r( $links, 1 ) . '</pre>';
                            
                            ?>
                            <nav>
                                <ul class="pagination justify-content-center">
                                    <?php echo $first_link; ?>
                                    <?php echo implode( "\n\t", $links ); ?>
                                    <?php echo $last_link; ?>
                                </ul>
                            </nav>
                            <?php

                            }
                    } 

                ?>

           </div>
        </div>
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
                <form method="post">
                    <input type="hidden" name="pt_wc_rb_search_local" value="1">
                    <div class="form-row align-items-center">
                        <div class="form-group col-md-3">
                            <label class="sr-only" for="localArea">Area Code</label>
                            <input type="text" name="pt_wc_rb_area" class="form-control" id="localArea" placeholder="Area Code" value="<?php echo sanitize_text_field( $_REQUEST['pt_wc_rb_area'] )?>">
                        </div>
                        <div class="form-group col-md-6">
                            <label class="sr-only" for="localWord"></label>
                            <input type="text" class="form-control" id="localWord" placeholder="keyword/number" name="pt_wc_rb_vanity" data-swplive="true" value="<?php echo sanitize_text_field( $_REQUEST['pt_wc_rb_vanity'] ); ?>" /> <!-- data-swplive="true" enables SearchWP Live Search -->
                        </div>
                        <div class="form-group col-md-3">
                            <button type="submit" class="btn btn-primary w-100">Search</button>
                        </div>
                    </div>
                </form>
                </div>
                <div class="tab-pane fade" id="nav-toll-free" role="tabpanel" aria-labelledby="nav-profile-tab">
                <form method="post">
                    <input type="hidden" name="pt_wc_rt_search_tollfree" value="1">
                    <div class="form-row align-items-center">
                        <div class="form-group col-md-3">
                           <span class="tollfree">1-8XX</span>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="sr-only" for="localWord"></label>
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
</section>


<?php get_template_part('parts/footer'); ?>
