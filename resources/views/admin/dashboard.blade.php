

@extends('layouts.admin')

@section('content')
    <!--  MAIN CONTENT  -->

  <div class="container-fluid py-4">

    <!-- Small Box Cards Row -->
    <div class="row g-3 mb-4">
        
        <!-- 1. Total Registered Users -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm bg-primary text-white h-100">
                <div class="card-body d-flex align-items-center justify-content-between p-3">
                    <div>
                        <span class="text-white-50 text-uppercase fw-semibold small">{{__('messages.Registered Users')}}</span>
                        <h3 class="fw-bold mb-0 mt-1" id="kpiTotalUsers">0</h3>
                    </div>
                    <div class="fs-1 text-white-50">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
               
            </div>
        </div>

        <!-- 2. Total Events -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm bg-info text-white h-100">
                <div class="card-body d-flex align-items-center justify-content-between p-3">
                    <div>
                        <span class="text-white-50 text-uppercase fw-semibold small">{{__('messages.Total Events')}}</span>
                        <h3 class="fw-bold mb-0 mt-1" id="kpiTotalEvents">0</h3>
                    </div>
                    <div class="fs-1 text-white-50">
                        <i class="bi bi-calendar-event-fill"></i>
                    </div>
                </div>
                
            </div>
        </div>

        <!-- 3. Total Bookings -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm bg-warning text-dark h-100">
                <div class="card-body d-flex align-items-center justify-content-between p-3">
                    <div>
                        <span class="text-dark-50 text-uppercase fw-semibold small">{{__('messages.Total Bookings')}}</span>
                        <h3 class="fw-bold mb-0 mt-1" id="kpiTotalBookings">0</h3>
                    </div>
                    <div class="fs-1 text-dark-50">
                        <i class="bi bi-ticket-perforated-fill"></i>
                    </div>
                </div>
               
            </div>
        </div>

        <!-- 4. Total Revenue -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm bg-success text-white h-100">
                <div class="card-body d-flex align-items-center justify-content-between p-3">
                    <div>
                        <span class="text-white-50 text-uppercase fw-semibold small">{{__('messages.Total Revenue')}}</span>
                        <h3 class="fw-bold mb-0 mt-1">₹<span id="kpiTotalRevenue">0</span></h3>
                    </div>
                    <div class="fs-1 text-white-50">
                        <i class="bi bi-currency-rupee"></i>
                    </div>
                </div>
                
            </div>
        </div>

    </div>

</div>
  
</div>

<script src="https://code.jquery.com/jquery-4.0.0.min.js" integrity="sha256-OaVG6prZf4v69dPg6PhVattBXkcOWQB62pdZ3ORyrao=" crossorigin="anonymous"></script>

<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        }
    });

    $(document).ready(function() {
    function loadDashboardKPIs() {
        $.ajax({
            url: '/api/admin/dashboard/report',
            type: 'GET',
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            success: function(response) {
                console.log(response);
                
                // Populate elements dynamically
                $('#kpiTotalUsers').text(response.total_users || 0);
                $('#kpiTotalEvents').text(response.total_events || 0);
                $('#kpiTotalBookings').text(response.total_bookings || 0);
                $('#kpiTotalRevenue').text(parseFloat(response.total_revenue || 0).toLocaleString('en-IN'));
            },
            error: function(xhr) {
                console.error('Failed to load dashboard indicators:', xhr);
}
        });
    }

    // Initial Load
    loadDashboardKPIs();
});

   
</script>

@endsection
   








