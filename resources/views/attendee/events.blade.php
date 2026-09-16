@extends('layouts.attendee')

@section('title', 'Events')

@section('content')

<style>
    /* Visual indicator that cards are clickable */
    .event-card {
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .event-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
</style>

<div class="container py-4">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold mb-0">Events</h2>
                    <small class="text-muted">Browse and discover all upcoming events</small>
                </div>
            </div>

            <!-- Event Cards injected via jQuery AJAX -->
            <div id="events-container" class="row g-4">
                <!-- Dynamic Event Cards -->
            </div>

            <!-- Pagination Container -->
            <div class="d-flex justify-content-end align-items-center mt-4">
                <div id="eventsPagination"></div>
            </div>
        </div>
    </div>
</div>


<!-- Reusable Modal with Ticket Booking Section -->
<div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="modalEventTitle" aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content shadow">

            <div class="modal-header bg-light">

                <h5 class="modal-title fw-bold" id="modalEventTitle">
                    Event Details
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close">
                </button>

            </div>

            <div class="modal-body p-4">

                <!-- Alert container for AJAX response feedback -->
                <div id="modalAlertContainer"></div>

                <div class="row mb-3">
                    <div class="col-md-6 mb-2">
                        <i class="bi bi-calendar-event text-primary me-2"></i>
                        <strong>Date:</strong>
                        <span id="modalEventDate"></span>
                    </div>

                    <div class="col-md-6 mb-2">
                        <i class="bi bi-people text-primary me-2"></i>
                        <strong>Available Seats:</strong>
                        <span id="modalEventCapacity"></span>
                    </div>

                    <div class="col-md-12 mb-2">
                        <i class="bi bi-geo-alt text-primary me-2"></i>
                        <strong>Location:</strong>
                        <span id="modalEventLocation"></span>
                    </div>
                </div>

                <hr>

                <h6 class="fw-semibold">Description</h6>
                <p id="modalEventDescription" class="text-secondary leading-relaxed"></p>

           <!-- Ticket Selection and Pricing Card -->
            <div class="card border-primary-subtle bg-light mt-3">
                <div class="card-body p-3">
                    <h6 class="fw-bold mb-2 small"><i class="bi bi-ticket-perforated me-1 text-primary"></i>Buy Tickets</h6>
                    
                    <div class="row align-items-center">
                        <!-- Pricing Info -->
                        <div class="col-6">
                            <div class="small text-secondary">Price: ₹<span id="modalTicketPrice">0</span></div>
                            <div class="fw-bold text-dark mt-1">Total: <span class="text-success">₹<span id="modalTotalPrice">0</span></span></div>
                        </div>

                        <!-- Compact Quantity Controls -->
                        <div class="col-6 d-flex flex-column align-items-end">
                            <label for="ticketQuantity" class="form-label mb-1 extra-small fw-semibold text-muted" style="font-size: 0.75rem;">Quantity</label>
                            <div class="input-group input-group-sm" style="max-width: 120px;">
                                <button class="btn btn-outline-secondary btn-sm px-2" type="button" id="btnQtyDecrease">-</button>
                                <input type="number" id="ticketQuantity" class="form-control form-control-sm text-center px-1" value="1" min="1" max="10" readonly>
                                <button class="btn btn-outline-secondary btn-sm px-2" type="button" id="btnQtyIncrease">+</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>

            <div class="modal-footer bg-light d-flex justify-content-between">

                <div id="modalRegisterContainer">
                    @auth
                        <button type="button" id="btnBuyTickets" class="btn btn-primary px-4 fw-semibold">
                            <i class="bi bi-cart-check me-1"></i> Buy Tickets
                        </button>
                    @else
                        <a href="/login" class="btn btn-outline-primary fw-semibold">
                            Log in to Buy Tickets
                        </a>
                    @endauth
                </div>

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal">
                    Close
                </button>

            </div>

        </div>

    </div>

</div>

<script src="https://code.jquery.com/jquery-4.0.0.min.js" integrity="sha256-OaVG6prZf4v69dPg6PhVattBXkcOWQB62pdZ3ORyrao=" crossorigin="anonymous"></script>

<script>
$(document).ready(function() {

    let currentEventId = null;
    let ticketUnitPrice = 0;

    // Helper: Format ISO/Date String into readable local date
    function formatEventDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-IN', {
            day: '2-digit',
            month: 'long',
            year: 'numeric'
        });
    }

    // Helper: Update calculated total price
    function updateTotalPrice() {
        const qty = parseInt($('#ticketQuantity').val()) || 1;
        const total = qty * ticketUnitPrice;
        $('#modelTicketPrice').text(ticketUnitPrice);
        $('#modalTotalPrice').text(total);
    }

    // 1. Fetch All Events using jQuery AJAX
     // Global Pagination State Variables
    let currentPage = 1;
    let currentLimit = 5 ;

    // 1. Load Categories with Pagination
    function loadEvents(page = 1, limit = 5) {
        currentPage = page;
        currentLimit = limit;

        $.ajax({
            url: `/api/events?page=${page}&limit=${limit}`,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                 const events = response.data;
                console.log(response.data);
                
                const $container = $('#events-container');
                $container.empty();

                if (!events || events.length === 0) {
                    $container.html(`
                        <div class="col-12">
                            <div class="alert alert-info">No events found.</div>
                        </div>
                    `);
                    return;
                }

                // Calculate Starting Serial Number based on pagination page & limit
                let pagination = response.pagination || {};
                // let startNumber = pagination.from || ((page - 1) * limit + 1);

                $.each(events, function(index, event) {
                    const cardHtml = `
                        <div class="col-12 col-md-6 col-lg-4 mb-4">
                            <!-- Clickable Card container (No button inside) -->
                            <div class="card h-100 shadow-sm border-0 event-card" data-event-id="${event.id}">
                                <img
                                    src="https://img.magnific.com/premium-photo/audience-conference-hall_386094-30.jpg?semt=ais_hybrid&w=740&q=80"
                                    class="card-img-top"
                                    alt="${event.title}"
                                    style="height: 220px; object-fit: cover;"
                                >

                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title fw-bold mb-2">${event.title}</h5>

                                    <p class="card-text text-muted mb-3 text-truncate">
                                        ${event.description || ''}
                                    </p>

                                    <p class="text-muted mb-2">
                                        <i class="bi bi-calendar me-2 text-primary"></i>
                                        ${formatEventDate(event.start_at || event.event_date)}
                                    </p>

                                    <p class="text-muted mb-0">
                                        <i class="bi bi-geo-alt me-2 text-primary"></i>
                                        ${event.location || 'N/A'}
                                    </p>
                                </div>
                            </div>
                        </div>
                    `;
                    $container.append(cardHtml);
                });
                // Render dynamic pagination controls
                renderPaginationControls(pagination);
            },
            error: function(xhr) {
                console.error('API Error:', error);
                $('#events-container').html(`
                    <div class="col-12">
                        <div class="alert alert-danger">
                            Failed to load events. Please try again later.
                        </div>
                    </div>
                `);
                renderPaginationControls(null);
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
    // function loadEvents() {
    //     $.ajax({
    //         url: '/api/events',
    //         type: 'GET',
    //         dataType: 'json',
            // success: function(response) {
                // const events = response.data;
                // console.log(response.data);
                
                // const $container = $('#events-container');
                // $container.empty();

                // if (!events || events.length === 0) {
                //     $container.html(`
                //         <div class="col-12">
                //             <div class="alert alert-info">No events found.</div>
                //         </div>
                //     `);
                //     return;
                // }

        //         $.each(events, function(index, event) {
        //             const cardHtml = `
        //                 <div class="col-12 col-md-6 col-lg-4 mb-4">
        //                     <!-- Clickable Card container (No button inside) -->
        //                     <div class="card h-100 shadow-sm border-0 event-card" data-event-id="${event.id}">
        //                         <img
        //                             src="https://img.magnific.com/premium-photo/audience-conference-hall_386094-30.jpg?semt=ais_hybrid&w=740&q=80"
        //                             class="card-img-top"
        //                             alt="${event.title}"
        //                             style="height: 220px; object-fit: cover;"
        //                         >

        //                         <div class="card-body d-flex flex-column">
        //                             <h5 class="card-title fw-bold mb-2">${event.title}</h5>

        //                             <p class="card-text text-muted mb-3 text-truncate">
        //                                 ${event.description || ''}
        //                             </p>

        //                             <p class="text-muted mb-2">
        //                                 <i class="bi bi-calendar me-2 text-primary"></i>
        //                                 ${formatEventDate(event.start_at || event.event_date)}
        //                             </p>

        //                             <p class="text-muted mb-0">
        //                                 <i class="bi bi-geo-alt me-2 text-primary"></i>
        //                                 ${event.location || 'N/A'}
        //                             </p>
        //                         </div>
        //                     </div>
        //                 </div>
        //             `;
        //             $container.append(cardHtml);
        //         });
        //     },
        //     error: function(xhr, status, error) {
        //         console.error('API Error:', error);
        //         $('#events-container').html(`
        //             <div class="col-12">
        //                 <div class="alert alert-danger">
        //                     Failed to load events. Please try again later.
        //                 </div>
        //             </div>
        //         `);
        //     }
        // });
    // }

    // // Call load function on page load
    // loadEvents();

    // 2. Click Event on Card: Fetch Details and Open Modal
    $(document).on('click', '.event-card', function() {
        currentEventId = $(this).data('event-id');
        $('#modalAlertContainer').empty();
        $('#ticketQuantity').val(1);

        $.ajax({
            url: `/api/events/${currentEventId}`,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                const eventData = response.data;
                console.log(response.data);

                // Set prices and populating details
                ticketUnitPrice = parseFloat(eventData.price) || 0;

                $('#modalEventTitle').text(eventData.title);
                $('#modalEventDate').text(formatEventDate(eventData.start_at || eventData.event_date));
                $('#modalEventLocation').text(eventData.location || 'N/A');
                $('#modalEventCapacity').text(eventData.available_seats ? `${eventData.available_seats} Seats` : 'unavailable');
                $('#modalEventDescription').text(eventData.description || 'No description available.');
                $('#modalTicketPrice').text(eventData.price);
                

                updateTotalPrice();

                // Open Bootstrap Modal
                const eventModal = new bootstrap.Modal($('#eventModal')[0]);
                eventModal.show();
            },
            error: function(xhr, status, error) {
                console.error('Event Detail API Error:', error);
                alert('Unable to load details for this event.');
            }
        });
    });

    // 3. Quantity Increment/Decrement Controls
    $('#btnQtyIncrease').on('click', function() {
        let currentVal = parseInt($('#ticketQuantity').val()) || 1;
        if (currentVal < 10) {
            $('#ticketQuantity').val(currentVal + 1);
            updateTotalPrice();
        }
    });

    $('#btnQtyDecrease').on('click', function() {
        let currentVal = parseInt($('#ticketQuantity').val()) || 1;
        if (currentVal > 1) {
            $('#ticketQuantity').val(currentVal - 1);
            updateTotalPrice();
        }
    });

    // 4. Buy Tickets AJAX Submission
    $(document).on('click', '#btnBuyTickets', function() {
        const quantity = parseInt($('#ticketQuantity').val()) || 1;
        const $btn = $(this);

        if (!currentEventId) return;

        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Processing...');

        $.ajax({
            url: '/api/event/register',
            type: 'POST',
            data: {
                event_id: currentEventId,
                quantity: quantity
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            success: function(response) {
                $btn.prop('disabled', false).html('<i class="bi bi-cart-check me-1"></i> Buy Tickets');
                
                $('#modalAlertContainer').html(`
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        ${response.message || 'Tickets registered successfully!'}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `);
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html('<i class="bi bi-cart-check me-1"></i> Buy Tickets');
                
                const errorMessage = xhr.responseJSON?.message || 'Failed to purchase tickets. Please try again.';
                $('#modalAlertContainer').html(`
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        ${errorMessage}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `);
            },
        });
    });

});
</script>

@endsection