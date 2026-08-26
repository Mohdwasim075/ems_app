@extends('layouts.attendee')

@section('title', 'Events')

@section('content')

<div class="container py-5">

    <h1 class="mb-4">Events</h1>

    <div id="events-container" class="row g-4">
        <!-- Cards will be inserted here -->
    </div>

</div>


<!-- One reusable modal -->
<div class="modal fade" id="eventModal" tabindex="-1">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title" id="modalEventTitle">
                    Event Details
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

              

                <p>
                    <strong>Date:</strong>
                    <span id="modalEventDate"></span>
                </p>

                <p>
                    <strong>Location:</strong>
                    <span id="modalEventLocation"></span>
                </p>

                <p>
                    <strong>Capacity:</strong>
                    <span id="modalEventCapacity"></span>
                </p>

                <hr>

                <p id="modalEventDescription"></p>

            </div>

            <div class="modal-footer">

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


<script>

    // const eventsContainer = document.getElementById('events-container');

    // async function loadEvents() {

    //     try {

    //         const response = await fetch('/api/events');

    //         if (!response.ok) {
    //             throw new Error('Failed to fetch events');
    //         }

    //         const result = await response.json();

    //         const events = result.data;

    //         eventsContainer.innerHTML = '';

    //         events.forEach(event => {

    //             const card = `
    //                 <div class="col-md-6 col-lg-4">

    //                     <div class="card h-100 shadow-sm">

    //                         <div class="card-body">

                              

    //                             <h5 class="card-title">
    //                                 ${event.title}
    //                             </h5>

    //                             <p class="card-text mt-3">
    //                                 ${event.description}
    //                             </p>

    //                             <p class="text-muted mb-1">
    //                                 <i class="bi bi-calendar"></i>
    //                                 ${event.start_at}
    //                             </p>

    //                             <p class="text-muted">
    //                                 <i class="bi bi-geo-alt"></i>
    //                                 ${event.location}
    //                             </p>

    //                             <button
    //                                 class="btn btn-primary view-event"
    //                                 data-event-id="${event.id}">
    //                                 View Details
    //                             </button>

    //                         </div>

    //                     </div>

    //                 </div>
    //             `;

    //             eventsContainer.insertAdjacentHTML(
    //                 'beforeend',
    //                 card
    //             );

    //         });

    //     } catch (error) {

    //         console.error(error);

    //         eventsContainer.innerHTML = `
    //             <div class="col-12">
    //                 <div class="alert alert-danger">
    //                     Failed to load events.
    //                 </div>
    //             </div>
    //         `;

    //     }
    // }


    // loadEvents();

    const eventsContainer = document.getElementById('events-container');

async function loadEvents() {

    try {

        const response = await fetch('/api/events');

        if (!response.ok) {
            throw new Error('Failed to fetch events');
        }

        const result = await response.json();

        const events = result.data;

        eventsContainer.innerHTML = '';

        events.forEach(event => {

            const card = `
                <div class="col-12 col-md-6 col-lg-4 mb-4">

                    <div class="card h-100 shadow-sm">

                        <!-- Event Image -->

                        <img
                            src="https://img.magnific.com/premium-photo/audience-conference-hall_386094-30.jpg?semt=ais_hybrid&w=740&q=80"
                            class="card-img-top"
                            alt="${event.title}"
                            style="height: 220px; object-fit: cover;"
                        >


                        <!-- Card Content -->

                        <div class="card-body d-flex flex-column">

                            <!-- Title -->

                            <h5 class="card-title fw-bold mb-2">
                                ${event.title}
                            </h5>


                            <!-- Description -->

                            <p class="card-text text-muted mb-3">
                                ${event.description ?? ''}
                            </p>


                            <!-- Date -->

                            <p class="text-muted mb-2">

                                <i class="bi bi-calendar me-2"></i>

                                 ${new Date(event.start_at).toLocaleDateString('en-IN', {
                                    day: '2-digit',
                                    month: 'long',
                                    year: 'numeric'
                                })}

                            </p>


                            <!-- Location -->

                            <p class="text-muted mb-3">

                                <i class="bi bi-geo-alt me-2"></i>

                                ${event.location}

                            </p>


                            <!-- Button -->

                            <div class="mt-auto">

                                <button
                                    class="btn btn-primary view-event"
                                    data-event-id="${event.id}"
                                >
                                    View Details
                                </button>

                            </div>

                        </div>

                    </div>

                </div>
            `;

            eventsContainer.insertAdjacentHTML(
                'beforeend',
                card
            );

        });

    } catch (error) {

        console.error(error);

        eventsContainer.innerHTML = `
            <div class="col-12">
                <div class="alert alert-danger">
                    Failed to load events.
                </div>
            </div>
        `;

    }
}

loadEvents();
function formatEventDate(dateString) {

    const date = new Date(dateString);

    return date.toLocaleDateString('en-IN', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });
}

  document.addEventListener('click', async function (event) {

    if (!event.target.classList.contains('view-event')) {
        return;
    }

    const eventId = event.target.dataset.eventId;

    try {

        const response = await fetch(`/api/events/${eventId}`);

        if (!response.ok) {
            throw new Error('Failed to fetch event');
        }

        const result = await response.json();

        const eventData = result.data;
    

        document.getElementById('modalEventTitle').textContent =
            eventData.title;

       

        document.getElementById('modalEventDate').textContent =
    formatEventDate(eventData.event_date);

        document.getElementById('modalEventLocation').textContent =
            eventData.location;

        document.getElementById('modalEventCapacity').textContent =
            eventData.capacity;

        document.getElementById('modalEventDescription').textContent =
            eventData.description;


        const modal = new bootstrap.Modal(
            document.getElementById('eventModal')
        );

        modal.show();

    } catch (error) {

        console.error(error);

        alert('Unable to load event details.');

    }

});

</script>

@endsection