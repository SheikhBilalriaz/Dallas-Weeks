$(document).ready(function () {
    /* Clear previous sessionStorage values */
    sessionStorage.removeItem("elements_array");
    sessionStorage.removeItem("elements_data_array");

    /* If no settings, initialize as empty object */
    var settings = JSON.parse(sessionStorage.getItem("settings")) || {};

    /* Loop through all checkboxes with class .linkedin_setting_switch */
    $(".linkedin_setting_switch").each(function () {
        var checkbox = $(this);
        var name = checkbox.prop('name');
        /* Get stored value from settings */
        var value = settings[name];

        /* Set checkbox state based on value ('yes' or 'no') */
        checkbox.prop("checked", value === 'yes');

        /* If settings don't have a value for this checkbox, set it to 'no' (unchecked) */
        if (!value) {
            settings[name] = "no";
        }
    });

    /* Save settings to sessionStorage after updating the checkboxes */
    sessionStorage.setItem("settings", JSON.stringify(settings));

    $('.linkedin_setting_switch').on('change', function () {
        var name = $(this).prop('name');
        settings[name] = $(this).is(":checked") ? "yes" : "no";
    });

    var form = $("#settings");
    if (campaign_details["campaign_type"] != undefined) {
        form.append(
            $("<input>")
                .attr("type", "hidden")
                .attr("name", "campaign_type")
                .val(campaign_details["campaign_type"])
        );
    }
    if (campaign_details["campaign_name"] != undefined) {
        form.append(
            $("<input>")
                .attr("type", "hidden")
                .attr("name", "campaign_name")
                .val(campaign_details["campaign_name"])
        );
    }
    if (campaign_details["campaign_url"] != undefined) {
        form.append(
            $("<input>")
                .attr("type", "hidden")
                .attr("name", "campaign_url")
                .val(campaign_details["campaign_url"])
        );
    }
    if (campaign_details["campaign_url_hidden"] != undefined) {
        form.append(
            $("<input>")
                .attr("type", "hidden")
                .attr("name", "campaign_url_hidden")
                .val(campaign_details["campaign_url_hidden"])
        );
    }
    if (campaign_details["connections"] != undefined) {
        form.append(
            $("<input>")
                .attr("type", "hidden")
                .attr("name", "connections")
                .val(campaign_details["connections"])
        );
    }

    /* Before submitting make every checkbox having valing */
    $("#create_sequence").on("click", function (e) {
        e.preventDefault();

        const form = $("#settings");
        const settings = {};

        /* Process all ".linkedin_setting_switch" inputs */
        form.find(".linkedin_setting_switch").each(function () {
            const $input = $(this);
            const inputName = $input.attr("name");
            const inputValue = $input.is(":checked") ? "yes" : "no";

            /* Set input value and update settings object */
            $input.val(inputValue);
            settings[inputName] = inputValue;
        });

        /* Store settings in sessionStorage */
        sessionStorage.setItem("settings", JSON.stringify(settings));

        /* Update form action and submit */
        form.attr("action", campaignFromScratchPagePath).submit();
    });

    $('#create_template').on("click", function (e) {
        e.preventDefault();

        const form = $("#settings");
        const settings = {};

        /* Process all ".linkedin_setting_switch" inputs */
        form.find(".linkedin_setting_switch").each(function () {
            const $input = $(this);
            const inputName = $input.attr("name");
            const inputValue = $input.is(":checked") ? "yes" : "no";

            /* Set input value accordingly */
            $input.val(inputValue);
            settings[inputName] = inputValue;
        });

        /* Store settings in sessionStorage */
        sessionStorage.setItem("settings", JSON.stringify(settings));

        /* Set form action and submit */
        form.attr("action", campaignFromTempelatePagePath).submit();
    });

    $(".next_tab, .prev_tab").on("click", function () {
        const $this = $(this);
        const $tabs = $this.closest(".comp_tabs").find(".nav-tabs .nav-link");
        const $activeTab = $tabs.filter(".active");

        /* Determine the direction */
        const $targetTab = $this.hasClass("next_tab")
            ? $activeTab.next(".nav-link")
            : $activeTab.prev(".nav-link");

        /* Trigger click on the target tab if it exists */
        if ($targetTab.length) {
            $targetTab.click();
        }
    });

    /* Changing tabs among settings */
    $(".schedule-btn").on("click", function (e) {
        e.preventDefault();

        /* Cache the current button */
        const $this = $(this);
        /* Get the target tab element */
        const targetTab = $("#" + $this.data("tab"));
        /* Find the closest parent wrapper */
        const $parent = $this.closest(".schedule-wrapper");

        /* Update active classes for content */
        $parent.find(".schedule-content.active").removeClass("active");
        targetTab.addClass("active");

        /* Update active classes for buttons */
        $parent.find(".schedule-btn.active").removeClass("active");
        $this.addClass("active");
    });

    $(".schedule_days").on("change", function () {
        const day = $(this).val();
        const isChecked = $(this).prop("checked");

        /* Define time inputs dynamically */
        const startTimeInput = $(`#${day}_start_time`);
        const endTimeInput = $(`#${day}_end_time`);

        if (isChecked) {
            /* Set default times */
            startTimeInput.val("09:00:00");
            endTimeInput.val("17:00:00");
        } else {
            /* Clear the values */
            startTimeInput.val("");
            endTimeInput.val("");
        }
    });

    $(".add_schedule").on("click", function (e) {
        e.preventDefault();

        const form = $(".schedule_form");
        const scheduleName = $("#schedule_name");
        const scheduleNameError = $("#schedule_name_error");
        const loader = $("#loader");
        const scheduleDays = form.find('input[type="checkbox"]');

        /* Validate schedule name */
        if (scheduleName.val().trim() === "") {
            scheduleName.addClass("error");
            scheduleNameError.text("Sequence name is required");
            return;
        }

        /* Normalize checkbox values */
        scheduleDays.each(function () {
            const checkbox = $(this);
            checkbox.val(checkbox.is(":checked") ? "true" : "false");
            /* Ensure all checkboxes are submitted */
            checkbox.prop("checked", true);
        });

        loader.show();

        /* Perform AJAX request */
        $.ajax({
            url: createSchedulePath,
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": csrfToken,
            },
            data: form.serialize(),
            success: function (response) {
                if (response.success) {
                    /* Hide modal */
                    $("#schedule_modal").modal("hide");

                    /* Generate updated schedule list */
                    const html = generateScheduleHTML(response.schedules, "email_settings_schedule_id");
                    $(".schedule_list_1").html(html);

                    /* Update global settings schedule list */
                    const globalHtml = html.replace(
                        /email_settings_schedule_id/g,
                        "global_settings_schedule_id"
                    );
                    $(".schedule_list_2").html(globalHtml);
                } else {
                    console.error("Error in response:", response);
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX error:", error);
            },
            complete: function () {
                loader.hide();
                scheduleName.removeClass("error");
                scheduleNameError.text("");
            },
        });
    });

    /* Function to generate schedule HTML */
    function generateScheduleHTML(schedules, inputName) {
        return schedules
            .map((schedule) => {
                const scheduleDays = schedule["Days"]
                    .map((day) => {
                        const isActiveClass = day["is_active"] === "1" ? "selected_day" : "";
                        return `<li class="schedule_day ${isActiveClass}">${day["day"].toUpperCase()}</li>`;
                    })
                    .join("");

                return `
                <li>
                    <div class="row schedule_list_item">
                        <div class="col-lg-1 schedule_item">
                            <input type="radio" name="${inputName}" class="schedule_id" 
                                value="${schedule["id"]}" ${schedule["user_id"] === 0 ? "checked" : ""}>
                        </div>
                        <div class="col-lg-3 schedule_name">
                            <span>${schedule["name"]}</span>
                        </div>
                        <div class="col-lg-6 schedule_days">
                            <ul class="schedule_day_list">
                                ${scheduleDays}
                            </ul>
                        </div>
                    </div>
                </li>`;
            })
            .join("");
    }

    $(".search_schedule").on("input", function () {
        const scheduleInput = $(this);
        const search = scheduleInput.val().trim() || "null";

        /* AJAX request to fetch schedules */
        $.ajax({
            url: filterSchedulePath.replace(":search", search),
            method: "GET",
            success: function (response) {
                const scheduleList = scheduleInput.parent().next(".schedule_list");
                let html = "";

                if (response.success && response.schedules.length > 0) {
                    html = generateScheduleHTML(response.schedules, scheduleList.attr("id"));
                } else {
                    html = getNotFoundHTML();
                }

                updateScheduleList(scheduleList.attr("id"), html);
            },
            error: function (xhr, status, error) {
                console.error("Error fetching schedules:", error);
            },
        });
    });

    /* Function to generate schedule HTML */
    function generateScheduleHTML(schedules, listId) {
        return schedules
            .map((schedule) => {
                const scheduleDays = schedule["Days"]
                    .map((day) => {
                        const isActiveClass = day["is_active"] === "1" ? "selected_day" : "";
                        return `<li class="schedule_day ${isActiveClass}">${day["day"].toUpperCase()}</li>`;
                    })
                    .join("");

                return `
                <li>
                    <div class="row schedule_list_item">
                        <div class="col-lg-1 schedule_item">
                            <input type="radio" name="${listId === "schedule_list_2"
                        ? "global_settings_schedule_id"
                        : "email_settings_schedule_id"
                    }" class="schedule_id" value="${schedule["id"]}">
                        </div>
                        <div class="col-lg-3 schedule_name">
                            <span>${schedule["name"]}</span>
                        </div>
                        <div class="col-lg-6 schedule_days">
                            <ul class="schedule_day_list">
                                ${scheduleDays}
                            </ul>
                        </div>
                    </div>
                </li>`;
            })
            .join("");
    }

    /* Function to get "Not Found" HTML */
    function getNotFoundHTML() {
        return `
        <li>
            <div class="text-center text-danger" style="font-size: 19px;">Not Found!</div>
        </li>`;
    }

    /* Function to update the schedule list */
    function updateScheduleList(listId, html) {
        const targetList =
            listId === "schedule_list_2"
                ? $("#my_campaign_schedule").find("#schedule_list_2")
                : $("#my_email_schedule").find("#schedule_list_1");

        targetList.html(html);
    }

    $(".team_search_schedule").on("input", function () {
        const scheduleInput = $(this);
        let search = scheduleInput.val().trim() || "null";

        /* AJAX request to fetch filtered schedules */
        $.ajax({
            url: filterTeamSchedulePath.replace(":search", search),
            method: "GET",
            success: function (response) {
                const scheduleList = scheduleInput.parent().next(".schedule_list");
                let html = "";

                if (response.success && response.schedules.length > 0) {
                    html = generateScheduleHTML(response.schedules, scheduleList.attr("id"));
                } else {
                    html = `<li><div class="text-center text-danger" style="font-size: 19px;">Not Found!</div></li>`;
                }

                updateScheduleList(scheduleList.attr("id"), html);
            },
            error: function (xhr, status, error) {
                console.error("Error fetching schedules:", error);
            },
        });
    });

    /* Function to generate schedule HTML */
    function generateScheduleHTML(schedules, listId) {
        let html = "";

        schedules.forEach((schedule) => {
            const scheduleId = schedule["id"];
            const scheduleName = schedule["name"];
            const scheduleDays = schedule["Days"];

            html += `<li><div class="row schedule_list_item">`;
            html += `<div class="col-lg-1 schedule_item">`;
            html += `<input type="radio" name="${listId === "schedule_list_2" ? "global_settings_schedule_id" : "email_settings_schedule_id"}" class="schedule_id" value="${scheduleId}"></div>`;
            html += `<div class="col-lg-3 schedule_name"><span>${scheduleName}</span></div>`;
            html += `<div class="col-lg-6 schedule_days"><ul class="schedule_day_list">`;

            scheduleDays.forEach((day) => {
                html += `<li class="schedule_day ${day["is_active"] === "1" ? "selected_day" : ""}">`;
                html += `${day["day"].toUpperCase()}</li>`;
            });

            html += `</ul></div></div></li>`;
        });

        return html;
    }

    /* Function to update the schedule list */
    function updateScheduleList(listId, html) {
        const targetList = listId === "schedule_list_2"
            ? $("#team_campaign_schedule").find("#schedule_list_2")
            : $("#team_email_schedule").find("#schedule_list_1");

        targetList.html(html);
    }
});
