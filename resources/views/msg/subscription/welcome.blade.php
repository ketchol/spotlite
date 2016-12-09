<div class="modal-header" style="border-bottom: 0">
    <div class="row">
        <div class="col-sm-12">
            <h3 class="text-center">
                <img src="{{asset('build/images/logo-fixed-2.png')}}" style="width: 30%;">
            </h3>
            <h2 class="text-center">
                Welcome!
            </h2>
            <p class="text-center">
                We want you to get the most out of SpotLite. To kick-off we want to personalise your experience.
            </p>
            <p class="text-center">
                Samply answer the questions and follow the journey. Enjoy!
            </p>

        </div>
    </div>
    <div class="row steps-container">
        <div class="col-sm-12">
            <ul class="steps">
                <li data-step="1" class="active">
                    <span class="step">
                        <i class="fa fa-check"></i>
                    </span>
                    <div class="title">
                        <div>
                            STEP 1
                        </div>
                        <div class="description">
                            Let's kick off by creating your Dashboard
                        </div>
                    </div>
                </li>
                <li data-step="2">
                    <span class="step">&nbsp;</span>
                    <div class="title">
                        <div>
                            STEP 2
                        </div>
                        <div class="description">
                            Set up product prices you want to track
                        </div>
                    </div>
                </li>
                <li data-step="3">
                    <span class="step">&nbsp;</span>
                    <div class="title">
                        <div>
                            STEP 3
                        </div>
                        <div class="description">
                            Set notifications
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12 ">
            <div class="jumbotron set-password-form-container">
                <p class="welcome-form-heading">
                    <strong>STEP 1:</strong> Let's start by looking at what industry and company type you want to track:
                </p>

                <ul class="text-danger errors-container" id="welcome-errors-container">
                </ul>
                <form action="{{route('profile.init_update')}}" id="init-update-form"
                      onsubmit="submitInitUpdate(); return false; ">
                    <div class="form-group">
                        <select class="form-control" name="industry">
                            <option value="">What industry are you wanting to track?</option>
                            @if(isset($industries))
                                @foreach($industries as $industry)
                                    <option value="{{$industry}}">{{$industry}}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="form-group">
                        <select name="company_type" class="form-control">
                            <option value="">What Company Type?</option>
                            <option value="Retailer">Retailer</option>
                            <option value="Brand">Brand</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <input type="text" name="company_url" class="form-control"
                               placeholder="Company URL? e.g. http://www.example.com">
                    </div>
                    <div class="form-group text-center">
                        <button class="btn btn-primary btn-flat" type="submit">Create Dashboard</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        function submitInitUpdate() {
            cleanErrorMessage();
            showLoading();
            $.ajax({
                "url": $("#init-update-form").attr("action"),
                "method": "put",
                "data": $("#init-update-form").serialize(),
                "success": function (response) {
                    hideLoading();
                    if (response.status == true) {
                        showLoading();
                        window.location.href = "{{route('dashboard.index')}}";
                    } else {
                        if (typeof response.errors != 'undefined') {
                            var $errorContainer = $("#welcome-errors-container");
                            $errorContainer.empty();
                            $.each(response.errors, function (index, error) {
                                $errorContainer.append(
                                        $("<li>").text(error)
                                );
                            });
                        } else {
                            alertP("Error", "Unable to perform initial set up, please try again later.");
                        }
                    }
                },
                "error": function (xhr, status, error) {
                    hideLoading();
                    describeServerRespondedError(xhr.status);
                }
            });
        }

        function cleanErrorMessage() {
            $("#welcome-errors-container").empty();
        }
    </script>
</div>