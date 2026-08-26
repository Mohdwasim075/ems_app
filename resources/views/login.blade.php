@extends('layouts.app')

@section('title', 'Home')

@section('content')

<form action="" method="post">
        <div class="container mt-5">
             <div>
            <label for="email">Email</label>
            <input type="text">
        </div>
         <div>
            <label for="password">Password</label>
            <input type="text">
        </div>
         <div>
            <label for="retype_password">Confirm password</label>
            <input type="text">
        </div>
        <button>Log In</button>


        </div>
       
     </form>


@endsection