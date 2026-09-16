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
   background-color:#1e003c;
    width: 400px;
    height: auto;
    border: 2px solid  ;
    padding: 20px;
    display: flex;
    flex-direction: column;

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
    
    color: #f3f0f6;
    display: inline;
    margin: 0;
    padding: 0;
}
#create-btn{
    float: right;
}
   

</style>
<body>
<div class="container">
     <!-- Alert Box for AJAX Responses -->
            <div id="alert-container"></div>
        <form class="needs-validation" id="forgotPasswordForm" novalidate >

        <div class="mb-3">

            <label for="email">
                To Reset your Password
            </label>

            <input
                type="email"
                class="form-control"
                id="email"
                name="email"
                placeholder="Enter your Email ID"
                required
            >

           <div class="invalid-feedback error-email"></div>
            <div>
                <button
                type="submit"
                class="btn btn-primary mt-3 "
                id="resent-btn"
            >
                Send
            </button>
                <button
                type="button"
                class="btn btn-primary mt-3  "
                id="back-btn"
            > Go back    </button>
            
            </div>

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