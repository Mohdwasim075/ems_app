<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
     <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
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
    <title>Register</title>
</head>
<style>
        :root {
            --theme-primary: #091540;
            --theme-accent: #3b82f6;
            --theme-bg: #f3f4f6;
            --theme-header-bg: #ffffff;
            --theme-text-light: #f8fafc;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            background-color: #cbd5e1 !important;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            margin: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1.5rem;
        }

        /* Container styled with Theme Colors */
        .container {
            background-color: var(--theme-primary);
            width: 100%;
            max-width: 420px;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 10px 25px -5px rgba(9, 21, 64, 0.1), 0 8px 10px -6px rgba(9, 21, 64, 0.05);
            padding: 2rem;
            display: flex;
            flex-direction: column;
        }

        .auth-title {
            color: var(--theme-bg);
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            text-align: center;
        }

        .auth-subtitle {
            color: #94a3b8;
            font-size: 0.875rem;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        /* Floating Input Customization */
        .form-floating > .form-control {
            border-color: #cbd5e1;
            border-radius: 8px;
            color: #0f172a;
        }

        .form-floating > .form-control:focus {
            border-color: var(--theme-accent);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        .form-floating > label {
            color: #64748b !important;
        }

        /* Buttons Styling */
        .btn-action-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-top: 1rem;
        }

        .btn-primary {
            background-color: var(--theme-accent);
            border: 1px solid var(--theme-accent);
            border-radius: 8px;
            padding: 0.6rem 1.25rem;
            font-weight: 600;
            color: #ffffff;
            transition: all 0.2s ease;
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3);
        }

        .btn-primary:hover {
            background-color: #2563eb;
            border-color: #2563eb;
            box-shadow: 0 6px 8px -1px rgba(59, 130, 246, 0.4);
        }

        .btn-secondary-theme {
            background-color: transparent;
            border: 1px solid #475569;
            border-radius: 8px;
            padding: 0.6rem 1.25rem;
            font-weight: 600;
            color: #cbd5e1;
            transition: all 0.2s ease;
        }

        .btn-secondary-theme:hover {
            background-color: rgba(255, 255, 255, 0.08);
            color: #ffffff;
            border-color: #94a3b8;
        }
    </style>
<body>
<div class="container">
        <h2 class="auth-title">Reset Password</h2>
        <p class="auth-subtitle">Enter your email address to receive a password reset link</p>

        <!-- Alert Box for AJAX Responses -->
        <div id="alert-container"></div>

        <form class="needs-validation" id="forgotPasswordForm" novalidate>
            
            <div class="form-floating mb-3">
                <input
                    type="email"
                    class="form-control"
                    id="email"
                    name="email"
                    placeholder="name@example.com"
                    required
                >
                <label for="email">Email address</label>
                <div class="invalid-feedback error-email"></div>
            </div>

            <div class="btn-action-row">
                <button
                    type="button"
                    class="btn btn-secondary-theme"
                    id="back-btn"
                    onclick="window.history.back();"
                >
                    Go Back
                </button>

                <button
                    type="submit"
                    class="btn btn-primary"
                    id="resent-btn"
                >
                    Send Link
                </button>
            </div>

        </form>
    </div>
<script src="https://code.jquery.com/jquery-4.0.0.min.js" integrity="sha256-OaVG6prZf4v69dPg6PhVattBXkcOWQB62pdZ3ORyrao=" crossorigin="anonymous"></script>

 <script>
    document.getElementById('back-btn').addEventListener('click', function toLoginPage(){
        window.location.href = "/login";
    });

     $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        }
    });
    $('#forgotPasswordForm').on('submit', function (e) {

    e.preventDefault();

    $.ajax({
        url: '/forgot-password',
        type: 'POST',

        data: $(this).serialize(),

        success: function (response) {

            console.log(response);

            showAlert(
                'success',
                'Password reset link has been sent to your email.'
            );
             setTimeout(function () {
                window.location.href = '/login';
            }, 2000);
        },

        error: function (xhr) {
 let response = xhr.responseJSON;

        // Reset previous validation state
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        /*
         * 1. Laravel validation errors
         *
         * Example:
         * {
         *   message: "...",
         *   errors: {
         *      email: ["The email field is required."]
         *   }
         * }
         */
        if (response.errors) {

            $.each(response.errors, function (fieldName, errorMessages) {

                $('[name="' + fieldName + '"]')
                    .addClass('is-invalid');

                $('.error-' + fieldName)
                    .text(errorMessages[0]);
            });
        }

        /*
         * 2. Password broker error
         *
         * Example:
         * {
         *   message: "We can't find a user with that email address."
         * }
         */
        else if (response.message) {

               $('#email').addClass('is-invalid');
            $('.error-email').text(response.message);
        }    
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
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js">
</script>


<!-- AdminLTE -->
<script
    src="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0/dist/js/adminlte.min.js">
</script>
    
</body>
</html>