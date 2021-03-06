function btnDeleteCategoryOnClick(el) {
    deletePopup("Delete Category", "Are you sure you want to delete this Category?",
        "By deleting this category, you will lose the following:",
        [
            "All Products you have added",
            "All URLs you have added",
            "All Category and Product Charts generated, including any Charts displayed on your Dashboards",
            "All Category Reports generated",
            "This Category's pricing information tracked to date"
        ],
        {
            "affirmative": {
                "text": "DELETE",
                "class": "btn-danger btn-flat",
                "dismiss": true,
                "callback": function () {
                    var $form = $(el).closest(".frm-delete-category");
                    showLoading();
                    $.ajax({
                        "url": $form.attr("action"),
                        "method": "delete",
                        "data": $form.serialize(),
                        "dataType": "json",
                        "success": function (response) {
                            hideLoading();
                            if (response.status == true) {
                                gaDeleteCategory();
                                alertP("Delete Category", "Category has been deleted.");
                                $(el).closest(".category-wrapper").remove();
                                updateUserProductCredit();
                            } else {
                                if (typeof response.errors != 'undefined') {
                                    var errorMessage = "";
                                    $.each(response.errors, function (index, error) {
                                        errorMessage += error + " ";
                                    });
                                    alertP("Oops! Something went wrong.", errorMessage);
                                } else {
                                    alertP("Oops! Something went wrong.", "Unable to delete category, please try again later.");
                                }
                            }
                        },
                        "error": function (xhr, status, error) {
                            hideLoading();
                            describeServerRespondedError(xhr.status);
                        }
                    })
                }
            },
            "negative": {
                "text": "CANCEL",
                "class": "btn-default btn-flat",
                "dismiss": true
            }
        });
}

function appendCreateProductBlock(el) {
    $(el).find(".add-item-label").slideUp();
    $(el).find(".add-item-controls").slideDown();
}

function appendUpgradeForCreateProductBlock(el) {
    $(el).find(".add-item-label").slideUp();
    $(el).find(".upgrade-for-add-item-controls").slideDown();
}

function cancelAddProduct(el) {
    $(el).closest(".add-item-block").find(".add-item-label").slideDown();
    $(el).closest(".add-item-block").find(".add-item-controls").slideUp();
    $(el).closest(".add-item-block").find(".add-item-controls input").val("");
}


function btnAddProductOnClick(el) {
    showLoading();
    var $categoryWrapper = $(el).closest(".category-wrapper");
    $.ajax({
        "url": "/product",
        "method": "post",
        "data": {
            "category_id": $(el).closest(".category-wrapper").attr('data-category-id'),
            "product_name": $categoryWrapper.find(".frm-store-product").find(".txt-product-name").val(),
            "meta": {
                "brand": $categoryWrapper.find(".frm-store-product").find(".txt-product-meta-brand").val(),
                "supplier": $categoryWrapper.find(".frm-store-product").find(".txt-product-meta-supplier").val(),
                "sku": $categoryWrapper.find(".frm-store-product").find(".txt-product-meta-sku").val(),
                "cost_price": $categoryWrapper.find(".frm-store-product").find(".txt-product-meta-cost-price").val()
            }
        },
        "dataType": "json",
        "success": function (response) {
            hideLoading();
            if (response.status == true) {
                cancelAddProduct($(el).closest(".category-wrapper").find(".btn-cancel-add-product"));
                gaAddProduct();
                if (response.product != null) {
                    showLoading();
                    loadSingleProduct(response.product.urls.show, function (html) {
                        hideLoading();

                        var $html = $("<div>").append(html);
                        $html.find(".btn-collapse.collapsed").removeClass("collapsed").attr("aria-expanded", "true");
                        $html.find(".collapsible-product-div").addClass("in").attr({
                            "aria-expanded": "true"
                        });
                        html = $html.html();
                        $(el).closest(".tbl-category").find(".collapsible-category-div").prepend(html);
                        updateProductOrder($(el).closest(".category-wrapper").attr('data-category-id'));
                        updateProductEmptyMessage();
                        updateUserProductCredit();
                    });
                } else {
                    alertP("Create product", "product has been created. But encountered error while page being loaded.", function () {
                        window.location.reload();
                    });
                }
            } else {
                alertP("Oops! Something went wrong.", 'Unable to add product, please try again later.');
            }
        },
        "error": function (xhr, status, error) {
            hideLoading();
            if (xhr.responseJSON != null && typeof xhr.responseJSON != 'undefined') {
                var errorMsg = "";
                $.each(xhr.responseJSON, function (key, error) {
                    $.each(error, function (index, message) {
                        errorMsg += message + " ";
                    })
                });
                alertP("Oops! Something went wrong.", errorMsg);
            } else {
                describeServerRespondedError(xhr.status);
            }
        }
    })
}

function loadSingleProduct(url, callback) {
    $.ajax({
        "url": url,
        "method": "get",
        "success": callback,
        "error": function (xhr, status, error) {
            hideLoading();
            describeServerRespondedError(xhr.status);
        }
    });
}


function toggleEditCategoryName(el) {
    var $tbl = $(el).closest(".tbl-category");
    if ($tbl.find(".btn-edit-category").hasClass("editing")) {
        $tbl.find(".btn-edit-category").removeClass("editing").show();
        $tbl.find(".category-name-link").show();
        $tbl.find(".frm-edit-category").hide();
    } else {
        $tbl.find(".btn-edit-category").addClass("editing").hide();
        $tbl.find("input.category-name").val($tbl.find(".category-name-link").text());
        $tbl.find(".category-name-link").hide();
        $tbl.find(".frm-edit-category").show();
        $tbl.find(".frm-edit-category .category-name").focus();
    }
}

function cancelEditCategoryName(el, event) {
    if (typeof event == 'undefined' || typeof event.keyCode == 'undefined') {
        toggleEditCategoryName(el);
    } else if (event.keyCode == 27) {
        $(el).blur();
    }
}

function txtCategoryOnBlur(el) {
    setTimeout(function () {
        if (!$(":focus").is($(el).siblings("span").find("button"))) {
            cancelEditCategoryName(el);
        }
    }, 10);
}

function submitEditCategoryName(el) {
    showLoading();
    $.ajax({
        "url": $(el).attr("action"),
        "method": "put",
        "data": $(el).serialize(),
        "dataType": "json",
        "success": function (response) {
            hideLoading();
            if (response.status == true) {
                gaEditCategory();
                alertP("Update Category", "Category name has been updated.");
                $(el).siblings(".category-name-link").text($(el).find(".category-name").val()).show();
                $(el).hide();
                $(el).closest(".tbl-category").find(".btn-edit-category.editing").removeClass("editing").show();
            } else {
                alertP("Oops! Something went wrong.", 'Unable to update category name, please try again later.');
            }
        },
        "error": function (xhr, status, error) {
            hideLoading();
            if (xhr.responseJSON != null && typeof xhr.responseJSON != 'undefined') {
                var errorMsg = "";
                $.each(xhr.responseJSON, function (key, error) {
                    $.each(error, function (index, message) {
                        errorMsg += message + " ";
                    })
                });
                alertP("Oops! Something went wrong.", errorMsg);
            } else {
                describeServerRespondedError(xhr.status);
            }
        }
    });
}

function assignProductOrderNumber(category_id) {
    $(".category-wrapper").filter(function () {
        return $(this).attr("data-category-id") == category_id;
    }).find(".product-wrapper").each(function (index) {
        $(this).attr("data-order", index + 1);
    });
}

function updateProductOrder(category_id) {
    assignProductOrderNumber(category_id);
    var orderList = [];
    $(".category-wrapper").filter(function () {
        return $(this).attr("data-category-id") == category_id;
    }).find(".product-wrapper").filter(function () {
        return !$(this).hasClass("gu-mirror");
    }).each(function () {
        if ($(this).attr("data-product-id")) {
            var productId = $(this).attr("data-product-id");
            var productOrder = parseInt($(this).attr("data-order"));
            orderList.push({
                "product_id": productId,
                "product_order": productOrder
            });
        }
    });
    $.ajax({
        "url": "product/order",
        "method": "put",
        "data": {
            "order": orderList
        },
        "dataType": "json",
        "success": function (response) {
            if (response.status == false) {
                if (typeof response.errors != 'undefined') {
                    var errorMessage = "";
                    $.each(response.errors, function (index, error) {
                        errorMessage += error + " ";
                    });
                    alertP("Oops! Something went wrong.", errorMessage);
                } else {
                    alertP("Oops! Something went wrong.", "Unable to update product order, please try again later.");
                }
            } else {
                gaMoveProduct();
            }
        },
        "error": function (xhr, status, error) {
            describeServerRespondedError(xhr.status);
        }
    })
}

function showCategoryChart(url) {
    showLoading();
    $.ajax({
        "url": url,
        "method": "get",
        "success": function (html) {
            hideLoading();
            var $modal = $(html);
            $modal.modal();
            $modal.on("shown.bs.modal", function () {
                if ($.isFunction(modalReady)) {
                    modalReady()
                }
            });
            $modal.on("hidden.bs.modal", function () {
                $(this).remove();
            });
        },
        "error": function (xhr, status, error) {
            hideLoading();
            describeServerRespondedError(xhr.status);
        }
    });
}


function showCategoryReportTaskForm(el) {
    showLoading();
    $.ajax({
        "url": $(el).closest(".category-wrapper").attr("data-report-task-link"),
        "method": "get",
        "success": function (html) {
            hideLoading();
            var $modal = $(html);
            $modal.modal();
            $modal.on("shown.bs.modal", function () {
                if ($.isFunction(modalReady)) {
                    modalReady({
                        "updateCallback": function (response) {
                            if (response.status == true) {
                                $(el).find("i").removeClass().addClass("fa fa-envelope ico-report-enabled");
                            }
                        },
                        "deleteCallback": function (response) {
                            if (response.status == true) {
                                $(el).find("i").removeClass().addClass("fa fa-envelope-o");
                            }
                        }
                    })
                }
            });
            $modal.on("hidden.bs.modal", function () {
                $("#modal-report-task-category").remove();
            });
        },
        "error": function (xhr, status, error) {
            hideLoading();
            describeServerRespondedError(xhr.status);
        }
    });
}


function updateUserSiteUsage(el) {
    $.ajax({
        "url": $(el).closest(".category-wrapper").attr("data-get-site-usage-link"),
        "method": "get",
        "dataType": "json",
        "success": function (response) {
            if (response.status == true) {
                var usage = response.usage;
                var $categoryWrapper = $(el).closest(".category-wrapper")
                $categoryWrapper.find(".lbl-site-usage").text(usage);
            }
        },
        "error": function (xhr, status, error) {
            describeServerRespondedError(xhr.status);
        }
    })
}