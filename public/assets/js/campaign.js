$(document).ready(function () {
    /* Clears all items, including the ones you're removing */
    sessionStorage.clear();

    /*Attach event listeners */
    $(".setting_btn").on("click", setting_list);
    $("#filterSelect").on("change", filter_search);
    $("#search_campaign").on("input", filter_search);

    $(document).on("change", ".switch", function () {
        var campaign_id = $(this).attr("id").replace("switch", "");
        $("#loader").show();

        $.ajax({
            url: activateCampaignRoute.replace(":campaign_id", campaign_id),
            type: "GET",
            success: function (response) {
                var message = response.success
                    ? (response.active == 1 ? "Campaign successfully Activated" : "Campaign successfully Deactivated")
                    : "An error occurred. Please try again.";

                toastr[response.success ? (response.active == 1 ? 'success' : 'info') : 'error'](message);

                /* Remove the campaign row if it's not in archive */
                if ($("#filterSelect").val() !== "archive") {
                    $("#table_row_" + campaign_id).remove();
                }

                /* If no campaigns remain, display the "Not Found!" message */
                if ($(".campaign_table_row").length === 0) {
                    $("#campaign_table_body").html('<tr><td colspan="8" class="text-center text-danger" style="font-size: 25px; font-weight: bold; font-style: italic;">Not Found!</td></tr>');
                }
            },
            error: function (xhr, status, error) {
                console.error(error);
                toastr.error("An error occurred while processing the request.");
            },
            complete: function () {
                $("#loader").hide();
            }
        });
    });

    $(document).on("click", ".delete_campaign", function (e) {
        if (!confirm("Are you sure to delete this campaign?")) return;

        var campaign_id = $(this).attr("id").replace("delete", "");
        $("#loader").show();

        $.ajax({
            url: deleteCampaignRoute.replace(":id", campaign_id),
            type: "GET",
            success: function (response) {
                toastr[response.success ? 'success' : 'error'](
                    response.success ? "Campaign successfully Deleted" : "Campaign cannot be Deleted"
                );

                /* Remove the campaign row directly */
                $("#table_row_" + campaign_id).remove();

                /* If no campaigns left, show "Not Found!" */
                if ($(".campaign_table_row").length === 0) {
                    $("#campaign_table_body").html('<tr><td colspan="8" class="text-center text-danger" style="font-size: 25px; font-weight: bold; font-style: italic;">Not Found!</td></tr>');
                }
            },
            error: function (xhr, status, error) {
                console.error(error);
            },
            complete: function () {
                $("#loader").hide();
            }
        });
    });

    $(document).on("click", ".archive_campaign", function (e) {
        if (!confirm("Are you sure to archive this campaign?")) return;

        var campaign_id = $(this).attr("id").replace("archive", "");
        $("#loader").show();

        $.ajax({
            url: archiveCampaignRoute.replace(":id", campaign_id),
            type: "GET",
            success: function (response) {
                const message = response.success && response.archive === 1
                    ? "Campaign successfully Archived"
                    : "Campaign successfully Archived";

                toastr.success(message);

                /* Remove the campaign row directly */
                $("#table_row_" + campaign_id).remove();

                /* If no campaigns left, show "Not Found!" */
                if ($(".campaign_table_row").length === 0) {
                    $("#campaign_table_body").html('<tr><td colspan="8" class="text-center text-danger" style="font-size: 25px; font-weight: bold; font-style: italic;">Not Found!</td></tr>');
                }
            },
            error: function (xhr, status, error) {
                console.error(error);
            },
            complete: function () {
                $("#loader").hide();
            }
        });
    });

    $(document).on("click", "#filterToggle", function (e) {
        e.preventDefault();
        $("#filterSelect").toggle();
    });

    function filter_search(e) {
        e.preventDefault();

        /* Cache selectors */
        var $loader = $("#loader");
        var $filterSelect = $("#filterSelect");
        var $searchCampaign = $("#search_campaign");
        var $campaignTableBody = $("#campaign_table_body");

        /* Get filter and search values */
        var filter = $filterSelect.val();
        var search = $searchCampaign.val().trim() || "null";

        /* Show loader */
        $loader.show();

        $.ajax({
            url: filterCampaignRoute.replace(":filter", filter).replace(":search", search),
            type: "GET",
            success: function (response) {
                var html = '';

                if (response.success) {
                    var campaigns = response.campaigns;

                    if (campaigns.length > 0) {
                        html = campaigns.map(function (campaign) {
                            /* Generate campaign row HTML */
                            var isActiveChecked = campaign.is_active === 1 ? 'checked' : '';
                            var switchId = `switch${campaign.id}`;
                            var switchLabel = `<label for="${switchId}">Toggle</label>`;
                            var stats = `
                                <li><span><img src="/assets/img/eye.svg" alt=""><span id="view_profile_count_${campaign.id}">${campaign.view_action_count}</span></span></li>
                                <li><span><img src="/assets/img/request.svg" alt=""><span id="invite_to_connect_count_${campaign.id}">${campaign.invite_action_count}</span></span></li>
                                <li><span><img src="/assets/img/mailmsg.svg" alt=""><span id="email_message_count_${campaign.id}">${campaign.email_action_count}</span></span></li>
                            `;

                            var settingList = is_manage_allowed ? `
                                <td>
                                    <a type="button" class="setting setting_btn" id=""><i class="fa-solid fa-gear"></i></a>
                                    <ul class="setting_list" style="display: none;">
                                        <li><a href="${detailsCampaignRoute.replace(':id', campaign.id)}">Check campaign details</a></li>
                                        <li><a href="${editCampaignRoute.replace(':id', campaign.id)}">Edit campaign</a></li>
                                        <li><a class="archive_campaign" id="archive${campaign.id}">Archive campaign</a></li>
                                        <li><a class="delete_campaign" id="delete${campaign.id}">Delete campaign</a></li>
                                    </ul>
                                </td>
                            ` : '';

                            return `
                                <tr id="table_row_${campaign.id}" class="campaign_table_row">
                                    <td>
                                        <div class="switch_box">
                                            <input type="checkbox" class="switch" id="${switchId}" ${isActiveChecked}>
                                            ${switchLabel}
                                        </div>
                                    </td>
                                    <td>${campaign.name}</td>
                                    <td id="lead_count_${campaign.id}">${campaign.lead_count}</td>
                                    <td id="sent_message_count_${campaign.id}">${campaign.message_count}</td>
                                    <td class="stats">
                                        <ul class="status_list d-flex align-items-center list-unstyled p-0 m-0">${stats}</ul>
                                    </td>
                                    ${settingList}
                                </tr>
                            `;
                        }).join('');
                    }
                }

                /* Fallback if no campaigns found */
                if (!html) {
                    html = '<tr><td colspan="8"><div class="text-center text-danger" style="font-size: 25px; font-weight: bold; font-style: italic;">Not Found!</div></td></tr>';
                }

                $campaignTableBody.html(html);
                $(".setting_btn").on("click", setting_list);

                /* Update archive button text based on filter */
                $(".archive_campaign").html($filterSelect.val() === "archive" ? "Remove From Archive" : "Archive campaign");
            },
            error: function () {
                $campaignTableBody.html('<tr><td colspan="8"><div class="text-center text-danger" style="font-size: 25px; font-weight: bold; font-style: italic;">Not Found!</div></td></tr>');
            },
            complete: function () {
                $loader.hide();
            }
        });
    }

    function setting_list() {
        /* Cache the selectors for better performance */
        var $settingList = $(".setting_list");

        /* Hide all setting lists initially */
        $settingList.hide();

        var $currentSettingList = $(this).siblings(".setting_list");

        /* Hide other setting lists and toggle the current one */
        $settingList.not($currentSettingList).hide();
        $currentSettingList.toggle();

        /* Close the setting list if click happens outside the setting container */
        $(document).on("click", function (e) {
            if (!$(e.target).closest(".setting").length) {
                $settingList.hide();
            }
        });
    }
});
