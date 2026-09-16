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

                @guest
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
              

                <!-- Register Button -->
                
                <li class="nav-item">

                    <a
                        href="/register"
                        class="btn btn-primary mt-1 me-2"
                    >
                        <i class="bi bi-box-arrow-in-right"></i>

                        Register
                    </a>

                </li>

                  @endguest

                @auth
                 <!-- Register Button -->
                
                <li class="nav-item">

                    <a
                        href="/logout"
                        class="btn btn-primary mt-1 me-2"
                    >
                        <i class="bi bi-box-arrow-in-right"></i>

                        Log out
                    </a>

                </li>
                    
                @endauth

                  <li class="nav-item">

                    <a
                        href="/logout"
                        class="btn btn-primary mt-1 me-2"
                    >
                        <i class="bi bi-box-arrow-in-right"></i>

                        Log out
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



