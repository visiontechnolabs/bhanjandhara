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
                        <li class="breadcrumb-item active" aria-current="page">Songs</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!--end breadcrumb-->
        <hr>
        <div class="card">
            <div class="card-body">
                <div class="d-lg-flex align-items-center mb-4 gap-3">
                    <input type="text" id="search" class="form-control w-25" placeholder="Search song...">
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Index#</th>
                                <th>Category</th>
                                <th>Title</th>
                                <th>Sub-category</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="songTable"></tbody>
                    </table>
                </div>
            </div>
        </div>
        <nav aria-label="Page navigation example">
            <ul class="pagination round-pagination justify-content-center" id="pagination"></ul>
        </nav>
    </div>
</div>