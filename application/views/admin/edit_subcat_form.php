<!--start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">

        <!-- Breadcrumb -->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item">
                            <a href="<?= base_url('admin/dashboard'); ?>">
                                <i class="bx bx-home-alt"></i>
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Edit Subcategory</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Subcategory Card -->
        <div class="card">
            <div class="card-body p-4">
                <h5 class="card-title">Edit Subcategory</h5>
                <hr>
                <div class="form-body mt-4">
                    <div class="row">
                        <div class="col">
                            <form id="EditSubcategoryForm" method="post" enctype="multipart/form-data" novalidate>
                                <!-- Subcategory Title -->
                                <div class="mb-3">
                                    <label for="subcategoryTitle" class="form-label">Subcategory Title</label>
                                    <input type="text" name="subcategory_title" class="form-control"
                                        id="subcategoryTitle" placeholder="Enter subcategory title"
                                        value="<?= htmlspecialchars($sub_categories->title); ?>" required>
                                    <input type="hidden" name="id" value="<?= $sub_categories->id ?>">
                                    <div class="invalid-feedback">Please enter the subcategory title.</div>
                                </div>

                                <!-- Main Category Select -->
                                <div class="mb-3">
                                    <label for="mainCategory" class="form-label">Main Category</label>
                                    <select name="main_category_id" class="form-select" id="mainCategory" required>
                                        <option value="">-- Select Main Category --</option>
                                        <?php foreach ($main_categories as $cat): ?>
                                            <option value="<?= $cat->id; ?>"
                                                <?= ($sub_categories->main_category_id == $cat->id) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($cat->name); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Please select a main category.</div>
                                </div>

                                <!-- Submit Button -->
                                <div class="mb-3">
                                    <button type="submit" class="btn btn-success w-100">Update Subcategory</button>
                                </div>
                            </form>
                        </div>
                    </div><!--end row-->
                </div>
            </div>
        </div>
    </div>
</div>
<script src="<?= base_url('assets/js/jquery.min.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $("#EditSubcategoryForm").on("submit", function (e) {
	e.preventDefault(); // Prevent form from submitting normally

	let form = $(this)[0];
	let formData = new FormData(form);

	$.ajax({
		url: site_url + "admin/category/edit_subcategory",
		type: "POST",
		data: formData,
		processData: false,
		contentType: false,
		dataType: "json",
		beforeSend: function () {
			// Optional: Show loading spinner
		},
		success: function (response) {
			if (response.status) {
				Swal.fire({
					icon: "success",
					title: "Updated",
					text: response.message,
					timer: 2000,
					showConfirmButton: false,
				}).then(() => {
					window.location.href = site_url + "sub_category";
				});
			} else {
				Swal.fire({
					icon: "error",
					title: "Error",
					text: response.message,
				});
			}
		},
		error: function () {
			Swal.fire({
				icon: "error",
				title: "AJAX Error",
				text: "Something went wrong with the request.",
			});
		},
	});
});
</script>