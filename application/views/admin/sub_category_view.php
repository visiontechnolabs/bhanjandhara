<div class="page-wrapper">
	<div class="page-content">
		<!--breadcrumb-->
		<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
			<!-- <div class="breadcrumb-title pe-3">Category</div> -->
			<div class="ps-3">
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb mb-0 p-0">
						<li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard'); ?>"><i
									class="bx bx-home-alt"></i></a>
						</li>
						<li class="breadcrumb-item active" aria-current="page">Category</li>
					</ol>
				</nav>
			</div>

		</div>
		<!--end breadcrumb-->

		<div class="card">
			<div class="card-body">
				<div class="d-lg-flex align-items-center mb-4 gap-3">
					<div class="position-relative">
						<input type="text" class="form-control ps-5 radius-30" placeholder="Search Order"> <span
							class="position-absolute top-50 product-show translate-middle-y"><i
								class="bx bx-search"></i></span>
					</div>
				</div>
				<div class="table-responsive">
					<table class="table mb-0">
						<thead class="table-light">
							<tr>
								<th>Index</th>
								<th>Categorie</th>
								<th>Sub Categorie</th>
								<th>Staus</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody id="SubcategoryTableBody">

						</tbody>
					</table>
				</div>
			</div>
		</div>
		<nav aria-label="Page navigation example">
			<ul class="pagination round-pagination justify-content-center">
				<li class="page-item"><a class="page-link" href="javascript:;">Previous</a>
				</li>
				<li class="page-item"><a class="page-link" href="javascript:;javascript:;">1</a>
				</li>
				<li class="page-item active"><a class="page-link" href="javascript:;">2</a>
				</li>
				<li class="page-item"><a class="page-link" href="javascript:;">3</a>
				</li>
				<li class="page-item"><a class="page-link" href="javascript:;">Next</a>
				</li>
			</ul>
		</nav>

	</div>
</div>
<script src="<?= base_url('assets/js/jquery.min.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
	// subcategory
	$(document).ready(function () {
		const perPage = 10;
		let currentPage = 1;
		let searchText = "";

		function loadSubcategories(page = 1) {
			$.ajax({
				url: site_url + "admin/category/sub_ajax_list",
				type: "POST",
				dataType: "json",
				data: {
					page: page,
					search: searchText,
					 limit: perPage
				},
				success: function (res) {
					renderTable(res.data, res.start_index);
					renderPagination(res.current_page, res.total_pages);
				},
			});
		}

		function renderTable(data, startIndex) {
			let rows = "";

			if (data.length === 0) {
				rows = `<tr><td colspan="5" class="text-center">No records found</td></tr>`;
			} else {
				data.forEach((row, i) => {
					rows += `
				<tr>
					<td>${startIndex + i}</td>
					<td>${row.category_name}</td>
					<td>${row.subcategory_name}</td>
					<td>
					
	  ${row.isActive == 1
							? `<div class="d-flex align-items-center text-success">
					
		  <i class="bx bx-radio-circle-marked bx-burst bx-rotate-90 align-middle font-18 me-1"></i>
		  <span>Published</span>
		  </div>`
							: `<div class="d-flex align-items-center text-danger">
		  <i class="bx bx-radio-circle-marked bx-burst bx-rotate-90 align-middle font-18 me-1"></i>
		  <span>Unpublished</span>
		  </div>`
						}
		</td>
					<td>
	<div class="d-flex order-actions align-items-center">
		<!-- Edit Button with Icon -->
		<a href="${site_url}admin/category/edit/${row.id}" class="me-2"><i class="bx bxs-edit"></i></a>

		<!-- Publish / Unpublish Button with Icon -->
		<a href="javascript:void(0);" 
   class="toggle-status-btn-2 ms-2" 
   data-id="${row.id}" 
   data-status="${row.isActive == 1 ? 0 : 1}" 
   title="${row.isActive == 1 ? 'Unpublish' : 'Publish'}">
    <i class="bx ${row.isActive == 1 ? 'bxs-hide text-danger fs-5' : 'bxs-show text-success fs-5'}"></i>
</a>
	</div>
</td>	
				</tr>`;
				});
			}

			$("#SubcategoryTableBody").html(rows);
		}

		function renderPagination(current, total) {
			let html = "";
			const maxPagesToShow = 3;
			let start = Math.max(1, current - 1);
			let end = Math.min(total, start + maxPagesToShow - 1);

			if (end - start + 1 < maxPagesToShow) {
				start = Math.max(1, end - maxPagesToShow + 1);
			}

			html += `<li class="page-item ${current === 1 ? "disabled" : ""}">
			<a class="page-link" href="javascript:;" data-page="${current - 1}">Previous</a>
		</li>`;

			for (let i = start; i <= end; i++) {
				html += `<li class="page-item ${i === current ? "active" : ""}">
				<a class="page-link" href="javascript:;" data-page="${i}">${i}</a>
			</li>`;
			}

			html += `<li class="page-item ${current === total ? "disabled" : ""}">
			<a class="page-link" href="javascript:;" data-page="${current + 1}">Next</a>
		</li>`;

			$(".pagination").html(html);
		}

		$(document).on("click", ".pagination .page-link", function () {
			const page = $(this).data("page");
			if (page) {
				currentPage = page;
				loadSubcategories(currentPage);
			}
		});

		$(".form-control[placeholder*='Search']").on("keyup", function () {
			searchText = $(this).val();
			currentPage = 1;
			loadSubcategories(currentPage);
		});

		$(document).on("click", ".toggle-status-btn-2", function () {
			const id = $(this).data("id");
			const status = $(this).data("status");

			$.ajax({
				url: site_url + "admin/category/toggle_status_sub_2",
				type: "POST",
				data: { id, status },
				dataType: "json",
				success: function (res) {
					if (res.success) {
						Swal.fire({
							icon: "success",
							title: res.message,
							timer: 2000,
							showConfirmButton: false,
						});
						setTimeout(() => loadSubcategories(currentPage), 2000);
					} else {
						Swal.fire("Error", res.message, "error");
					}
				},
			});
		});

		// Initial load
		loadSubcategories(currentPage);
	});
</script>