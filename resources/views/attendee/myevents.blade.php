@extends('layouts.attendee')

@section('title', __('messages.My Registered Events'))

@section('content')

<style>
    /* Clickable card effect */
    .my-event-card {
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .my-event-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
</style>

<div class="container py-4">

    <div class="card shadow-sm border-0">

        <div class="card-body p-4">

            <h2 class="fw-bold mb-4">
                {{ __('messages.My Registered Events') }}
            </h2>

            <div class="row g-4" id="myEvents">
                <!-- Event Cards injected via jQuery -->
            </div>
             <!-- Pagination Container -->
            <div class="d-flex justify-content-end align-items-center mt-4">
                <div id="myEventsPagination"></div>
            </div>

        </div>


    </div>

</div>
<!-- Reusable Event Modal Popup -->
<div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="modalEventTitle" aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content shadow">

            <div class="modal-header bg-light">

                <h5 class="modal-title fw-bold" id="modalEventTitle">
                    {{ __('messages.Event Details') }}
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close">
                </button>

            </div>

            <div class="modal-body p-4">

                <div class="row mb-3">
                    <div class="col-md-6 mb-2">
                        <i class="bi bi-calendar-event text-primary me-2"></i>
                        <strong>{{ __('messages.Date:') }}</strong>
                        <span id="modalEventDate"></span>
                    </div>

                    <div class="col-md-6 mb-2">
                        <i class="bi bi-people text-primary me-2"></i>
                        <strong>{{ __('messages.Capacity:') }}</strong>
                        <span id="modalEventCapacity"></span>
                    </div>

                    <div class="col-md-12 mb-2">
                        <i class="bi bi-geo-alt text-primary me-2"></i>
                        <strong>{{ __('messages.Location:') }}</strong>
                        <span id="modalEventLocation"></span>
                    </div>
                </div>

                <hr>

                <h6 class="fw-semibold">{{ __('messages.Description') }}</h6>
                <p id="modalEventDescription" class="text-secondary leading-relaxed"></p>

            </div>

            <div class="modal-footer bg-light">

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal">
                    {{ __('messages.Close') }}
                </button>

            </div>

        </div>

    </div>

</div>
<script src="https://code.jquery.com/jquery-4.0.0.min.js" integrity="sha256-OaVG6prZf4v69dPg6PhVattBXkcOWQB62pdZ3ORyrao=" crossorigin="anonymous"></script>

<script>
function formatEventDate(dateString) {
    if (!dateString) return '{{ __('messages.N/A') }}';
    const date = new Date(dateString);
    return date.toLocaleDateString('{{ app()->getLocale() == 'ar' ? 'ar-SA' : (app()->getLocale() == 'es' ? 'es-ES' : 'en-US') }}', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });
}
 
function displayMyEvents(registrations) {

    const container = $('#myEvents');

    container.empty();

    if (!registrations || registrations.length === 0) {

        container.html(`
            <div class="col-12">
                <div class="alert alert-info">
                    {{ __('messages.You have not registered for any events yet.') }}
                </div>
            </div>
        `);

        return;
    }

    registrations.forEach(function (registration) {

        const registered = registration;
        const event = registration.event;

        const formattedDate = formatEventDate(event.start_at || event.event_date);
        const eventRegisteredDate = formatEventDate(registered.registered_at)

        container.append(`

            <div class="col-12 col-md-6 col-lg-4 mb-4">

                <div class="card h-100 shadow-sm border-0 my-event-card" data-event-id="${event.id}">

                    <!-- Event image -->
                    <img
                        src="https://img.magnific.com/premium-photo/audience-conference-hall_386094-30.jpg?semt=ais_hybrid&w=740&q=80"
                        class="card-img-top"
                        alt="${event.title}"
                        style="height: 200px; object-fit: cover;"
                    >

                    <div class="card-body d-flex flex-column">

                        <!-- Event title -->
                        <h5 class="card-title fw-bold mb-2">
                            ${event.title}
                        </h5>

                        <!-- Event date -->
                        <p class="card-text text-muted mb-3">
                            <i class="bi bi-calendar-event me-2 text-primary"></i>
                            ${formattedDate}
                        </p>

                        <div class="bg-light p-3 rounded-3 mt-auto">

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small text-muted">{{ __('messages.Reg #') }}</span>
                                <span class="badge bg-secondary font-monospace">${registered.registration_number}</span>
                            </div>
                              <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small text-muted">{{ __('messages.Registration Date:') }}</span>
                                <span class="fw-semibold">${eventRegisteredDate}</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small text-muted">{{ __('messages.Tickets:') }}</span>
                                <span class="fw-semibold">${registered.quantity}</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small text-muted">{{ __('messages.Total Paid:') }}</span>
                                <span class="fw-bold text-success">₹${registered.total_price}</span>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        `);
    });
}

$(document).ready(function () {
    // Global Pagination State Variables
    let currentPage = 1;
    let currentLimit = 3 ;

    // 1. Load Categories with Pagination
    function loadEvents(page = 1, limit = 3) {
        currentPage = page;
        currentLimit = limit;

        $.ajax({
            url: `/api/myevents?page=${page}&limit=${limit}`,
            type: 'GET',
            dataType: 'json',
            success: function (response) {
            console.log(response);
            displayMyEvents(response.data);
            let pagination = response.pagination || {};
             // Render dynamic pagination controls
            renderPaginationControls(pagination);
        },

        error: function (xhr) {
            console.log('Status:', xhr.status);
            console.log('Response:', xhr.responseJSON);

            if (xhr.status === 401) {
                window.location.href = '/login';
            } else {
                $('#myEvents').html(`
                    <div class="col-12">
                        <div class="alert alert-danger">
                            {{ __('messages.Failed to load registered events.') }}
                        </div>
                    </div>
                `);
            }
        }
        });
    }

    // Dynamic Pagination UI Generator
    function renderPaginationControls(pagination) {
        if (!pagination) {
            $('#myEventsPagination').html('');
            return;
        }

        let paginationHtml = '<ul class="pagination pagination-sm mb-0">';

        // Previous Button
        let prevDisabled = pagination.current_page === 1 ? 'disabled' : '';
        paginationHtml += `
            <li class="page-item ${prevDisabled}">
                <a class="page-link pagination-link" href="#" data-page="${pagination.current_page - 1}">{{ __('messages.Previous') }}</a>
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
                <a class="page-link pagination-link" href="#" data-page="${pagination.current_page + 1}">{{ __('messages.Next') }}</a>
            </li>
        `;

        paginationHtml += '</ul>';
        $('#myEventsPagination').html(paginationHtml);
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

//     // 1. Fetch user's registered events
//     $.ajax({
//         url: '/api/myevents',
//         method: 'GET',
//         headers: {
//             'Accept': 'application/json'
//         },

//         success: function (response) {
//             console.log(response);
//             displayMyEvents(response.events);
//         },

//         error: function (xhr) {
//             console.log('Status:', xhr.status);
//             console.log('Response:', xhr.responseJSON);

//             if (xhr.status === 401) {
//                 window.location.href = '/login';
//             } else {
//                 $('#myEvents').html(`
//                     <div class="col-12">
//                         <div class="alert alert-danger">
//                             Failed to load registered events.
//                         </div>
//                     </div>
//                 `);
//             }
//         }
//     });

    // 2. Click Event on Card: Open modal with details
    $(document).on('click', '.my-event-card', function () {

        const eventId = $(this).data('event-id');

        $.ajax({
            url: `/api/events/${eventId}`,
            type: 'GET',
            dataType: 'json',

            success: function(response) {
                const eventData = response.data;

                // Populate Modal Fields
                $('#modalEventTitle').text(eventData.title);
                $('#modalEventDate').text(formatEventDate(eventData.start_at || eventData.event_date));
                $('#modalEventLocation').text(eventData.location || '{{ __('messages.N/A') }}');
                $('#modalEventCapacity').text(eventData.capacity ? `${eventData.capacity} {{ __('messages.Seats') }}` : '{{ __('messages.Unlimited') }}');
                $('#modalEventDescription').text(eventData.description || '{{ __('messages.No description available.') }}');

                // Show Modal
                const eventModal = new bootstrap.Modal($('#eventModal')[0]);
                // eventModal.show();
            },

            error: function(xhr, status, error) {
                console.error('Event Detail API Error:', error);
                alert('{{ __('messages.Unable to load details for this event.') }}');
            }
        });
    });

});
</script>

@endsection