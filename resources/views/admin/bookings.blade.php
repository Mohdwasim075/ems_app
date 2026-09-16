@extends('layouts.admin')

@section('content')
<div class="container-fluid pt-4 px-4">
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <!-- Header & Back Button -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="fw-bold mb-0">Events Booking</h4>
                    <small class="text-muted">View, update, and manage all booked events</small>
                </div>
                <div>
                    <a href="{{ url('/admin/dashboard') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                    </a>
                </div>
            </div>

            <!-- Top Toolbar: Page Selector on Left (No Add button for Bookings) -->
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
                
            </div>

            <!-- Alert Box for AJAX Responses -->
            <div id="alert-container"></div>

            <!-- BOOKINGS TABLE -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="bookings-table">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">S.No</th>
                            <th>Registration No</th>
                            <th>Event Title</th>
                            <th>User Name</th>
                            <th>Quantity</th>
                            <th>Total Price</th>
                            <th>Status</th>
                            <th width="15%" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody id="bookings-tbody">
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="mb-0 mt-2 text-muted">Loading bookings...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
              <div class="d-flex  justify-content-end align-items-center mt-3">
     
            <div id="bookingsPagination"></div>
    </div>
        </div>
    </div>
</div>
<!-- ==================== VIEW BOOKING MODAL ==================== -->
<div class="modal fade" id="viewBookingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg ">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title fw-bold">Booking Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <h4 id="view_registration_number" class="fw-bold text-primary mb-1">---</h4>
                        <p class="text-muted mb-0">Event: <span id="view_event_title" class="fw-semibold text-dark">---</span></p>
                    </div>
                    <div class="col-md-4 text-end">
                        <span id="view_status_badge"></span>
                    </div>

                    <hr class="my-2">

                    <div class="col-md-6">
                        <strong>User Name:</strong> <span id="view_user_name">---</span>
                    </div>
                    <div class="col-md-6">
                        <strong>Registered At:</strong> <span id="view_registered_at">---</span>
                    </div>
                    <div class="col-md-6">
                        <strong>Ticket Quantity:</strong> <span id="view_quantity">---</span>
                    </div>
                    <div class="col-md-6">
                        <strong>Unit Price:</strong> <span id="view_unit_price">---</span>
                    </div>
                    <div class="col-md-6">
                        <strong>Total Price:</strong> <span id="view_total_price" class="fw-bold text-success">---</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal: Add / Edit Booking -->
<div class="modal fade" id="bookingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="bookingForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Create Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="booking_id" name="id">
                    
                    <div class="mb-3">
                        <label class="form-label">Event ID</label>
                        <input type="number" class="form-field form-control" id="event_id" name="event_id" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">User ID</label>
                        <input type="number" class="form-field form-control" id="user_id" name="user_id" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ticket Quantity</label>
                        <input type="number" class="form-field form-control" id="quantity" name="quantity" min="1" value="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="CONFIRMED">CONFIRMED</option>
                            <option value="PENDING">PENDING</option>
                            <option value="CANCELLED">CANCELLED</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btn-save">Save Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: View Booking Details -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Booking Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-sm table-borderless">
                    <tr><th>Registration No:</th><td id="view-reg-no"></td></tr>
                    <tr><th>Event:</th><td id="view-event"></td></tr>
                    <tr><th>User:</th><td id="view-user"></td></tr>
                    <tr><th>Quantity:</th><td id="view-quantity"></td></tr>
                    <tr><th>Unit Price:</th><td id="view-unit-price"></td></tr>
                    <tr><th>Total Price:</th><td id="view-total-price"></td></tr>
                    <tr><th>Status:</th><td id="view-status"></td></tr>
                    <tr><th>Registered At:</th><td id="view-registered-at"></td></tr>
                </table>
            </div>
        </div>
    </div>
</div>


<!-- JQUERY SCRIPT -->
<script src="https://code.jquery.com/jquery-4.0.0.min.js" crossorigin="anonymous"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>

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
$(document.body).ready(function() {

    // Setup CSRF token for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    const bookingModal = new bootstrap.Modal('#bookingModal');
    const viewModal = new bootstrap.Modal('#viewModal');

    // 1. READ: Fetch and populate bookings

    // Global Pagination State Variables
    let currentPage = 1;
    let currentLimit = $('#perPageSelect').val() || 10;

    // 1. Load Categories with Pagination
    function loadEventBookings(page = 1, limit = 10) {
        currentPage = page;
        currentLimit = limit;

        $.ajax({
            url: `/api/admin/bookings?page=${page}&limit=${limit}`,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log(response);

                let bookings = response.data;
                let tableBody = $('#bookings-tbody');

                tableBody.empty();

                if (!bookings || bookings.length === 0) {
                    tableBody.append(`
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                No bookings found.
                            </td>
                        </tr>
                    `);
                    renderPaginationControls(null);
                    return;
                }

                // Calculate Starting Serial Number based on pagination page & limit
                let pagination = response.pagination || {};
                let startNumber = pagination.from || ((page - 1) * limit + 1);

                $.each(bookings, function(index, item) {
                     const statusBadge = item.status === 'CONFIRMED' 
                            ? '<span class="badge bg-success">CONFIRMED</span>' 
                            : '<span class="badge bg-warning text-dark">' + item.status + '</span>';
                    let row = `
                        <tr>
                            <td>${startNumber + index}</td>
                             <td><strong>${item.registration_number || 'N/A'}</strong></td>
                                <td>${item.event ? item.event.title : '<span class="text-danger">N/A</span>'}</td>
                                <td>${item.user ? item.user.name : '<span class="text-danger">N/A</span>'}</td>
                                <td>${item.quantity}</td>
                                <td>₹${item.total_price}</td>
                                <td>${statusBadge}</td>
                                <td class="text-center">
                                     <button class="btn btn-sm btn-view btn-outline-primary edit-category-btn me-1" data-id="${item.id}" title="Edit Category">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm  btn-delete btn-outline-danger delete-category-btn" data-id="${item.id}" title="Delete Category">
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
                console.error('Failed to load bookings:', xhr);
                $('#bookings-tbody').html(`
                    <tr>
                        <td colspan="5" class="text-center text-danger py-3">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Failed to load bookings.
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
            $('#bookingsPagination').html('');
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
        $('#bookingsPagination').html(paginationHtml);
    }

    // Initial Load Execution
    loadEventBookings(currentPage, currentLimit);

    // Event 1: Change Entries Per Page Select Dropdown
    $(document).on('change', '#perPageSelect', function() {
        let limit = $(this).val();
        loadEventBookings(1, limit);
    });

    // Event 2: Click Pagination Links
    $(document).on('click', '.pagination-link', function(e) {
        e.preventDefault();
        let targetPage = $(this).data('page');
        if (targetPage && targetPage > 0) {
            loadEventBookings(targetPage, currentLimit);
        }
    });
    // function fetchBookings() {
    //     $.ajax({
    //         url: '/api/admin/bookings/get',
    //         type: 'GET',
    //         dataType: 'json',
    //         success: function(response) {
                // let rows = '';
                // const bookings = response.data || response; // Handles wrapped or direct array payload
                // console.log(response);

                // if (bookings.length === 0) {
                //     rows = `<tr><td colspan="8" class="text-center py-3">No bookings found.</td></tr>`;
                // } else {
                //     $.each(bookings, function(index, item) {
                        // const statusBadge = item.status === 'CONFIRMED' 
                        //     ? '<span class="badge bg-success">CONFIRMED</span>' 
                        //     : '<span class="badge bg-warning text-dark">' + item.status + '</span>';

                //         rows += `
                //             <tr>
                //                 <td>${index + 1}</td>
                                // <td><strong>${item.registration_number || 'N/A'}</strong></td>
                                // <td>${item.event ? item.event.title : '<span class="text-danger">N/A</span>'}</td>
                                // <td>${item.user ? item.user.name : '<span class="text-danger">N/A</span>'}</td>
                                // <td>${item.quantity}</td>
                                // <td>₹${item.total_price}</td>
                                // <td>${statusBadge}</td>
                                // <td class="text-center">
                                //      <button class="btn btn-sm btn-view btn-outline-primary edit-category-btn me-1" data-id="${item.id}" title="Edit Category">
                                //     <i class="bi bi-eye"></i>
                                // </button>
                                // <button class="btn btn-sm  btn-delete btn-outline-danger delete-category-btn" data-id="${item.id}" title="Delete Category">
                                //     <i class="bi bi-trash"></i>
                                // </button>

                                 
                                // </td>
                //             </tr>`;
                //     });
                // }
                // $('#bookings-tbody').html(rows);
    //         },
    //         error: function(xhr) {
    //             showAlert('danger', 'Failed to load bookings from server.');
    //         }
    //     });
    // }

    // fetchBookings(); // Initial call

    // 2. CREATE: Open modal for new record
    $('#btn-create-booking').on('click', function() {
        $('#bookingForm')[0].reset();
        $('#booking_id').val('');
        $('#modalTitle').text('Create New Booking');
        bookingModal.show();
    });

    // 3. CREATE / UPDATE: Submit handler
    $('#bookingForm').on('submit', function(e) {
        e.preventDefault();
        
        const bookingId = $('#booking_id').val();

        const formData = $(this).serialize();

        $.ajax({
            url: '/api/admin/bookings/update/' + bookingId,
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                bookingModal.hide();
                showAlert('success', response.message || 'Booking saved successfully.');
                fetchBookings();
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    let errorMsg = '';
                    $.each(errors, function(key, val) { errorMsg += val[0] + '<br>'; });
                    showAlert('danger', errorMsg);
                } else {
                    showAlert('danger', 'An error occurred while saving.');
                }
            }
        });
    });

    // 4. VIEW: Get single record
  // Initialize Bootstrap Modal instance
const viewBookingModal = new bootstrap.Modal(document.getElementById('viewBookingModal'));

// Event handler for View button
$(document).on('click', '.btn-view', function () {
    const bookingId = $(this).data('id');

    $.ajax({
        url: `/api/admin/bookings/get/${bookingId}`,
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            const booking = response.data || response;

            // Populate text elements instead of inputs
            $('#view_registration_number').text(booking.registration_number || 'N/A');
            $('#view_event_title').text(booking.event ? booking.event.title : 'N/A');
            $('#view_user_name').text(booking.user ? booking.user.name : 'N/A');
            $('#view_registered_at').text(booking.registered_at ? booking.registered_at.split('T')[0] : 'N/A');
            $('#view_quantity').text(booking.quantity || 0);

            // Format price values
            const unitPrice = parseFloat(booking.unit_price || 0);
            const totalPrice = parseFloat(booking.total_price || 0);
            $('#view_unit_price').text(unitPrice === 0 ? 'Free' : '₹' + unitPrice.toLocaleString('en-IN'));
            $('#view_total_price').text('₹' + totalPrice.toLocaleString('en-IN'));

            // Format Status Badge dynamically
            const statusMap = {
                'confirmed': '<span class="badge bg-success fs-6"><i class="bi bi-check-circle-fill me-1"></i> Confirmed</span>',
                'pending': '<span class="badge bg-warning text-dark fs-6"><i class="bi bi-hourglass-split me-1"></i> Pending</span>',
                'cancelled': '<span class="badge bg-danger fs-6"><i class="bi bi-x-circle-fill me-1"></i> Cancelled</span>'
            };
            const currentStatus = (booking.status || '').toLowerCase();
            $('#view_status_badge').html(statusMap[currentStatus] || `<span class="badge bg-secondary fs-6">${booking.status || 'N/A'}</span>`);

            // Show the modal
            viewBookingModal.show();
        },
        error: function (xhr) {
            showAlert('danger', 'Failed to fetch booking details.');
        }
    });
});
    

    // 6. DELETE: Remove record
    $(document).on('click', '.btn-delete', function() {
        const id = $(this).data('id');
        if (confirm('Are you sure you want to delete this booking?')) {
            $.ajax({
                url: '/api/admin/booking/delete/' + id,
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    showAlert('success', response.message || 'Booking deleted successfully.');
                    fetchBookings();
                },
                error: function(xhr) {
                    let errors = xhr.responseJSON.message;
                    showAlert('danger',errors);
                }
            });
        }
    });

    // Helper: Bootstrap Alert Renderer
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