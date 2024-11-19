$(document).ready(function () {
    var inputElement = null;
    var outputElement = null;
    var isElement = true;

    if (elements_array) {
        var maxDropPadHeight = 0;
        for (var key in elements_array) {
            if (elements_array.hasOwnProperty(key) && key != "step-1") {
                var value = elements_array[key];
                var hyphenIndex = key.lastIndexOf("_");
                var new_key = key.slice(0, hyphenIndex);
                var clone = $("#" + new_key).clone();
                clone.css({
                    position: "absolute",
                });
                clone.attr("id", key);
                clone.addClass("drop_element");
                clone.addClass("drop-pad-element");
                clone.addClass("placedElement");
                clone.removeClass("drop_element");
                clone.removeClass("element");
                $(".task-list").append(clone);
                $(".element_change_output").on("click", attachOutputElement);
                $(".element_change_input").on("click", attachInputElement);
                $(".drop-pad-element").on("click", elementProperties);
                if (elements_data_array.hasOwnProperty(key)) {
                    var element_data = elements_data_array[key];
                    for (var prop_key in element_data) {
                        $("#loader").show();
                        $.ajax({
                            url: getPropertyRequiredPath.replace(
                                ":id",
                                prop_key
                            ),
                            async: false,
                            type: "GET",
                            success: function (response) {
                                if (response.success) {
                                    var property = response.property;
                                    if (property["property_name"] == "Days") {
                                        if (
                                            elements_data_array[key][
                                            prop_key
                                            ] == ""
                                        ) {
                                            clone.find(".item_days").html("0");
                                        } else {
                                            clone
                                                .find(".item_days")
                                                .html(
                                                    elements_data_array[key][
                                                    prop_key
                                                    ]
                                                );
                                        }
                                    } else if (
                                        property["property_name"] == "Hours"
                                    ) {
                                        if (
                                            elements_data_array[key][
                                            prop_key
                                            ] == ""
                                        ) {
                                            clone.find(".item_hours").html("0");
                                        } else {
                                            clone
                                                .find(".item_hours")
                                                .html(
                                                    elements_data_array[key][
                                                    prop_key
                                                    ]
                                                );
                                        }
                                    }
                                }
                            },
                            error: function (xhr, status, error) {
                                console.error(xhr.responseText);
                            },
                            complete: function () {
                                $("#loader").hide();
                            }
                        });
                    }
                }
                clone.css({
                    left: value["position_x"] - 20,
                    top: value["position_y"] - 10,
                    border: "none",
                });
                var newDropPadHeight =
                    parseInt(clone.css("top")) +
                    parseInt(clone.css("height")) +
                    30;
                if (maxDropPadHeight < newDropPadHeight) {
                    maxDropPadHeight = newDropPadHeight;
                    $(".drop-pad").css("height", maxDropPadHeight + "px");
                }
            }
        }

        for (var key in elements_array) {
            current_element = key;
            $("#" + current_element)
                .find(".attach-elements-out")
                .removeClass("selected");
            if (elements_array[current_element]["0"] != "") {
                $("#" + current_element)
                    .find(".condition_false")
                    .on("click", function (e) {
                        e.stopPropagation();
                        attachOutputElement();
                    })
                    .trigger("click");
                $("#" + elements_array[current_element]["0"])
                    .find(".element_change_input")
                    .on("click", function (e) {
                        e.stopPropagation();
                        attachInputElement();
                    })
                    .trigger("click");
            }
            if (elements_array[current_element]["1"] != "") {
                $("#" + current_element)
                    .find(".condition_true")
                    .on("click", function (e) {
                        e.stopPropagation();
                        attachOutputElement();
                    })
                    .trigger("click");
                $("#" + elements_array[current_element]["1"])
                    .find(".element_change_input")
                    .on("click", function (e) {
                        e.stopPropagation();
                        attachInputElement();
                    })
                    .trigger("click");
            }
            $("#" + current_element).css({
                left: "-=20px",
            });
            if ($("#" + current_element).width() > 365) {
                $("#" + current_element).css({
                    left: "-=10px",
                });
            }
            $("#properties").removeClass("active");
            $("#properties-btn").removeClass("active");
            $("#element-list-btn").addClass("active");
            $("#element-list").addClass("active");
        }
    } else {
        elements_array = {};
        elements_data_array = {};
        elements_array["step-1"] = {};
        elements_array["step-1"][0] = "";
        elements_array["step-1"][1] = "";
        sessionStorage.setItem(
            "elements_array",
            JSON.stringify(elements_array)
        );
        sessionStorage.setItem(
            "elements_data_array",
            JSON.stringify(elements_data_array)
        );
    }

    function attachOutputElement(e) {
        if (!$(this).hasClass("selected")) {
            if (inputElement == null && outputElement == null) {
                var attachDiv = $(this);
                attachDiv.addClass("selected");
                if (attachDiv.hasClass("condition_true")) {
                    condition = "True";
                } else if (attachDiv.hasClass("condition_false")) {
                    condition = "False";
                } else {
                    condition = "";
                }
                outputElement = attachDiv.closest(".element_item");
            }
        }
    }

    function attachInputElement(e) {
        if (!$(this).hasClass("selected")) {
            if (
                outputElement != null &&
                outputElement.attr("id") != $(this).parent().attr("id")
            ) {
                var attachDiv = $(this);
                attachDiv.addClass("selected");
                inputElement = attachDiv.closest(".element_item");
                if (outputElement && inputElement) {
                    var outputElementId = outputElement.attr("id");
                    var inputElementId = inputElement.attr("id");
                    if (condition == "True") {
                        elements_array[outputElementId][1] = inputElementId;
                        var attachOutputElement = $(outputElement).find(
                            ".element_change_output.condition_true"
                        );
                    } else if (condition == "False") {
                        elements_array[outputElementId][0] = inputElementId;
                        var attachOutputElement = $(outputElement).find(
                            ".element_change_output.condition_false"
                        );
                    } else {
                        $("#" + inputElementId).css({
                            border: "1px solid red",
                        });
                    }
                    $(".drop-pad").append(
                        '<div class="line" id="' +
                        outputElement.attr("id") +
                        "-to-" +
                        inputElement.attr("id") +
                        '"></div>'
                    );
                    var attachInputElement = $(inputElement).find(
                        ".element_change_input"
                    );
                    if (attachInputElement && attachOutputElement) {
                        var inputPosition = attachInputElement.offset();
                        var outputPosition = attachOutputElement.offset();

                        var x1 = inputPosition.left;
                        var y1 = inputPosition.top;
                        var x2 = outputPosition.left;
                        var y2 = outputPosition.top;

                        var distance = Math.sqrt(
                            Math.pow(x2 - x1, 2) + Math.pow(y2 - y1, 2)
                        );
                        var angle =
                            Math.atan2(y2 - y1, x2 - x1) * (180 / Math.PI);

                        var lineId =
                            outputElement.attr("id") +
                            "-to-" +
                            inputElement.attr("id");
                        var line = $("#" + lineId);
                        line.css({
                            width: distance + "px",
                            transform: "rotate(" + angle + "deg)",
                            top: y1 - 320 + "px",
                            left: x1 - 207 + "px",
                        });
                        inputElement = null;
                        outputElement = null;
                    }
                }
                sessionStorage.setItem(
                    "elements_array",
                    JSON.stringify(elements_array)
                );
                sessionStorage.setItem(
                    "elements_data_array",
                    JSON.stringify(elements_data_array)
                );
            }
        }
    }

    function elementProperties(e) {
        $("#element-list").removeClass("active");
        $("#properties").addClass("active");
        $("#element-list-btn").removeClass("active");
        $("#properties-btn").addClass("active");
        $(this).removeClass("error");
        $(this).find(".item_name").removeClass("error");
        $(".drop-pad-element .cancel-icon").css({
            display: "none",
        });
        $("#properties").empty();
        $(".drop-pad-element").css({
            "z-index": "0",
            border: "none",
        });
        $(this).css({
            "z-index": "999",
            border: "1px solid rgb(23, 172, 203)",
        });
        $(this).find(".cancel-icon").css({
            display: "flex",
        });
        $(this).find(".item_name").css({
            color: "#fff",
        });
        var item_slug = $(this).data("filterName");
        var item_name = $(this).find(".item_name").text();
        var list_icon = $(this).find(".list-icon").html();
        var item_id = $(this).attr("id");
        var name_html = "";
        if (elements_data_array[item_id] == null) {
            $("#loader").show();
            $.ajax({
                url: getCampaignElementPath.replace(":slug", item_slug),
                type: "GET",
                dataType: "json",
                success: function (response) {
                    if (response.success) {
                        name_html += '<div class="element_properties">';
                        name_html +=
                            '<div class="element_name" data-bs-target="' +
                            item_id +
                            '">' +
                            list_icon +
                            "<p>" +
                            item_name +
                            "</p></div>";
                        arr = {};
                        response.properties.forEach((property) => {
                            name_html += '<div class="property_item">';
                            name_html +=
                                "<p>" + property["property_name"] + "</p>";
                            name_html +=
                                '<input type="' +
                                property["data_type"] +
                                '" placeholder="Enter the ' +
                                property["property_name"] +
                                '" class="property_input" name="' +
                                property["id"] +
                                '"';
                            if (property["optional"] == 1) {
                                name_html += "required";
                            }
                            name_html += ">";
                            name_html += "</div>";
                            arr[property["id"]] = "";
                        });
                        elements_data_array[item_id] = arr;
                        sessionStorage.setItem(
                            "elements_data_array",
                            JSON.stringify(elements_data_array)
                        );
                        name_html += "</div>";
                    } else {
                        name_html += '<div class="element_properties">';
                        name_html +=
                            '<div class="element_name">' +
                            list_icon +
                            "<p>" +
                            item_name +
                            "</p></div>";
                        name_html +=
                            '<div class="text-center">' +
                            response.message +
                            "</div></div>";
                    }
                    $("#properties").html(name_html);
                    $(".property_input").on("input", propertyInput);
                },
                error: function (xhr, status, error) {
                    console.error(xhr.responseText);
                },
                complete: function () {
                    $("#loader").hide();
                }
            });
        } else {
            name_html += '<div class="element_properties">';
            name_html +=
                '<div class="element_name" data-bs-target="' +
                item_id +
                '">' +
                list_icon +
                "<p>" +
                item_name +
                "</p></div>";
            elements = elements_data_array[item_id];
            var ajaxRequests = [];
            for (const key in elements) {
                ajaxRequests.push(
                    $.ajax({
                        url: getPropertyDatatypePath
                            .replace(":id", key)
                            .replace(":element_slug", item_slug),
                        type: "GET",
                        dataType: "json",
                    }).then(function (response) {
                        if (response.success) {
                            const value = elements[key];
                            name_html += '<div class="property_item">';
                            name_html +=
                                "<p>" +
                                response.property["property_name"] +
                                "</p>";
                            name_html +=
                                '<input type="' +
                                response.property["data_type"];
                            if (value == "") {
                                name_html +=
                                    '" placeholder="Enter the ' +
                                    response.property["property_name"] +
                                    '" class="property_input" name="' +
                                    key +
                                    '"';
                            } else {
                                name_html +=
                                    '" value="' +
                                    value +
                                    '" class="property_input"';
                            }
                            if (response.optional == "1") {
                                name_html += "required";
                            }
                            name_html += ">";
                            name_html += "</div>";
                        } else {
                            name_html += '<div class="property_item">';
                            name_html += "<p>" + key + "</p>";
                            name_html +=
                                '<input type="text" placeholder="' +
                                value +
                                '" class="property_input" name="' +
                                key +
                                '">';
                            name_html += "</div>";
                        }
                    })
                );
            }
            $.when.apply($, ajaxRequests).then(function () {
                name_html += "</div>";
                $("#properties").html(name_html);
                $(".property_input").on("input", propertyInput);
            });
        }
    }

    function propertyInput(e) {
        var element_id = $(this)
            .parent()
            .parent()
            .find(".element_name")
            .data("bs-target");
        if (element_id != undefined) {
            var properties = $(this).attr("name");
            elements_data_array[element_id][properties] = $(this).val();
            sessionStorage.setItem(
                "elements_data_array",
                JSON.stringify(elements_data_array)
            );
            $("#" + element_id).css({
                border: "1px solid rgb(23, 172, 203)",
            });
            $("#" + element_id)
                .find(".item_name")
                .css({
                    color: "#fff",
                });
            if ($(this).parent().find("p").text() == "Days") {
                if ($(this).val() != "") {
                    $("#" + element_id)
                        .find(".item_days")
                        .html($(this).val());
                } else {
                    $("#" + element_id)
                        .find(".item_days")
                        .html(0);
                }
            } else if ($(this).parent().find("p").text() == "Hours") {
                if ($(this).val() != "") {
                    $("#" + element_id)
                        .find(".item_hours")
                        .html($(this).val());
                } else {
                    $("#" + element_id)
                        .find(".item_hours")
                        .html(0);
                }
            }
        }
    }

    $(".element-btn").on("click", function () {
        var targetTab = $(this).data("tab");
        $(".element-content").removeClass("active");
        $("#" + targetTab).addClass("active");
        $(".element-btn").removeClass("active");
        $(this).addClass("active");
    });

    $("#save-changes").on("click", function () {
        html2canvas(document.getElementById("capture")).then(function (canvas) {
            var img = canvas.toDataURL();
            elements_array = JSON.parse(JSON.stringify(elements_array));
            elements_data_array = JSON.parse(
                JSON.stringify(elements_data_array)
            );
            $(".drop-pad-element .cancel-icon").css({
                display: "none",
            });
            $(".drop-pad-element").css({
                "z-index": "0",
                border: "none",
            });
            submitCampaign(img);
        });
    });

    async function submitCampaign(img) {
        if (await check_elements()) {
            $.ajax({
                url: createCampaignPath,
                type: "POST",
                dataType: "json",
                contentType: "application/json",
                data: JSON.stringify({
                    final_data: elements_data_array,
                    final_array: elements_array,
                    settings: campaign_details,
                    img_url: img,
                }),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content"
                    ),
                },
                beforeSend: function () {
                    $("#loader").show();
                },
                success: function (response) {
                    if (response.success) {
                        window.location = campaignsPath;
                    } else {
                        toastr.error(response.message);
                        console.log(response);
                    }
                },
                error: function (xhr, status, error) {
                    console.error(xhr.responseText);
                },
                complete: function () {
                    $("#loader").hide();
                }
            });
        }
    }

    async function check_elements() {
        var deferreds = [];
        var allValid = true;

        for (var key in elements_array) {
            if (key !== "step-1") {
                if (find_element(key) == undefined) {
                    $("#" + key).addClass("error");
                    $("#" + key).find(".item_name").addClass("error");
                    key = key.replace(/[0-9]/g, "").replace(/_/g, " ");
                    key = capitalize(key);
                    toastr.error(key + " is not connected as campaign sequence.");
                    allValid = false;
                    break;
                } else {
                    var element_data = elements_data_array[key];
                    for (var prop_key in element_data) {
                        var deferred = $.ajax({
                            url: getPropertyRequiredPath.replace(":id", prop_key),
                            type: "GET"
                        }).then(function (response) {
                            if (response.success) {
                                var property = response.property;
                                if (property["optional"] == 1 && element_data[prop_key] === "") {
                                    $("#" + key).addClass("error");
                                    $("#" + key).find(".item_name").addClass("error");
                                    toastr.error(property["property_name"] + " is not filled as required.");
                                    allValid = false;
                                }
                            }
                        }).catch(function (xhr, status, error) {
                            console.error(xhr.responseText);
                            toastr.error("An error occurred while fetching property data.");
                            allValid = false;
                        });
                        deferreds.push(deferred);
                    }
                }
            }
        }

        await $.when.apply($, deferreds);
        return allValid;
    }

    function find_element(element_id) {
        for (var key in elements_array) {
            if (
                elements_array[key][0] == element_id ||
                elements_array[key][1] == element_id
            ) {
                return key;
            }
        }
    }
});