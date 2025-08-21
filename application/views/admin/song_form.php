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

                                <!-- Category Select (recursive) -->
                                <div class="mb-3">
                                    <label for="category" class="form-label">Category</label>
                                    <select name="category_id[]" class="form-select" id="mainCategory" required>
                                        <option value="">-- Select Category --</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?= $cat->id; ?>"><?= $cat->name; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Please select a category.</div>
                                </div>

                                <!-- Dynamic subcategory containers -->
                                <div id="subCategoryContainer" class="mb-3">

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

// Load child categories dynamically
function loadSubCategories(parentId, container) {
    // alert('h');
    $.ajax({
        url: "<?= base_url('admin/song/get_subcategories'); ?>",
        type: "POST",
        data: JSON.stringify({ parent_id: parentId }),
        contentType: "application/json",
        dataType: "json",
        success: function(res) {
            if (res.status && res.data.length > 0) {
                let select = $('<select class="form-select mt-2" name="category_id[]" required></select>');
                select.append('<option value="">-- Select Sub Category --</option>');
                res.data.forEach(cat => {
                    select.append('<option value="'+cat.id+'">'+cat.name+'</option>');
                });

                // Remove any deeper levels when changing
                select.on('change', function() {
                    $(this).nextAll('select').remove(); // remove lower dropdowns
                    let newParent = $(this).val();
                    if (newParent) {
                        loadSubCategories(newParent, container);
                    }
                });

                container.append(select);
            }
        }
    });
}

// When main category changes, load its first level subcategories
$('#mainCategory').on('change', function() {
    // alert('h');
    let mainCatId = $(this).val();
    let container = $('#subCategoryContainer');
    container.empty(); // clear old dropdowns
    if (mainCatId) {
        loadSubCategories(mainCatId, container);
    }
});


$(document).ready(function () {
    $("#SongForm").on("submit", function (e) {
        e.preventDefault();

        for (instance in CKEDITOR.instances) {
            CKEDITOR.instances[instance].updateElement();
        }

        var form = $(this)[0];
        var formData = new FormData(form);

        $.ajax({
            url: "<?= base_url('admin/song/save_song'); ?>",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
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
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'Something went wrong!' });
                }
            },
            error: function (xhr, status, error) {
                Swal.close();
                Swal.fire({ icon: 'error', title: 'Request Failed', text: 'Could not save song. Please try again!' });
                console.error(error);
            }
        });
    });
});
</script>
