<div class="modal fade" tabindex="-1" role="dialog" id="modal-dashboard-widget-update">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Update Chart</h4>
            </div>
            <div class="modal-body">
                <ul class="text-danger errors-container">
                </ul>
                {!! Form::model($widget, array('route' => array('dashboard.widget.update', $widget->getKey()), 'method'=>'put', "onsubmit"=>"return false", "class" => "form-horizontal sl-form-horizontal", "id"=>"frm-dashboard-widget-update")) !!}
                @include('dashboard.widget.forms.widget')
                {!! Form::close() !!}
            </div>
            <div class="modal-footer text-right">
                <button class="btn btn-primary btn-flat" id="btn-create-dashboard-widget">
                    SAVE
                </button>
                <button data-dismiss="modal" class="btn btn-default btn-flat">CANCEL</button>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        function modalReady(options) {
            $("#btn-create-dashboard-widget").on("click", function () {
                submitEditContent(function (response) {
                    if ($.isFunction(options.callback)) {
                        options.callback(response);
                    }
                });
            })
        }

        function submitEditContent(callback) {
            showLoading();
            $.ajax({
                "url": $("#frm-dashboard-widget-update").attr("action"),
                "method": "put",
                "data": $("#frm-dashboard-widget-update").serialize(),
                "dataType": "json",
                "success": function (response) {
                    hideLoading();
                    if (response.status == true) {
                        if ($.isFunction(callback)) {
                            callback(response);
                        }
                        $("#modal-dashboard-widget-update").modal("hide");
                    } else {
                        alertP("Oops! Something went wrong.", "Unable to update chart, please try again later.");
                    }
                },
                "error": function (xhr, status, error) {
                    hideLoading();
                    if (xhr.responseJSON != null && typeof xhr.responseJSON != 'undefined') {
                        var $errorContainer = $(".errors-container");
                        $errorContainer.empty();
                        $.each(xhr.responseJSON, function (key, error) {
                            $.each(error, function (index, message) {
                                $errorContainer.append(
                                        $("<li>").text(message)
                                );
                            })
                        });
                    } else {
                        describeServerRespondedError(xhr.status);
                    }
                }
            });
        }
    </script>
</div>
