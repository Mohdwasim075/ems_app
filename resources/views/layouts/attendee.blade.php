<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    dir="{{in_array(app()->getLocale(), ['ar']) ? 'rtl' : 'ltr'}}">

<head>

    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Event Hub</title>

    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0/dist/css/adminlte.min.css">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <style>
        /* Color Palette Theme */
        :root {
            --theme-primary: #091540;
            --theme-accent: #3b82f6;
            --theme-bg: #f3f4f6;
            --theme-header-bg: #ffffff;
            --theme-text-light: #f8fafc;
        }

        /* Body & Main Layout */
        body {
            background-color: var(--theme-bg) !important;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        .app-main {
            background-color: var(--theme-bg);
            padding: 1.5rem;
            min-height: calc(100vh - 115px);
        }

        /* Navbar Header Styling */
        .app-header {
            background-color: var(--theme-header-bg) !important;
            border-bottom: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        }

        .app-header .nav-link {
            color: #4b5563 !important;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .app-header .nav-link:hover {
            color: var(--theme-accent) !important;
        }

        /* Sidebar & Brand Header Styling */
        .app-sidebar {
            background-color: var(--theme-primary) !important;
        }

        .sidebar-brand {
            background-color: rgba(0, 0, 0, 0.15) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .sidebar-brand .brand-link {
            color: var(--theme-text-light) !important;
            text-decoration: none;
        }

        .sidebar-brand p {
            margin: 0;
            font-weight: 600;
            letter-spacing: 0.5px;
            color: #ffffff;
        }

        /* Sidebar Navigation Links */
        .sidebar-wrapper {
            background-color: var(--theme-primary) !important;
            padding-top: 0.5rem;
        }

        .sidebar-menu .nav-link {
            color: #94a3b8 !important;
            border-radius: 6px;
            margin: 2px 8px;
            transition: all 0.2s ease;
        }

        .sidebar-menu .nav-link p,
        .sidebar-menu .nav-link i {
            color: inherit !important;
        }

        /* Active & Hover States */
        .sidebar-menu .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.08) !important;
            color: #ffffff !important;
        }

        .sidebar-menu .nav-link.active {
            background-color: var(--theme-accent) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3);
        }

        /* Footer Styling */
        .app-footer {
            background-color: #ffffff;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
        }
    </style>
</head>

<body class="layout-fixed sidebar-expand-lg bg-dark">

    <div class="app-wrapper">

        <!-- HEADER / NAVBAR -->
        <nav class="app-header navbar navbar-expand">

            <div class="container-fluid">

                <!-- Sidebar Toggle -->
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                            <i class="bi bi-list fs-5"></i>
                        </a>
                    </li>
                </ul>

                <!-- Right Side -->
                <ul class="navbar-nav ms-auto align-items-center">

                    <!-- Language Dropdown -->
                    <li class="nav-item dropdown me-2">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-translate me-1"></i>
                            {{ __('messages.language') }}
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                            <li>
                                <a href="{{ route('lang.switch', 'en') }}" class="dropdown-item language-option">
                                    {{ __('messages.English') }}
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('lang.switch', 'es') }}" class="dropdown-item language-option">
                                    {{ __('messages.Spanish') }}
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('lang.switch', 'ar') }}" class="dropdown-item language-option">
                                    {{ __('messages.Arabic') }}
                                </a>
                            </li>

                        </ul>
                    </li>
                    @auth
                        <!-- Right navbar -->
                        <ul class="navbar-nav ms-auto ">

                            <!-- User Dropdown -->
                            <li class="nav-item dropdown user-menu">

                                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <i class="bi bi-person-circle fs-5"></i>
                                    <span class="d-none d-md-inline ms-2">
                                        {{ Auth::user()->name }}
                                    </span>
                                </a>

                                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end shadow">

                                    <!-- User Header -->
                                    <li class="dropdown-header text-center py-3">
                                        <i class="bi bi-person-circle display-5 text-primary"></i>
                                        <h6 class="mt-2 mb-0 fw-bold">
                                            {{ Auth::user()->name }}
                                        </h6>
                                        <small class="text-muted">{{ Auth::user()->email }}</small>
                                    </li>

                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>

                                    <!-- Profile Link -->
                                    <li>
                                        <a href="{{ route('profileview') }}" class="dropdown-item">
                                            <i class="bi bi-person me-2"></i>
                                            {{ __('messages.My Profile') }}
                                        </a>
                                    </li>

                                </ul>

                            </li>

                        </ul>
                    @endauth
                    @guest
                        <!-- Guest links -->
                        <li class="nav-item">
                            <a href="/login" class="btn btn-outline-primary btn-sm me-2 fw-medium">
                                {{ __('messages.Login') }}
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="/register" class="btn btn-primary btn-sm me-2 fw-medium">
                                <i class="bi bi-person-plus me-1"></i>
                                {{ __('messages.Register') }}
                            </a>
                        </li>
                    @endguest

                    @auth
                        <!-- Logout Button -->
                        <li class="nav-item">
                            <form id="logoutForm" method="post">
                                <button type="submit" class="btn btn-danger btn-sm me-2 fw-medium">
                                    <i class="bi bi-box-arrow-right me-1"></i>
                                    {{ __('messages.Log out') }}
                                </button>
                            </form>
                        </li>
                    @endauth

                </ul>

            </div>

        </nav>

        <!-- SIDEBAR -->
        <aside class="app-sidebar shadow">

            <!-- Brand -->
            <div class="sidebar-brand">
                <a href="#" class="brand-link px-3 py-3 d-flex align-items-center">
                    <i class="bi bi-calendar2-event-fill text-primary fs-4 me-2"></i>
                    <span class="brand-text">
                        <p class="h5 mb-0">{{ __('messages.Event Hub') }}</p>
                    </span>
                </a>
            </div>

            <!-- Sidebar Content -->
            <div class="sidebar-wrapper">
                <nav>
                    <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu">
                        <!-- Home -->
                        <li class="nav-item">
                            <a href="{{ route('attendee.home') }}"
                                class="nav-link {{ request()->routeIs('attendee.home') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-house-fill me-2"></i>
                                <p class="d-inline">{{ __('messages.Home') }}</p>
                            </a>
                        </li>

                        <!-- Events -->
                        <li class="nav-item">
                            <a href="/events" class="nav-link {{ request()->is('events*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-calendar-event me-2"></i>
                                <p class="d-inline">{{ __('messages.Events') }}</p>
                            </a>
                        </li>

                        <!-- Authenticated user links -->
                        @can('isAttendee')


                            <li class="nav-item">
                                <a href="/profile" class="nav-link {{ request()->is('profile*') ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-person me-2"></i>
                                    <p class="d-inline">{{ __('messages.Profile') }}</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="/myevents" class="nav-link {{ request()->is('myevents*') ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-calendar-check me-2"></i>
                                    <p class="d-inline">{{ __('messages.My Events') }}</p>
                                </a>
                            </li>
                        @endcan
                    </ul>
                </nav>
            </div>

        </aside>

        <main class="app-main">
            @yield('content')
        </main>

        <!-- FOOTER -->
        <footer class="app-footer py-3 px-4">
            <div class="float-end d-none d-sm-inline text-muted small">
                {{ __('messages.Event Management System') }}
            </div>
            <strong class="small">
                {{ __('messages.Copyright') }} &copy; 2026
            </strong>
        </footer>

    </div>

    <script src="https://code.jquery.com/jquery-4.0.0.min.js"
        integrity="sha256-OaVG6prZf4v69dPg6PhVattBXkcOWQB62pdZ3ORyrao=" crossorigin="anonymous"></script>

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            }
        });

        $('#loginForm').on('submit', function (event) {
            event.preventDefault();
            $.ajax({
                url: '/login',
                method: 'POST',
                data: $(this).serialize(),
                success: function (response) {
                    window.location.href = '/';
                },
                error: function (xhr) {
                    console.log('Status:', xhr.status);
                    console.log('Response:', xhr.responseJSON);
                }
            });
        });

        $('#logoutForm').on('submit', function (event) {
            event.preventDefault();
            $.ajax({
                url: '/api/logout',
                method: 'POST',
                data: $(this).serialize(),
                success: function (response) {
                    window.location.href = '/';
                },
                error: function (xhr) {
                    console.log(xhr.status);
                    console.log(xhr.responseJSON);
                }
            });
        });
    </script>

    <!-- Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <!-- AdminLTE -->
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0/dist/js/adminlte.min.js"></script>

</body>

</html>