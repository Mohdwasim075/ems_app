@extends('layouts.admin')

@section('content')
<div class="container-fluid pt-4 px-4">
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <!-- Header & Back Button -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="fw-bold mb-0">Events Management</h4>
                    <small class="text-muted">View, update, and manage all scheduled events</small>
                </div>
                <div>
                    <a href="{{ url('/admin/dashboard') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                    </a>
                </div>
            </div>

            <!-- Top Toolbar: Page Selector on Left, Add Event Button on Right -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
                    <label for="perPageSelect" class="form-label mb-0 text-muted small fw-bold">Show</label>
                    <select id="perPageSelect" class="form-select form-select-sm w-auto">
                        <option value="5" selected>5</option>
                        <option value="10">10</option>
                        <option value="15">15</option>
                    </select>
                    <span class="text-muted small">entries</span>
                </div>
                <div>
                    <button class="btn btn-primary btn-sm" id="add-event-btn">Add Event</button>
                </div>
            </div>

            <!-- EVENTS TABLE -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="eventsTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">S.No</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Location</th>
                            <th>Dates</th>
                            <th>Capacity</th>
                            <th>Available seats</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th width="15%" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="eventsTableBody">
                        <!-- AJAX content rendered here -->
                    </tbody>
                </table>
            </div>
             <div class="d-flex  justify-content-end align-items-center mt-3">
     
            <div id="eventsPagination"></div>
    </div>
        </div>
    </div>
</div>
<!-- CREATE EVENT MODAL -->
<div class="modal fade" id="addEventModal" tabindex="-1" aria-labelledby="addEventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="addEventModalLabel">
                    <i class="bi bi-calendar-plus me-2"></i>Create New Event
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="createEventForm">
                <div class="modal-body p-4">
                    <!-- Title -->
                    <div class="mb-3">
                        <label for="create_title" class="form-label fw-semibold">Event Title </label>
                        <input type="text" class="form-control" id="create_title" name="title" required placeholder="e.g., Annual Tech Conference 2026">
                        <div class="invalid-feedback error-title"></div>
                    </div>

                    <!-- Category & Price -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="create_category_id" class="form-label fw-semibold">Category</label>
                            <select class="form-select" id="create_category_id" name="category_id" required>
                                <option value="" selected disabled>Select Category</option>
                                <!-- Populate dynamically via API or Blade loop -->
                            </select>
                            <div class="invalid-feedback error-category_id"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="create_price" class="form-label fw-semibold">Ticket Price (₹)</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="create_price" name="price" required placeholder="0.00">
                            <div class="invalid-feedback error-price"></div>
                        </div>
                    </div>
                    
                    <!-- status-->
                    <div class="col-md-6">
                        <label for="create_status" class="form-label fw-semibold">Event Status</label>
                        <select class="form-select" id="create_status" name="status" required>
                            <option value="draft" selected>Draft</option>
                            <option value="published">Published</option>
                        </select>
                        <div class="invalid-feedback error-status"></div>
                    </div>

                    <!-- Location -->
                    <div class="mb-3">
                        <label for="create_location" class="form-label fw-semibold">Location</label>
                        <input type="text" class="form-control" id="create_location" name="location" placeholder="e.g. Bengaluru, Karnataka" required>
                        <div class="invalid-feedback error-location"></div>
                    </div>

                    <!-- Start & End Date -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="create_start_at" class="form-label fw-semibold">Start Date & Time</label>
                            <input type="datetime-local" class="form-control" id="create_start_at" name="start_at" required>
                            <div class="invalid-feedback error-start_at"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="create_end_at" class="form-label fw-semibold">End Date & Time</label>
                            <input type="datetime-local" class="form-control" id="create_end_at" name="end_at" required>
                            <div class="invalid-feedback error-end_at"></div>
                        </div>
                    </div>

                    <!-- Capacity -->
                    <div class="mb-3">
                        <label for="create_capacity" class="form-label fw-semibold">Capacity (Total Seats)</label>
                        <input type="number" min="1" class="form-control" id="create_capacity" name="capacity" required placeholder="e.g., 250">
                        <div class="invalid-feedback error-capacity"></div>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="create_description" class="form-label fw-semibold">Description</label>
                        <textarea class="form-control" id="create_description" name="description" rows="3" placeholder="Provide event agenda and highlights..."></textarea>
                        <div class="invalid-feedback error-description"></div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveEvent">
                        <i class="bi bi-check-circle me-1"></i> Save Event
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- ==================== 1. VIEW EVENT MODAL ==================== -->
<div class="modal fade" id="viewEventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg ">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title fw-bold">Event Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <h4 id="view_title" class="fw-bold text-primary mb-1">---</h4>
                        <p id="view_description" class="text-muted">---</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <span id="view_featured_badge"></span>
                    </div>
                    <hr class="my-2">
                    <div class="col-md-6">
                        <strong>Category:</strong> <span id="view_category">---</span>
                    </div>
                    <div class="col-md-6">
                        <strong>Location:</strong> <span id="view_location">---</span>
                    </div>
                    <div class="col-md-6">
                        <strong>Start Date:</strong> <span id="view_start_date">---</span>
                    </div>
                    <div class="col-md-6">
                        <strong>End Date:</strong> <span id="view_end_date">---</span>
                   
                    <div class="col-md-6">
                        <strong>Capacity:</strong> <span id="view_capacity">---</span> Seats
                    </div>
                     </div>
                      <div class="col-md-6">
                        <strong>Available seats:</strong> <span id="view_available_seats">---</span> Seats
                    </div>
                    <div class="col-md-6">
                        <strong>Price:</strong> <span id="view_price" class="fw-bold text-success">---</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- ==================== 2. EDIT EVENT MODAL ==================== -->
<div class="modal fade" id="editEventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Event</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editEventForm" method="post">
                <div class="modal-body">
                    <input type="hidden" id="edit_event_id" name="id">

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="edit_title" class="form-label fw-semibold">Event Title</label>
                            <input type="text" class="form-control" id="edit_title" name="title" >
                            <div class="invalid-feedback error-title"></div>
                        </div>

                        <div class="col-md-12">
                            <label for="edit_description" class="form-label fw-semibold">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                            <div class="invalid-feedback error-description"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="edit_start_date" class="form-label fw-semibold">Start Date</label>
                            <input type="datetime-local" class="form-control" id="edit_start_date" name="start_at" required>
                            <div class="invalid-feedback error-start_date"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="edit_end_date" class="form-label fw-semibold">End Date</label>
                            <input type="datetime-local" class="form-control" id="edit_end_date" name="end_at" required>
                            <div class="invalid-feedback error-end_date"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="edit_category_id" class="form-label fw-semibold">Category</label>
                            <select class="form-select" id="edit_category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <!-- Populate dynamically via API or Blade loop -->
                            </select>
                            <div class="invalid-feedback error-category_id"></div>
                        </div>

                         <div class="col-md-6">
                            <label for="edit_capacity" class="form-label fw-semibold"> capacity</label>
                            <input type="number" class="form-control" id="edit_capacity" name="capacity" required>
                            <div class="invalid-feedback error-edit_capacity"></div>
                        </div>

                      

                        <div class="col-md-6">
                            <label for="edit_location" class="form-label fw-semibold">Location</label>
                            <input type="text" class="form-control" id="edit_location" name="location" required>
                            <div class="invalid-feedback error-location"></div>
                        </div>

                        {{-- <div class="col-md-6">
                            <label for="edit_state" class="form-label fw-semibold">State</label>
                            <input type="text" class="form-control" id="edit_state" name="state" required>
                            <div class="invalid-feedback error-state"></div>
                        </div> --}}

                        {{-- <div class="col-md-6">
                            <label for="edit_capacity" class="form-label fw-semibold">Available seats <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="edit_capacity" name="available_seats" min="1" required>
                            <div class="invalid-feedback error-capacity"></div>
                        </div> --}}

                        <div class="col-md-6">
                            <label for="edit_price" class="form-label fw-semibold">Price (₹) </label>
                            <input type="number" step="0.01" class="form-control" id="edit_price" name="price" >
                            <div class="invalid-feedback error-price"></div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="edit_is_published" name="status" value="published">
                                <label class="form-check-label fw-semibold" for="edit_is_published">Mark as Published Event</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btnUpdateEvent">
                        <i class="bi bi-check-circle me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JQUERY SCRIPT -->
<script src="https://code.jquery.com/jquery-4.0.0.min.js" crossorigin="anonymous"></script>

<script>
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        'Accept': 'application/json'
    }
});
// Helper function to format ISO date string for datetime-local input
function formatForDateTimeLocal(isoString) {
    if (!isoString) return '';
    
    // Creates a Date object to handle timezone offset properly
    let date = new Date(isoString);
    
    // Format to YYYY-MM-DDTHH:mm
    let year = date.getFullYear();
    let month = String(date.getMonth() + 1).padStart(2, '0');
    let day = String(date.getDate()).padStart(2, '0');
    let hours = String(date.getHours()).padStart(2, '0');
    let minutes = String(date.getMinutes()).padStart(2, '0');

    return `${year}-${month}-${day}T${hours}:${minutes}`;
}

$(document).ready(function() {
    // Global cache for fetched events array
let cachedEvents = [];



    // Global Pagination State Variables
    let currentPage = 1;
    let currentLimit = $('#perPageSelect').val() || 5;

    // 1. Load Categories with Pagination
    function loadEvents(page = 1, limit = 5) {
        currentPage = page;
        currentLimit = limit;

        $.ajax({
            url: `/api/admin/events?page=${page}&limit=${limit}`,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log(response);

                let events = response.data;
                cachedEvents = response.data;
                let tableBody = $('#eventsTableBody');

                tableBody.empty();

                if (!events || events.length === 0) {
                    tableBody.append(`
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                No events found.
                            </td>
                        </tr>
                    `);
                    renderPaginationControls(null);
                    return;
                }

                // Calculate Starting Serial Number based on pagination page & limit
                let pagination = response.pagination || {};
                let startNumber = pagination.from || ((page - 1) * limit + 1);

                $.each(events, function(index, event) {
                     let location = event.location || 'N/A';

                    let publishedBadge = event.status === "published"
                        ? '<span class="badge bg-warning text-dark"><i class="bi bi-star-fill me-1"></i>Published</span>' 
                        : '<span class="badge bg-light text-secondary">Not published</span>';

                    let categoryName = event.category ? event.category.name : `Cat ID: ${event.category_id}`;
                    let priceFormatted = parseFloat(event.price || 0) === 0 ? 'Free' : '₹' + parseFloat(event.price).toLocaleString('en-IN');

                    let row = `
                        <tr>
                            <td>${startNumber + index}</td>
                            <td class="fw-semibold">${event.title}</td>
                            <td><span class="badge bg-info text-dark">${categoryName}</span></td>
                            <td>${location}</td>
                            <td class="small">${event.start_at ? event.start_at.split('T')[0] : 'N/A'}</td>
                            <td>${event.capacity|| 0}</td>
                            <td>${event.available_seats|| 0}</td>
                            <td class="fw-semibold text-success">${priceFormatted}</td>
                            <td>${publishedBadge}</td>
                            <td class = "text-center">
                                <button class="btn btn-sm btn-outline-info view-event-btn me-1" data-id="${event.id}" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-primary edit-event-btn me-1" data-id="${event.id}" title="Edit Event">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger delete-event-btn" data-id="${event.id}" title="Delete Event">
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

    // 1. Show Modal on Button Click & Reset Form State
    $('#add-event-btn').on('click', function() {
        // Reset form inputs
        $('#createEventForm')[0].reset();
        
        // Clear all validation styles and text
        $('#createEventForm .form-control, #createEventForm .form-select').removeClass('is-invalid');
        $('#createEventForm .invalid-feedback').text('');

        // Fetch categories dynamically if select dropdown is empty
        loadCategoriesDropdown();

        // Show Bootstrap Modal
        let eventModal = new bootstrap.Modal(document.getElementById('addEventModal'));
        eventModal.show();
    });

    // 2. Form Submission Handler via AJAX
    $('#createEventForm').on('submit', function(e) {
        e.preventDefault();

        let $btn = $('#btnSaveEvent');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

        // Clear previous error messages
        $('#createEventForm .form-control, #createEventForm .form-select').removeClass('is-invalid');
        $('#createEventForm .invalid-feedback').text('');

        // Convert datetime-local values (YYYY-MM-DDTHH:mm) to MySQL DATETIME (YYYY-MM-DD HH:mm:ss)
        let rawStartAt = $('#create_start_at').val();
        let rawEndAt = $('#create_end_at').val();

        let formattedStartAt = rawStartAt ? rawStartAt.replace('T', ' ') + ':00' : null;
        let formattedEndAt = rawEndAt ? rawEndAt.replace('T', ' ') + ':00' : null;

        // Prepare JSON payload
        let formData = {
            title: $('#create_title').val(),
            category_id: $('#create_category_id').val(),
            price: $('#create_price').val(),
            location: $('#create_location').val(),
            status: $('#create_status').val(),
            start_at: formattedStartAt,
            end_at: formattedEndAt,
            capacity: $('#create_capacity').val(),
            description: $('#create_description').val()
        };

        $.ajax({
            url: '/api/admin/events/create',
            type: 'POST',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            success: function(response) {
                $btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Save Event');

                // Hide Modal
                let modalElement = document.getElementById('addEventModal');
                let modalInstance = bootstrap.Modal.getInstance(modalElement);
                if (modalInstance) {
                    modalInstance.hide();
                }

                alert(response.message || 'Event created successfully!');

                // Refresh table if loadEvents function exists
                if (typeof loadEvents === 'function') {
                    loadEvents();
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Save Event');

                if (xhr.status === 422) {
                    // Map Laravel backend validation errors to inputs
                    let errors = xhr.responseJSON.errors;

                    $.each(errors, function(fieldName, errorMessages) {
                        let $input = $('#create_' + fieldName);
                        let $errorContainer = $('.error-' + fieldName);

                        if ($input.length) {
                            $input.addClass('is-invalid');
                        }
                        if ($errorContainer.length) {
                            $errorContainer.text(errorMessages[0]); // First validation message
                        }
                    });
                } else {
                    alert('An unexpected server error occurred (Status: ' + xhr.status + '). Please try again.');
                }
            }
        });
    });

    // Helper to populate category options in create and edit dropdowns
    function loadCategoriesDropdown(callback) {
        let createNeedsLoading = $('#create_category_id').children('option').length <= 1;
        let editNeedsLoading = $('#edit_category_id').children('option').length <= 1;

        if (!createNeedsLoading && !editNeedsLoading) {
            if (typeof callback === 'function') {
                callback();
            }
            return;
        }

        $.ajax({
            url: '/api/admin/categories/list',
            type: 'GET',
            headers: { 'Accept': 'application/json' },
            success: function(response) {
                let categories = response.data || response;

                if (createNeedsLoading) {
                    let $createSelect = $('#create_category_id');
                    $createSelect.find('option:not(:first)').remove();
                    $.each(categories, function(index, cat) {
                        $createSelect.append(`<option value="${cat.id}">${cat.name}</option>`);
                    });
                }

                if (editNeedsLoading) {
                    let $editSelect = $('#edit_category_id');
                    $editSelect.find('option:not(:first)').remove();
                    $.each(categories, function(index, cat) {
                        $editSelect.append(`<option value="${cat.id}">${cat.name}</option>`);
                    });
                }

                if (typeof callback === 'function') {
                    callback();
                }
            },
            error: function(xhr) {
                console.error('Failed to load categories dropdown:', xhr);
            }
        });
    }




    // Dynamic Pagination UI Generator
    function renderPaginationControls(pagination) {
        if (!pagination) {
            $('#eventsPagination').html('');
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
        $('#eventsPagination').html(paginationHtml);
    }

    // Initial Load Execution
    loadEvents(currentPage, currentLimit);
    loadCategoriesDropdown();

    // Event 1: Change Entries Per Page Select Dropdown
    $(document).on('change', '#perPageSelect', function() {
        let limit = $(this).val();
        loadEvents(1, limit);
    });

    // Event 2: Click Pagination Links
    $(document).on('click', '.pagination-link', function(e) {
        e.preventDefault();
        let targetPage = $(this).data('page');
        if (targetPage && targetPage > 0) {
            loadEvents(targetPage, currentLimit);
        }
    });
    // // 1. Fetch & Populate Events Table
    // function loadEvents() {
    //     $.ajax({
    //         url: '/api/admin/events',
    //         type: 'GET',

    //         dataType: 'json',
    //         success: function(response) {
    //             console.log(response.data);
    //             cachedEvents = response.data || [];
    //             let tableBody = $('#eventsTableBody');
    //             tableBody.empty();

    //             if (!cachedEvents || cachedEvents.length === 0) {
    //                 tableBody.append(`
    //                     <tr>
    //                         <td colspan="9" class="text-center text-muted py-4">No events found.</td>
    //                     </tr>
    //                 `);
    //                 return;
    //             }

    //             $.each(cachedEvents, function(index, event) {
                    // let city = event.city ? event.city.trim() : '';
                    // let state = event.state ? event.state.trim() : '';
                    // let location = event.location || 'N/A';

                    // let publishedBadge = event.status === "published"
                    //     ? '<span class="badge bg-warning text-dark"><i class="bi bi-star-fill me-1"></i>Published</span>' 
                    //     : '<span class="badge bg-light text-secondary">Not published</span>';

                    // let categoryName = event.category ? event.category.name : `Cat ID: ${event.category_id}`;
                    // let priceFormatted = parseFloat(event.price || 0) === 0 ? 'Free' : '₹' + parseFloat(event.price).toLocaleString('en-IN');

    //                 let row = `
    //                     <tr>
    //                         <td>${index + 1}</td>
                            // <td class="fw-semibold">${event.title}</td>
                            // <td><span class="badge bg-info text-dark">${categoryName}</span></td>
                            // <td>${location}</td>
                            // <td class="small">${event.start_at ? event.start_at.split('T')[0] : 'N/A'}</td>
                            // <td>${event.capacity|| 0}</td>
                            // <td>${event.available_seats|| 0}</td>
                            // <td class="fw-semibold text-success">${priceFormatted}</td>
                            // <td>${publishedBadge}</td>
                            // <td class = "text-center">
                            //     <button class="btn btn-sm btn-outline-info view-event-btn me-1" data-id="${event.id}" title="View Details">
                            //         <i class="bi bi-eye"></i>
                            //     </button>
                            //     <button class="btn btn-sm btn-outline-primary edit-event-btn me-1" data-id="${event.id}" title="Edit Event">
                            //         <i class="bi bi-pencil"></i>
                            //     </button>
                            //     <button class="btn btn-sm btn-outline-danger delete-event-btn" data-id="${event.id}" title="Delete Event">
                            //         <i class="bi bi-trash"></i>
                            //     </button>
                            // </td>
    //                     </tr>
    //                 `;
    //                 tableBody.append(row);
    //             });
    //         },
    //         error: function(xhr) {
    //             console.error('Failed to load events:', xhr);
    //             $('#eventsTableBody').html(`
    //                 <tr>
    //                     <td colspan="9" class="text-center text-danger py-3">
    //                         <i class="bi bi-exclamation-triangle-fill me-1"></i> Failed to load events list.
    //                     </td>
    //                 </tr>
    //             `);
    //         }
    //     });
    // }

    // loadEvents();

    // 2. View Event Modal Handler
    $(document).on('click', '.view-event-btn', function() {
        const eventId = $(this).data('id');
        const event = cachedEvents.find(item => item.id == eventId);

        if (event) {
            $('#view_title').text(event.title);
            $('#view_description').text(event.description || 'No description provided.');
            $('#view_category').text(event.category ? event.category.name : `ID: ${event.category_id}`);
            $('#view_location').text(`${event.location || 'N/A'}`);
            $('#view_start_date').text(event.start_at ? event.start_at.split('T')[0] : 'N/A');
            $('#view_end_date').text(event.end_at ? event.end_at.split('T')[0] : 'N/A');
            $('#view_capacity').text(event.capacity || 0);
             $('#view_available_seats').text(event.available_seats || 0);
            $('#view_price').text(parseFloat(event.price || 0) === 0 ? 'Free' : '₹' + parseFloat(event.price).toLocaleString('en-IN'));
            
            $('#view_published_badge').html(event.status == 1 
                ? '<span class="badge bg-warning text-dark fs-6"><i class="bi bi-star-fill me-1"></i> Published</span>' 
                : '<span class="badge bg-secondary fs-6">Not Published</span>'
            );

            let viewModal = new bootstrap.Modal(document.getElementById('viewEventModal'));
            viewModal.show();
        }
    });

    // 3. Edit Event Modal Trigger
    $(document).on('click', '.edit-event-btn', function() {
        const eventId = $(this).data('id');
        
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        $.ajax({
            url: `/api/admin/events/${eventId}`,
            type: 'get',
            success: function(response) {
                let event = response.data;
                console.log(event);
                console.log('location : ' + event.location);
                console.log('category : ' + (event.category ? event.category.name : event.category_id));

                $('#edit_event_id').val(event.id);
                $('#edit_title').val(event.title);
                $('#edit_description').val(event.description);
                $('#edit_start_date').val(formatForDateTimeLocal(event.start_at));
                $('#edit_end_date').val(formatForDateTimeLocal(event.end_at));
                $('#edit_capacity').val(event.capacity);
                $('#edit_location').val(event.location);
                $('#edit_price').val(event.price);
                $('#edit_is_published').prop('checked', event.status === 'published');

                let selectedCategoryId = event.category_id || (event.category ? event.category.id : '');

                // Ensure categories are loaded before selecting category value
                loadCategoriesDropdown(function() {
                    $('#edit_category_id').val(selectedCategoryId);
                });

                let editModal = new bootstrap.Modal(document.getElementById('editEventModal'));
                editModal.show();
            },
            error: function() {
                showAlert('danger','Could not fetch event details.');
            }
        });
    });

    // 4. Update Event AJAX Submission
    $('#editEventForm').on('submit', function(e) {
        e.preventDefault();

        const eventId = $('#edit_event_id').val();
        let $btn = $('#btnUpdateEvent');
        $btn.prop('disabled', true).text('Updating...');

        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        let formData = {
            title: $('#edit_title').val(),
            description: $('#edit_description').val(),
            start_at: $('#edit_start_date').val(),
            end_at: $('#edit_end_date').val(),
            location: $('#edit_location').val(),
             category_id: $('#edit_category_id').val(),
            capacity: $('#edit_capacity').val(),
            price: $('#edit_price').val(),
            status: $('#edit_is_published').is(':checked') ? 'published' : 'draft',
            _method: 'PATCH'
        };

        $.ajax({
            url: `/api/admin/events/update/${eventId}`,
            type: 'patch',
            data: formData,
            success: function(response) {
                $btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Save Changes');
                showAlert('success','Event updated successfully!.')
                
                let modalInstance = bootstrap.Modal.getInstance(document.getElementById('editEventModal'));
                if (modalInstance) modalInstance.hide();

                loadEvents();
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
                    showAlert('danger','Failed to update event.');
                }
            }
        });
    });

    // 5. Delete Event Action
    $(document).on('click', '.delete-event-btn', function() {
        const eventId = $(this).data('id');

        if (confirm('Are you sure you want to delete this event?')) {
            $.ajax({
                url: `/api/admin/events/delete/${eventId}`,
                type: 'post',
                success: function(response) {
                    showAlert('success',response.message);
                    loadEvents();
                },
                error: function(xhr) {
                   
                    let errors = xhr.responseJSON.message;
                    alert(errors);
                   
                }
            });
        }
    });

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