<!doctype html>
<html lang="en">

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>Event Hub</title>

    <!-- AdminLTE CSS -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0/dist/css/adminlte.min.css"
    >

    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
    >

</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">

<div class="app-wrapper">


    <!--  HEADER / NAVBAR -->

    <nav class="app-header navbar navbar-expand bg-body">

        <div class="container-fluid">

            <!-- Sidebar Toggle -->

            <ul class="navbar-nav">

                <li class="nav-item">

                    <a
                        class="nav-link"
                        data-lte-toggle="sidebar"
                        href="#"
                        role="button"
                    >
                        <i class="bi bi-list"></i>
                    </a>

                </li>

            </ul>


            <!-- Right Side -->

            <ul class="navbar-nav ms-auto">


                <!-- Language Dropdown -->

                <li class="nav-item dropdown">

                    <a
                        class="nav-link dropdown-toggle"
                        href="#"
                        role="button"
                        data-bs-toggle="dropdown"
                    >

                        <i class="bi bi-translate"></i>

                        Language

                    </a>


                    <ul class="dropdown-menu dropdown-menu-end">

                        <li>
                            <a
                                class="dropdown-item"
                                href="#"
                            >
                                English
                            </a>
                        </li>

                        <li>
                            <a
                                class="dropdown-item"
                                href="#"
                            >
                                Tamil
                            </a>
                        </li>

                        <li>
                            <a
                                class="dropdown-item"
                                href="#"
                            >
                                Hindi
                            </a>
                        </li>

                    </ul>

                </li>


                <!-- Login Button -->

                <li class="nav-item">

                    <a
                        href="/login"
                        class="btn btn-primary mt-1 me-2"
                    >
                        <i class="bi bi-box-arrow-in-right"></i>

                        Login
                    </a>

                </li>

            </ul>

        </div>

    </nav>


    <!--  SIDEBAR  -->

    <aside class="app-sidebar ">

        <!-- Brand -->

        <div class="sidebar-brand">

            <a
                href="/"
                class="brand-link"
            >

                <span class="brand-text fw-light">
                    Event Hub
                </span>

            </a>

        </div>


        <!-- Sidebar Content -->

        <div class="sidebar-wrapper">

            <nav>

                <ul
                    class="nav sidebar-menu flex-column"
                    data-lte-toggle="treeview"
                    role="menu"
                >


                    <!-- Home -->

                   <li class="nav-item">

                        <a
                            href="{{ route('home') }}"
                            class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}"
                        >

                            <i class="bi bi-house-fill"></i>

                            <p>Home</p>

                        </a>

                    </li>


                    <!-- Events -->

                    <li class="nav-item">

                        <a
                             href="{{ route('events') }}"
                            class="nav-link {{ request()->routeIs('events') ? 'active' : '' }}"
                        >

                            <i class="nav-icon bi bi-calendar-event"></i>

                            <p>
                                Events
                            </p>

                        </a>

                    </li>

                </ul>

            </nav>

        </div>

    </aside>


    <!--  MAIN CONTENT  -->

    <main class="app-main">

        <!-- Page Header -->

        <div class="app-content-header">

            <div class="container-fluid">
                 <!-- Section 1: Featured Events -->
    <div class="mb-4">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">Featured Events</h3>
        </div>

        <div id="featuredEvents" class="row g-3">
            <!-- Cards will be inserted here -->
        </div>

    </div>


    <!-- Section 2: Upcoming Events -->
    <div class="mb-4">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">Upcoming Events</h3>
        </div>

        <div id="upcomingEvents" class="row g-3">
            <!-- Cards will be inserted here -->
        </div>

    </div>


    <!-- Section 3: Popular Events -->
    <div class="mb-4">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">Popular Events</h3>
        </div>

        <div id="popularEvents" class="row g-3">
            <!-- Cards will be inserted here -->
        </div>

    </div>

</div>

              

            </div>

        </div>


        <!-- Page Content -->

   
    <script>
        function createEventCard(event) {

    return `
        <div class="col-12 col-md-6 col-lg-4">

            <div class="card h-100 shadow-sm">

                <img
                    src="https://b2685282.assetcdn.net/2685282/wp-content/uploads/2022/11/event-software-memberclicks-1280x720.jpeg?lossy=0&strip=1&webp=1" alt="Card image cap"
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
                            ${event.start_at}
                        </p>

                        <p class="mb-3">
                            <i class="bi bi-geo-alt"></i>
                            ${event.location}
                        </p>

                        <a
                            href="/events/${event.id}"
                            class="btn btn-primary"
                        >
                            View Event
                        </a>

                    </div>

                </div>

            </div>

        </div>
    `;
}
async function loadEvents() {

    try {

        const [
            featuredResponse,
            upcomingResponse,
            popularResponse
        ] = await Promise.all([

            fetch('/api/events/featured'),
            fetch('/api/events/upcoming'),
            fetch('/api/events/popular')

        ]);

        const featuredEvents = await featuredResponse.json();
        const upcomingEvents = await upcomingResponse.json();
        const popularEvents = await popularResponse.json();


        document.getElementById('featuredEvents').innerHTML =
            featuredEvents.map(createEventCard).join('');


        document.getElementById('upcomingEvents').innerHTML =
            upcomingEvents.map(createEventCard).join('');


        document.getElementById('popularEvents').innerHTML =
            popularEvents.map(createEventCard).join('');

    } catch (error) {

        console.error('Error loading events:', error);

    }
}

loadEvents();
    </script>
    </main>


    <!-- =========================
         FOOTER
    ========================== -->

    <footer class="app-footer">

        <div class="float-end d-none d-sm-inline">
            Event Management System
        </div>

        <strong>
            Copyright &copy; 2026
        </strong>

    </footer>

</div>


<!-- =========================
     JAVASCRIPT
========================== -->

<!-- Bootstrap -->
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js">
</script>


<!-- AdminLTE -->
<script
    src="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0/dist/js/adminlte.min.js">
</script>


</body>

</html>



