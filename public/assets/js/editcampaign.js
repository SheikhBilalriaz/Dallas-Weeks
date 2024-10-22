$(document).ready(function () {
    var edit_campaign_details = campaign;
    if (edit_campaign_details["type"] == undefined) {
        edit_campaign_details["type"] = "linkedin";
    }
    var campaign_pane = $(".campaign_pane");
    for (var i = 0; i < campaign_pane.length; i++) {
        var campaignType = $(campaign_pane[i]).find("#campaign_type").val();
        if (campaignType == edit_campaign_details["type"]) {
            $(campaign_pane[i]).addClass("active");
            $(
                '[data-bs-target="' + $(campaign_pane[i]).attr("id") + '"]'
            ).addClass("active");
        }
        $(".campaign_tab.active").parent(".border_box").css({
            "background-color": "#16adcb",
        });
    }
    if (
        edit_campaign_details["name"] == undefined ||
        edit_campaign_details["url"] == undefined ||
        edit_campaign_details["connection"] == undefined
    ) {
        edit_campaign_details["name"] = "";
        edit_campaign_details["url"] = "";
        edit_campaign_details["connection"] = "";
        $(".campaign_pane.active")
            .find("form")
            .find("#campaign_name")
            .val(edit_campaign_details["name"]);
        if (
            $(".campaign_pane.active").find("form").attr("id") !=
            "campaign_form_4"
        ) {
            $(".campaign_pane.active")
                .find("form")
                .find("#campaign_url")
                .val(edit_campaign_details["url"]);
        }
        if (
            $(".campaign_pane.active").find("form").attr("id") !=
            "campaign_form_4" &&
            $(".campaign_pane.active").find("form").attr("id") !=
            "campaign_form_3"
        ) {
            $(".campaign_pane.active")
                .find("form")
                .find("#connections")
                .val(edit_campaign_details["connection"]);
        }
    } else {
        var active_form = $(".campaign_pane.active").find("form");
        active_form
            .find("#campaign_name")
            .val(edit_campaign_details["name"]);
        if (active_form.attr("id") != "campaign_form_4") {
            active_form
                .find("#campaign_url")
                .val(edit_campaign_details["url"]);
        }
        if (
            active_form.attr("id") != "campaign_form_4" &&
            active_form.attr("id") != "campaign_form_3"
        ) {
            active_form
                .find("#connections")
                .val(edit_campaign_details["connection"]);
        }
    }
    $(".campaign_name").on("change", function (e) {
        edit_campaign_details["name"] = $(this).val();
        sessionStorage.setItem(
            "edit_campaign_details",
            JSON.stringify(edit_campaign_details)
        );
    });
    $(".campaign_url").on("change", function (e) {
        edit_campaign_details["url"] = $(this).val();
        sessionStorage.setItem(
            "edit_campaign_details",
            JSON.stringify(edit_campaign_details)
        );
        var active_form = $(".campaign_pane.active").find("form");
        if (active_form.attr("id") != "campaign_form_4") {
            if (active_form.attr("id") == "campaign_form_1") {
                var url = $(".campaign_pane.active")
                    .find("#campaign_url")
                    .val();
                const queryString = url.split('?')[1];
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
                find_connection_linkedin(query);
            } else if (active_form.attr("id") == "campaign_form_2") {
                var url = $(".campaign_pane.active")
                    .find("#campaign_url")
                    .val();
                const queryString = url.split('?')[1];
                const params = new URLSearchParams(queryString);
                const query = params.get('query');
                find_connection_sales_navigator(query);
            }
            edit_campaign_details["campaign_url"] = $(this).val();
            sessionStorage.setItem(
                "edit_campaign_details",
                JSON.stringify(edit_campaign_details)
            );
        }
    });

    function find_connection_linkedin(query) {
        if (!query['network']) {
            $(".campaign_pane.active").find("form").find('.connections').val('o');
        } else {
            const array = query['network'];
            if (array.length > 1) {
                $(".campaign_pane.active").find("form").find('.connections').val('o');
            } else {
                if (array[0] == 'F') {
                    $(".campaign_pane.active").find("form").find('.connections').val('1');
                } else if (array[0] == 'S') {
                    $(".campaign_pane.active").find("form").find('.connections').val('2');
                } else if (array[0] == 'O') {
                    $(".campaign_pane.active").find("form").find('.connections').val('3');
                } else {
                    $(".campaign_pane.active").find("form").find('.connections').val('o');
                }
            }
        }
        $(".campaign_pane.active").find("form").find('.connections').prop('disabled', true);
        edit_campaign_details["connections"] = $(".campaign_pane.active").find("form").find('.connections').val();
        sessionStorage.setItem(
            "edit_campaign_details",
            JSON.stringify(edit_campaign_details)
        );
    }

    function find_connection_sales_navigator(query) {
        var relation_index = query.search(/RELATIONSHIP/i);
        if (relation_index > 0) {
            var newStr = query.substr(0, relation_index);
            var paran = newStr.lastIndexOf('(');
            var newSubStr = query.substring(paran, query.length);
            var include = newSubStr.search(/INCLUDED/i);
            if (include > 0) {
                var newSubStr = query.substr(paran, include);
                if (newSubStr.includes('1st')) {
                    $(".campaign_pane.active").find("form").find('.connections').val('1');
                } else if (newSubStr.includes('2nd')) {
                    $(".campaign_pane.active").find("form").find('.connections').val('2');
                } else if (newSubStr.includes('3rd')) {
                    $(".campaign_pane.active").find("form").find('.connections').val('3');
                } else {
                    $(".campaign_pane.active").find("form").find('.connections').val('o');
                }
            } else {
                $(".campaign_pane.active").find("form").find('.connections').val('o');
            }
        } else {
            $(".campaign_pane.active").find("form").find('.connections').val('o');
        }
        $(".campaign_pane.active").find("form").find('.connections').prop('disabled', true);
        edit_campaign_details["connections"] = $(".campaign_pane.active").find("form").find('.connections').val();
        sessionStorage.setItem(
            "edit_campaign_details",
            JSON.stringify(edit_campaign_details)
        );
    }

    $(".connections").on("change", function (e) {
        edit_campaign_details["connection"] = $(this).val();
        sessionStorage.setItem(
            "edit_campaign_details",
            JSON.stringify(edit_campaign_details)
        );
    });
    $(".campaign_tab").on("click", function (e) {
        e.preventDefault();
        $(".campaign_tab").parent(".border_box").css({
            "background-color": "rgb(17 19 23)",
        });
        $(".campaign_tab").removeClass("active");
        $(this).addClass("active");
        var id = $(this).data("bs-target");
        $(".campaign_pane").removeClass("active");
        $("#" + id).addClass("active");
        $(".campaign_tab.active").parent(".border_box").css({
            "background-color": "#16adcb",
        });
        var new_form = $("#" + id).find("form");
        edit_campaign_details["type"] = new_form
            .find("#campaign_type")
            .val();
        sessionStorage.setItem(
            "edit_campaign_details",
            JSON.stringify(edit_campaign_details)
        );
        new_form
            .find("#campaign_name")
            .val(edit_campaign_details["name"]);
        if (new_form.attr("id") != "campaign_form_4") {
            new_form
                .find("#campaign_url")
                .val(edit_campaign_details["url"]);
        }
        if (
            new_form.attr("id") != "campaign_form_4" &&
            new_form.attr("id") != "campaign_form_3"
        ) {
            new_form
                .find("#connections")
                .val(edit_campaign_details["connection"]);
        }
    });
    $(".nxt_btn").on("click", function (e) {
        e.preventDefault();
        var form = $(".campaign_pane.active").find("form");
        form.submit();
    });
});
