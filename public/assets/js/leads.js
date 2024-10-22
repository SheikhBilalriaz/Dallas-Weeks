$(document).ready(function () {
    var chart = new CanvasJS.Chart("chartContainer", {
        backgroundColor: "rgba(0, 0, 0, 0)",
        animationEnabled: true,
        title: {
            text: "",
            fontColor: "#ffffff4d"
        },
        toolTip: {
            shared: true
        },
        axisX: {
            title: "",
            lineColor: "#0000",
            tickColor: "#0000",
            logarithmic: false,
            gridColor: "#0000",
            gridThickness: 0,
            intervalType: "day",
            valueFormatString: "MMM DD",
            labelFontSize: 12,
            labelFontColor: "#ffffff4d"
        },
        axisY: {
            title: "",
            labelFontColor: "#ffffff4d",
            interval: 20,
            labelFontSize: 12,
            gridColor: "#0000",
        },
        data: [
            { type: "spline", name: "Views", dataPoints: viewsDataPoints },
            { type: "spline", name: "Invites", dataPoints: inviteDataPoints },
            { type: "spline", name: "Messages", dataPoints: messageDataPoints },
            { type: "spline", name: "InMails", dataPoints: inMailDataPoints },
            { type: "spline", name: "Follows", dataPoints: followDataPoints },
            { type: "spline", name: "Emails", dataPoints: emailDataPoints }
        ]
    });

    $(document).on('click', '.stats_list li', function () {
        var activeItems = $('.stats_list li.active');
        if (activeItems.length === 1 && $(this).hasClass('active')) {
            return;
        }
        $(this).toggleClass('active');
        activeItems = $('.stats_list li.active');
        chart.options.data.forEach(function (data) {
            data.dataPoints = [];
        });
        $i = 0;
        activeItems.each(function () {
            const dataSpan = $(this).data('span');
            if (dataSpan == "viewsDataPoints") {
                chart.options.data[$i].name = "Views";
                chart.options.data[$i].dataPoints = viewsDataPoints;
                $i++;
            } else if (dataSpan == "inviteDataPoints") {
                chart.options.data[$i].name = "Invites";
                chart.options.data[$i].dataPoints = inviteDataPoints;
                $i++;
            } else if (dataSpan == "messageDataPoints") {
                chart.options.data[$i].name = "Messages";
                chart.options.data[$i].dataPoints = messageDataPoints;
                $i++;
            } else if (dataSpan == "inMailDataPoints") {
                chart.options.data[$i].name = "InMails";
                chart.options.data[$i].dataPoints = inMailDataPoints;
                $i++;
            } else if (dataSpan == "followDataPoints") {
                chart.options.data[$i].name = "Follows";
                chart.options.data[$i].dataPoints = followDataPoints;
                $i++;
            } else if (dataSpan == "emailDataPoints") {
                chart.options.data[$i].name = "Emails";
                chart.options.data[$i].dataPoints = emailDataPoints;
                $i++;
            }
        });
        chart.render();
    });

    $("#search_lead").on("input", filter_search);
    $("#campaign").on("change", filter_search);

    $(".lead_tab").on("click", function (e) {
        e.preventDefault();
        $(".lead_tab").removeClass("active");
        $(this).addClass("active");
        var id = $(this).data("bs-target");
        $(".lead_pane").removeClass("active");
        $("#" + id).addClass("active");
    });

    function setting_list() {
        $(".setting_list").hide();
        $(".setting_btn").on("click", function (e) {
            $(".setting_list").not($(this).siblings(".setting_list")).hide();
            $(this).siblings(".setting_list").toggle();
        });
        $(document).on("click", function (e) {
            if (!$(event.target).closest(".setting").length) {
                $(".setting_list").hide();
            }
        });
    }

    function filter_search(e) {
        e.preventDefault();
        var campaign_id = $("#campaign").val();
        var search = $("#search_lead").val();
        if (search === "") {
            search = "null";
        }
        $("#loader").show();
        $.ajax({
            url: leadsCampaignFilterPath
                .replace(":id", campaign_id)
                .replace(":search", search),
            type: "GET",
            success: function (response) {
                if (response.success) {
                    var leads = response.leads;
                    var html = ``;
                    for (var key in leads) {
                        html += `<tr style="z-index: 999;">`;
                        html +=
                            `<td class="title_cont">` +
                            `${leads[key]["contact"] ?? ''}` +
                            `</td>`;
                        html +=
                            `<td class="title_comp">` +
                            `${leads[key]["title_company"] ?? ''}` +
                            `</td>`;
                        html += `<td class="">`;
                        if (leads[key]['send_connections'] == 'connected_not_replied') {
                            html += `<div class="per connected_not_replied">Connected, not replied</div>`;
                        } else if (leads[key]['send_connections'] == 'profile_viewed') {
                            html += `<div class="per discovered">Profile Viewed</div>`;
                        } else if (leads[key]['send_connections'] == 'followed') {
                            html += `<div class="per discovered">Followed</div>`;
                        } else if (leads[key]['send_connections'] == 'messaged') {
                            html += `<div class="per discovered">Messaged</div>`;
                        } else if (leads[key]['send_connections'] == 'replied_not_connected') {
                            html += `<div class="per replied_not_connected">Replied, not connected</div>`;
                        } else if (leads[key]['send_connections'] == 'connection_pending') {
                            html += `<div class="per connection_pending">Connection pending</div>`;
                        } else if (leads[key]['send_connections'] == 'connected') {
                            html += `<div class="per connected_not_replied">Connected</div>`;
                        } else if (leads[key]['send_connections'] == 'replied') {
                            html += `<div class="per replied">Replied</div>`;
                        } else if (leads[key]['send_connections'] == 'not_connected') {
                            html += `<div class="per replied">Not Connected</div>`;
                        } else {
                            html += `<div class="per discovered">Discovered</div>`;
                        }
                        html += `</td>`;
                        if (leads[key]['current_step'] != null) {
                            html += `<td>`;
                            html += leads[key]['current_step'];
                            html += `</td>`;
                        } else {
                            html += `<td style='color: red; font-weight: bold'>Step 1</td>`;
                        }
                        if (leads[key]["next_step"] != null) {
                            html += `<td>`;
                            html += leads[key]['next_step'];
                            html += `</td>`;
                        } else {
                            html += `<td style='color: green; font-weight: bold'>Completed</td>`;
                        }
                        var createdAtDate = new Date(leads[key]["created_at"]);
                        var now = new Date();
                        var diffMs = now - createdAtDate;
                        var diffDays = Math.floor(
                            diffMs / (1000 * 60 * 60 * 24)
                        );
                        if (diffDays < 0) {
                            diffDays = 0;
                        }
                        html +=
                            `<td><div class="">` +
                            diffDays +
                            ` days ago</div></td>`;
                        html += `</tr>`;
                    }
                    $(".leads_list table tbody").html(html);
                } else {
                    var html = ``;
                    html += `<tr style="z-index: 999;"><td colspan="8"><div class="text-center text-danger" `;
                    html += `style="font-size: 25px; font-weight: bold;`;
                    html += ` font-style: italic;">Not Found!</div></td></tr>`;
                    $(".leads_list table tbody").html(html);
                }
                var reports = response.reports;
                const tableBody = $('#report_data');
                const tfoot = $('#report_totals');
                tfoot.empty();
                tableBody.empty();
                let totalView = 0;
                let totalEmail = 0;
                let totalFollow = 0;
                let totalInvite = 0;
                if (Object.keys(reports).length > 0) {
                    for (const [date, counts] of Object.entries(reports)) {
                        totalView += counts.view_count ?? 0;
                        totalEmail += counts.email_count ?? 0;
                        totalFollow += counts.follow_count ?? 0;
                        totalInvite += counts.invite_count ?? 0;
                        const row = `
                            <tr>
                                <td>${date}</td>
                                <td>${counts.view_count ?? 0}</td>
                                <td>${counts.email_count ?? 0}</td>
                                <td>${counts.follow_count ?? 0}</td>
                                <td>${counts.invite_count ?? 0}</td>
                            </tr>
                        `;
                        tableBody.append(row);
                    }
                } else {
                    const currentDate = new Date().toISOString().split('T')[0];
                    const emptyRow = `
                        <tr>
                            <td>${currentDate}</td>
                            <td>0</td>
                            <td>0</td>
                            <td>0</td>
                            <td>0</td>
                        </tr>
                    `;
                    tableBody.append(emptyRow);
                }
                tfoot.html(`
                    <tr>
                        <td>Total</td>
                        <td>${totalView}</td>
                        <td>${totalEmail}</td>
                        <td>${totalFollow}</td>
                        <td>${totalInvite}</td>
                    </tr>
                `);
                viewsDataPoints = [];
                inviteDataPoints = [];
                messageDataPoints = [];
                inMailDataPoints = [];
                followDataPoints = [];
                emailDataPoints = [];
                Object.keys(response.past_month_data).forEach(function (date) {
                    var dateParts = date.split('-');
                    var fullDate = new Date(dateParts[0], dateParts[1] - 1, dateParts[2]);
                    viewsDataPoints.push({
                        x: fullDate,
                        y: response.past_month_data[date]['view_count']
                    });
                    inviteDataPoints.push({
                        x: fullDate,
                        y: response.past_month_data[date]['invite_count']
                    });
                    messageDataPoints.push({
                        x: fullDate,
                        y: response.past_month_data[date]['message_count']
                    });
                    inMailDataPoints.push({
                        x: fullDate,
                        y: response.past_month_data[date]['in_mail_count']
                    });
                    followDataPoints.push({
                        x: fullDate,
                        y: response.past_month_data[date]['follow_count']
                    });
                    emailDataPoints.push({
                        x: fullDate,
                        y: response.past_month_data[date]['email_count']
                    });
                });
                $('.stats_list li.active').each(function () {
                    $(this).removeClass('active');
                });
                $('.stats_list li').first().trigger('click');
                if (response.campaign != null) {
                    var campaign = response.campaign;
                    $("#campaign-name").val(campaign["name"]);
                    $("#linkedin-url").val(campaign["url"]);
                    const timestamp = campaign["created_at"];
                    const formattedTimestamp = new Date(timestamp)
                        .toISOString()
                        .replace("T", " ")
                        .slice(0, 16);
                    $("#created_at").html(
                        '<i class="fa-solid fa-calendar-days"></i>Created at: ' +
                        formattedTimestamp
                    );
                    var linkedin_settings = response.settings.linkedin_setting;
                    linkedin_settings.forEach(element => {
                        var field = $('#' + element['setting_slug']);
                        if (element['value'] === 'yes') {
                            field.prop('checked', true);
                        } else if (element['value'] === 'no') {
                            field.prop('checked', false);
                        }
                    });
                    var global_settings = response.settings.global_setting;
                    global_settings.forEach(element => {
                        var field = $('#' + element['setting_slug']);
                        if (element['value'] === 'yes') {
                            field.prop('checked', true);
                        } else if (element['value'] === 'no') {
                            field.prop('checked', false);
                        } else {
                            var schedule = $('.' + element['setting_slug']);
                            schedule.each(function () {
                                if (element['value'] == $(this).val()) {
                                    $(this).prop('checked', true);
                                    return;
                                } else {
                                    $(this).prop('checked', false);
                                }
                            });
                        }
                    });
                    var email_settings = response.settings.email_setting;
                    email_settings.forEach(element => {
                        var field = $('#' + element['setting_slug']);
                        if (element['value'] === 'yes') {
                            field.prop('checked', true);
                        } else if (element['value'] === 'no') {
                            field.prop('checked', false);
                        } else if (element['setting_slug'].includes('email_id')) {
                            var email = $('.' + element['setting_slug']);
                            email.each(function () {
                                if (element['value'] == $(this).val()) {
                                    $(this).prop('checked', true);
                                    return;
                                } else {
                                    $(this).prop('checked', false);
                                }
                            });
                        } else {
                            var schedule = $('.' + element['setting_slug']);
                            schedule.each(function () {
                                if (element['value'] == $(this).val()) {
                                    $(this).prop('checked', true);
                                    return;
                                } else {
                                    $(this).prop('checked', false);
                                }
                            });
                        }
                    });
                } else {
                    $("#campaign-name").val("");
                    $("#linkedin-url").val("");
                    const currentDate = new Date();
                    const year = currentDate.getFullYear();
                    const month = String(currentDate.getMonth() + 1).padStart(
                        2,
                        "0"
                    );
                    const day = String(currentDate.getDate()).padStart(2, "0");
                    const hours = String(currentDate.getHours()).padStart(
                        2,
                        "0"
                    );
                    const minutes = String(currentDate.getMinutes()).padStart(
                        2,
                        "0"
                    );
                    const formattedDate = `${year}-${month}-${day} ${hours}:${minutes}`;
                    $("#created_at").html(
                        '<i class="fa-solid fa-calendar-days"></i>Created at: ' +
                        formattedDate
                    );
                    var setting_switch = $('.setting_switch');
                    setting_switch.each(function (index, setting) {
                        $(setting).prop('checked', false);
                    });
                    var schedule = $('.schedule_id');
                    schedule.each(function (index, setting) {
                        if (parseInt($(setting).val()) === 1) {
                            $(this).prop('checked', true);
                        } else {
                            $(this).prop('checked', false);
                        }
                    });
                }
                $(".setting_btn").on("click", setting_list);
                $(".setting_list").hide();
            },
            error: function (xhr, status, error) {
                console.error(xhr.responseText);
            },
            complete: function () {
                $("#loader").hide();
            }
        });
    }

    $("#export_leads").on("click", function (e) {
        var form = $("#export_form");
        var campaign_id = $("#campaign").val();
        var email = form.find("#export_email").val();
        var csrfToken = $('meta[name="csrf-token"]').attr("content");
        var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (emailPattern.test(email)) {
            form.find("#export_email").css({
                border: "1px solid oklch(0.69 0.12 216.55 / 0.6)",
                color: "#16adcb",
            });
            $("#email_error").text("").css({
                display: "none",
            });
            $("#loader").show();
            $.ajax({
                url: sendLeadsToEmail,
                type: "POST",
                data: {
                    _token: csrfToken,
                    email: email,
                    campaign_id: campaign_id,
                },
                success: function (response) {
                    if (response.success) {
                        $("#export_modal").modal("hide");
                    }
                },
                error: function (xhr, status, error) {
                    console.error(error);
                },
                complete: function () {
                    $("#loader").hide();
                }
            });
        } else {
            form.find("#export_email").css({
                border: "1px solid red",
            });
            $("#email_error").text("Insert valid email").css({
                display: "block",
            });
        }
    });
});
