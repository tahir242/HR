/**
 * HELPER FUNCTIONS
 *
*/

//It restrict the non-numbers
var specialKeys = new Array();
specialKeys.push(8,46); //Backspace
function IsNumeric(e) {
    var keyCode = e.which ? e.which : e.keyCode;
    var ret = ((keyCode >= 48 && keyCode <= 57) || specialKeys.indexOf(keyCode) != -1);
    return ret;
}

function debounce(func, delay = 300) {
  let timer;
  return function(...args) {
    clearTimeout(timer);
    timer = setTimeout(() => func.apply(this, args), delay);
  };
}

// Return url parameter/query string
function getParameterByName(name, url) {
    if (!url) url = window.location.href;
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}

// live datetime
function liveDateTime (id) {
    var date = new Date().toLocaleString("en", {
        day : "numeric",
        month : "short",
        year : "numeric",
        hour : "numeric",
        minute : "numeric",
        second : "numeric",
        hour12: true,
        timeZone: "Asia/Karachi"
    });
    document.getElementById(id).innerHTML = date;
    setTimeout('liveDateTime("'+id+'");','1000');
    return true;
}

function is_numeric(t) {
    return ("number" == typeof t || "string" == typeof t && -1 === " \n\r\t\f\v\u2028\u2029　".indexOf(t.slice(-1))) && "" !== t && !isNaN(t);
}

function is_float(t) {
    return !(+t !== t || isFinite(t) && !(t % 1));
}

function generateHashCode (str) {
    var hash = 0;
    var char;
    if (str.length == 0) return hash;
    for (i = 0; i < str.length; i++) {
        char = str.charCodeAt(i);
        hash = ((hash<<5)-hash)+char;
        hash = hash & hash;
    }
    return hash;
};

function isHTML(str) {
  var doc = new DOMParser().parseFromString(str, "text/html");
  return Array.from(doc.body.childNodes).some(node => node.nodeType === 1);
}

function formatDate(inputDate, format = "datetime") {
    // Convert the input string date to a Date object
    var dateObj = new Date(inputDate);
  
    // Array to store month names
    var monthNames = [
      "01", "02", "03", "04", "05", "06", "07",
      "08", "09", "10", "11", "12"
    ];
  
    // Extract date components
    var day = dateObj.getDate().toString().padStart(2, '0');
    var month = monthNames[dateObj.getMonth()];
    var year = dateObj.getFullYear();
    var hours = dateObj.getHours();
    var minutes = dateObj.getMinutes();
    var seconds = dateObj.getSeconds();
    
    // Format the time as AM/PM
    var ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    hours = hours ? hours : 12;
    
    // Add leading zeros to minutes and seconds if needed
    minutes = String(minutes).padStart(2, '0');
    seconds = String(seconds).padStart(2, '0');
  
    // Assemble the formatted date string
    if(format == "datetime"){
        var formattedDate = day + '-' + month + '-' + year + ' ' + hours.toString().padStart(2, '0') + ':' + minutes + ' ' + ampm;
    }else{
        var monthDigits = dateObj.getMonth() + 1;
        var formattedMonthDigits = monthDigits.toString().padStart(2, '0');
        var formattedDate = day.toString().padStart(2, '0') + '-' + formattedMonthDigits + '-' + year;
    }
    
    return formattedDate;
}

function calculateAge(dateString) {
    const dob = new Date(dateString);
    const today = new Date();

    const yearsDiff = today.getFullYear() - dob.getFullYear();
    const monthsDiff = today.getMonth() - dob.getMonth();
    const daysDiff = today.getDate() - dob.getDate();

    let ageYears = yearsDiff;
    let ageMonths = monthsDiff;
    let ageDays = daysDiff;

    // Adjust for negative differences
    if (monthsDiff < 0 || (monthsDiff === 0 && daysDiff < 0)) {
      ageYears--;
      ageMonths += 12;
    }
    if (daysDiff < 0) {
      ageMonths--;
      const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, dob.getDate());
      ageDays = Math.floor((today - lastMonth) / (1000 * 60 * 60 * 24));
    }

    return {
      years: ageYears,
      months: ageMonths,
      days: ageDays
    };
}

function printRecord(formData) {
    axios.get(window.baseUrl + "/_inc/" + 'apply.php?action=PRINTFORM')
      .then(response => {
        let width = 750;
        let height = 650;
        let settings = "width=" + width + ",height=" + height + ",top=" + ((window.innerHeight - height) / 2) + ",left=" + ((window.innerWidth - width) / 2) + ",toolbars=no,scrollbars=yes,status=no,resizable=yes";
        let WindowObject = window.open("", "PrintWindow", settings);
        WindowObject.document.write(response.data);
        WindowObject.document.close();
        WindowObject.focus();
        myDelay = setInterval(checkReadyState, 10);
        function checkReadyState() {
          if (WindowObject.document.readyState == "complete") {
            clearInterval(myDelay);
            WindowObject.focus(); // necessary for IE >= 10
            WindowObject.print();
            // Check if the print dialog is closed
            setTimeout(function () {
              if (WindowObject && !WindowObject.closed) {
                WindowObject.close();
              }
            }, 500);
          }
        }
      })
      .catch(error => {
        // Handle errors
        Swal.fire({
          title: '<h1 style="color: red;">Error!</h1>',
          text: 'An error occurred while processing the request.',
        });
      });
  }