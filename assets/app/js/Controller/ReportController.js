function docReady(fn) {
    if (document.readyState === "complete" || document.readyState === "interactive") {
        setTimeout(fn, 1);
    } else {
        document.addEventListener("DOMContentLoaded", fn);
    }
}

let charts = {};

docReady(function () {
    // Initialize Pie Chart
    var pieOptions = {
        series: window.pieSerials,
        chart: {
            type: 'pie',
            height: 300
        },
        labels: window.pieLabels,
        colors: window.pieColors,
        responsive: [{
            breakpoint: 480,
            options: {
                legend: {
                    position: 'bottom'
                }
            }
        }],
        legend: {
            position: 'bottom'
        }
    };

    charts.pie = new ApexCharts(document.querySelector("#chart"), pieOptions);
    charts.pie.render();

    // Initialize Location Chart
    if (window.locationLabels && window.locationLabels.length > 0) {
        renderLocationChart();
    }

    // Load additional data
    loadIssueTypeBreakdown();
    loadDailyTrend();
    loadDepartmentBreakdown();
    loadPendingList();
    
    if (window.isAdmin) {
        loadUserStats();
    }
});

function loadIssueTypeBreakdown() {
    axios.post('../_inc/ration_report.php', {
        action_type: "GET_ISSUE_TYPE_BREAKDOWN",
        year: window.workingYear
    }).then(response => {
        if (response.data.valid && response.data.data) {
            const data = response.data.data;
            const options = {
                series: [{
                    name: 'Issued',
                    data: [data.Oil || 0, data.Ghee || 0, data.Both || 0]
                }],
                chart: {
                    type: 'bar',
                    height: 300
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '55%',
                        endingShape: 'rounded'
                    },
                },
                dataLabels: {
                    enabled: true
                },
                xaxis: {
                    categories: ['Oil (10kg)', 'Ghee (10kg)', 'Both (5kg+5kg)'],
                },
                colors: ['#28a745'],
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            height: 250
                        }
                    }
                }]
            };
            charts.issueType = new ApexCharts(document.querySelector("#issueTypeChart"), options);
            charts.issueType.render();
        }
    }).catch(error => {
        console.error('Error loading issue type breakdown:', error);
    });
}

function loadDailyTrend() {
    axios.post('../_inc/ration_report.php', {
        action_type: "GET_DAILY_TREND",
        year: window.workingYear
    }).then(response => {
        if (response.data.valid && response.data.data) {
            const data = response.data.data;
            const dates = data.map(item => {
                const date = new Date(item.Date);
                return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            });
            const counts = data.map(item => item.Count);

            const options = {
                series: [{
                    name: 'Distributed',
                    data: counts
                }],
                chart: {
                    type: 'area',
                    height: 300,
                    zoom: {
                        enabled: true
                    },
                    toolbar: {
                        show: true
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    width: 2
                },
                xaxis: {
                    categories: dates,
                    labels: {
                        rotate: -45,
                        rotateAlways: false
                    }
                },
                colors: ['#007bff'],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.7,
                        opacityTo: 0.3,
                    }
                },
                tooltip: {
                    x: {
                        format: 'dd MMM'
                    }
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            height: 250
                        },
                        xaxis: {
                            labels: {
                                rotate: -90
                            }
                        }
                    }
                }]
            };
            charts.trend = new ApexCharts(document.querySelector("#trendChart"), options);
            charts.trend.render();
        }
    }).catch(error => {
        console.error('Error loading daily trend:', error);
    });
}

function loadDepartmentBreakdown() {
    axios.post('../_inc/ration_report.php', {
        action_type: "GET_DEPARTMENT_BREAKDOWN",
        year: window.workingYear
    }).then(response => {
        if (response.data.valid && response.data.data) {
            const data = response.data.data;
            
            // Sort by Distributed (Issued) in descending order
            data.sort((a, b) => (b.Issued || 0) - (a.Issued || 0));
            
            const departments = data.map(item => item.Department || 'Unknown');
            const issued = data.map(item => item.Issued || 0);
            const pending = data.map(item => item.Pending || 0);

            const options = {
                series: [{
                    name: 'Distributed',
                    data: issued
                }, {
                    name: 'Pending',
                    data: pending
                }],
                chart: {
                    type: 'bar',
                    height: Math.max(350, departments.length * 50),
                    stacked: true,
                    toolbar: {
                        show: true
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        dataLabels: {
                            total: {
                                enabled: true,
                                style: {
                                    fontSize: '13px',
                                    fontWeight: 900
                                }
                            }
                        }
                    },
                },
                dataLabels: {
                    enabled: true
                },
                stroke: {
                    width: 1,
                    colors: ['#fff']
                },
                xaxis: {
                    categories: departments,
                    title: {
                        text: 'Number of Employees'
                    }
                },
                yaxis: {
                    title: {
                        text: 'Department'
                    }
                },
                colors: ['#28a745', '#dc3545'],
                legend: {
                    position: 'top',
                    horizontalAlign: 'left'
                },
                fill: {
                    opacity: 1
                },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return val + " employees";
                        }
                    }
                },
                responsive: [{
                    breakpoint: 768,
                    options: {
                        chart: {
                            height: Math.max(300, departments.length * 40)
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }]
            };
            charts.department = new ApexCharts(document.querySelector("#departmentChart"), options);
            charts.department.render();
        }
    }).catch(error => {
        console.error('Error loading department breakdown:', error);
    });
}

function loadPendingList() {
    axios.post('../_inc/ration_report.php', {
        action_type: "GET_NOT_DISTRIBUTED",
        year: window.workingYear
    }).then(response => {
        if (response.data.valid && response.data.data) {
            const data = response.data.data;
            const listEl = document.getElementById('pendingList');
            
            if (data.length === 0) {
                listEl.innerHTML = '<div class="text-center p-3 text-success"><i class="bi bi-check-circle"></i> All distributed!</div>';
            } else {
                let html = '';
                data.slice(0, 10).forEach(emp => {
                    html += `
                        <div class="pending-item">
                            <strong>${emp.Employee_ID}</strong> - ${emp.Name || 'N/A'}<br>
                            <small class="text-muted">${emp.Department || 'N/A'} | ${emp.Designation || 'N/A'}</small>
                        </div>
                    `;
                });
                if (data.length > 10) {
                    html += `<div class="text-center p-2 text-muted"><small>And ${data.length - 10} more...</small></div>`;
                }
                listEl.innerHTML = html;
            }
        }
    }).catch(error => {
        console.error('Error loading pending list:', error);
        document.getElementById('pendingList').innerHTML = '<div class="text-center p-3 text-danger"><i class="bi bi-exclamation-triangle"></i> Error loading</div>';
    });
}

function loadUserStats() {
    axios.post('../_inc/ration_report.php', {
        action_type: "GET_ISSUED_BY_USER",
        year: window.workingYear
    }).then(response => {
        if (response.data.valid && response.data.data) {
            const data = response.data.data;
            const users = data.map(item => item.Fullname || 'Unknown');
            const counts = data.map(item => item.Count || 0);

            const options = {
                series: [{
                    name: 'Distributed',
                    data: counts
                }],
                chart: {
                    type: 'bar',
                    height: 300
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        distributed: true
                    },
                },
                dataLabels: {
                    enabled: true
                },
                xaxis: {
                    categories: users
                },
                colors: ['#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8'],
                legend: {
                    show: false
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            height: 250
                        }
                    }
                }]
            };
            charts.userStats = new ApexCharts(document.querySelector("#userStatsChart"), options);
            charts.userStats.render();
        }
    }).catch(error => {
        console.error('Error loading user stats:', error);
    });
}

function renderLocationChart() {
    const options = {
        series: [{
            name: 'Distributed',
            data: window.locationDistributed
        }, {
            name: 'Not Distributed',
            data: window.locationNotDistributed
        }],
        chart: {
            type: 'bar',
            height: Math.max(350, window.locationLabels.length * 50),
            stacked: true,
            toolbar: {
                show: true
            }
        },
        plotOptions: {
            bar: {
                horizontal: true,
                dataLabels: {
                    total: {
                        enabled: true,
                        style: {
                            fontSize: '13px',
                            fontWeight: 900
                        }
                    }
                }
            },
        },
        dataLabels: {
            enabled: true
        },
        stroke: {
            width: 1,
            colors: ['#fff']
        },
        xaxis: {
            categories: window.locationLabels,
            title: {
                text: 'Number of Employees'
            }
        },
        yaxis: {
            title: {
                text: 'Location'
            }
        },
        colors: ['#28a745', '#dc3545'],
        legend: {
            position: 'top',
            horizontalAlign: 'left'
        },
        fill: {
            opacity: 1
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return val + " employees";
                }
            }
        },
        responsive: [{
            breakpoint: 768,
            options: {
                chart: {
                    height: Math.max(300, window.locationLabels.length * 40)
                },
                legend: {
                    position: 'bottom'
                }
            }
        }]
    };

    charts.location = new ApexCharts(document.querySelector("#locationChart"), options);
    charts.location.render();
}

function refreshData() {
    location.reload();
}
