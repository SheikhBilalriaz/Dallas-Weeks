$(document).ready(function () {
    sessionStorage.removeItem("settings");
    sessionStorage.removeItem("elements_array");
    sessionStorage.removeItem("elements_data_array")

    $(document).on("change", "#campaign_url", function (e) {
        var file = e.target.files[0];

        /* Remove any existing label */
        $(".import_field").find("label").remove();

        if (file) {
            /* Display the selected file name */
            $(".import_field").append('<label style="margin-bottom: 0px">' + file.name + '</label>');
        } else {
            /* Default upload file button */
            var html = `
                <label class="file-input__label" for="file-input">
                    <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="upload"
                        class="svg-inline--fa fa-upload fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 512 512">
                        <path fill="currentColor" d="M296 384h-80c-13.3 0-24-10.7-24-24V192h-87.7c-17.8 0-26.7-21.5-14.1-34.1L242.3 5.7c7.5-7.5 19.8-7.5 27.3 0l152.2 152.2c12.6 12.6 3.7 34.1-14.1 34.1H320v168c0 13.3-10.7 24-24 24zm216-8v112c0 13.3-10.7 24-24 24H24c-13.3 0-24-10.7-24-24V376c0-13.3 10.7-24 24-24h136v8c0 30.9 25.1 56 56 56h80c30.9 0 56-25.1 56-56v-8h136c13.3 0 24 10.7 24 24zm-124 88c0-11-9-20-20-20s-20 9-20 20 9 20 20 20 20-9 20-20zm64 0c0-11-9-20-20-20s-20 9-20 20 9 20 20 20 20-9 20-20z"></path>
                    </svg><span>Upload file</span>
                </label>
            `;
            $(".import_field").append(html);
        }
    });

    if (campaign_details["campaign_type"] == undefined) {
        campaign_details["campaign_type"] = "linkedin";
    }

    var campaign_pane = $(".campaign_pane");

    campaign_pane.each(function () {
        /* Get the campaign type from the current pane */
        var campaignType = $(this).find("#campaign_type").val();

        /* Check if the campaign type matches the campaign details */
        if (campaignType === campaign_details["campaign_type"]) {
            $(this).addClass("active");
            $('[data-bs-target="' + $(this).attr("id") + '"]').addClass("active");
        }
    });

    /* Change the background color of the active campaign tab */
    $(".campaign_tab.active").parent(".border_box").css({
        "background-color": "#16adcb",
    });


    /* Initialize campaign details if undefined */
    if (
        campaign_details["campaign_name"] === undefined ||
        campaign_details["campaign_url"] === undefined ||
        campaign_details["connections"] === undefined
    ) {
        campaign_details["campaign_name"] = "";
        campaign_details["campaign_url"] = "";
        campaign_details["connections"] = "1";
    } else {
        /* Highlight active campaign tab */
        const activeTab = $(".campaign_tab.active");
        activeTab.parent(".border_box").css({
            "background-color": "#16adcb",
        });

        /* Update the active form with campaign details */
        const active_form = $(".campaign_pane.active").find("form");

        /* Set values for campaign name and URL
        active_form.find("#campaign_name").val(campaign_details["campaign_name"]);

        /* If not the campaign form 4, update campaign URL */
        if (active_form.attr("id") !== "campaign_form_4") {
            active_form.find("#campaign_url").val(campaign_details["campaign_url"]);
        }

        /* If not forms 4, 3, or 6, update connections */
        if (
            active_form.attr("id") !== "campaign_form_4" &&
            active_form.attr("id") !== "campaign_form_3" &&
            active_form.attr("id") !== "campaign_form_6"
        ) {
            active_form.find("#connections").val(campaign_details["connections"]);
        }
    }

    $(".campaign_name").on("change", function (e) {
        const campaignName = $(this).val();
        campaign_details["campaign_name"] = campaignName;
        sessionStorage.setItem("campaign_details", JSON.stringify(campaign_details));
    });

    /* Helper function to extract query parameters from a URL */
    function extractQueryParams(url) {
        const queryString = url.split('?')[1];
        if (!queryString) return {};
        const params = new URLSearchParams(queryString);
        const query = {};
        params.forEach((value, key) => {
            try {
                const parsedValue = JSON.parse(value);
                if (key === 'keywords' && typeof parsedValue === 'string') {
                    query[key] = encodeURIComponent(parsedValue.trim());
                } else {
                    query[key] = parsedValue;
                }
            } catch (e) {
                query[key] = encodeURIComponent(value.trim());
            }
        });
        return query;
    }

    $(".campaign_url").on("change", function (e) {
        var active_form = $(".campaign_pane.active").find("form");

        /* Check if the form is not "campaign_form_4" */
        if (active_form.attr("id") !== "campaign_form_4") {
            var url = $(".campaign_pane.active").find("#campaign_url").val();
            var query = extractQueryParams(url);

            /* Depending on the form ID, call the respective function */
            if (active_form.attr("id") === "campaign_form_1") {
                find_connection_linkedin(query);
            } else if (active_form.attr("id") === "campaign_form_2") {
                const queryString = url.split('?')[1];
                const params = new URLSearchParams(queryString);
                const query = params.get('query');
                find_connection_sales_navigator(query);
            }

            /* Update the campaign_url in campaign_details and store it */
            campaign_details["campaign_url"] = $(this).val();
            sessionStorage.setItem("campaign_details", JSON.stringify(campaign_details));
        }
    });

    function find_connection_sales_navigator(query) {
        /* Cache the .connections field for later use */
        var connectionsField = $(".campaign_pane.active").find("form").find('.connections');

        /* Default connection value */
        var connectionValue = 'o';

        /* Search for 'RELATIONSHIP' in the query string */
        var relationIndex = query.search(/RELATIONSHIP/i);

        /* If 'RELATIONSHIP' is found in the query string */
        if (relationIndex > 0) {
            /* Extract the part before 'RELATIONSHIP' and after '(' */
            var newStr = query.substring(0, relationIndex);
            var paranIndex = newStr.lastIndexOf('(');
            var newSubStr = query.slice(paranIndex);

            /* Check if 'INCLUDED' is found */
            if (newSubStr.search(/INCLUDED/i) > 0) {
                /* Check for the presence of 1st, 2nd, or 3rd */
                if (newSubStr.includes('1st')) {
                    connectionValue = '1';
                } else if (newSubStr.includes('2nd')) {
                    connectionValue = '2';
                } else if (newSubStr.includes('3rd')) {
                    connectionValue = '3';
                }
            }
        }

        /* Set the value of the connections field and disable it */
        connectionsField.val(connectionValue).prop('disabled', true);

        /* Update campaign_details with the new connections value */
        campaign_details["connections"] = connectionValue;
        sessionStorage.setItem("campaign_details", JSON.stringify(campaign_details));
    }

    function find_connection_linkedin(query) {
        /* Get the active form and the connections field */
        var form = $(".campaign_pane.active").find("form");
        var connectionsField = form.find('.connections');

        /* Initialize campaign_details from sessionStorage or default to empty object */
        var campaign_details = JSON.parse(sessionStorage.getItem("campaign_details")) || {};

        /* Handle the 'network' query parameter and set the 'connections' value accordingly */
        if (!query['network'] || query['network'].length > 1) {
            connectionsField.val('o');
        } else {
            const networkType = query['network'][0];
            switch (networkType) {
                case 'F':
                    connectionsField.val('1');
                    break;
                case 'S':
                    connectionsField.val('2');
                    break;
                case 'O':
                    connectionsField.val('3');
                    break;
                default:
                    connectionsField.val('o');
                    break;
            }
        }

        /* Disable the connections field and update campaign_details */
        connectionsField.prop('disabled', true);
        campaign_details["connections"] = connectionsField.val();

        /* Save updated campaign_details to sessionStorage */
        sessionStorage.setItem("campaign_details", JSON.stringify(campaign_details));
    }

    $(".connections").on("change", function (e) {
        /* Retrieve campaign details from sessionStorage or initialize as an empty object */
        var campaign_details = JSON.parse(sessionStorage.getItem("campaign_details")) || {};

        /* Update the "connections" value in campaign_details */
        var newConnectionsValue = $(this).val();
        if (campaign_details["connections"] !== newConnectionsValue) {
            campaign_details["connections"] = newConnectionsValue;

            /* Save the updated campaign_details back to sessionStorage */
            sessionStorage.setItem("campaign_details", JSON.stringify(campaign_details));
        }
    });

    $(".campaign_tab").on("click", function (e) {
        e.preventDefault();

        /* Retrieve campaign details from sessionStorage or initialize an empty object */
        var campaign_details = JSON.parse(sessionStorage.getItem("campaign_details")) || {};

        /* Reset background color for all campaign tabs */
        $(".campaign_tab").parent(".border_box").css({
            "background-color": "rgb(17 19 23)",
        });

        /* Remove the 'active' class from all tabs */
        $(".campaign_tab").removeClass("active");

        /* Add the 'active' class to the clicked tab */
        $(this).addClass("active");

        /* Update the background color for the active tab's parent */
        var id = $(this).data("bs-target");
        $(".campaign_pane").removeClass("active");
        $("#" + id).addClass("active");
        $(".campaign_tab.active").parent(".border_box").css({
            "background-color": "#16adcb",
        });

        /* Get the new form inside the active campaign pane */
        var new_form = $("#" + id).find("form");

        /* Update campaign details and store in sessionStorage */
        campaign_details["campaign_type"] = new_form.find("#campaign_type").val();
        sessionStorage.setItem("campaign_details", JSON.stringify(campaign_details));

        /* Populate form fields with campaign details */
        new_form.find("#campaign_name").val(campaign_details["campaign_name"]);

        if (new_form.attr("id") != "campaign_form_4") {
            new_form.find("#campaign_url").val(campaign_details["campaign_url"]);
        }

        if (new_form.attr("id") != "campaign_form_4" && new_form.attr("id") != "campaign_form_3" && new_form.attr("id") != "campaign_form_6") {
            new_form.find("#connections").val(campaign_details["connections"]);
        }
    });

    $(".nxt_btn").on("click", function (e) {
        e.preventDefault();
        var form = $(".campaign_pane.active").find("form");

        if (form.attr("id") == "campaign_form_4") {
            var fileInput = form.find("#campaign_url")[0].files[0];

            if (!fileInput) {
                form.find("span.campaign_url").text("Please select a file.");
                form.find(".import_field").css({ border: "1px solid red" });
                /* Stop if no file selected */
                return;
            }

            var formData = new FormData();
            formData.append("campaign_url", fileInput);
            var csrfToken = $('meta[name="csrf-token"]').attr("content");

            $("#loader").show();

            $.ajax({
                url: importCSVPath,
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                headers: { "X-CSRF-TOKEN": csrfToken },
                success: function (response) {
                    if (response.success) {
                        $("#sequance_modal")
                            .find("ul li #total_leads")
                            .text(response.total + " leads");
                        $("#sequance_modal")
                            .find("ul li #blacklist_leads")
                            .text(response.global_blacklists + " leads");
                        $("#sequance_modal")
                            .find("ul li #duplicate_among_teams")
                            .text(response.duplicates_across_team + " leads");
                        $("#sequance_modal")
                            .find("ul li #duplicate_csv_file")
                            .text(response.duplicates + " leads");
                        $("#sequance_modal")
                            .find("ul li #total_without_leads")
                            .text(response.total_without_duplicate_blacklist + " leads");
                        $("#campaign_url_hidden").val(response.path);
                        campaign_details["campaign_url"] = response.path;
                        sessionStorage.setItem("campaign_details", JSON.stringify(campaign_details));
                        $("#sequance_modal").modal("show");
                    } else {
                        form.find("span.campaign_url").text(response.error || "Upload failed");
                        form.find(".import_field").css({
                            border: "1px solid red",
                            "margin-bottom": "7px !important",
                        });
                        form.find(".file-input__label").css({
                            "background-color": "red",
                        });
                    }
                },
                error: function (xhr, status, error) {
                    toastr.error('Something went wrong');
                },
                complete: function () {
                    $("#loader").hide();
                    /* Re-enable button after completion */
                    $(".nxt_btn").prop("disabled", false);
                }
            });
        } else {
            form.find('.connections').prop('disabled', false);
            form.submit();
        }
    });

    $(".import_btn").on("click", function (e) {
        e.preventDefault();
        var form = $(".campaign_pane.active").find("form");

        if (form.length > 0) {
            form.submit();
        } else {
            console.error("Form not found in the active campaign pane.");
        }
    });
});
