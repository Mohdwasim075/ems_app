@extends('layouts.attendee')

@section('title', __('messages.Profile'))

@section('content')




<div class="container my-5">
  <div class="row g-4">
    
    <!-- Column 1: User Profile Details -->
    <div class="col-12 col-md-6">
      <div class="card h-100 shadow-sm border-0">
        <div class="card-header bg-white  py-3 border-bottom">
          <h5 class="card-title mb-0 fw-semibold text-primary">{{ __('messages.Profile Details') }}</h5>
        </div>
        <div class="card-body">
          <form id="profileForm">
            <!-- Name -->
            <div class="mb-3">
              <label for="fullName" class="form-label">{{ __('messages.Full Name') }}</label>
              <input type="text" class="form-control" id="fullName" name="name" :value={{old('name')}}>
              <div class="invalid-feedback error-name"></div>
            </div>

            <!-- Email & Phone -->
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label for="email" class="form-label">{{ __('messages.Email Address') }}</label>
                <input type="email" class="form-control" id="email" name="email" :value={{old('email')}}>
                <div class="invalid-feedback error-email"></div>
              </div>
              <div class="col-md-6">
                <label for="phone" class="form-label">{{ __('messages.Phone Number') }}</label>
                <input type="tel" class="form-control" id="phone" name="phone_number" :value={{old('phone_number')}}>
                <div class="invalid-feedback error-phone_number"></div>
              </div>
            </div>

            <!-- Address Details: City, State, Zip -->
            <div class="row g-3 mb-3">
              <div class="col-md-5">
                <label for="city" class="form-label">{{ __('messages.City') }}</label>
                <input type="text" class="form-control" id="city" name="city" :value={{old('city')}}>
              </div>
              <div class="col-md-4">
                <label for="state" class="form-label">{{ __('messages.State') }}</label>
                <input type="text" class="form-control" id="state" name="state" :value={{old('city')}}>
              </div>
              <div class="col-md-3">
                <label for="zip" class="form-label">{{ __('messages.ZIP Code') }}</label>
                <input type="text" class="form-control" id="zip" name="zip" :value={{old('zip')}}>
              </div>
            </div>

            <div class="text-end mt-4">
              <button id="updateProfile" type="submit" class="btn btn-primary px-4">{{ __('messages.Update Profile') }}</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Column 2: Password Reset -->
    <div class="col-12 col-md-6">
      <div class="card h-100 shadow-sm border-0">
        <div class="card-header bg-white py-3 border-bottom">
          <h5 class="card-title mb-0 fw-semibold text-danger">{{ __('messages.Reset Password') }}</h5>
        </div>
        <div class="card-body d-flex flex-column justify-content-between">
          <form id="passwordResetForm">

              <!-- old Password -->
            <div class="mb-3">
              <label for="current_password" class="form-label">{{ __('messages.Old Password') }}</label>
              <input type="password" class="form-control" id="current_password" name="current_password" >
                <div class="invalid-feedback error-current_password"></div>
            </div>

            <!-- New Password -->
            <div class="mb-3">
              <label for="password" class="form-label">{{ __('messages.New Password') }}</label>
              <input type="password" class="form-control" id="password" name="password" >
                <div class="invalid-feedback error-password"></div>
            </div>

            <!-- Confirm Password -->
            <div class="mb-3">
              <label for="password_confirmation" class="form-label">{{ __('messages.Confirm Password') }}</label>
              <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" >
                <div class="invalid-feedback error-password_confirmation"></div>
            </div>

            <div class="text-end mt-4">
              <button type="submit" class="btn btn-danger px-4">{{ __('messages.Change Password') }}</button>
            </div>
          </form>
        </div>
      </div>
    </div>

  </div>
</div>

<script src="https://code.jquery.com/jquery-4.0.0.min.js" integrity="sha256-OaVG6prZf4v69dPg6PhVattBXkcOWQB62pdZ3ORyrao=" crossorigin="anonymous"></script>


<script>

$(document).ready(function () {

    

    $.ajax({

        url: '/api/profile',

        method: 'GET',

        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        'Accept': 'application/json'
    },

        success: function (response) {

            console.log('Profile:', response);
            
            const user = response.user;


            $('#fullName').val(user.name);
            $('#email').val(user.email);
            $('#phone').val(user.phone_number);
            $('#city').val(user.city);
            $('#state').val(user.state);
            $('#zip').val(user.zip);


        },

        error: function (xhr) {

            console.log('Status:', xhr.status);
            console.log('Error:', xhr.responseJSON);

            if (xhr.status === 401) {

                localStorage.removeItem('token');

                window.location.href = '/login';
            }

        }

    });

     $('#profileForm').on('submit', function(e) {
    e.preventDefault();

    // Collect all form inputs including files
    let formData = new FormData(this);
    
    // Spoof the PATCH method for Laravel
    formData.append('_method', 'PATCH');

    $.ajax({
        url: '/api/profile/update',
        type: 'POST', // Keep as POST so multipart/form-data processes correctly
        data: formData,
        contentType: false, // Required for FormData/File uploads
        processData: false, // Required for FormData/File uploads
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        },
        success: function(response) {
            alert('{{ __('messages.Profile updated successfully!') }}');
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
            }
        }
    });
});

  $('#passwordResetForm').on('submit', function(e) {
    e.preventDefault();

    // Collect all form inputs including files
    let formData = new FormData(this);
    
    // Spoof the PATCH method for Laravel
    formData.append('_method', 'PATCH');

    $.ajax({
        url: '/api/password/update',
        type: 'POST', // Keep as POST so multipart/form-data processes correctly
        data: formData,
        contentType: false, // Required for FormData/File uploads
        processData: false, // Required for FormData/File uploads
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        },
        success: function(response) {
            alert('{{ __('messages.Password updated successfully!') }}');
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
            }
        }
    });
});



});

</script>

@endsection