function docReady(fn) {
    if (document.readyState === "complete" || document.readyState === "interactive") {
        setTimeout(fn, 1);
    } else {
        document.addEventListener("DOMContentLoaded", fn);
    }
}

docReady(function () {

    // Hourly Report
    var ctx = document.getElementById('hourlyReport').getContext('2d');
    var labels = [];
    var shelfRecords = [];
    var uploadRecords = [];

    window.hourlydata.forEach(function (item) {
        labels.push(item.date + ' ' + item.hour + ':00');
        shelfRecords.push(item.index_Record);
        uploadRecords.push(item.upload_Record);
    });

    var myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'File Uploading',
                    data: uploadRecords,
                    backgroundColor: '#dc3545',
                    borderColor: '#ff4c4c',
                    fill: false,
                    tension: 0.4
                },
                {
                    label: 'File Indexing',
                    data: shelfRecords,
                    backgroundColor: '#28a745',
                    borderColor: '#28a745',
                    fill: false,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    ticks: {
                        beginAtZero: true
                    },
                    display: true,
                    scaleLabel: {
                        display: true,
                        labelString: "Value"
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Hourly Report - File Uploading & Indexing',
                    font: {
                        size: 16,
                    }
                }
            }
        }
    });

    // Daily Report
    var ctx = document.getElementById('dailyReport').getContext('2d');
    var labels = [];
    var fileIndexRecords = [];
    var fileUploadRecords = [];

    window.dailydata.forEach(function (item) {
        labels.push(item.date);
        fileIndexRecords.push(item.File_Index);
        fileUploadRecords.push(item.File_Upload);
    });

    var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'File Uploading',
                    data: fileUploadRecords,
                    backgroundColor: '#dc3545',
                    borderWidth: 1,
                },
                {
                    label: 'File Indexing',
                    data: fileIndexRecords,
                    backgroundColor: '#28a745',
                    borderWidth: 1,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    gridLines: {
                        drawBorder: false,
                        display: false
                    },
                    ticks: {
                        display: true,
                        beginAtZero: true
                    },
                    barPercentage: 0.9, // Adjusted for better appearance
                    categoryPercentage: 0.4
                },
                y: {
                    gridLines: {
                        drawBorder: false,
                        display: false
                    },
                    ticks: {
                        display: true,
                        beginAtZero: true
                    },
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                title: {
                    display: true,
                    text: 'Daily Report - File Uploading & Indexing',
                    font: {
                        size: 16,
                    }
                },
            }
        },
    });


    var total = window.totalIndexed + window.nonIndexed;
    var ctx = document.getElementById('indexChart').getContext('2d');
    var indexChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: [`Indexed`, `Non-Indexed`],
            datasets: [{
                data: [totalIndexed, nonIndexed],
                backgroundColor: ['#28a745', '#dc3545'],
                hoverOffset: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function (tooltipItem) {
                            let value = tooltipItem.raw;
                            return ` ${value} (${((value / total) * 100).toFixed(1)}%)`;
                        }
                    }
                },
                title: {
                    display: true,
                    text: 'Ex-Employee Indexing',
                    font: {
                        size: 16,
                    }
                },
            },
        }
    });

    var ctx = document.getElementById('missingDataChart').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: Object.keys(window.missingData),
            datasets: [{
                data: Object.values(window.missingData),
                backgroundColor: ['#f7b84b', '#f1556c', '#f7b84b', '#3bafda', '#007bff'],
                hoverOffset: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function (tooltipItem) {
                            let value = tooltipItem.raw;
                            return ` ${value} (${((value / total) * 100).toFixed(1)}%)`;
                        }
                    }
                },
                title: {
                    display: true,
                    text: 'Ex-Employee Missing Data',
                    font: {
                        size: 16,
                    }
                },
            }
        }
    });


});