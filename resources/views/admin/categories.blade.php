@extends('layouts.admin')

@section('content')
    <!--  MAIN CONTENT HEADER & BACK BUTTON  -->

<div class="container-fluid pt-4 px-4">
    <div class="card shadow-sm border-0 ">
    <div class="card-body">
        <!-- Header & Back Button -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="fw-bold mb-0">{{__('messages.Events Category')}} </h4>
                <small class="text-muted">View, update, and manage all scheduled categories</small>
            </div>
            <div>
                <a href="{{ url('/admin/dashboard') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> {{__('messages.Back to Dashboard')}}
                </a>
            </div>
        </div>

        <!-- Top Toolbar: Page Selector on Left, Add Category Button on Right -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-2">
                <label for="perPageSelect" class="form-label mb-0 text-muted small fw-bold">{{__('Show')}}</label>
                <select id="perPageSelect" class="form-select form-select-sm w-auto">
                    <option value="5" selected>5</option>
                    <option value="10">10</option>
                    <option value="25">25</option>
                </select>
                <span class="text-muted small">entries</span>
            </div>
            <div>
                <button class="btn btn-primary btn-sm" id="add-category-btn">{{__('messages.Add Category')}}</button>
            </div>
        </div>

        <!-- Table Container -->
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="categoriesTable">
                <thead class="table-light">
                    <tr>
                        <th>S.No</th>
                        <th>Category Name</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="categoriesTableBody">
                    <!-- Dynamic Rows populated via AJAX -->
                </tbody>
            </table>
        </div>
           <div class="d-flex  justify-content-end align-items-center mt-3">
     
            <div id="categoriesPagination"></div>
    </div>
 
</div>

</div>

</div>
    <!-- CREATE CATEGORY MODAL -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold" id="addCategoryModalLabel">
                    <i class="bi bi-plus-circle me-2"></i>Add New Category
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="createCategoryForm">
                <div class="modal-body">
                    <!-- Category Name -->
                    <div class="mb-3">
                        <label for="create_name" class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="create_name" name="name" required placeholder="e.g., Music & Concerts">
                        <div class="invalid-feedback error-name"></div>
                    </div>

                    <!-- Category Description -->
                    <div class="mb-3">
                        <label for="create_description" class="form-label fw-semibold">Description</label>
                        <textarea class="form-control" id="create_description" name="description" rows="3" placeholder="Brief details about this category..."></textarea>
                        <div class="invalid-feedback error-description"></div>
                    </div>

                    <!-- Active Status Switch -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold d-block">Status</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="create_is_active" name="is_active" value="1" checked>
                            <label class="form-check-label" for="create_is_active" id="createStatusLabel">Active</label>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="btnSaveCategory">
                        <i class="bi bi-check-circle me-1"></i> Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- ==================== VIEW CATEGORY MODAL ==================== -->
<div class="modal fade" id="viewCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title fw-bold">Category Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <strong> Name:</strong> <span id="view_category_name">---</span>
                    </div>
                    <div class="col-md-6">
                        <strong> Status:</strong> <span id="view_category_status">---</span>
                    </div>
                    <div class="col-md-12">
                        <strong> Description:</strong> <span id="view_category_description">---</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

    <!-- UPDATE CATEGORY MODAL -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="editCategoryModalLabel">
                        <i class="bi bi-pencil-square me-2"></i>Edit Category
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form id="updateCategoryForm">
                    <div class="modal-body">
                        <!-- Hidden Category ID -->
                        <input type="hidden" id="edit_category_id" name="category_id">

                        <!-- Category Name -->
                        <div class="mb-3">
                            <label for="edit_name" class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_name" name="name" required placeholder="e.g., Tech Conferences">
                            <div class="invalid-feedback error-name"></div>
                        </div>

                        <!-- Category Description -->
                        <div class="mb-3">
                            <label for="edit_description" class="form-label fw-semibold">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3" placeholder="Brief details about this category..."></textarea>
                            <div class="invalid-feedback error-description"></div>
                        </div>

                        <!-- Active Status Switch -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold d-block">Status</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" value="1">
                                <label class="form-check-input-label" for="edit_is_active" id="statusLabel">Active</label>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="btnUpdateCategory">
                            <i class="bi bi-check-circle me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script src="https://code.jquery.com/jquery-4.0.0.min.js" integrity="sha256-OaVG6prZf4v69dPg6PhVattBXkcOWQB62pdZ3ORyrao=" crossorigin="anonymous"></script>

<script>
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        'Accept': 'application/json'
    }
});

$(document).ready(function() {

    // Global Pagination State Variables
    let currentPage = 1;
    let currentLimit = $('#perPageSelect').val() || 10;

    // 1. Load Categories with Pagination
    function loadEventCategory(page = 1, limit = 10) {
        currentPage = page;
        currentLimit = limit;

        $.ajax({
            url: `/api/admin/categories?page=${page}&limit=${limit}`,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log(response);

                let categories = response.data;
                let tableBody = $('#categoriesTableBody');

                tableBody.empty();

                if (!categories || categories.length === 0) {
                    tableBody.append(`
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                No categories found.
                            </td>
                        </tr>
                    `);
                    renderPaginationControls(null);
                    return;
                }

                // Calculate Starting Serial Number based on pagination page & limit
                let pagination = response.pagination || {};
                let startNumber = pagination.from || ((page - 1) * limit + 1);

                $.each(categories, function(index, category) {
                    let description = category.description 
                        ? category.description.trim() 
                        : '<span class="text-muted fs-7">N/A</span>';
                    
                    let categoryName = category.name ? category.name.trim() : '';

                    let statusBadge = category.is_active == 1 
                        ? '<span class="badge bg-success">Active</span>' 
                        : '<span class="badge bg-secondary">Inactive</span>';

                    let row = `
                        <tr>
                            <td>${startNumber + index}</td>
                            <td class="fw-semibold">${categoryName}</td>
                            <td>${description}</td>
                            <td>${statusBadge}</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-view btn-outline-info view-category-btn me-1" data-id="${category.id}" title="View Category">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-primary edit-category-btn me-1" data-id="${category.id}" title="Edit Category">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger delete-category-btn" data-id="${category.id}" title="Delete Category">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;

                    tableBody.append(row);
                });

                // Render dynamic pagination controls
                renderPaginationControls(pagination);
            },
            error: function(xhr) {
                console.error('Failed to load categories:', xhr);
                $('#categoriesTableBody').html(`
                    <tr>
                        <td colspan="5" class="text-center text-danger py-3">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Failed to load categories.
                        </td>
                    </tr>
                `);
                renderPaginationControls(null);
            }
        });
    }

    // Dynamic Pagination UI Generator
    function renderPaginationControls(pagination) {
        if (!pagination) {
            $('#categoriesPagination').html('');
            return;
        }

        let paginationHtml = '<ul class="pagination pagination-sm mb-0">';

        // Previous Button
        let prevDisabled = pagination.current_page === 1 ? 'disabled' : '';
        paginationHtml += `
            <li class="page-item ${prevDisabled}">
                <a class="page-link pagination-link" href="#" data-page="${pagination.current_page - 1}">Previous</a>
            </li>
        `;

        // Numeric Page Buttons
        for (let i = 1; i <= pagination.last_page; i++) {
            let activeClass = i === pagination.current_page ? 'active' : '';
            paginationHtml += `
                <li class="page-item ${activeClass}">
                    <a class="page-link pagination-link" href="#" data-page="${i}">${i}</a>
                </li>
            `;
        }

        // Next Button
        let nextDisabled = pagination.current_page === pagination.last_page ? 'disabled' : '';
        paginationHtml += `
            <li class="page-item ${nextDisabled}">
                <a class="page-link pagination-link" href="#" data-page="${pagination.current_page + 1}">Next</a>
            </li>
        `;

        paginationHtml += '</ul>';
        $('#categoriesPagination').html(paginationHtml);
    }

    // Initial Load Execution
    loadEventCategory(currentPage, currentLimit);

    // Event 1: Change Entries Per Page Select Dropdown
    $(document).on('change', '#perPageSelect', function() {
        let limit = $(this).val();
        loadEventCategory(1, limit);
    });

    // Event 2: Click Pagination Links
    $(document).on('click', '.pagination-link', function(e) {
        e.preventDefault();
        let targetPage = $(this).data('page');
        if (targetPage && targetPage > 0) {
            loadEventCategory(targetPage, currentLimit);
        }
    });

    // 2. Trigger Modal Popup on 'add-category-btn' Click
    $('#add-category-btn').on('click', function() {
        $('#createCategoryForm')[0].reset();
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        $('#create_is_active').prop('checked', true);
        $('#createStatusLabel').text('Active');

        let addModal = new bootstrap.Modal(document.getElementById('addCategoryModal'));
        addModal.show();
    });

    // 3. Dynamic Switch Label Text Toggle
    $('#create_is_active').on('change', function() {
        $('#createStatusLabel').text($(this).is(':checked') ? 'Active' : 'Inactive');
    });

    // 4. Handle Category Creation via AJAX
    $('#createCategoryForm').on('submit', function(e) {
        e.preventDefault();

        let $btn = $('#btnSaveCategory');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        let formData = {
            name: $('#create_name').val(),
            description: $('#create_description').val(),
            is_active: $('#create_is_active').is(':checked') ? 1 : 0
        };

        $.ajax({
            url: '/api/admin/category/create',
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            success: function(response) {
                $btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Save Category');

                let modalElement = document.getElementById('addCategoryModal');
                let modalInstance = bootstrap.Modal.getInstance(modalElement);
                if (modalInstance) {
                    modalInstance.hide();
                }

                // Refresh Table Records to First Page after creating new entry
                loadEventCategory(1, currentLimit);
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Save Category');

                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    $.each(errors, function(fieldName, errorMessages) {
                        $('#create_' + fieldName).addClass('is-invalid');
                        $('.error-' + fieldName).text(errorMessages[0]);
                    });
                } else {
                    alert('An error occurred while creating the category. Please try again.');
                }
            }
        });
    });

    // 5. View Category Modal Handler
    const viewCategoryModal = new bootstrap.Modal(document.getElementById('viewCategoryModal'));

    $(document).on('click', '.view-category-btn', function () {
        const categoryId = $(this).data('id');

        $.ajax({
            url: `/api/admin/category/${categoryId}`,
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                const categoryData = response.category ? (Array.isArray(response.category) ? response.category[0] : response.category) : response;
                
                if (categoryData) {
                    $('#view_category_id').text(categoryData.id || 'N/A');
                    $('#view_category_name').text(categoryData.name ? categoryData.name.trim() : 'N/A');
                    $('#view_category_description').text(categoryData.description ? categoryData.description.trim() : 'No description provided.');

                    if (categoryData.is_active == 1) {
                        $('#view_category_status').html('<span class="badge bg-success fs-6"> Active</span>');
                    } else {
                        $('#view_category_status').html('<span class="badge bg-secondary fs-6"> Inactive</span>');
                    }

                    viewCategoryModal.show();
                }
            },
            error: function (xhr) {
                alert('Could not fetch category details. Please try again.');
            }
        });
    });

    // 6. Fetch Specific Category Data & Show Pop-up Modal for Edit
    $(document).on('click', '.edit-category-btn', function () {
        const categoryId = $(this).data('id');
        
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        $.ajax({
            url: "/api/admin/category/" + categoryId,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                let category = response.category ? (Array.isArray(response.category) ? response.category[0] : response.category) : response;

                if (category) {
                    $('#edit_category_id').val(category.id);
                    $('#edit_name').val(category.name ? category.name.trim() : '');
                    $('#edit_description').val(category.description ? category.description.trim() : '');

                    if (category.is_active == 1) {
                        $('#edit_is_active').prop('checked', true);
                        $('#statusLabel').text('Active');
                    } else {
                        $('#edit_is_active').prop('checked', false);
                        $('#statusLabel').text('Inactive');
                    }

                    let editModal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
                    editModal.show();
                }
            },
            error: function(xhr) {
                alert('Could not fetch category details. Please try again.');
            }
        });
    });

    // 7. Dynamic Switch Label Text Change (Edit Modal)
    $('#edit_is_active').on('change', function() {
        $('#statusLabel').text($(this).is(':checked') ? 'Active' : 'Inactive');
    });

    // 8. Update Category AJAX Form Submit
    $('#updateCategoryForm').on('submit', function(e) {
        e.preventDefault();

        const categoryId = $('#edit_category_id').val();
        let $btn = $('#btnUpdateCategory');
        $btn.prop('disabled', true).text('Saving Changes...');

        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        let formData = {
            name: $('#edit_name').val(),
            description: $('#edit_description').val(),
            is_active: $('#edit_is_active').is(':checked') ? 1 : 0,
        };

        $.ajax({
            url: "/api/admin/category/update/" + categoryId,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            data: formData,
            success: function(response) {
                $btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Save Changes');
                
                let modalElement = document.getElementById('editCategoryModal');
                let modalInstance = bootstrap.Modal.getInstance(modalElement);
                if (modalInstance) {
                    modalInstance.hide();
                }

                // Refresh Current Page
                loadEventCategory(currentPage, currentLimit);
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Save Changes');

                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    $.each(errors, function(fieldName, errorMessages) {
                        $('#edit_' + fieldName).addClass('is-invalid');
                        $('.error-' + fieldName).text(errorMessages[0]);
                    });
                } else {
                    alert('Failed to update category.');
                }
            }
        });
    });

    // 9. Delete Category Click Event
    $(document).on('click', '.delete-category-btn', function () {
        const categoryId = $(this).data('id');

        if (confirm('Are you sure you want to delete this category?')) {
            $.ajax({
                url: "/api/admin/category/delete/" + categoryId,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                success: function(response) {
                    // Reload current page after deletion
                    loadEventCategory(currentPage, currentLimit);
                },
                error: function(xhr){
                    let errors = xhr.responseJSON ? xhr.responseJSON.message : 'Deletion failed.';
                    alert(errors);
                }
            });
        }
    });

    // 10. Logout Form Handler
    $('#logoutForm').on('submit', function(event) {
        event.preventDefault();
        $.ajax({
            url: '/api/logout',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                window.location.href = '/';
            },
            error: function(xhr) {
                console.log(xhr.status);
            }
        });
    });

});
</script>
@endsection