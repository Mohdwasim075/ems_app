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
    <title>LogIn</title>
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
            <form class=" needs-validation" id="loginForm" method="post" >
             <div class="form-floating mb-3">
                    <input  type="email" class="form-control " name="email" id="email-input" :value="{{old('email')}}" placeholder="name@example.com">
                    <label  for="email-input" >Email address</label>
                    <div class="invalid-feedback error-email"></div>
            </div>
            <div class="form-floating mb-3">
                    <input type="password" class="form-control" name= "password" id="password-input" value="{{old('password')}}" placeholder="Password" >
                    <label for="password-input">Password</label>
                     <div class="invalid-feedback error-password"></div>



            </div>
            

            <div >
                <span><a href="/forgot-password" style="text-decoration: underline;">Forgot password</a>
                    <button type="submit" class="btn btn-primary" id="login-btn" >Log in</button>
                </span><br>
            </div>
            
           <span><p> <b>Don't have an account?</b></p> <a href="/register">Register</a></span><p></p>
            </form>
        </div>

   
<script src="https://code.jquery.com/jquery-4.0.0.min.js" integrity="sha256-OaVG6prZf4v69dPg6PhVattBXkcOWQB62pdZ3ORyrao=" crossorigin="anonymous"></script>
<script>
     $('#loginForm').on('submit', function(event) {

    event.preventDefault();

    $.ajax({

        url: '/login',

        method: 'POST',
    

        data: $(this).serialize(),

        success: function(response) {

            console.log(response);

            if (response.user === 'admin') { 
                window.location.href = '/admin/dashboard'; 
            } else { 
                window.location.href = '/'; 
            }

        },

         error: function(xhr) {
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
            // }elseif(xhr.status === 401){
            //      $('.form-control').removeClass('is-invalid');
            //      $('.invalid-feedback').text('');

            //      $('error')


             }
        }

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