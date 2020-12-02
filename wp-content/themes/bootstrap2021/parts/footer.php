<a href="#0" class="scrollup"></a>

<footer>
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <div class="copy">&copy; <?php echo date('Y'); ?> phoneNumber<sup>+</sup></div>
            </div>
            <div class="col-md-4">
                <div class="terms">
                    <a href="/terms-of-service">Terms of Service</a> | <a href="/privacy-policy">Privacy Policy</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="credit"><span class="site-by">site by</span> <a target="_blank" href="https://914digital.com"><img alt="914Digital" src="<?php bloginfo('template_directory') ?>/img/footer-logo-wh.png"></a>
                </div><!-- /credit -->
            </div>
        </div><!--row-->
    </div><!--container-->
</footer>
<?php wp_footer(); ?>

<!-- Back To Top Button -->
<script>
    $(document).ready(function () {

    $(window).scroll(function () {
        if ($(this).scrollTop() > 100) {
            $('.scrollup').fadeIn();
        } else {
            $('.scrollup').fadeOut();
        }
    });

    $('.scrollup').click(function () {
        $("html, body").animate({
            scrollTop: 0
        }, 600);
        return false;
        });

    });
</script>

<script>
    AOS.init({
       once: true, 
    });
  </script>

<script>
/* Open when someone clicks on the span element */
function openNav() {
  document.getElementById("myNav").style.width = "100%";
}

/* Close when someone clicks on the "x" symbol inside the overlay */
function closeNav() {
  document.getElementById("myNav").style.width = "0%";
}
</script>


</body>
</html>
