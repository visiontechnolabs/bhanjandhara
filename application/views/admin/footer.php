<!--start overlay-->
		 <div class="overlay mobile-toggle-icon"></div>
		<!--end overlay-->
		<!--Start Back To Top Button-->
		  <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
		<!--End Back To Top Button-->
		<footer class="page-footer">
			<p class="mb-0">Copyright © 2024. All right reserved.</p>
		</footer>
	</div>
	<!--end wrapper-->
    <!-- Bootstrap JS -->
	
	 
	<script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>

<!--plugins-->
<script src="<?= base_url('assets/js/jquery.min.js') ?>"></script>
<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  const site_url = "<?= base_url() ?>";
</script>
<script src="<?= base_url('assets/js/app.js') ?>"></script>

<script src="<?= base_url('assets/plugins/simplebar/js/simplebar.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/metismenu/js/metisMenu.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js') ?>"></script>
<script>
  $('#menu').metisMenu(); 
</script>
<!--app JS-->
<script src="<?= base_url('assets/js/index.js') ?>"></script>
<script src="<?= base_url('assets/plugins/peity/jquery.peity.min.js') ?>"></script>

    <script>
       $(".data-attributes span").peity("donut")
    </script>
</body>

</html>