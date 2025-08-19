$(function () {
	"use strict";

	new PerfectScrollbar(".app-container"),
		new PerfectScrollbar(".header-message-list"),
		new PerfectScrollbar(".header-notifications-list"),
		$(".mobile-toggle-icon").on("click", function () {
			$(".wrapper").toggleClass("toggled");
		}),
		/* dark mode button */

		$(".dark-mode").click(function () {
			$("html").attr("data-bs-theme", function (i, v) {
				return v === "dark" ? "light" : "dark";
			});
		});

	$(".dark-mode").on("click", function () {
		if ($(".dark-mode-icon i").attr("class") == "bx bx-sun") {
			$(".dark-mode-icon i").attr("class", "bx bx-moon");
		} else {
			$(".dark-mode-icon i").attr("class", "bx bx-sun");
		}
	}),
		$(".mobile-toggle-menu").click(function () {
			$(".wrapper").hasClass("toggled")
				? ($(".wrapper").removeClass("toggled"),
				  $(".sidebar-wrapper").unbind("hover"))
				: ($(".wrapper").addClass("toggled"),
				  $(".sidebar-wrapper").hover(
						function () {
							$(".wrapper").addClass("sidebar-hovered");
						},
						function () {
							$(".wrapper").removeClass("sidebar-hovered");
						}
				  ));
		}),
		// back to top button
		$(document).ready(function () {
			$(window).on("scroll", function () {
				$(this).scrollTop() > 300
					? $(".back-to-top").fadeIn()
					: $(".back-to-top").fadeOut();
			}),
				$(".back-to-top").on("click", function () {
					return (
						$("html, body").animate(
							{
								scrollTop: 0,
							},
							600
						),
						!1
					);
				});
		}),
		// menu
		$(function () {
			$("#menu").metisMenu();
		}),
		// active
		$(function () {
			for (
				var e = window.location,
					o = $(".metismenu li a")
						.filter(function () {
							return this.href == e;
						})
						.addClass("")
						.parent()
						.addClass("mm-active");
				o.is("li");

			)
				o = o.parent("").addClass("mm-show").parent("").addClass("mm-active");
		}),
		// chat process
		$(".chat-toggle-btn").on("click", function () {
			$(".chat-wrapper").toggleClass("chat-toggled");
		}),
		$(".chat-toggle-btn-mobile").on("click", function () {
			$(".chat-wrapper").removeClass("chat-toggled");
		}),
		// email
		$(".email-toggle-btn").on("click", function () {
			$(".email-wrapper").toggleClass("email-toggled");
		}),
		$(".email-toggle-btn-mobile").on("click", function () {
			$(".email-wrapper").removeClass("email-toggled");
		}),
		$(".compose-mail-btn").on("click", function () {
			$(".compose-mail-popup").show();
		}),
		$(".compose-mail-close").on("click", function () {
			$(".compose-mail-popup").hide();
		}),
		/* switcher */

		$("#LightTheme").on("click", function () {
			$("html").attr("data-bs-theme", "light");
		}),
		$("#DarkTheme").on("click", function () {
			$("html").attr("data-bs-theme", "dark");
		}),
		$("#SemiDarkTheme").on("click", function () {
			$("html").attr("data-bs-theme", "semi-dark");
		}),
		$("#BoderedTheme").on("click", function () {
			$("html").attr("data-bs-theme", "bodered-theme");
		});

	$(".switcher-btn").on("click", function () {
		$(".switcher-wrapper").toggleClass("switcher-toggled");
	}),
		$(".close-switcher").on("click", function () {
			$(".switcher-wrapper").removeClass("switcher-toggled");
		});
});

$("#submit_product_form").click(function (e) {
	e.preventDefault();

	var isValid = true;

	// Validate all inputs (text, number) and textarea
	$(
		"#productForm input[type='text'], #productForm input[type='number'], #productForm textarea"
	).each(function () {
		var input = $(this);
		if (!input.val().trim()) {
			input.addClass("is-invalid");
			isValid = false;
		} else {
			input.removeClass("is-invalid").addClass("is-valid");
		}
	});

	// Validate image input
	var imageInput = $("#image-uploadify");
	var file = imageInput.get(0).files[0];

	if (!file) {
		imageInput.addClass("is-invalid");
		isValid = false;
	} else {
		// Validate file type
		var allowedTypes = ["image/jpeg", "image/png", "image/jpg"];
		if ($.inArray(file.type, allowedTypes) === -1) {
			imageInput.addClass("is-invalid");
			Swal.fire(
				"Invalid file type",
				"Only JPG, JPEG, and PNG files are allowed.",
				"error"
			);
			isValid = false;
		}
		// Validate file size (2MB = 2 * 1024 * 1024)
		else if (file.size > 2 * 1024 * 1024) {
			imageInput.addClass("is-invalid");
			Swal.fire("File too large", "Image size must be less than 2MB.", "error");
			isValid = false;
		} else {
			imageInput.removeClass("is-invalid").addClass("is-valid");
		}
	}

	if (!isValid) {
		return; // Stop form submission
	}

	var form = $("#productForm")[0];
	var formData = new FormData(form);

	$.ajax({
		url: site_url + "admin/products/new_product",
		type: "POST",
		data: formData,
		contentType: false,
		processData: false,
		dataType: "json",
		success: function (response) {
			if (response.status == "success") {
				Swal.fire({
					title: "Success!",
					text: "Product added successfully",
					icon: "success",
					timer: 3000,
				});
				$("#productForm")[0].reset();
				$("#productForm input, #productForm textarea").removeClass(
					"is-valid is-invalid"
				);
			} else {
				Swal.fire({
					title: "Error!",
					text: response.message || "An error occurred.",
					icon: "error",
				});
			}
		},
	});
});
let limit = 5;
let offset = 0;
let currentPage = 1;
let totalRecords = 0;

function fetchProducts(page = 1, search = "") {
	offset = (page - 1) * limit;

	$.ajax({
		url: site_url + "admin/products/fetch_products",
		type: "POST",
		data: { limit: limit, offset: offset, search: search },
		dataType: "json",
		success: function (response) {
			totalRecords = response.total;
			renderTable(response.products);
			renderPagination(totalRecords, page);
		},
	});
}

function renderTable(products) {
	let html = "";
	if (products.length === 0) {
		html =
			'<tr><td colspan="7" class="text-center">No products found.</td></tr>';
	} else {
		products.forEach((product, index) => {
			html += `<tr>
                <td>${offset + index + 1}</td>
                <td>${product.name}</td>
                <td>${product.price}</td>
                <td>${product.mrp}</td>
               
                <td><img src="${site_url}${product.image}" width="50" /></td>
<td><button class="btn btn-sm btn-danger delete-product" data-id="${
				product.id
			}">Delete</button></td>            </tr>`;
		});
	}
	$("#search-results").html(html);
}

function renderPagination(total, currentPage) {
	let totalPages = Math.ceil(total / limit);
	let html = "";

	const maxButtonsToShow = 3;
	let startPage = Math.max(1, currentPage - Math.floor(maxButtonsToShow / 2));
	let endPage = startPage + maxButtonsToShow - 1;

	if (endPage > totalPages) {
		endPage = totalPages;
		startPage = Math.max(1, endPage - maxButtonsToShow + 1);
	}

	// Previous button
	html += `<li class="page-item ${currentPage === 1 ? "disabled" : ""}">
        <a class="page-link" href="javascript:void(0)" onclick="fetchProducts(${currentPage - 1}, $('#product-search').val())">Previous</a>
    </li>`;

	// Page buttons
	for (let i = startPage; i <= endPage; i++) {
		html += `<li class="page-item ${i === currentPage ? "active" : ""}">
            <a class="page-link" href="javascript:void(0)" onclick="fetchProducts(${i}, $('#product-search').val())">${i}</a>
        </li>`;
	}

	// Next button
	html += `<li class="page-item ${currentPage === totalPages ? "disabled" : ""}">
        <a class="page-link" href="javascript:void(0)" onclick="fetchProducts(${currentPage + 1}, $('#product-search').val())">Next</a>
    </li>`;

	$(".pagination").html(html);
}


// Initial fetch
$(document).ready(function () {
	fetchProducts();

	$("#product-search").on("input", function () {
		fetchProducts(1, $(this).val());
	});
});
$(document).ready(function() {
    // Handle delete button click
    $(document).on('click', '.delete-product', function() {
        let productId = $(this).data('id');
        let row = $(this).closest('tr');

        // Show SweetAlert confirmation
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Make AJAX request to delete product
                $.ajax({
                    url: site_url + "admin/products/delete_product",
                    type: 'POST',
                    data: { product_id: productId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Show success message
                            Swal.fire(
                                'Deleted!',
                                'Product has been deleted successfully.',
                                'success'
                            );
                            // Remove the row from the table
                            row.remove();
                            location.reload();
                        } else {
                            // Show error message
                            Swal.fire(
                                'Error!',
                                response.message || 'Failed to delete product.',
                                'error'
                            );
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        Swal.fire(
                            'Error!',
                            'An error occurred while deleting the product.',
                            'error'
                        );
                    }
                });
            }
        });
    });
});
(function () {
	"use strict";
	const form = document.querySelector("#user-register");

	form.addEventListener(
		"submit",
		function (event) {
			event.preventDefault();

			if (!form.checkValidity()) {
				event.stopPropagation();
				form.classList.add("was-validated");
			} else {
				const formData = {
					first_name: form.querySelector('[name="first_name"]').value,
					last_name: form.querySelector('[name="last_name"]').value,
					phone: form.querySelector('[name="phone"]').value,
					email: form.querySelector('[name="email"]').value,
					password: form.querySelector('[name="password"]').value,
				};

				$.ajax({
					url: site_url + "login/add_user",
					type: "POST",
					data: formData,
					dataType: "json",
					success: function (response) {
						if (response.status === "success") {
							Swal.fire({
								icon: "success",
								title: "Success!",
								text: response.message,
							});
							form.reset();
							form.classList.remove("was-validated");
						} else {
							Swal.fire({
								icon: "error",
								title: "Oops!",
								text: response.message,
							});
						}
					},
					error: function () {
						Swal.fire({
							icon: "error",
							title: "Error!",
							text: "An error occurred while submitting the form.",
						});
					},
				});
			}
		},
		false
	);
})();

