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
        .container {
            background-color: var(--theme-primary);
            width: 100%;
            max-width: 450px;
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
            margin-bottom: 1.5rem;
            text-align: center;
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

        /* Links & Buttons */
        a {
            color: var(--theme-accent);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        a:hover {
            color: #2563eb;
            text-decoration: underline;
        }

        button.btn-primary {
            background-color: var(--theme-accent);
            border: 1px solid var(--theme-accent);
            border-radius: 8px;
            padding: 0.5rem 1.25rem;
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
    

        .action-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 0.5rem;
            margin-bottom: 1.5rem;
        }

        #register-bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 1rem;
}

#register-bottom p {
    margin: 0;
    font-size: 0.875rem;
    color: #94a3b8;
    line-height: 1.4;
}

#register-bottom p b {
    color: var(--theme-text-light);
}

#register-bottom button {
    margin: 0;
}

        .register-text {
            color: #ffffff !important;
            font-size: 0.9rem;
            text-align: center;
            margin-top: 1rem;
        }

        .register-text p {
            color: #ffffff;
            display: inline;
        }
    </style>
<body>

    {{-- @dd($errors->any()); --}}

 <div class="container">
        <h2 class="auth-title">Create Account</h2>
        {{-- <p class="auth-subtitle">Sign up to get started with your portal</p> --}}
        
        <form id="registerForm" class="needs-validation" novalidate method="post">
            @csrf

            <!-- Dynamic Alert Slot -->
            <div id="resetAlert" class="alert d-none" role="alert"></div>

            <div class="form-floating mb-3">
                <input type="text" class="form-control" name="name" id="fullname-input" placeholder="FullName">
                <label for="fullname-input">Full Name</label>
                <div class="invalid-feedback error-name"></div>
            </div>

            <div class="form-floating mb-3">
                <input type="email" class="form-control" name="email" id="email-input" placeholder="name@gmail.com">
                <label for="email-input">Email address</label>
                <div class="invalid-feedback error-email"></div>
            </div>
                
            <div class="form-floating mb-3">
                <input type="password" class="form-control" name="password" id="password-input" placeholder="Password">
                <label for="password-input">Password</label>
                <div class="invalid-feedback error-password"></div>
            </div>
            
            <div class="form-floating mb-4">
                <input type="password" class="form-control" name="password_confirmation" id="retypePassword-input" placeholder="Retype Password">
                <label for="retypePassword-input">Retype Password</label>
            </div>
            
            
            <div id="register-bottom">
                <p>
                    <b>Already have an account?</b> <br>
                    <a href="/login">Sign In</a>
                </p>
                <button type="submit" class="btn btn-primary" id="create-btn">Create Account</button>
            </div>
        </form>
    </div>
<script src="https://code.jquery.com/jquery-4.0.0.min.js" integrity="sha256-OaVG6prZf4v69dPg6PhVattBXkcOWQB62pdZ3ORyrao=" crossorigin="anonymous"></script>

<script>
   $(document).ready(function () {
    $('#registerForm').on('submit', function (e) {
        e.preventDefault();
        let form = $(this);
        let formData = form.serialize();

        // Clear previous error messages & alerts
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        $('#alert-message').addClass('d-none').removeClass('alert alert-success alert-danger').text('');

        // Disable button & show loading spinner
        $('#submit-btn').prop('disabled', true);
        $('#btn-spinner').removeClass('d-none');
        $('#btn-text').text('Registering...');

        $.ajax({
            url: '/register',
            type: 'POST',
            data: formData,
            headers: {
                'Accept': 'application/json'
            },
            success: function (response) {
                // Display success message
                $('#resetAlert')
                .removeClass('d-none alert-danger')
                .addClass('alert-success')
                .text(response.message || 'Registered successfully!' );

                form[0].reset();

                // Redirect to the URL sent by the controller response
                if (response.redirect) {
                    setTimeout(function () {
                        window.location.href = response.redirect;
                    }, 2000);
                }
            },
            error: function (xhr) {
                 if (xhr.status === 422) {
               let errors = xhr.responseJSON.errors;

                // Reset previous validation state
                $('.form-control').removeClass('is-invalid');
                $('.invalid-feedback').text('');

                // Loop through errors and output custom messages
                $.each(errors, function (fieldName, errorMessages) {
                    $('[name="' + fieldName + '"]').addClass('is-invalid');
                    $('.error-' + fieldName).text(errorMessages[0]); // Display custom message
                });
                    


            }
        }
           
        });
    });
});
</script>
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