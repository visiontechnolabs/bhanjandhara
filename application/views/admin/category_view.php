<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Table</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item">
                            <a href="<?= base_url('dashboard'); ?>"><i class="bx bx-home-alt"></i></a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Category</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!--end breadcrumb-->
        <hr>
        <div class="card">
            <div class="card-body">
                <div class="d-lg-flex align-items-center mb-4 gap-3">
                    <input type="text" id="search" class="form-control w-25" placeholder="Search category...">
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Index#</th>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="category"></tbody>
                    </table>
                </div>
            </div>
        </div>
        <nav aria-label="Page navigation example">
            <ul class="pagination round-pagination justify-content-center" id="pagination"></ul>
        </nav>
    </div>
</div>

<script src="<?= base_url('assets/js/jquery.min.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const site_url = "<?= base_url(); ?>";

    function renderCategoryTree(categories, level = 0, pageOffset = 0) {
        let html = '';
        categories.forEach((item, index) => {
            const padding = '&nbsp;'.repeat(level * 6); // indent children
            html += `
<tr>
    <td>${pageOffset + index + 1}</td>
    <td><img src="${site_url + item.image}" width="60"></td>
    <td>${padding}${item.name}</td>
    <td>
        ${
            item.isActive == 1
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
            <a href="${site_url}admin/category/edit_main/${item.id}" class="me-2">
                <i class="bx bxs-edit"></i>
            </a>
            ${
                item.isActive == 1
                    ? `<a href="javascript:void(0);" class="toggle-status-btn text-danger ms-2"
                        data-id="${item.id}" data-status="0" title="Unpublish">
                        <i class="bx bxs-hide fs-5"></i>
                      </a>`
                    : `<a href="javascript:void(0);" class="toggle-status-btn text-success ms-2"
                        data-id="${item.id}" data-status="1" title="Publish">
                        <i class="bx bxs-show fs-5"></i>
                      </a>`
            }
        </div>
    </td>
</tr>`;

            // Render children if any
            if (item.children && item.children.length > 0) {
                html += renderCategoryTree(item.children, level + 1, pageOffset);
            }
        });
        return html;
    }

    // Build improved pagination with Prev / Next and max 3 buttons
    function buildPagination(totalPages, currentPage) {
        let paginationHTML = '';

        // Previous button
        paginationHTML += `
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage - 1}">Prev</a>
            </li>`;

        // Calculate range to show max 3 pages
        let start = Math.max(1, currentPage - 1);
        let end = Math.min(totalPages, currentPage + 1);

        if (currentPage === 1) {
            end = Math.min(3, totalPages);
        } else if (currentPage === totalPages) {
            start = Math.max(1, totalPages - 2);
        }

        for (let i = start; i <= end; i++) {
            paginationHTML += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>`;
        }

        // Next button
        paginationHTML += `
            <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
            </li>`;

        return paginationHTML;
    }

    function loadCategories(page = 1, search = '') {
        $.ajax({
            url: site_url + "admin/category/fetch_categories",
            type: "POST",
            data: { page: page, search: search },
            dataType: "json",
            success: function (response) {
                const tbody = $("#category");
                tbody.empty();

                if (!response.data || response.data.length === 0) {
                    tbody.append("<tr><td colspan='5' class='text-center'>No categories found.</td></tr>");
                    $("#pagination").empty();
                    return;
                }

                // Render hierarchical rows
                tbody.html(renderCategoryTree(response.data, 0, (response.page - 1) * response.limit));

                // Build pagination
                const totalPages = Math.ceil(response.total / response.limit);
                $("#pagination").html(buildPagination(totalPages, response.page));

                // Handle pagination click
                $("#pagination .page-link").off('click').on('click', function (e) {
                    e.preventDefault();
                    const pageNo = parseInt($(this).data("page"));
                    if (!isNaN(pageNo) && pageNo >= 1 && pageNo <= totalPages) {
                        loadCategories(pageNo, $("#search").val());
                    }
                });
            },
            error: function () {
                Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: "Failed to load categories!"
                });
            }
        });
    }

    $(document).ready(function () {
        loadCategories(); // initial load

        $("#search").on("keyup", function () {
            const query = $(this).val();
            loadCategories(1, query);
        });

        // Toggle status button handler
        $(document).on("click", ".toggle-status-btn", function () {
            const button = $(this);
            const postId = button.data("id");
            const newStatus = button.data("status");

            $.ajax({
                url: site_url + "admin/category/toggle_status",
                type: "POST",
                data: { id: postId, status: newStatus },
                dataType: "json",
                success: function (res) {
                    if (res.success) {
                        Swal.fire({
                            icon: "success",
                            title: res.message,
                            timer: 2000,
                            showConfirmButton: false,
                        });
                        setTimeout(function () {
                            loadCategories(); // reload list without full page refresh
                        }, 2000);
                    } else {
                        Swal.fire("Error", res.message, "error");
                    }
                },
                error: function () {
                    Swal.fire("Error", "Something went wrong!", "error");
                },
            });
        });
    });
</script>
