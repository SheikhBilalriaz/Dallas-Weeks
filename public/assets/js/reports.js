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
                chart.options.data[$i].dataPoints = viewsDataPoints;
                $i++;
            } else if (dataSpan == "inviteDataPoints") {
                chart.options.data[$i].dataPoints = inviteDataPoints;
                $i++;
            } else if (dataSpan == "messageDataPoints") {
                chart.options.data[$i].dataPoints = messageDataPoints;
                $i++;
            } else if (dataSpan == "inMailDataPoints") {
                chart.options.data[$i].dataPoints = inMailDataPoints;
                $i++;
            } else if (dataSpan == "followDataPoints") {
                chart.options.data[$i].dataPoints = followDataPoints;
                $i++;
            } else if (dataSpan == "emailDataPoints") {
                chart.options.data[$i].dataPoints = emailDataPoints;
                $i++;
            }
        });
        chart.render();
    });
});