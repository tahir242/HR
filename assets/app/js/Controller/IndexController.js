function autocomplete(inp) {
    var currentFocus;

    const debounceFetch = debounce(function (val, column) {
        closeAllLists();
        axios.get(`../_inc/value.php?action_type=AUTOCOMPLETE&value=${val}&column=${column}`)
            .then(function (response) {
                var arr = response.data.results;
                currentFocus = -1;

                var a = $("<div>").attr("id", inp.id + "autocomplete-list").addClass("autocomplete-items");
                $(inp).parent().append(a);
                console.log($(inp).parent());
                for (var i = 0; i < arr.length; i++) {
                    if (arr[i].toUpperCase().includes(val.toUpperCase())) {
                        var start = arr[i].toUpperCase().indexOf(val.toUpperCase());
                        var b = $("<div>").html(arr[i].substr(0, start) + "<strong>" + arr[i].substr(start, val.length) + "</strong>" + arr[i].substr(start + val.length));
                        b.append("<input type='hidden' value='" + arr[i] + "'>");
                        b.on("click", function (e) {
                            inp.value = $(this).find("input").val();
                            closeAllLists();
                        });
                        a.append(b);
                    }
                }
            })
            .catch(function (error) {
                console.error('Error fetching data:', error);
            });
    }, 300);

    inp.addEventListener("input", function (e) {
        var val = this.value;
        debounceFetch(val, inp.name);
    });

    inp.addEventListener("keydown", function (e) {
        var x = $("#" + this.id + "autocomplete-list div");
        if (e.keyCode === 40) {
            currentFocus++;
            addActive(x);
        } else if (e.keyCode === 38) {
            currentFocus--;
            addActive(x);
        } else if (e.keyCode === 13) {
            e.preventDefault();
            if (currentFocus > -1 && x.length && currentFocus < x.length) {
                x[currentFocus].click();
            } else {
                $('.submit').click();
            }
        }
    });

    function addActive(x) {
        if (!x) return false;
        removeActive(x);
        if (currentFocus >= x.length) currentFocus = 0;
        if (currentFocus < 0) currentFocus = (x.length - 1);
        x[currentFocus].classList.add("autocomplete-active");
    }

    function removeActive(x) {
        for (var i = 0; i < x.length; i++) {
            x[i].classList.remove("autocomplete-active");
        }
    }

    function closeAllLists(elmnt) {
        $(".autocomplete-items").each(function () {
            if (elmnt !== this && elmnt !== inp) {
                $(this).remove();
            }
        });
    }

    $(document).on("click", function (e) {
        closeAllLists(e.target);
    });
}

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
                if (focusedElement.tabIndex === 6) {
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

    autocomplete(document.getElementById("Department"));
    autocomplete(document.getElementById("Designation"));

    function showError(message) {
        window.swal.fire({
            title: "Field Missing!",
            text: message,
            icon: "warning",
        });
    }

});

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