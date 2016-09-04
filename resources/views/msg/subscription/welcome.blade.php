<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span>
    </button>
    <!--<h4 class="modal-title" id="myModalLabel">Welcome to Composer!</h4>-->
    <div class="row">
        <div class="col-sm-12">
            <p class="text-center">
                <img src="{{asset('build/images/logo-fixed-2.png')}}" style="width: 40%; padding-bottom: 20px">
            </p>

            <h2 class="text-center">
                Welcome to SpotLite!
            </h2>

            <p class="text-center" style="padding: 20px 60px 20px 60px">
                Spotlite is an affordable and easy-to-use price tracking solution
                specifically designed for retailers and brands.
            </p>
            @if(isset($apiSubscription))
                <p>
                    You have subscribed into {{$apiSubscription->product->name}} plan.
                </p>
                <p>
                    {{dump($apiSubscription)}}
                </p>
            @endif
            <p class="text-center">
                [Simple introduction, tutorial or description here]
            </p>
            <p class="text-center">
                [If we are giving tutorial to user, we might need to following two buttons.]
            </p>

            &nbsp;
            <p class="text-center" style="padding: 20px">
                <button class="btn btn-default" title="Close this popup" data-dismiss="modal">No, thanks
                </button>
                <button class="btn btn-success" title="Close this popup and start the tour" id="start_tour"
                        data-dismiss="modal" onclick="return false;">Start the tour
                </button>
            </p>
        </div>
    </div>
</div>