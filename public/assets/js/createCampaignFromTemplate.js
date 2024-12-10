$(document).ready(function () {
    var inputElement = null;
    var outputElement = null;
    var isElement = true;

    if (elements_array) {
        var maxDropPadHeight = 0;

        /* Loop through the elements_array */
        for (var key in elements_array) {
            if (elements_array.hasOwnProperty(key) && key !== "step-1") {
                var value = elements_array[key];
                var hyphenIndex = key.lastIndexOf("_");
                var new_key = key.slice(0, hyphenIndex);
                var clone = $("#" + new_key).clone().css({
                    position: "absolute",
                    left: value["position_x"] - 20,
                    top: value["position_y"] - 10,
                    border: "none"
                });

                clone.attr("id", key)
                    .addClass("drop_element drop-pad-element placedElement")
                    .removeClass("drop_element element");

                $(".task-list").append(clone);

                /* Attach event listeners to clone */
                $(".element_change_output").on("click", attachOutputElement);
                $(".element_change_input").on("click", attachInputElement);
                $(".drop-pad-element").on("click", elementProperties);

                if (elements_data_array.hasOwnProperty(key)) {
                    var element_data = elements_data_array[key];
                    /* Process each property asynchronously */
                    for (var prop_key in element_data) {
                        if (element_data.hasOwnProperty(prop_key)) {
                            $("#loader").show();
                            $.ajax({
                                url: getPropertyRequiredPath.replace(":id", prop_key),
                                type: "GET",
                                success: function (response) {
                                    if (response.success) {
                                        var property = response.property;
                                        var propertyValue = element_data[prop_key] || "0";
                                        if (property["property_name"] === "Days") {
                                            clone.find(".item_days").html(propertyValue);
                                        } else if (property["property_name"] === "Hours") {
                                            clone.find(".item_hours").html(propertyValue);
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
                }

                /* Update max height of drop-pad */
                var newDropPadHeight = parseInt(clone.css("top")) + parseInt(clone.css("height")) + 30;
                maxDropPadHeight = Math.max(maxDropPadHeight, newDropPadHeight);
                $(".drop-pad").css("height", maxDropPadHeight + "px");
            }
        }

        /* Event binding and condition checks for each element */
        for (var key in elements_array) {
            current_element = key;
            $("#" + current_element).find(".attach-elements-out").removeClass("selected");

            if (elements_array[current_element]["0"] !== "") {
                $("#" + current_element).find(".condition_false")
                    .on("click", function (e) {
                        e.stopPropagation();
                        attachOutputElement();
                    })
                    .trigger("click");

                $("#" + elements_array[current_element]["0"]).find(".element_change_input")
                    .on("click", function (e) {
                        e.stopPropagation();
                        attachInputElement();
                    })
                    .trigger("click");
            }

            if (elements_array[current_element]["1"] !== "") {
                $("#" + current_element).find(".condition_true")
                    .on("click", function (e) {
                        e.stopPropagation();
                        attachOutputElement();
                    })
                    .trigger("click");

                $("#" + elements_array[current_element]["1"]).find(".element_change_input")
                    .on("click", function (e) {
                        e.stopPropagation();
                        attachInputElement();
                    })
                    .trigger("click");
            }

            $("#" + current_element).css({ left: "-=20px" });

            /* Adjust position based on width */
            if ($("#" + current_element).width() > 365) {
                $("#" + current_element).css({ left: "-=10px" });
            }

            /* Update properties panel */
            $("#properties").removeClass("active");
            $("#properties-btn").removeClass("active");
            $("#element-list-btn").addClass("active");
            $("#element-list").addClass("active");
        }
    } else {
        /* Reset if elements_array doesn't exist */
        elements_array = { "step-1": { "0": "", "1": "" } };
        elements_data_array = {};
        sessionStorage.setItem("elements_array", JSON.stringify(elements_array));
        sessionStorage.setItem("elements_data_array", JSON.stringify(elements_data_array));
    }

    function attachOutputElement() {
        /* Ensure the element is not already selected */
        if ($(this).hasClass("selected") || inputElement != null || outputElement != null) {
            return;
        }

        var attachDiv = $(this).addClass("selected");
        condition = "";

        /* Determine the condition based on the class of the clicked element */
        if (attachDiv.hasClass("condition_true")) {
            condition = "True";
        } else if (attachDiv.hasClass("condition_false")) {
            condition = "False";
        }

        /* Set the outputElement to the closest .element_item */
        outputElement = attachDiv.closest(".element_item");
    }

    function attachInputElement() {
        /* If the element is already selected, or no output element exists, exit early */
        if ($(this).hasClass("selected") || outputElement == null) {
            return;
        }

        var attachDiv = $(this);
        var inputElement = attachDiv.closest(".element_item");
        var inputElementId = inputElement.attr("id");

        /* Check if input element is not the same as the output element's parent */
        if (outputElement.attr("id") === inputElementId) {
            return;
        }

        attachDiv.addClass("selected");

        /* Handle the output element and input element connections based on condition */
        var outputElementId = outputElement.attr("id");
        if (condition === "True") {
            elements_array[outputElementId][1] = inputElementId;
            var attachOutputElement = $(outputElement).find(".element_change_output.condition_true");
        } else if (condition === "False") {
            elements_array[outputElementId][0] = inputElementId;
            var attachOutputElement = $(outputElement).find(".element_change_output.condition_false");
        } else {
            /* If no condition, highlight the input element in red */
            $("#" + inputElementId).css({
                border: "1px solid red",
            });
            return;
        }

        /* Append a connecting line */
        $(".drop-pad").append(
            `<div class="line" id="${outputElementId}-to-${inputElementId}"></div>`
        );

        /* Get positions for the connection line */
        var attachInputElement = $(inputElement).find(".element_change_input");
        var attachOutputElement = $(outputElement).find(".element_change_output");

        if (attachInputElement.length && attachOutputElement.length) {
            var inputPosition = attachInputElement.offset();
            var outputPosition = attachOutputElement.offset();

            var x1 = inputPosition.left, y1 = inputPosition.top;
            var x2 = outputPosition.left, y2 = outputPosition.top;

            /* Calculate distance and angle between points */
            var distance = Math.sqrt(Math.pow(x2 - x1, 2) + Math.pow(y2 - y1, 2));
            var angle = Math.atan2(y2 - y1, x2 - x1) * (180 / Math.PI);

            var lineId = `${outputElementId}-to-${inputElementId}`;
            var line = $("#" + lineId);
            line.css({
                width: distance + "px",
                transform: `rotate(${angle}deg)`,
                top: y1 - 320 + "px",
                left: x1 - 207 + "px",
            });
        }

        /* Reset the elements for the next connection */
        outputElement = null;
        inputElement = null;

        /* Store the updated arrays in sessionStorage */
        sessionStorage.setItem("elements_array", JSON.stringify(elements_array));
        sessionStorage.setItem("elements_data_array", JSON.stringify(elements_data_array));
    }

    function elementProperties(e) {
        const $properties = $("#properties");
        const $elementList = $("#element-list");
        const $elementListBtn = $("#element-list-btn");
        const $propertiesBtn = $("#properties-btn");
        const $loader = $("#loader");

        /* Reset states */
        $elementList.removeClass("active");
        $properties.addClass("active");
        $elementListBtn.removeClass("active");
        $propertiesBtn.addClass("active");

        $(this).removeClass("error").find(".item_name").removeClass("error");
        $(".drop-pad-element .cancel-icon").hide();

        $(".drop-pad-element").css({ "z-index": "0", border: "none" });
        $(this).css({ "z-index": "999", border: "1px solid rgb(23, 172, 203)" });
        $(this).find(".cancel-icon").css({ display: "flex" });
        $(this).find(".item_name").css({ color: "#fff" });

        const itemSlug = $(this).data("filterName");
        const itemName = $(this).find(".item_name").text();
        const listIcon = $(this).find(".list-icon").html();
        const itemId = $(this).attr("id");

        let nameHtml = `<div class="element_properties">
                          <div class="element_name" data-bs-target="${itemId}">
                            ${listIcon}<p>${itemName}</p>
                          </div>`;

        /* Check if data already exists in sessionStorage */
        if (elements_data_array[itemId]) {
            const elements = elements_data_array[itemId];
            const ajaxRequests = Object.keys(elements).map((key) =>
                $.ajax({
                    url: getPropertyDatatypePath.replace(":id", key).replace(":element_slug", itemSlug),
                    type: "GET",
                    dataType: "json",
                }).then(function (response) {
                    if (response.success) {
                        const value = elements[key];
                        nameHtml += `<div class="property_item">
                                        <p>${response.property["property_name"]}</p>
                                        <input type="${response.property["data_type"]}"
                                               ${value ? `value="${value}"` : `placeholder="Enter the ${response.property["property_name"]}"`}
                                               class="property_input" name="${key}" ${response.optional === "1" ? "required" : ""}>
                                      </div>`;
                    } else {
                        nameHtml += `<div class="property_item">
                                        <p>${key}</p>
                                        <input type="text" placeholder="${value}" class="property_input" name="${key}">
                                      </div>`;
                    }
                })
            );

            $.when.apply($, ajaxRequests).then(function () {
                nameHtml += "</div>";
                $properties.html(nameHtml);
                $(".property_input").on("input", propertyInput);
            });
        } else {
            /* Data not in sessionStorage, fetch from server */
            $loader.show();
            $.ajax({
                url: getCampaignElementPath.replace(":slug", itemSlug),
                type: "GET",
                dataType: "json",
                success: function (response) {
                    if (response.success) {
                        response.properties.forEach((property) => {
                            nameHtml += `<div class="property_item">
                                            <p>${property["property_name"]}</p>
                                            <input type="${property["data_type"]}"
                                                   placeholder="Enter the ${property["property_name"]}"
                                                   class="property_input" name="${property["id"]}" ${property["optional"] === 1 ? "required" : ""}>
                                          </div>`;
                            elements_data_array[itemId] = elements_data_array[itemId] || {};
                            elements_data_array[itemId][property["id"]] = "";
                        });

                        /* Save the updated data to sessionStorage */
                        sessionStorage.setItem("elements_data_array", JSON.stringify(elements_data_array));
                    } else {
                        nameHtml += `<div class="element_properties">
                                        <div class="element_name">${listIcon}<p>${itemName}</p></div>
                                        <div class="text-center">${response.message}</div>
                                      </div>`;
                    }
                    nameHtml += "</div>";
                    $properties.html(nameHtml);
                },
                error: function (xhr) {
                    console.error(xhr.responseText);
                },
                complete: function () {
                    $loader.hide();
                    $(".property_input").on("input", propertyInput);
                }
            });
        }
    }

    function propertyInput(e) {
        const $this = $(this);
        const $parent = $this.closest(".property_item");
        const elementId = $parent.siblings(".element_name").data("bs-target");

        if (elementId === undefined) return;

        const propertyName = $this.attr("name");
        const value = $this.val();

        /* Update the elements_data_array */
        elements_data_array[elementId][propertyName] = value;
        sessionStorage.setItem("elements_data_array", JSON.stringify(elements_data_array));

        /* Update the border and item name color */
        const $element = $("#" + elementId);
        $element.css({ border: "1px solid rgb(23, 172, 203)" });
        $element.find(".item_name").css({ color: "#fff" });

        /* Update Days or Hours if applicable */
        const propertyLabel = $parent.find("p").text();
        const updatedValue = value !== "" ? value : 0;

        if (propertyLabel === "Days") {
            $element.find(".item_days").html(updatedValue);
        } else if (propertyLabel === "Hours") {
            $element.find(".item_hours").html(updatedValue);
        }
    }

    $(".element-btn").on("click", function () {
        /* Cache the clicked button */
        const $this = $(this);
        const targetTab = $this.data("tab");

        /* Toggle active class on the target tab and button */
        $(".element-content").removeClass("active").filter(`#${targetTab}`).addClass("active");
        $(".element-btn").removeClass("active");
        $this.addClass("active");
    });

    $("#save-changes").on("click", function () {
        const $dropPadElement = $(".drop-pad-element");
        const $cancelIcon = $dropPadElement.find(".cancel-icon");

        /* Capture the screenshot and then proceed with the rest of the actions */
        html2canvas(document.getElementById("capture")).then(function (canvas) {
            const img = canvas.toDataURL();

            /* Hide cancel icons and reset element styles */
            $cancelIcon.css("display", "none");
            $dropPadElement.css({
                "z-index": "0",
                "border": "none"
            });

            /* Submit the campaign data with the captured image */
            submitCampaign(img);
        });
    });

    async function submitCampaign(img) {
        try {
            /* Check if elements are valid */
            if (await check_elements()) {
                const $loader = $("#loader");

                /* Prepare campaign data */
                const data = JSON.stringify({
                    final_data: elements_data_array,
                    final_array: elements_array,
                    settings: campaign_details,
                    img_url: img,
                });

                /* Submit campaign via AJAX */
                $.ajax({
                    url: createCampaignPath,
                    type: "POST",
                    dataType: "json",
                    contentType: "application/json",
                    data: data,
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                    },
                    beforeSend: function () {
                        $loader.show();
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
                        console.error("Error in submitCampaign:", xhr.responseText);
                        toastr.error("An error occurred while submitting the campaign.");
                    },
                    complete: function () {
                        $loader.hide();
                    }
                });
            }
        } catch (error) {
            console.error("Error in check_elements:", error);
            toastr.error("An unexpected error occurred. Please try again.");
        }
    }

    async function check_elements() {
        let allValid = true;
        const deferreds = [];

        for (let key in elements_array) {
            if (key !== "step-1") {
                const element = find_element(key);

                if (!element) {
                    handleElementError(key, key);
                    allValid = false;
                    break;
                }

                const element_data = elements_data_array[key];
                for (let prop_key in element_data) {
                    const deferred = checkProperty(prop_key, key, element_data[prop_key]);
                    deferreds.push(deferred);
                }
            }
        }

        /* Wait for all AJAX requests to complete */
        const results = await Promise.all(deferreds);

        // Check if all results are true
        for (let result of results) {
            if (!result) {
                allValid = false;
                break;
            }
        }

        return allValid;
    }

    function handleElementError(key, originalKey) {
        $("#" + key).addClass("error");
        $("#" + key).find(".item_name").addClass("error");
        const displayKey = capitalize(originalKey.replace(/[0-9]/g, "").replace(/_/g, " "));
        toastr.error(`${displayKey} is not connected as campaign sequence.`);
    }

    function checkProperty(prop_key, key, prop_value) {
        return $.ajax({
            url: getPropertyRequiredPath.replace(":id", prop_key),
            type: "GET"
        }).then(function (response) {
            if (response.success) {
                const property = response.property;
                if (property.optional === 1 && prop_value === "") {
                    $("#" + key).addClass("error");
                    $("#" + key).find(".item_name").addClass("error");
                    toastr.error(`${property.property_name} is not filled as required.`);
                    /* invalid property */
                    return false;
                }
            }
        }).catch(function (xhr) {
            console.error(xhr.responseText);
            toastr.error("An error occurred while fetching property data.");
            /* return false on error */
            return false;
        });
    }

    function find_element(element_id) {
        /* Iterate through the elements_array */
        for (let key in elements_array) {
            /* Check if the element_id matches either of the two conditions */
            if (elements_array[key][0] === element_id || elements_array[key][1] === element_id) {
                /* Return immediately if a match is found */
                return key;
            }
        }
        /* Return null if no matching element is found */
        return null;
    }
});