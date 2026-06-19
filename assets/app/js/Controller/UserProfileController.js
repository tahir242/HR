function docReady(fn) {
  if (document.readyState === "complete" || document.readyState === "interactive") {
    setTimeout(fn, 1);
  } else {
    document.addEventListener("DOMContentLoaded", fn);
  }
}

docReady(function () {

  var userid = window.userid;
  $(document).on('click', '.action', function (e) {
    e.stopImmediatePropagation();
    e.stopPropagation();
    e.preventDefault();
    let $tag = $(this);
    let action = $tag.data().action;
    const params = new URLSearchParams();
    params.append('User_ID', userid);
    axios.post(`${window.baseUrl}/_inc/user.php?action_type=${action}`, params)
      .then((response) => {
        if (response.data.valid == false) {
          window.swal.fire({
            title: "Note!",
            text: response.data.msg,
            icon: "info",
          });
        } else {
          $('#rawHtml').html(response.data);
        }
      })
      .catch((error) => {
        Swal.fire({
          position: 'top-end',
          icon: 'error',
          title: 'Something Went Wrong Contact System Administrator',
          showConfirmButton: false,
          timer: 1500
        });
      });
  });

  const data = new URLSearchParams();
  data.append('User_ID', userid);
  axios.post(`${window.baseUrl}/_inc/user.php?action_type=USERACCOUNTINFORMATION`, data)
    .then((response) => {
      if (response.data.valid == false) {
        window.swal.fire({
          title: "Note!",
          text: response.data.msg,
          icon: "info",
        });
      } else {
        $('#rawHtml').html(response.data);
      }
    })
    .catch((error) => {
      Swal.fire({
        position: 'top-end',
        icon: 'error',
        title: 'Something Went Wrong Contact System Administrator',
        showConfirmButton: false,
        timer: 1500
      });
      // console.error('Error:', error);
    });

  $(document).on("click", "#password_change_submit", function (e) {
    e.stopImmediatePropagation();
    e.stopPropagation();
    e.preventDefault();
    var $tag = $(this);
    var form = $($tag.data("form"));
    form.find(".alert").remove();
    var actionUrl = form.attr("action");

    axios({
      url: window.sso + "/_inc/" + actionUrl,
      method: "POST",
      data: new FormData(form[0]),
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
      .then(function (response) {
        window.swal.fire({
          title: "Success!",
          text: response.data.msg,
          icon: "success",
          showConfirmButton: false,
          timer: 1500
        });
        $("#reset").click();
      })
      .catch(function (error) {
        if (error.response && error.response.data && error.response.data.errorMsg) {
          window.swal.fire({
            title: "Error!",
            text: error.response.data.errorMsg,
            icon: "error",
            showConfirmButton: false,
            timer: 1500
          });
        } else {
          console.error('Error:', error);
        }
      });
  });

  $(document).delegate('#assignRole', 'click', function (e) {
    e.stopImmediatePropagation();
    e.stopPropagation();
    e.preventDefault();
    var $tag = $(this);
    var form = $($tag.data("form"));
    var actionUrl = form.attr("action");

    const fFormData = new FormData(form[0]);
    axios.post(`${window.baseUrl}/_inc/${actionUrl}`, fFormData)
      .then(function (response) {
        if (response.data.valid == false) {
          window.swal.fire({
            title: "Error!",
            text: response.data.msg,
            icon: "error",
          });
        } else if (response.data.valid == true) {
          Swal.fire({
            position: 'top-end',
            icon: 'success',
            title: response.data.msg,
            showConfirmButton: false,
            timer: 1500
          });
          setTimeout(() => {
            $("#sr").click();
          }, 1500);
        } else {
          console.log(response.data);
        }
      })
      .catch(function (error) {
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

  $(document).delegate('.user-permission-update', 'click', function (e) {
    e.stopImmediatePropagation();
    e.stopPropagation();
    e.preventDefault();
    var $tag = $(this);
    var form = $($tag.data("form"));
    var actionUrl = form.attr("action");

    const fFormData = new FormData(form[0]);
    axios.post(`${window.baseUrl}/_inc/${actionUrl}`, fFormData)
      .then(function (response) {
        if (response.data.valid == false) {
          window.swal.fire({
            title: "Error!",
            text: response.data.msg,
            icon: "error",
          });
        } else if (response.data.valid == true) {
          Swal.fire({
            position: 'top-end',
            icon: 'success',
            title: response.data.msg,
            showConfirmButton: false,
            timer: 1500
          });
          setTimeout(() => {
            $("#up").click();
          }, 1500);
        } else {
          console.log(response.data);
        }
      })
      .catch(function (error) {
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


});


