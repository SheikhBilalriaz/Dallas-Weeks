$(document).ready(function () {
    $(document).on('change', '#seat_options', filterInvoices);
});

function filterInvoices(e) {
    var selectedOption = $(this).find('option:selected');
    var id = selectedOption.val().replace('seat_', '');
    $.ajax({
        url: invoiceBySeatRoute.replace(':id', id),
        type: "GET",
        beforeSend: function () {
            let html = ``;
            for (let index = 0; index < 5; index++) {
                html += `
                    <tr id="table_row_${index}" class="seat_table_row skel_table_row">
                        <td class="seat_table_data" style="display: flex;">
                            <img style="width: 36px; height: 36px;" class="seat_img" src="${accImage}" alt="">
                            <div style="width: 250px; height: 35px; display:inline-block; border-radius: 15px;" bis_skin_checked="1"></div>
                        </td>
                        <td class="text-left seat_table_data">
                            <div style="width: 250px; height: 35px; border-radius: 15px;" bis_skin_checked="1"></div>
                        </td>
                        <td class="text-left seat_table_data">
                            <div style="width: 250px; height: 35px; border-radius: 15px;" bis_skin_checked="1"></div>
                        </td>
                        <td class="text-left seat_table_data">
                            <div style="width: 250px; height: 35px; border-radius: 15px;" bis_skin_checked="1"></div>
                        </td>
                        <td>
                            <div style="width: 250px; height: 35px; border-radius: 15px;" bis_skin_checked="1"></div>
                        </td>
                    </tr>
                `;
            }
            $('#invoice_row').html(html);
        },
        success: function (response) {
            if (response.success && response.invoices.length > 0) {
                let html = ``;
                response.invoices.forEach(function (element) {
                    const createdTimestamp = element.stripe_invoice.created * 1000;
                    const date = new Date(createdTimestamp);
                    const options = { day: '2-digit', month: 'short', year: 'numeric' };
                    const formattedDate = date.toLocaleDateString('en-GB', options).replace(',', '');
                    html += `
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="${accImage}" alt="">
                                    <strong>${element.company_info.name}</strong>
                                </div>
                            </td>
                            <td>${element.seat_info.email}</td>
                            <td class="inv_data">
                                Sed ut perspiciatis unde omnis iste natus error sit voluptatem
                            </td>
                            <td class="inv_date">${formattedDate}</td>
                            <td>
                                <a href="${downloadInvoiceRoute.replace(':id', element.id)}"
                                    class="black_list_activate download">Download</a>
                            </td>
                        </tr>
                    `;
                });
                $('#invoice_row').html(html);
            }
        },
        error: function (xhr, error, status) {
            let html = ``;
            html += `
                <tr>
                    <td colspan="5">
                        <div style="width: 50%; margin: 0 auto;"
                            class="empty_blacklist text-center">
                            <img style="margin-right: 0px;"
                                src="${emptyImage}" alt="">
                            <p>
                                Sorry, no results for that query
                            </p>
                        </div>
                    </td>
                </tr>
            `;
            $('#invoice_row').html(html);
        }
    });
}