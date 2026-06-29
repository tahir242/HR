function docReady(fn) {
    if (document.readyState === "complete" || document.readyState === "interactive") {
        setTimeout(fn, 1);
    } else {
        document.addEventListener("DOMContentLoaded", fn);
    }
}

docReady(function () {
    var form = $('#filterForm');
    var chartInstance = null;
    
    $('#resetBtn').on('click', function() {
        form[0].reset();
        $('#reportContainer').slideUp();
        $('#printBtnContainer').hide();
        if (chartInstance) {
            chartInstance.destroy();
            chartInstance = null;
        }
    });

    form.on('submit', function (e) {
        e.preventDefault();
        
        var fromMonth = $('#fromMonth').val(); // format: YYYY-MM
        var toMonth = $('#toMonth').val();     // format: YYYY-MM
        
        var d = new Date();
        var currentMonth = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0');
        
        if (fromMonth > currentMonth || toMonth > currentMonth) {
            window.swal.fire({ title: "Validation Error", text: "Dates cannot be in the future.", icon: "warning" });
            return;
        }
        if (toMonth < fromMonth) {
            window.swal.fire({ title: "Validation Error", text: "To Month cannot be earlier than From Month.", icon: "warning" });
            return;
        }

        window.swal.fire({
            title: 'Fetching Report...',
            text: 'Please wait while we gather the data.',
            allowOutsideClick: false,
            didOpen: () => {
                window.swal.showLoading();
            }
        });

        var formData = new FormData();
        formData.append('action_type', 'GET_TENURE');
        formData.append('fromMonth', fromMonth);
        formData.append('toMonth', toMonth);

        axios.post(window.baseUrl + '/_inc/report_tenure.php', formData)
            .then(function(response) {
                window.swal.close();
                if (response.data.valid) {
                    renderReport(response.data.data, fromMonth, toMonth);
                }
            })
            .catch(function(error) {
                var msg = "An error occurred while fetching the report.";
                if (error.response && error.response.data && error.response.data.errorMsg) {
                    msg = error.response.data.errorMsg;
                }
                window.swal.fire({ title: "Error!", text: msg, icon: "error" });
            });
    });

    function formatMonthYear(yyyymm) {
        var parts = yyyymm.split('-');
        var d = new Date(parts[0], parseInt(parts[1])-1, 1);
        return d.toLocaleDateString('en-US', { month: 'short', year: '2-digit' });
    }

    function renderReport(data, fromMonth, toMonth) {
        if (!data || parseInt(data.Total) === 0) {
            window.swal.fire({ title: "No Records", text: "No turnovers found for the selected period.", icon: "info" });
            $('#reportContainer').hide();
            $('#printBtnContainer').hide();
            return;
        }

        var startLabel = formatMonthYear(fromMonth);
        var endLabel = formatMonthYear(toMonth);
        $('#reportTitle').html(`Tenure of Service in SIUT (${startLabel} - ${endLabel})`);

        var b0 = parseInt(data.bucket_0_1) || 0;
        var b1 = parseInt(data.bucket_1_3) || 0;
        var b3 = parseInt(data.bucket_3_5) || 0;
        var b5 = parseInt(data.bucket_5_10) || 0;
        var b10 = parseInt(data.bucket_10_plus) || 0;
        var total = parseInt(data.Total) || 0;

        $('#val_0_1').text(b0);
        $('#val_1_3').text(b1);
        $('#val_3_5').text(b3);
        $('#val_5_10').text(b5);
        $('#val_10_plus').text(b10);
        $('#val_total').text(total);

        // Find the highest bucket for summary
        var buckets = [
            { name: 'Less than 1 year', val: b0 },
            { name: '1 - 3 years', val: b1 },
            { name: '3+ - 5 years', val: b3 },
            { name: '5+ - 10 Years', val: b5 },
            { name: '10+ Years', val: b10 }
        ];
        buckets.sort((a,b) => b.val - a.val);
        var topBucket = buckets[0];
        
        var pct = ((topBucket.val / total) * 100).toFixed(1);

        var summaryHtml = `During the period from <b class="text-primary">${startLabel}</b> to <b class="text-primary">${endLabel}</b>, a total of <b>${total}</b> employees departed from the organization. The majority of these turnovers occurred within the <b>${topBucket.name}</b> tenure bracket, which accounted for <b>${topBucket.val}</b> departures (<b>${pct}%</b> of the total).`;
        $('#summaryText').html(summaryHtml);

        if (chartInstance) {
            chartInstance.destroy();
        }

        var options = {
            series: [b0, b1, b3, b5, b10],
            chart: {
                type: 'pie',
                height: 350,
                fontFamily: 'inherit'
            },
            labels: ['Less than 1 year', '1 - 3 years', '3+ - 5 years', '5+ - 10 Years', '10+'],
            colors: ['#008FFB', '#00E396', '#FEB019', '#FF4560', '#775DD0'],
            dataLabels: {
                enabled: true,
                formatter: function (val) {
                    return val.toFixed(1) + "%";
                },
                style: {
                    fontSize: '14px',
                    fontWeight: 'bold',
                    colors: ['#fff']
                },
                dropShadow: {
                    enabled: true,
                    top: 1,
                    left: 1,
                    blur: 1,
                    color: '#000',
                    opacity: 0.45
                }
            },
            legend: {
                position: 'bottom',
                fontSize: '13px'
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return val + " Employees";
                    }
                }
            }
        };

        // Don't render empty pie segments, it breaks labels sometimes
        if (total > 0) {
            chartInstance = new ApexCharts(document.querySelector("#tenureChart"), options);
            chartInstance.render();
        } else {
            $("#tenureChart").html('<p class="text-muted mt-5 text-center">No chart data available</p>');
        }

        $('#reportContainer').slideDown();
        $('#printBtnContainer').show();
    }
});
