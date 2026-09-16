@extends('layouts.admin')

@section('content')

<div class="container-fluid pt-4 px-4">
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <!-- Header & Back Button -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="fw-bold mb-0">Users Management</h4>
                    <small class="text-muted">View, update, and manage all users</small>
                </div>
                <div>
                    <a href="{{ url('/admin/dashboard') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                    </a>
                </div>
            </div>

            <!-- Top Toolbar: Page Selector on Left -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
                    <label for="perPageSelect" class="form-label mb-0 text-muted small fw-bold">Show</label>
                    <select id="perPageSelect" class="form-select form-select-sm w-auto">
                        <option value="5" selected>5</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                    <span class="text-muted small">entries</span>
                </div>
            </div>

            <!-- Global Alert Container -->
            <div id="alert-container"></div>

            <!-- USERS TABLE -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="users-table">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">S.No</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone No</th>
                            <th>Role</th>
                            <th width="15%" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="users-tbody">
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                <span class="ms-2 text-muted">Loading users...</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
              <!-- Pagination Container -->
            <div class="d-flex justify-content-end align-items-center mt-4">
                <div id="usersPagination"></div>
            </div>
            
        </div>
       
    </div>
</div>
<!-- Read-Only View User Modal -->
<!-- ==================== VIEW USER MODAL ==================== -->
<div class="modal fade" id="viewUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg ">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title fw-bold">User Profile Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <h4 id="view_name" class="fw-bold text-primary mb-1">---</h4>
                        {{-- <p id="view_email" class="text-muted mb-0">---</p> --}}
                        <strong>Email:</strong> <span id="view_email" class="text-muted mb-0"> </span>
                    </div>
                    <div class="col-md-4 text-end">
                        <span id="view_status_badge"></span>
                    </div>

                    <hr class="my-2">

                    <div class="col-md-6">
                        <strong>Role:</strong> <span id="view_role" class="fw-semibold text-primary">---</span>
                    </div>
                    <div class="col-md-6">
                        <strong>Phone Number:</strong> <span id="view_phone">---</span>
                    </div>
                    <div class="col-md-6">
                        <strong>Joined On:</strong> <span id="view_created_at">---</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add / Edit User -->
<div class="modal fade" id="userFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form id="userForm">
                <div class="modal-header  bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="formModalTitle">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="user_id" name="id">

                    <!-- Modal Validation Alert Box -->
                    <div id="modal-alert" class="alert alert-danger d-none p-2 mb-3 small"></div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                        <div class="invalid-feedback" id="err-name"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                        <div class="invalid-feedback" id="err-email"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Phone Number</label>
                        <input type="text" class="form-control" id="phone_number" name="phone_number">
                        <div class="invalid-feedback" id="err-phone"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Role</label>
                        <select class="form-select" id="role_id" name="role_id" required>
                            <option value="">Select Role</option>
                            <!-- Dynamic options populated via AJAX -->
                        </select>
                        <div class="invalid-feedback" id="err-role_id"></div>
                    </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary px-3" id="btn-save">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- JQUERY SCRIPT -->
<script src="https://code.jquery.com/jquery-4.0.0.min.js" crossorigin="anonymous"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>



$(document).ready(function() {  
        // Function to populate the <select> dropdown with roles
function loadRolesDropdown() {
    $.ajax({
        url: '/api/admin/user/roles',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            const roles = response.data || response;
            let options = '<option value="">Select Role</option>';

            $.each(roles, function(index, role) {
                // Uses role.id as value and formats role.name for display
                options += `<option value="${role.id}">${role.name.toUpperCase()}</option>`;
            });

            $('#role_id').html(options);
        },
        error: function() {
            alert( 'Failed to load roles list.');
        }
    });
}

// Call once when page loads
loadRolesDropdown();
    

    // Global Setup for CSRF Token
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });


    const formModal = new bootstrap.Modal('#userFormModal');
    const editModal = new bootstrap.Modal('#userFormModal');
    
    // Global Pagination State Variables
    let currentPage = 1;
    let currentLimit = $('#perPageSelect').val() || 5;

    // 1. Load Categories with Pagination
    function loadUsers(page = 1, limit = 5) {
        currentPage = page;
        currentLimit = limit;

        $.ajax({
            url: `/api/admin/users?page=${page}&limit=${limit}`,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log(response);
                 let rows = '';

                let users = response.data;
                 console.log(users);
                  let pagination = response.pagination || {};
                     let startNumber = pagination.from || ((page - 1) * limit + 1);
                

                if (!users || users.length === 0) {
                    rows = `<tr><td colspan="6" class="text-center py-3 text-muted">No users found.</td></tr>`;
                } else {
                    
                    $.each(users, function(index, user) {
                       // Extract nested role name safely
                    const roleName = (user.role && user.role.name) ? user.role.name.toUpperCase() : 'N/A';
                    
                    // Choose badge color based on role
                    const badgeClass = roleName === 'ADMIN' ? 'bg-danger' 
                                    : roleName === 'ORGANIZER' ? 'bg-primary' 
                                    : 'bg-info text-dark';

                rows += `
                    <tr>
                        <td>${startNumber + index}</td>
                        <td class="fw-semibold">${user.name}</td>
                        <td>${user.email}</td>
                        <td>${user.phone_number || 'N/A'}</td>
                        <td><span class="badge ${badgeClass}">${roleName}</span></td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-info btn-view me-1" data-id="${user.id}" title="View">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-warning btn-edit me-1" data-id="${user.id}" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-delete" data-id="${user.id}" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>`;
                    });
                }
                $('#users-tbody').html(rows);

                // Render dynamic pagination controls
                renderPaginationControls(pagination);
            },
            error: function(xhr) {
                console.error('Failed to load users:', xhr);
                $('#users-tbody').html(`
                    <tr>
                        <td colspan="5" class="text-center text-danger py-3">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Failed to load users.
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
            $('#usersPagination').html('');
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
        $('#usersPagination').html(paginationHtml);
    }

    // Initial Load Execution
    loadUsers(currentPage, currentLimit);

    // Event 1: Change Entries Per Page Select Dropdown
    $(document).on('change', '#perPageSelect', function() {
        let limit = $(this).val();
        loadUsers(1, limit);
    });

    // Event 2: Click Pagination Links
    $(document).on('click', '.pagination-link', function(e) {
        e.preventDefault();
        let targetPage = $(this).data('page');
        if (targetPage && targetPage > 0) {
            loadUsers(targetPage, currentLimit);
        }
    });
    // // 1. Fetch Users
    // function fetchUsers() {
    //     $.ajax({
    //         url: '/api/admin/users/get',
    //         type: 'GET',
    //         dataType: 'json',
    //         success: function(response) {
    //             let rows = '';
    //             const users = response.data || response;
    //             console.log(users);
                

    //             if (!users || users.length === 0) {
    //                 rows = `<tr><td colspan="6" class="text-center py-3 text-muted">No users found.</td></tr>`;
    //             } else {
    //                 $.each(users, function(index, user) {
    //                    // Extract nested role name safely
    //                 const roleName = (user.role && user.role.name) ? user.role.name.toUpperCase() : 'N/A';
                    
    //                 // Choose badge color based on role
    //                 const badgeClass = roleName === 'ADMIN' ? 'bg-danger' 
    //                                 : roleName === 'ORGANIZER' ? 'bg-primary' 
    //                                 : 'bg-info text-dark';

    //             rows += `
    //                 <tr>
    //                     <td>${index + 1}</td>
    //                     <td class="fw-semibold">${user.name}</td>
    //                     <td>${user.email}</td>
    //                     <td>${user.phone_number || 'N/A'}</td>
    //                     <td><span class="badge ${badgeClass}">${roleName}</span></td>
    //                     <td class="text-center">
    //                         <button class="btn btn-sm btn-outline-info btn-view me-1" data-id="${user.id}" title="View">
    //                             <i class="bi bi-eye"></i>
    //                         </button>
    //                         <button class="btn btn-sm btn-outline-warning btn-edit me-1" data-id="${user.id}" title="Edit">
    //                             <i class="bi bi-pencil"></i>
    //                         </button>
    //                         <button class="btn btn-sm btn-outline-danger btn-delete" data-id="${user.id}" title="Delete">
    //                             <i class="bi bi-trash"></i>
    //                         </button>
    //                     </td>
    //                 </tr>`;
    //                 });
    //             }
    //             $('#users-tbody').html(rows);
    //         },
    //         error: function() {
    //            alert( 'Failed to fetch users list.');
    //         }
    //     });
    // }

    // fetchUsers();

    // 2. Clear Validation State
    function clearValidation() {
        $('#modal-alert').addClass('d-none').text('');
        $('.form-control, .form-select').removeClass('is-invalid');
        $('.invalid-feedback').text('');
    }

    // 3. Open Add User Modal
    $('#btn-add-user').on('click', function() {
        clearValidation();
        $('#userForm')[0].reset();
        $('#user_id').val('');
        $('#formModalTitle').text('Add New User');
        formModal.show();
    });

    // 4. View User Details
 const viewModal = new bootstrap.Modal(document.getElementById('viewUserModal'));

$(document).on('click', '.btn-view', function () {
    const id = $(this).data('id');

    $.ajax({
        url: `/api/admin/user/${id}`,
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            const user = response.data || response;

            // Populate Text Placeholders
            $('#view_name').text(user.name || 'N/A');
            $('#view_email').text(user.email || 'N/A');
            $('#view_role').text(user.role ? user.role.name.toUpperCase() : 'N/A');
            $('#view_phone').text(user.phone_number || 'N/A');

            // Format Joined Date
            const joinedDate = user.created_at ? new Date(user.created_at).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            }) : 'N/A';
            $('#view_created_at').text(joinedDate);

            // Format Status Badge Dynamically
            const statusMap = {
                'active': '<span class="badge bg-success fs-6"><i class="bi bi-check-circle-fill me-1"></i> Active</span>',
                'inactive': '<span class="badge bg-secondary fs-6"><i class="bi bi-dash-circle-fill me-1"></i> Inactive</span>',
                'suspended': '<span class="badge bg-danger fs-6"><i class="bi bi-x-circle-fill me-1"></i> Suspended</span>',
                'admin': '<span class="badge bg-primary fs-6"><i class="bi bi-shield-lock-fill me-1"></i> Admin</span>',
                'attendee': '<span class="badge bg-info text-white fs-6"><i class="bi bi-person-fill me-1"></i> Attendee</span>'
            };
            const currentStatus = (user.status || '').toLowerCase();
            $('#view_status_badge').html(statusMap[currentStatus] || `<span class="badge bg-secondary fs-6">${user.status || 'N/A'}</span>`);

            // Show Modal
            viewModal.show();
        },
        error: function () {
            showAlert('danger', 'Could not load user details.');
        }
    });
});

    // 5. Open Edit Modal & Populate Data
    $(document).on('click', '.btn-edit', function() {
       const id = $(this).data('id');
    clearValidation();

    $.ajax({
        url: `/api/admin/user/${id}`,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            const user = response.data || response;

            $('#user_id').val(user.id);
            $('#name').val(user.name);
            $('#email').val(user.email);
            $('#phone_number').val(user.phone_number || 'NA');
            
            // Automatically selects the corresponding role option by foreign key ID
            $('#role_id').val(user.role_id);
            editModal.show();
        }
    });
    });

    // 6. Submit Add / Edit Form (Handles Validation Errors)
    $('#userForm').on('submit', function(e) {
        e.preventDefault();
        clearValidation();

        const userId = $('#user_id').val();
    

        $.ajax({
            url:  `/api/admin/user/update/${userId}`,
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                console.log(response);
                
                formModal.hide();
                showAlert('success', response.message || 'User saved successfully.');
                fetchUsers();
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    
                    // Display field-specific validation errors below inputs
                    $.each(errors, function(field, messages) {
                        $(`#${field}`).addClass('is-invalid');
                        $(`#err-${field}`).text(messages[0]);
                    });

                    // Summary message in modal alert
                    $('#modal-alert').removeClass('d-none').text(xhr.responseJSON.message || 'Please fix the validation errors below.');
                } else {
                    showAlert('danger', 'An error occurred while saving user data.');
                }
            }
        });
    });

    // 7. Delete User (POST Method)
    $(document).on('click', '.btn-delete', function() {
        const id = $(this).data('id');

        if (confirm('Are you sure you want to delete this user?')) {
            $.ajax({
                url: `/api/admin/user/delete/${id}`,
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    // showAlert('success', response.message || 'User deleted successfully.');
                    alert(response.message);
                    fetchUsers();
                },
                error: function(xhr) {
                    const errorMsg = xhr.responseJSON ? xhr.responseJSON.message : 'Failed to delete user.';
                    showAlert('danger',errorMsg);
                }
            });
        }
    });

    // Alert helper function
    function showAlert(type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`;
        $('#alert-container').html(alertHtml);
        setTimeout(() => { $('.alert').alert('close'); }, 4000);
    }
});

</script>
@endsection