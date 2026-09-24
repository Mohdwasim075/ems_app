<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
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
        .auth-card {
            background-color: var(--theme-primary);
            width: 100%;
            max-width: 440px;
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
            margin-bottom: 0.25rem;
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
        button.btn-primary {
            background-color: var(--theme-accent);
            border: 1px solid var(--theme-accent);
            border-radius: 8px;
            padding: 0.75rem 1.25rem;
            font-weight: 600;
            color: #ffffff;
            transition: all 0.2s ease;
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3);
        }

        button.btn-primary:hover {
            background-color: #2563eb;
            border-color: #2563eb;
            box-shadow: 0 6px 8px -1px rgba(59, 130, 246, 0.4);
        }
    </style>
<body>
<div class="auth-card">
        
        <div class="text-center">
            <h3 class="auth-title">Reset Password</h3>
            <p class="auth-subtitle">Enter your new password below</p>
        </div>

        {{-- <!-- Alert Box -->
        <div id="resetAlert" class="alert d-none" role="alert"></div> --}}

        <form id="resetPasswordForm" novalidate method="post">
            @csrf

            <!-- Reset Token -->
            <input type="hidden" name="token" value="{{ $token }}">

            <!-- Email (Read-Only floating input for clarity) -->
            <div class="form-floating mb-3">
                <input
                    type="email"
                    class="form-control"
                    id="resetEmail"
                    name="email"
                    value="{{ $email }}"
                    placeholder="Enter your email"
                >
                <label for="resetEmail">Email Address</label>
                <div class="invalid-feedback error-email"></div>
            </div>

            <!-- New Password -->
            <div class="form-floating mb-3">
                <input
                    type="password"
                    class="form-control"
                    id="resetPassword"
                    name="password"
                    placeholder="Enter new password"
                >
                <label for="resetPassword">New Password</label>
                <div class="invalid-feedback error-password"></div>
            </div>

            <!-- Confirm Password -->
            <div class="form-floating mb-4">
                <input
                    type="password"
                    class="form-control"
                    id="resetPasswordConfirmation"
                    name="password_confirmation"
                    placeholder="Confirm your new password"
                >
                <label for="resetPasswordConfirmation">Confirm Password</label>
                <div class="invalid-feedback error-password_confirmation"></div>
            </div>

            <!-- Submit Button with Spinner Support -->
            <button
                type="submit"
                class="btn btn-primary w-100"
                id="resetPasswordBtn"
            >
                <span id="resetBtnText">Reset Password</span>
                <span
                    id="resetBtnSpinner"
                    class="spinner-border spinner-border-sm d-none ms-1"
                    role="status"
                ></span>
            </button>

        </form>

    </div>
<script src="https://code.jquery.com/jquery-4.0.0.min.js" integrity="sha256-OaVG6prZf4v69dPg6PhVattBXkcOWQB62pdZ3ORyrao=" crossorigin="anonymous"></script>

 <script>
  
     $.ajaxSetup({
        headers: {
         
            'Accept': 'application/json'
        }
    });
    $('#resetPasswordForm').on('submit', function (e) {

    e.preventDefault();

    let form = this;

    // Remove previous validation
    $(form).find('.is-invalid').removeClass('is-invalid');
    $(form).find('.invalid-feedback').text('');

    // Hide previous alert
    $('#resetAlert')
        .addClass('d-none')
        .removeClass('alert-success alert-danger')
        .text('');

    // Basic browser validation
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }

    // Disable button
    $('#resetPasswordBtn').prop('disabled', true);

    $('#resetBtnText').text('Resetting...');

    $('#resetBtnSpinner').removeClass('d-none');

    $.ajax({

        url: '/reset-password',

        type: "POST",

        data: $(form).serialize(),

        success: function (response) {

            console.log(response);

            $('#resetAlert')
                .removeClass('d-none alert-danger')
                .addClass('alert-success')
                .text(response.message);

            // Clear password fields
            $('#resetPassword').val('');
            $('#resetPasswordConfirmation').val('');

            // Redirect after successful reset
            setTimeout(function () {
                window.location.href = '/login';
            }, 2000);
        },

        error: function (xhr) {

            console.log(xhr.responseJSON);

            if (xhr.status === 422) {

                let response = xhr.responseJSON;

                // Laravel validation errors
                if (response.errors) {

                    $.each(response.errors, function (field, messages) {

                        let input = $('[name="' + field + '"]');

                        input.addClass('is-invalid');

                        $('.error-' + field).text(messages[0]);
                    });
                }

                // Password reset token errors
                if (response.message) {

                    $('#resetAlert')
                        .removeClass('d-none alert-success')
                        .addClass('alert-danger')
                        .text(response.message);
                }

            } else {

                $('#resetAlert')
                    .removeClass('d-none alert-success')
                    .addClass('alert-danger')
                    .text('Something went wrong. Please try again.');
            }
        },

        complete: function () {

            $('#resetPasswordBtn').prop('disabled', false);

            $('#resetBtnText').text('Reset Password');

            $('#resetBtnSpinner').addClass('d-none');
        }

    });

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