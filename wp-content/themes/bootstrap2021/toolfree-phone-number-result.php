<?php
<<<<<<< HEAD
		
	$result = false;

	$result = pt_wc_rt_search_number();

	/*

[
	{
		"respOrgId": "AAM99",
		"statusTs": "1994-12-01T01:45:00.000Z",
		"effectiveTs": "1994-12-01T01:45:00.000Z",
		"lastUpdateTs": "2017-04-03T21:21:55.926Z",
		"templateName": null,
		"interLataCarriers": null,
		"intraLataCarriers": null,
		"tfn": "8002078624",
		"tfnStatusId": "W"
	}
]   

	*/
=======
        
    $result = false;

    //$result = pt_wc_rt_search_number();

>>>>>>> 445ad8f913a86648e831b215d45f3f9ddceed758
?>

<?php get_template_part('parts/header'); ?>

<div class="page-banner">
<<<<<<< HEAD
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

				<h1>Tool Free results</h1>

				<h2><?php esc_html_e( 'Search results' ); ?></h2>

				<?php wc_print_notices(); ?>

				<?php echo 'Result: <pre>'. print_r( $result,1  ) . '</pre>'; ?>

				<?php if ( ! $result ) { ?>

					<p><?php esc_html_e( 'Sorry, an error occurred while searching phone numbers. Please try again.' ); ?></p>


				<?php } elseif ( 0 == count( $result ) ) { ?>

					<p><?php esc_html_e( 'Sorry, no phone numbers matched your criteria. Please try again.' ); ?></p>

				<?php } else { ?>

					<table class="table table-striped">
						<thead class="thead-light">
							<tr>
								<th scope="col"><?php esc_html_e( 'Phone number' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Price' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Buy' ); ?></th>
							</tr>
						</thead>
						<tbody>

					<?php

						foreach ( $result as $item ) {

							//echo '<pre>' . print_r( $item, 1 ) . '</pre>';

							/*
							[
	{
		"respOrgId": "AAM99",
		"statusTs": "1994-12-01T01:45:00.000Z",
		"effectiveTs": "1994-12-01T01:45:00.000Z",
		"lastUpdateTs": "2017-04-03T21:21:55.926Z",
		"templateName": null,
		"interLataCarriers": null,
		"intraLataCarriers": null,
		"tfn": "8002078624",
		"tfnStatusId": "W"
	}
]   
							*/

							?>

							<tr>
								<th scope="row"><?php echo $item->tfn; ?></th>
								<td><?php echo wc_price( $item->price / 100 ); ?></td>
								<td>
									<?php

										if ( $item->tfnStatusId != 'R' ) {

											?>
	
											  <a href="?pt_wc_rt_phone=<?php echo $item->tfn;?>" class="button"><?php esc_html_e( 'Buy' ); ?></a>

											<?php

										} else {

								
											$product_id      = wc_get_product_id_by_sku( $item->tfn );
											$product_cart_id = WC()->cart->generate_cart_id( $product_id );
											$in_cart         = WC()->cart->find_product_in_cart( $product_cart_id );

											if ( $in_cart ) {

												esc_html_e( 'Already in cart' );

											} else {

												esc_html_e( 'Reserved' );

											}
										}
									?>
								</td>
							</tr>

							<?php

						} 

						?>

						</tbody>
					</table>

				<?php } ?>

		   </div>
		</div>
		<div class="mb-search mt-5" data-aos="fade-in" data-aos-duration="1000">
			<div class="row">
				<div class="col-md-8 offset-md-2">
				<nav>
				<div class="nav nav-tabs justify-content-center mb-3" id="nav-tab" role="tablist">
					<a class="nav-link" id="nav-home-tab" data-toggle="tab" href="#nav-local" role="tab" aria-controls="nav-local" aria-selected="false">Local</a>
					<a class="nav-link active" id="nav-toll-free-tab" data-toggle="tab" href="#nav-toll-free" role="tab" aria-controls="nav-profile" aria-selected="true">Toll Free</a>
				</div>
			</nav>
			<div class="tab-content" id="nav-tabContent">
				<div class="tab-pane fade" id="nav-local" role="tabpanel" aria-labelledby="nav-home-tab">
				<form method="post">
					<input type="hidden" name="pt_wc_rb_search_local" value="1">
					<div class="form-row align-items-center">
						<div class="form-group col-md-3">
							<label class="sr-only" for="localArea">Area Code</label>
							<input type="text" name="pt_wc_rb_area" class="form-control" id="localArea" placeholder="Area Code" value="<?php echo sanitize_text_field( $_POST['pt_wc_rb_area'] )?>">
						</div>
						<div class="form-group col-md-6">
							<label class="sr-only" for="localWord"></label>
							<input type="text" class="form-control" id="localWord" placeholder="keyword/number" name="pt_wc_rb_vanity" data-swplive="true" value="<?php echo sanitize_text_field( $_POST['pt_wc_rb_vanity'] ); ?>" /> <!-- data-swplive="true" enables SearchWP Live Search -->
						</div>
						<div class="form-group col-md-3">
							<button type="submit" class="btn btn-primary w-100">Search</button>
						</div>
					</div>
				</form>
				</div>
				<div class="tab-pane fade show active" id="nav-toll-free" role="tabpanel" aria-labelledby="nav-profile-tab">
				<form method="post">
					<input type="hidden" name="pt_wc_rt_search_tollfree" value="1">
					<div class="form-row align-items-center">
						<div class="form-group col-md-3">
						   <span class="tollfree">1-8XX</span>
						</div>
						<div class="form-group col-md-6">
							<label class="sr-only" for="localWord"></label>
							<input type="text" name="pt_wc_rt_vanity" value="<?php echo sanitize_text_field( $_POST['pt_wc_rt_vanity'] ); ?>"class="form-control" id="tollFreeNumber" placeholder="keyword/number">
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
=======
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

                <h1>Tool Free results</h1>

                <h2><?php esc_html_e( 'Search results' ); ?></h2>

                <?php wc_print_notices(); ?>

                <?php  //echo 'Result: <pre>'. print_r( $result,1  ) . '</pre>'; ?>

                <?php if ( ! $result || ! $result->items ) { ?>

                    <p><?php esc_html_e( 'Sorry, an error occurred while searching phone numbers. Please try again.' ); ?></p>


                <?php } elseif ( 0 == count( $result->items ) ) { ?>

                    <p><?php esc_html_e( 'Sorry, no phone numbers matched your criteria. Please try again.' ); ?></p>

                <?php } else { ?>

                    <table class="table table-striped">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col"><?php esc_html_e( 'Phone number' ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Area code' ); ?></th>
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

                            ?>

                            <tr>
                                <th scope="row"><?php echo str_replace( $item->area_code, '', $item->phone ); ?></th>
                                <td><?php echo $item->area_code; ?></td>
                                <td><?php echo $item->city; ?></td>
                                <td><?php echo $item->state; ?></td>
                                <td><?php echo wc_price( $item->price / 100 ); ?></td>
                                <td><a href="?pt_wc_rb_phone=<?php echo $item->phone;?>" class="button"><?php esc_html_e( 'Buy' ); ?></a></td>
                            </tr>

                            <?php

                        } 

                        ?>

                        </tbody>
                    </table>

                <?php } ?>

           </div>
        </div>
        <div class="mb-search mt-5" data-aos="fade-in" data-aos-duration="1000">
            <div class="row">
                <div class="col-md-8 offset-md-2">
                <nav>
                <div class="nav nav-tabs justify-content-center mb-3" id="nav-tab" role="tablist">
                    <a class="nav-link" id="nav-home-tab" data-toggle="tab" href="#nav-local" role="tab" aria-controls="nav-local" aria-selected="false">Local</a>
                    <a class="nav-link active" id="nav-toll-free-tab" data-toggle="tab" href="#nav-toll-free" role="tab" aria-controls="nav-profile" aria-selected="true">Toll Free</a>
                </div>
            </nav>
            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade" id="nav-local" role="tabpanel" aria-labelledby="nav-home-tab">
                <form method="post">
                    <input type="hidden" name="pt_wc_rb_search_local" value="1">
                    <div class="form-row align-items-center">
                        <div class="form-group col-md-3">
                            <label class="sr-only" for="localArea">Area Code</label>
                            <input type="text" name="pt_wc_rb_area" class="form-control" id="localArea" placeholder="Area Code" value="<?php echo sanitize_text_field( $_POST['pt_wc_rb_area'] )?>">
                        </div>
                        <div class="form-group col-md-6">
                            <label class="sr-only" for="localWord"></label>
                            <input type="text" class="form-control" id="localWord" placeholder="keyword/number" name="pt_wc_rb_vanity" data-swplive="true" value="<?php echo sanitize_text_field( $_POST['pt_wc_rb_vanity'] ); ?>" /> <!-- data-swplive="true" enables SearchWP Live Search -->
                        </div>
                        <div class="form-group col-md-3">
                            <button type="submit" class="btn btn-primary w-100">Search</button>
                        </div>
                    </div>
                </form>
                </div>
                <div class="tab-pane fade show active" id="nav-toll-free" role="tabpanel" aria-labelledby="nav-profile-tab">
                <form method="post">
                    <input type="hidden" name="pt_wc_rt_search_tollfree" value="1">
                    <div class="form-row align-items-center">
                        <div class="form-group col-md-3">
                           <span class="tollfree">1-8XX</span>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="sr-only" for="localWord"></label>
                            <input type="text" name="pt_wc_rt_vanity" value="<?php echo sanitize_text_field( $_POST['pt_wc_rt_vanity'] ); ?>"class="form-control" id="tollFreeNumber" placeholder="keyword/number">
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
>>>>>>> 445ad8f913a86648e831b215d45f3f9ddceed758
</section>


<?php get_template_part('parts/footer'); ?>
