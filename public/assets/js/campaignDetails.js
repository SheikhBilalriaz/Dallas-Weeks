$(document).ready(function () {
    /* Making every setting to unchangable */
    $(".linkedin_setting_switch").prop("disabled", true);

    /* Initialize variables */
    // var inputElement = null;
    // var outputElement = null;
    // var condition = "";
    // var elements_array = {};
    // var element_data_array = {};

    // $.ajax({
    //     url: getElementPath.replace(":campaign_id", campaign_id),
    //     method: "GET",
    //     success: function (response) {
    //         if (response.success) {
    //             const { elements_array, path } = response;
    //             let maxDropPadHeight = 0;

    //             /* Append the initial step (Step 1) */
    //             const step1Html = `
    //                 <div class="step-1 element_item" id="step-1">
    //                     <div class="list-icon"><i class="fa-solid fa-certificate"></i></div>
    //                     <div class="item_details">
    //                         <p class="item_name">Lead Source (Step 1)</p>
    //                         <p class="item_desc">
    //                             <i class="fa-solid fa-clock"></i>Wait for: 
    //                             <span class="item_days">0</span> days 
    //                             <span class="item_hours">0</span> hours
    //                         </p>
    //                     </div>
    //                     <div class="element_change_output attach-elements-out condition_true"></div>
    //                 </div>
    //             `;
    //             $(".task-list").append(step1Html);

    //             elements_array.forEach((item) => {
    //                 const { original_element: element, properties } = item;
    //                 const elementData = {};
    //                 let days = 0, hours = 0;

    //                 /* Process properties and assign values to days and hours */
    //                 properties.forEach((prop) => {
    //                     elementData[prop.id] = prop.value;
    //                     if (prop.original_properties.property_name === "Hours") {
    //                         hours = prop.value;
    //                     } else if (prop.original_properties.property_name === "Days") {
    //                         days = prop.value;
    //                     }
    //                 });

    //                 /* Build the element's HTML */
    //                 let elementHtml = `
    //                     <div class="element_item drop-pad-element placedElement" id="${item.id}" data-filter-name="${element.element_name}" style="position: absolute;">
    //                         <div class="element_change_input ${element.is_conditional === "1" ? "conditional-elements conditional-elements-in" : "attach-elements attach-elements-in"}"></div>
    //                         <div class="cancel-icon"><i class="fa-solid fa-x"></i></div>
    //                         <div class="list-icon">${element.element_icon}</div>
    //                         <div class="item_details">
    //                             <p class="item_name">${element.element_name}</p>
    //                             <p class="item_desc"><i class="fa-solid fa-clock"></i>Wait for: 
    //                                 <span class="item_days">${days}</span> days 
    //                                 <span class="item_hours">${hours}</span> hours
    //                             </p>
    //                         </div>
    //                         <div class="menu-icon"><i class="fa-solid fa-bars"></i></div>
    //                         <div class="element_change_output attach-elements attach-elements-out condition_true"></div>
    //                     </div>
    //                 `;

    //                 $(".task-list").append(elementHtml);
    //                 const clone = $("#" + item.id);

    //                 /* Calculate new positions based on step1 and append to drop-pad */
    //                 const step1Pos = $("#step-1").position();
    //                 const step1Height = $("#step-1").outerHeight(true);
    //                 let left = item.position_x - (parseInt(item.position_x) - step1Pos.left);
    //                 let top = item.position_y - (parseInt(item.position_y) - (step1Height + step1Pos.top));
    //                 left = Math.max(0, Math.min(left, $(".drop-pad").width() - $(clone).width()));
    //                 top = Math.max(0, top);

    //                 $(clone).css({ left, top });

    //                 const newDropPadHeight = parseInt($(clone).css("top")) + parseInt($(clone).css("height")) + step1Pos.top;
    //                 maxDropPadHeight = Math.max(maxDropPadHeight, newDropPadHeight);
    //                 $(".drop-pad").css("height", maxDropPadHeight + "px");

    //                 /* Store element data */
    //                 elements_array[item.id] = { "0": "", "1": "", ...elementData };
    //             });

    //             /* Handle paths and conditional clicks */
    //             path.forEach((pathItem) => {
    //                 const { current_element_id, next_true_element_id, next_false_element_id } = pathItem;

    //                 /* Set up conditions for current element */
    //                 const currentElement = $("#" + current_element_id);
    //                 currentElement.find(".condition_true").on("click", attachOutputElement).trigger("click");
    //                 currentElement.find(".condition_false").on("click", attachOutputElement).trigger("click");

    //                 /* Setup next elements based on true/false conditions */
    //                 if (next_false_element_id) {
    //                     $("#" + next_false_element_id).find(".element_change_input").on("click", attachInputElement).trigger("click");
    //                 }
    //                 if (next_true_element_id) {
    //                     $("#" + next_true_element_id).find(".element_change_input").on("click", attachInputElement).trigger("click");
    //                 }

    //                 /* Store next elements in elements array */
    //                 elements_array[current_element_id][1] = next_true_element_id;
    //                 elements_array[current_element_id][0] = next_false_element_id;
    //             });

    //             /* Finalizing the click handlers for elements */
    //             $(".drop-pad-element").on("click", elementProperties);
    //         }
    //     },
    //     error: function (xhr, status, error) {
    //         console.error(error);
    //     },
    // });

    // function attachOutputElement(e) {
    //     if (inputElement === null && outputElement === null) {
    //         const attachDiv = $(this).addClass("selected");

    //         /* Determine the condition based on the class of the div */
    //         if (attachDiv.hasClass("condition_true")) {
    //             condition = "True";
    //         } else if (attachDiv.hasClass("condition_false")) {
    //             condition = "False";
    //         } else {
    //             condition = "";
    //         }

    //         /* Set the output element */
    //         outputElement = attachDiv.closest(".element_item");
    //     }
    // }

    // function attachInputElement(e) {
    //     /* Ensure outputElement is valid and not the same as the clicked parent element */
    //     if (outputElement && outputElement.attr("id") !== $(this).parent().attr("id")) {
    //         const attachDiv = $(this).addClass("selected");
    //         const inputElement = attachDiv.closest(".element_item");

    //         if (outputElement && inputElement) {
    //             const outputElementId = outputElement.attr("id");
    //             const inputElementId = inputElement.attr("id");
    //             let attachOutputElement;

    //             /* Determine which output element to attach based on condition */
    //             if (condition === "True") {
    //                 attachOutputElement = $(outputElement).find(".element_change_output.condition_true");
    //             } else if (condition === "False") {
    //                 attachOutputElement = $(outputElement).find(".element_change_output.condition_false");
    //             } else {
    //                 /* Early exit if condition is invalid */
    //                 $("#" + inputElementId).css({ border: "1px solid red" });
    //                 return;
    //             }

    //             const attachInputElement = $(inputElement).find(".element_change_input");

    //             /* Ensure both input and output elements are found before proceeding */
    //             if (attachInputElement.length && attachOutputElement.length) {
    //                 const lineId = `${outputElementId}-to-${inputElementId}`;

    //                 /* Create the line (connection) */
    //                 $("body").append(`<div class="line" id="${lineId}"></div>`);

    //                 const [x1, y1] = getElementCenter(attachOutputElement);
    //                 const [x2, y2] = getElementCenter(attachInputElement);

    //                 const distance = calculateDistance(x1, y1, x2, y2);
    //                 const angle = calculateAngle(x1, y1, x2, y2);

    //                 const extraLeft = ($(".drop-pad").outerWidth(true) - $(".drop-pad").width()) / 2;

    //                 /* Apply styles for the line */
    //                 $("#" + lineId).css({
    //                     width: `${distance}px`,
    //                     transform: `rotate(${angle}deg)`,
    //                     top: `${y1}px`,
    //                     left: `${x1 + extraLeft}px`,
    //                 });

    //                 /* Reset variables after drawing the line */
    //                 inputElement = null;
    //                 outputElement = null;
    //             }
    //         }
    //     }
    // }

    // /* Utility function to get the center of an element */
    // function getElementCenter(element) {
    //     const offset = element.offset();
    //     const width = element.outerWidth();
    //     const height = element.outerHeight();
    //     return [offset.left + width / 2, offset.top + height / 2];
    // }

    // /* Utility function to calculate the distance between two points */
    // function calculateDistance(x1, y1, x2, y2) {
    //     return Math.sqrt(Math.pow(x2 - x1, 2) + Math.pow(y2 - y1, 2));
    // }

    // /* Utility function to calculate the angle between two points */
    // function calculateAngle(x1, y1, x2, y2) {
    //     return Math.atan2(y2 - y1, x2 - x1) * (180 / Math.PI);
    // }

    // function elementProperties(e) {
    //     /* Cache the jQuery object */
    //     var $element = $(this);
    //     var item_name = $element.find(".item_name").text();
    //     var list_icon = $element.find(".list-icon").html();
    //     var item_id = $element.attr("id");
    //     var name_html = '';
    //     var properties_html = '';

    //     /* Show loader or indication of loading state, if required */
    //     $("#loader").show();

    //     $.ajax({
    //         url: getCampaignElementPath.replace(":element_id", item_id),
    //         type: "GET",
    //         dataType: "json",
    //         success: function (response) {
    //             if (response.success) {
    //                 /* Join the properties as a single string */
    //                 properties_html = response.properties.map(function (property) {
    //                     return `
    //                         <div class="property_item">
    //                             <p>
    //                                 ${property.original_properties.property_name}: <br>
    //                                 <span style="font-style: italic;">${property.value}</span>
    //                             </p>
    //                         </div>
    //                     `;
    //                 }).join('');

    //                 name_html = `
    //                     <div class="element_properties">
    //                         <div class="element_name" data-bs-target="${item_id}">
    //                             ${list_icon}
    //                             <p>${item_name}</p>
    //                         </div>
    //                         ${properties_html}
    //                     </div>
    //                 `;
    //             } else {
    //                 name_html = `
    //                     <div class="element_properties">
    //                         <div class="element_name">
    //                             ${list_icon}
    //                             <p>${item_name}</p>
    //                         </div>
    //                         <div class="text-center">Not Found</div>
    //                     </div>
    //                 `;
    //             }

    //             /* Update the DOM in one go */
    //             $(".drop-pad-element").css({ border: "none" });
    //             $element.css({
    //                 "z-index": "999999",
    //                 border: "1px solid #16adcb"
    //             });
    //             $("#properties").html(name_html);
    //         },
    //         error: function (xhr, status, error) {
    //             console.error(xhr.responseText);
    //         },
    //         complete: function () {
    //             /* Hide the loader when the request is complete */
    //             $("#loader").hide();
    //         }
    //     });
    // }
});
