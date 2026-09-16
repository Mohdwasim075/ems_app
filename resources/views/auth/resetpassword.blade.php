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
html{
    scroll-behavior: smooth;
}

body{
    background-image:url('https://cdn.pixabay.com/photo/2022/04/18/17/26/artwork-7141119_640.png') ;
    background-size: cover;


    margin: 0;
    height: 100vh;
    display: flex;
    justify-content: center; /* horizontal */
    align-items: center;     /* vertical */
    }



.container{
   /* background-color:#1e003c;
    width: auto;
    height: auto;
    border: 2px solid  ;
    padding: 20px;
    display: flex;
    flex-direction: column; */

}
.incorrect label{
    background-color: #ff0000;
}

form{
    display: flex;
    flex-direction: column;
}
a {
    text-decoration: underline;
    color: #dcd8e0;
}
.center{
    text-align: center;
}
a:active {
  color: ghostwhite;
}
button:hover{
    background-color: #ACBFA4;
    color: #A03A13;
}
/* input {
  border: 2px solid blue;
  border-radius: 8px;
  padding: 10px;
} */
button {
  border: 1px solid blue;
  border-radius: 8px;
  padding: 10px;
}


#login-btn, #resent-btn {
    float : right;

}
label , p{
    
    color: black;
    display: inline;
    margin: 0;
    padding: 0;
}
#create-btn{
    float: right;
}
   

</style>
<body>
<div class="container d-flex justify-content-center align-items-center min-vh-100">

    <div class="card shadow-sm border-0" style="width: 100%; max-width: 450px;">

        <div class="card-body p-4">

            <div class="text-center mb-4">
                <h3 class="fw-bold mb-2">Reset Password</h3>
                <p class="text-muted mb-0">
                    Enter your new password below.
                </p>
            </div>

            <!-- Alert -->
            <div id="resetAlert" class="alert d-none" role="alert"></div>

            <form id="resetPasswordForm" novalidate>

                @csrf

                <!-- Reset Token -->
                <input
                    type="hidden"
                    name="token"
                    value="{{ $token }}"
                >

                <!-- Email -->
                <div class="mb-3">
                    <label for="resetEmail" class="form-label fw-semibold">
                        Email Address
                    </label>

                    <input
                        type="email"
                        class="form-control"
                        id="resetEmail"
                        name="email"
                        value="{{ $email }}"
                        placeholder="Enter your email"
                        
                    >

                    <div class="invalid-feedback error-email"></div>
                </div>

                <!-- New Password -->
                <div class="mb-3">
                    <label for="resetPassword" class="form-label fw-semibold">
                        New Password
                    </label>

                    <input
                        type="password"
                        class="form-control"
                        id="resetPassword"
                        name="password"
                        placeholder="Enter new password"
                        
                    >

                    <div class="invalid-feedback error-password"></div>
                </div>

                <!-- Confirm Password -->
                <div class="mb-4">
                    <label for="resetPasswordConfirmation"
                           class="form-label fw-semibold">
                        Confirm Password
                    </label>

                    <input
                        type="password"
                        class="form-control"
                        id="resetPasswordConfirmation"
                        name="password_confirmation"
                        placeholder="Confirm your new password"
                        
                    >

                    <div class="invalid-feedback error-password_confirmation"></div>
                </div>

                <!-- Submit -->
                <button
                    type="submit"
                    class="btn btn-primary w-100"
                    id="resetPasswordBtn"
                >
                    <span id="resetBtnText">
                        Reset Password
                    </span>

                    <span
                        id="resetBtnSpinner"
                        class="spinner-border spinner-border-sm d-none"
                        role="status"
                    ></span>
                </button>

            </form>

        </div>

    </div>

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