var tour;

$(function () {
    tour = new Tour({
        steps: [
            {
                element: ".add-category-container",
                title: "ADD CATEGORY",
                content: "Start with naming the Category. You can add multiple Categories. More Category examples: Running Shoes, Eye Liner or Books.",
                placement: "top"
            },
            {
                element: ".add-product-container:first",
                title: "ADD PRODUCT",
                content: "Now add Products within the Category. You can add multiple products. More product examples, considering the Running Shoes Category: Nike Zoom, Mizuno Wave Raider.",
                placement: "top"
            },
            {
                element: ".add-site-container:first",
                title: "ADD PRODUCT PAGE URL",
                content: "Last step of Products set up! Just add each Product Page URLs you want to track. Go into the webpage where the product price is, copy the whole URL and paste it here.",
                placement: "top"
            },
            {
                element: ".btn-delete-category:first",
                title: "DELETE",
                content: "You can delete Categories & Products easily through here.",
                placement: "bottom"
            },
            {
                element: ".btn-report:first",
                title: "EMAIL REPORTS",
                content: "Set your Email reports at your preferred frequency and time. We'll deliver the report directly to your inbox!",
                placement: "bottom"
            },
            {
                element: ".btn-chart:first",
                title: "CHARTS",
                content: "Create Category & Product Charts based on a period (e.g. month) and granularity (e.g. day). You can also add it to your Dashboard to easily visualise past and current price trends. Once you've added a Chart to your Dashboard, it will be automatically updated if any price change occurs.",
                placement: "left",
                path: "/product"
            },
            {
                element: "#btn-add-new-dashboard",
                title: "YOUR DASHBOARDS",
                content: "You can view your Dashboards or add a new one anytime through the menu navigation.",
                onShown: function (tour) {
                    $(".tour-step-background").append(
                        $("<div>").css({
                            "height": "100%",
                            "padding": "20px"
                        }).append(
                            $("<a>").addClass("tour-step-backdrop tour-tour-element tour-tour-0-element").attr({
                                "href": "#",
                                "onclick": "showAddDashboardForm(this); return false;",
                                "id": "btn-add-new-dashboard"
                            }).css({
                                "background-color": "#7ed0c0",
                                "height": "100%",
                                "display": "block",
                                "padding": "10px",
                                "color": "#fff"
                            }).append(
                                $("<i>").addClass("fa fa-plus"),
                                $("<span>").text(" ADD A NEW DASHBOARD")
                            )
                        )
                    )
                }
            },
            {
                element: "#btn-dropdown-manage-dashboard",
                title: "MANAGE DASHBOARD",
                content: "This is your Default Dashboard with Sample Data. You can Add a Chart, Rename or Delete a Dashboard through this menu.",
                path: "/dashboard?tour=product"
            },
            {
                element: ".widget-container:first .box-tools",
                title: "CHART CONFIGURATION",
                content: "You can view information about this Chart, download it, edit or delete it through these icons.",
                placement: "bottom"
            },
            {
                element: ".widget-container:first .box-tools .btn-edit-widget",
                title: "EDIT CHART",
                content: "You can change the Chart type category and product, time span, resolution period and name.",
                placement: "bottom"
            },
            {
                element: ".add-chart-to-dashboard-placeholder",
                title: "ADD CHART TO DASHBOARD",
                content: "You can choose your own Chart characteristic and add it to the Dashboard",
                placement: "top"
            },
            {
                element: ".lnk-drop-down-need-help",
                title: "WE'RE HERE FOR YOU",
                content: "You can always check our FAQ, Tutorials or contact us in case you have questions or concerns.",
                placement: "bottom",
                onShown: function () {
                    $(".tour-step-background").append(
                        $("<div>").css({
                            "height": "100%",
                            "padding": "15px"
                        }).append(
                            $("<a>").addClass("dropdown-toggle lnk-drop-down-need-help tour-step-backdrop tour-tour-element tour-tour-13-element").attr({
                                "href": "/product",
                                "data-toggle": "dropdown",
                                "aria-expanded": "false"
                            }).css({
                                "background-color": "#7ed0c0",
                                "height": "100%",
                                "display": "block",
                                "padding": "10px",
                                "color": "#fff"
                            }).append(
                                '&nbsp; <i class="fa fa-question-circle"></i>&nbsp;<i class="fa fa-caret-down"></i> &nbsp;&nbsp;&nbsp;'
                            )
                        )
                    )
                }
            }
        ],
        backdrop: true,
        storage: window.localStorage,
        backdropPadding: 20
    });
    tour.init();
});

function startTour() {
    tour.restart();
}

function setTourVisited() {
    $.ajax({
        "url": "preference/PRODUCT_TOUR_VISITED/1",
        "method": "put",
        "dataType": "json",
        "success": function (response) {

        },
        "error": function (xhr, status, error) {

        }
    })
}

function tourNotYetVisit() {
    return user.preferences.PRODUCT_TOUR_VISITED != 1 && user.preferences.ALL_TOUR_VISITED != 1
}