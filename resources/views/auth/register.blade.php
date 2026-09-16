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

    {{-- @dd($errors->any()); --}}

 <div class="container">
            <form  id="registerForm" class="needs-validation" novalidate>
                @csrf

                 <!-- Alert -->
                 <div id="resetAlert" class="alert d-none" role="alert"></div>
            
                <!-- <input type="text" class="" name="user_name" placeholder="Full name"><br>
                <input type="email" name="user_email" placeholder="Email ID"> <br>
                <input type="text" name="user_password" placeholder="Password"><br>
                <input type="text" name="re-password" placeholder="Retype password"><br> -->
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" name ="name" id="fullname-input" placeholder="FullName" >
                    <label for="fullname-input">Full Name</label>
                   <div class="invalid-feedback error-name"></div>
                    
                </div>
                <div class="form-floating mb-3">
                    <input type="email" class="form-control" name ="email" id="email-input" placeholder="name@gmail.com" >
                    <label for="email-input">Email address</label>
                    <div class="invalid-feedback error-email"></div>
                    </div>
                    
                <div class="form-floating">
                    <input type="password" class="form-control" name="password" id="password-input" placeholder="Password" >
                    <label for="password-input">Password</label>
                    <div class="invalid-feedback error-password"></div>
                </div>
                
                <div class="form-floating mt-2 mb-4">
                    <input type="password" class="form-control" name="password_confirmation" id="retypePassword-input" placeholder="Password" >
                    <label for="retypePassword-input">Retype Password</label>
                </div>
                
                <div id="register-bottom">
                    <span><p><b>Already have an account! </b></p><a href="/login">Sign In </a> &nbsp;<button type='submit' class="btn btn-primary" id="create-btn" >Create </button></span>
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