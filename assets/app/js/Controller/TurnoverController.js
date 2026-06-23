

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
        var EmpName = $('#Employee_Name').val();
        var doj = $('#DOJ').val().trim();
        var dob = $('#Date_of_Birth').val().trim();
        var dol = $('#Date_of_Leaving').val().trim();

        // Clear previous errors
        $(".error-message").text("");

        if (!EmpID) {
            $("#employee-id").text("Please enter Employee ID.");
            return;
        }

        if (EmpID == 0) {
            $("#employee-id").text("Employee ID must be greater or equal to 2 digits.");
            return;
        }

        if (!EmpName) {
            $("#employee-name").text("Please enter Employee Name.");
            return;
        }

        // Validate Gender
        var gender = $('#Gender').val();
        if (!gender) {
            showError("Please select Gender.");
            return;
        }

        // Validate Date of Birth (required)
        if (!dob) {
            $("#employee-dob").text("Please enter Date of Birth.");
            return;
        }
        if (!validateDateField(dob, '#employee-dob')) return;

        // Validate Department
        var department = $('#Department').val();
        if (!department) {
            showError("Please select Department.");
            return;
        }

        // Validate Designation
        var designation = $('#Designation').val();
        if (!designation) {
            showError("Please select Designation.");
            return;
        }

        // Validate Location
        var location = $('#Location').val();
        if (!location) {
            showError("Please select Location.");
            return;
        }

        // Validate Date of Joining (required)
        if (!doj) {
            $("#employee-doj").text("Please enter Date of Joining.");
            return;
        }
        if (!validateDateField(doj, '#employee-doj')) return;

        // Validate Date of Leaving (required)
        if (!dol) {
            $("#employee-dol").text("Please enter Date of Leaving.");
            return;
        }
        if (!validateDateField(dol, '#employee-dol')) return;

        // Validate Employee Category
        var empCategory = $('#Employee_Category').val();
        if (!empCategory) {
            showError("Please select Employee Category.");
            return;
        }

        // Validate Resignation Type
        var resType = $('#Resignation_Type').val();
        if (!resType) {
            showError("Please select Resignation Type.");
            return;
        }

        // Validate Reason of Turnover
        var reason = $('#Reason_of_Turnover').val();
        if (!reason) {
            showError("Please select Reason of Turnover.");
            return;
        }

        // Validate PDF file if selected (optional)
        var fileInput = document.getElementById('Scan');
        if (fileInput && fileInput.files.length > 0) {
            var file = fileInput.files[0];
            var ext = file.name.split('.').pop().toLowerCase();
            if (ext !== 'pdf') {
                $("#scan-error").text("Only PDF files are allowed.");
                return;
            }
        }


        $("#ajaxWait").children().show();
        var form = $tag.closest('form');
        var actionUrl = form.attr("action");
        formSubmittion(form, actionUrl);
        $("#ajaxWait").children().hide();
    });

    function validateDateField(dateVal, errorSelector) {
        if (!/^\d{2}\-\d{2}\-\d{4}$/.test(dateVal)) {
            $(errorSelector).text('Date must be in the format DD-MM-YYYY.');
            return false;
        }

        let [day, month, year] = dateVal.split('-').map(Number);
        const currentYear = new Date().getFullYear();

        if (day < 1 || day > 31) {
            $(errorSelector).text('Day must be between 01 and 31.');
            return false;
        }
        if (month < 1 || month > 12) {
            $(errorSelector).text('Month must be between 01 and 12.');
            return false;
        }
        if (year > currentYear || year < 1970) {
            $(errorSelector).text(`Year must be between 1970 and ${currentYear}.`);
            return false;
        }

        $(errorSelector).text('');
        return true;
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            let focusedElement = document.activeElement;
            if (focusedElement.tabIndex >= 1) {
                event.preventDefault();
                if (focusedElement.tabIndex === 15) {
                    document.getElementById('create-submit').click();
                } else {
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
        if (this.value !== '') {
            $("#employee-id").text("");
        }
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

    var rtSelectEl = document.getElementById('Resignation_Type');
    var rotSelectEl = document.getElementById('Reason_of_Turnover');

    if (rtSelectEl && rotSelectEl && rtSelectEl.tomselect && rotSelectEl.tomselect) {
        rtSelectEl.tomselect.on('change', function(value) {
            var rot = rotSelectEl.tomselect;
            rot.clear(true);
            rot.clearOptions();
            if (value) {
                axios.get(window.baseUrl + "/_inc/value.php?action_type=GET_REASON_BY_TYPE&Resignation_Type_ID=" + value)
                    .then(function(response) {
                        if (response.data.valid && response.data.results) {
                            response.data.results.forEach(function(item) {
                                rot.addOption({value: item.Reason_ID, text: item.Reason});
                            });
                        }
                    }).catch(function(error) {
                        console.error('Error fetching reasons:', error);
                    });
            }
        });
    }

    function showError(message) {
        window.swal.fire({
            title: "Field Missing!",
            text: message,
            icon: "warning",
        });
    }

});

$(document).keydown(function (e) {
    // Check if Alt + S is pressed
    if (e.altKey && e.key.toLowerCase() === 's') {
        e.preventDefault();
        $('#create-submit').trigger('click');
    }
});


function formatDate(input) {
    let value = input.value.replace(/\D/g, '');
    let formattedValue = '';

    if (value.length > 0) {
        let day = value.substring(0, 2);
        if (parseInt(day) > 31) {
            $(input).next('.error-message').text('Day can\'t be greater than 31.');
        }
        formattedValue = day;
    }
    if (value.length > 2) {
        let month = value.substring(2, 4);
        if (parseInt(month) > 12) {
            $(input).next('.error-message').text('Month can\'t be greater than 12.');
        }
        formattedValue += '-' + month;
    }
    if (value.length > 4) {
        let year = parseInt(value.substring(4, 8));
        const currentYear = new Date().getFullYear();

        if (year > currentYear) {
            $(input).next('.error-message').text(`Year can\'t be greater than ${currentYear}.`);
        } else if (year < 1970) {
            $(input).next('.error-message').text(`Year can\'t be less than 1970.`);
        } else {
            $(input).next('.error-message').text('');
        }
        formattedValue += '-' + year;
    }

    input.value = formattedValue;
}
