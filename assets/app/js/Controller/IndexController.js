

function docReady(fn) {
    if (document.readyState === "complete" || document.readyState === "interactive") {
        setTimeout(fn, 1);
    } else {
        document.addEventListener("DOMContentLoaded", fn);
    }
}

docReady(function () {

    let formSubmittion = (form, url) => {
        const formData = new FormData(form[0]);
        axios.post(window.baseUrl + "/_inc/" + url, formData)
            .then(response => {
                if (response.data.valid == true) {
                    window.swal.fire({
                        position: 'top-end',
                        text: response.data.msg,
                        icon: "success",
                        showConfirmButton: false,
                        timer: 1500
                    });
                    setTimeout(function () {
                        window.location.reload();
                    }, 1500);
                } else {
                    window.swal.fire({
                        title: "Error!",
                        text: response.data.msg,
                        icon: "error",
                    });
                }
            }).catch(error => {
                if (error.response && error.response.data && error.response.data.errorMsg) {
                    window.swal.fire({
                        title: "Error!",
                        text: error.response.data.errorMsg,
                        icon: "error",
                    });
                } else {
                    console.error('Error:', error);
                }
            });
    }

    $(document).on("click", "#create-submit", function (e) {
        e.preventDefault();
        var $tag = $(this);
        var EmpID = $('#Employee_ID').val();
        var EmpNmae = $('#Employee_Name').val();
        var doj = $('#DOJ').val().trim();

        if (!EmpID) {
            $("#employee-id").text("please enter employee id. if employee id is missing in the file then press the button next to field.");
            return;
        }

        if (EmpID == 0) {
            $("#employee-id").text("employee id must be greater or equal to 2 digits.");
            return;
        }

        if (!EmpNmae) {
            $("#employee-name").text("please write employee name");
            return;
        }

        if (doj) {
            let isValid = true;
            if (!/^\d{2}\-\d{2}\-\d{4}$/.test(doj)) {
                $('#employee-doj').text('Date must be in the format DD-MM-YYYY.');
                isValid = false;
            } else {
                // Split the date into components
                let [day, month, year] = doj.split('-').map(Number);
                const currentYear = new Date().getFullYear();

                // Day validation
                if (day < 1 || day > 31) {
                    $('#employee-doj').text('Day must be between 01 and 31.');
                    isValid = false;
                }

                // Month validation
                if (month < 1 || month > 12) {
                    $('#employee-doj').text('Month must be between 01 and 12.');
                    isValid = false;
                }

                // Year validation
                if (year > currentYear || year < 1970) {
                    $('#employee-doj').text(`Year must be between 1970 and ${currentYear}.`);
                    isValid = false;
                }

                // If everything is valid, clear the error message
                if (isValid) {
                    $('#employee-doj').text('');
                } else {
                    return;
                }
            }
        }

        $("#ajaxWait").children().show();
        var form = $tag.closest('form');
        var actionUrl = form.attr("action");
        formSubmittion(form, actionUrl);
        $("#ajaxWait").children().hide();
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            let focusedElement = document.activeElement;
            if (focusedElement.tabIndex >= 1) {
                event.preventDefault();
                if (focusedElement.tabIndex === 14) {
                    document.getElementById('create-submit').click();
                } else {
                    // Move focus to the next element
                    let nextElement = document.querySelector(`[tabindex="${focusedElement.tabIndex + 1}"]`);
                    if (nextElement) {
                        nextElement.focus();
                    }
                }
            }
        }
    });

    $('#Employee_ID').on('input', function () {
        this.value = this.value.replace(/\D/g, '');
    });

    $('#Employee_Name').on('input', function () {
        if (this.value !== '') {
            $("#employee-name").text("");
        }
    });

    document.querySelectorAll('.tom-select').forEach((el)=>{
        new TomSelect(el, {
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            }
        });
    });

    initializeTurnoverReasonFields(document);

    function showError(message) {
        window.swal.fire({
            title: "Field Missing!",
            text: message,
            icon: "warning",
        });
    }

});

function initializeTurnoverReasonFields(root) {
    var rtSelectEl = root.getElementById ? root.getElementById('Resignation_Type') : root.querySelector('#Resignation_Type');
    var rotSelectEl = root.getElementById ? root.getElementById('Reason_of_Turnover') : root.querySelector('#Reason_of_Turnover');

    if (!rtSelectEl || !rotSelectEl || !rtSelectEl.tomselect || !rotSelectEl.tomselect) {
        return;
    }

    var loadReasons = function(value, selectedReason) {
        var rot = rotSelectEl.tomselect;
        rot.clear(true);
        rot.clearOptions();
        rot.addOption({ value: '', text: 'Select Reason' });

        if (!value) {
            rot.setValue('', true);
            return;
        }

        axios.get(window.baseUrl + "/_inc/value.php?action_type=GET_REASON_BY_TYPE&Resignation_Type_ID=" + value)
            .then(function(response) {
                if (response.data.valid && response.data.results) {
                    response.data.results.forEach(function(item) {
                        rot.addOption({ value: item.Reason_ID, text: item.Reason });
                    });
                    rot.refreshOptions(false);
                    if (selectedReason) {
                        rot.setValue(selectedReason, true);
                    }
                }
            }).catch(function(error) {
                console.error('Error fetching reasons:', error);
            });
    };

    rtSelectEl.tomselect.on('change', function(value) {
        rotSelectEl.dataset.selectedValue = '';
        loadReasons(value, '');
    });

    loadReasons(rtSelectEl.tomselect.getValue(), rotSelectEl.dataset.selectedValue || rotSelectEl.value);
}

$(document).delegate("#missing-id", "click", function (e) {
    e.stopPropagation();
    e.preventDefault();
    const postQuery = `action_type=GETMISSINGID`;
    axios.post("../_inc/indexing.php", postQuery, {
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).then(function (response) {
        if (response.data.valid == true) {
            $("#Employee_ID").val(response.data.ID);
        } else {
            window.swal.fire({
                title: "Error!",
                text: response.data.msg,
                icon: "error",
            });
        }
    }).catch(function (error) {
        if (error.response && error.response.data && error.response.data.errorMsg) {
            window.swal.fire({
                title: "Error!",
                text: error.response.data.errorMsg,
                icon: "error",
            });
        } else {
            console.error('Error:', error);
        }
    });

});

$(document).keydown(function (e) {
    // Check if Alt + S is pressed
    if (e.altKey && e.key.toLowerCase() === 's') {
        e.preventDefault(); // Prevent default action
        $('#create-submit').trigger('click'); // Trigger click event on the button
    }
});


function formatDate(input) {
    let value = input.value.replace(/\D/g, '');
    let formattedValue = '';

    if (value.length > 0) {
        let day = value.substring(0, 2);
        if (parseInt(day) > 31) {
            $('#employee-doj').text('Day can\'t be greater than 31.');
        }
        formattedValue = day;
    }
    if (value.length > 2) {
        let month = value.substring(2, 4);
        if (parseInt(month) > 12) {
            $('#employee-doj').text('Month can\'t be greater than 12.');
        }
        formattedValue += '-' + month;
    }
    if (value.length > 4) {
        let year = parseInt(value.substring(4, 8));
        const currentYear = new Date().getFullYear();

        if (year > currentYear) {
            $('#employee-doj').text(`Year can\'t be greater than ${currentYear}.`); // Limit the year to the current year
        } else if (year < 1970) {
            $('#employee-doj').text(`Year can\'t be less than 1970.`); // Limit the year to 1970
        } else {
            $('#employee-doj').text('');
        }
        formattedValue += '-' + year; // Year remains the same as it's not limited
    }

    input.value = formattedValue;
}
