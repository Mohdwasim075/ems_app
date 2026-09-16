

@extends('layouts.attendee')

@section('content')
    <!--  MAIN CONTENT  -->

    <main class="app-main">

    <div class="app-content-header">
    <div class="container-fluid">
        <!-- Section 1: Featured Events -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="fw-bold mb-0">Top Events</h3>
                </div>

                <div id="featuredEvents" class="row g-3">
                    <!-- Cards will be inserted here -->
                </div>
            </div>
        </div>

        <!-- Section 2: Upcoming Events -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="fw-bold mb-0">Upcoming Events</h3>
                </div>

                <div id="upcomingEvents" class="row g-3">
                    <!-- Cards will be inserted here -->
                </div>
            </div>
        </div>
    </div>
</div>

        <!-- Page Content -->

   

    </main>
    <script>
       function createEventCard(event) {

    return `
        <div class="col-12 col-md-6 col-lg-4">

            <div class="card h-100 shadow-sm">

                <img
                    src="https://img.magnific.com/premium-photo/audience-conference-hall_386094-30.jpg?semt=ais_hybrid&w=740&q=80"
                    class="card-img-top"
                    alt="${event.title}"
                    style="height: 200px; object-fit: cover;"
                >

                <div class="card-body d-flex flex-column">

                    <h5 class="card-title">
                        ${event.title}
                    </h5>

                    <p class="card-text text-muted">
                        ${event.description}
                    </p>

                    <div class="mt-auto">

                        <p class="mb-2">
                            <i class="bi bi-calendar"></i>
                           ${new Date(event.start_at).toLocaleDateString('en-IN', {
                                    day: '2-digit',
                                    month: 'long',
                                    year: 'numeric'
                                })}
                        </p>

                        <p class="mb-3">
                            <i class="bi bi-geo-alt"></i>
                            ${event.location}
                        </p>

                     

                    </div>

                </div>

            </div>

        </div>
    `;
}

function formatEventDate(dateString) {

    const date = new Date(dateString);

    return date.toLocaleDateString('en-IN', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });
}
async function loadEvents() {

    try {

        const [
            featuredResponse,
            upcomingResponse,
            popularResponse
        ] = await Promise.all([

            fetch('/api/events/featured'),
            fetch('/api/events/upcoming')

        ]);

       const featuredResult = await featuredResponse.json();
        const upcomingResult = await upcomingResponse.json();
     

        const featuredEvents = featuredResult.data;
        const upcomingEvents = upcomingResult.data;
  


        document.getElementById('featuredEvents').innerHTML =
            featuredEvents.map(createEventCard).join('');


        document.getElementById('upcomingEvents').innerHTML =
            upcomingEvents.map(createEventCard).join('');


    } catch (error) {

        console.error('Error loading events:', error);

    }
}

loadEvents();

document.addEventListener('click', function (event) {

    if (event.target.classList.contains('view-event')) {

        const eventId = event.target.dataset.eventId;

        console.log('Selected event:', eventId);

    }

});
    </script>
@endsection
   


