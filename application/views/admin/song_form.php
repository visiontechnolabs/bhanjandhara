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
                        <li class="breadcrumb-item active" aria-current="page">Add New Song</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Song Form Card -->
        <div class="card">
            <div class="card-body p-4">
                <h5 class="card-title">Add New Song</h5>
                <hr>
                <div class="form-body mt-4">
                    <div class="row">
                        <div class="col">
                            <form id="SongForm" method="post" enctype="multipart/form-data" novalidate>
                                
                                <!-- Main Category Select -->
                                <div class="mb-3">
                                    <label for="mainCategory" class="form-label">Main Category</label>
                                    <select name="main_category_id" class="form-select" id="mainCategory" required>
                                        <option value="">-- Select Main Category --</option>
                                        <?php foreach ($main_categories as $cat): ?>
                                            <option value="<?= $cat->id; ?>"><?= $cat->name; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Please select a main category.</div>
                                </div>

                                <!-- Sub Category Select (dynamic) -->
                                <div class="mb-3">
                                    <label for="subCategory" class="form-label">Sub Category</label>
                                    <select name="sub_category_id" class="form-select" id="subCategory" required>
                                        <option value="">-- Select Sub Category --</option>
                                    </select>
                                    <div class="invalid-feedback">Please select a sub category.</div>
                                </div>

                                <!-- Song Name -->
                                <div class="mb-3">
                                    <label for="songName" class="form-label">Song Name</label>
                                    <input type="text" name="song_name" class="form-control" id="songName"
                                        placeholder="Enter song name" required>
                                    <div class="invalid-feedback">Please enter the song name.</div>
                                </div>

                                <!-- Song Lyrics with CKEditor -->
                                <div class="mb-3">
                                    <label for="songLyrics" class="form-label">Song Lyrics</label>
                                    <textarea name="song_lyrics" class="form-control" id="songLyrics" rows="6" placeholder="Enter song lyrics"></textarea>
                                    <div class="invalid-feedback">Please enter the song lyrics.</div>
                                </div>

                                <!-- Submit Button -->
                                <div class="mb-3">
                                    <button type="submit" class="btn btn-primary w-100">Save Song</button>
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
    CKEDITOR.replace('songLyrics');

    // Load subcategories dynamically
   document.getElementById('mainCategory').addEventListener('change', function() {
    var mainCategoryId = this.value;
    var subCategorySelect = document.getElementById('subCategory');
    subCategorySelect.innerHTML = '<option value="">Loading...</option>';

    if (mainCategoryId) {
        fetch('<?= base_url("admin/song/get_subcategories"); ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ category_id: mainCategoryId })
        })
        .then(response => response.json())
        .then(data => {
            subCategorySelect.innerHTML = '<option value="">-- Select Sub Category --</option>';
            if (data.status && data.data) {
                data.data.forEach(sub => {
                    let option = document.createElement('option');
                    option.value = sub.id;
                    option.textContent = sub.title; // assuming DB column is "title"
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
$(document).ready(function () {
    $("#SongForm").on("submit", function (e) {
        e.preventDefault(); 

        // Get CKEditor content into textarea
        for (instance in CKEDITOR.instances) {
            CKEDITOR.instances[instance].updateElement();
        }

        var form = $(this)[0];
        var formData = new FormData(form);

        $.ajax({
            url: "<?= base_url('admin/song/save_song'); ?>", // your save method
            type: "POST",
            data: formData,
            processData: false, // important for FormData
            contentType: false, // important for FormData
            dataType: "json",
            beforeSend: function () {
                Swal.fire({
                    title: 'Please wait...',
                    text: 'Saving song details',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
            },
            success: function (res) {
                Swal.close();
                if (res.status) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: res.message || 'Song saved successfully!',
                        showConfirmButton: false,
                        timer: 2000
                    }).then(() => {
                        window.location.href = "<?= base_url('admin/song'); ?>";
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: res.message || 'Something went wrong!'
                    });
                }
            },
            error: function (xhr, status, error) {
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Request Failed',
                    text: 'Could not save song. Please try again!'
                });
                console.error(error);
            }
        });
    });
});
</script>
