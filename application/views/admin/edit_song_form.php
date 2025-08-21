<!--start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">

        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard'); ?>"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit Song</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!--end breadcrumb-->
        <div class="card">
            <div class="card-body p-4">
                <h5 class="card-title">Edit Song</h5>
                <hr>
                <div class="form-body mt-4">
                    <div class="row">
                        <div class="col">
                            <form id="editSongForm" method="post" enctype="multipart/form-data" novalidate>
                                <input type="hidden" name="id" value="<?= $song->id; ?>">

                                <!-- Song Title -->
                                <div class="mb-3">
                                    <label for="songTitle" class="form-label">Song Title</label>
                                    <input type="text" name="title" class="form-control" id="songTitle"
                                        value="<?= htmlspecialchars($song->title, ENT_QUOTES) ?>"
                                        placeholder="Enter song title" required>
                                    <div class="invalid-feedback">Please enter the song title.</div>
                                </div>

                                <!-- Main Category Select -->
                                <div class="mb-3">
                                    <label for="mainCategory" class="form-label">Main Category</label>
                                    <select name="category_id" class="form-select" id="mainCategory" required>
                                        <option value="">-- Select Main Category --</option>
                                        <?php foreach ($main_categories as $cat): ?>
                                            <option value="<?= $cat->id; ?>" <?= ($song->category_id == $cat->id) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($cat->name, ENT_QUOTES); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Please select a main category.</div>
                                </div>

                                <!-- Sub Category Select (preloaded if available) -->
                                <div class="mb-3">
                                    <label for="subCategory" class="form-label">Sub Category</label>
                                    <select name="sub_category_id" class="form-select" id="subCategory">
                                        <option value="">-- Select Sub Category --</option>
                                        <?php if (!empty($sub_categories)): ?>
                                            <?php foreach ($sub_categories as $sub): ?>
                                                <option value="<?= $sub->id; ?>" <?= ($song->sub_category_id == $sub->id) ? 'selected' : ''; ?>>
                                                    <?= htmlspecialchars($sub->title, ENT_QUOTES); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <div class="invalid-feedback">Please select a sub category.</div>
                                </div>

                                <!-- Song Description (CKEditor) -->
                                <div class="mb-3">
                                    <label for="songDescription" class="form-label">Description</label>
                                    <textarea name="description" id="songDescription" class="form-control" rows="6"><?= htmlspecialchars($song->description, ENT_QUOTES) ?></textarea>
                                    <div class="invalid-feedback">Please enter the song description.</div>
                                </div>

                                <!-- Submit Button -->
                                <div class="mb-3">
                                    <button type="submit" class="btn btn-success w-100">Update Song</button>
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
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>

<script>
    var site_url = "<?= site_url(); ?>";
    CKEDITOR.replace('songDescription', { height: 250 });

    // Fetch subcategories dynamically on main category change
    document.getElementById('mainCategory').addEventListener('change', function () {
        var mainCategoryId = this.value;
        var subCategorySelect = document.getElementById('subCategory');
        subCategorySelect.innerHTML = '<option value="">Loading...</option>';

        if (mainCategoryId) {
            fetch(site_url + "admin/song/get_subcategories", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ category_id: mainCategoryId })
            })
            .then(response => response.json())
            .then(data => {
                subCategorySelect.innerHTML = '<option value="">-- Select Sub Category --</option>';
                if (data.status && data.data) {
                    data.data.forEach(sub => {
                        let option = document.createElement('option');
                        option.value = sub.id;
                        option.textContent = sub.title;
                        subCategorySelect.appendChild(option);
                    });
                } else {
                    subCategorySelect.innerHTML = '<option value="">No subcategories found</option>';
                }
            })
            .catch(err => {
                subCategorySelect.innerHTML = '<option value="">Error loading subcategories</option>';
                console.error(err);
            });
        } else {
            subCategorySelect.innerHTML = '<option value="">-- Select Sub Category --</option>';
        }
    });

    // Submit form with AJAX
    $("#editSongForm").on("submit", function (e) {
        e.preventDefault();

        // Ensure CKEditor data is pushed into textarea
        for (var instance in CKEDITOR.instances) {
            CKEDITOR.instances[instance].updateElement();
        }

        var formData = new FormData(this);

        $.ajax({
            url: site_url + "admin/song/update_song",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (response) {
                if (response.status) {
                    Swal.fire({
                        icon: "success",
                        title: "Updated",
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false,
                    }).then(() => {
                        window.location.href = site_url + "songs";
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
                Swal.fire("Error", "Something went wrong!", "error");
            },
        });
    });
</script>
