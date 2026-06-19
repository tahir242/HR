function docReady(fn) {
    if (document.readyState === "complete" || document.readyState === "interactive") {
        setTimeout(fn, 1);
    } else {
        document.addEventListener("DOMContentLoaded", fn);
    }
}

docReady(function () {
    Dropzone.autoDiscover = false;
    var previewNode = document.querySelector("#template");
    previewNode.id = "";
    var previewTemplate = previewNode.parentNode.innerHTML;
    previewNode.parentNode.removeChild(previewNode);
    var myDropzone = new Dropzone("#pdf-dropzone", {
        url: "../_inc/upload.php",
        acceptedFiles: "application/pdf",
        parallelUploads: 100,
        previewTemplate: previewTemplate,
        autoQueue: false,
        previewsContainer: "#previews",
        clickable: ".fileinput-button"
    });
    myDropzone.on("addedfile", function (file) {
        file.previewElement.querySelector(".start").onclick = function () {
            myDropzone.enqueueFile(file);
        };
    });
    myDropzone.on("totaluploadprogress", function (progress) {
        document.querySelector("#total-progress .progress-bar").style.width = progress + "%";
    });
    myDropzone.on("sending", function (file) {
        document.querySelector("#total-progress").style.opacity = "1";
        file.previewElement.querySelector(".start").setAttribute("disabled", "disabled");
    });
    myDropzone.on("removedfile", async function (file) {
        // Validate file name (No special characters)
        const fileNamePattern = /^[\w\s\-]+\.pdf$/i;
        if (!fileNamePattern.test(file.name)) {
            Swal.fire({
                title: "Invalid File!",
                text: "File name contains special characters or is not a valid PDF.",
                icon: "error",
            });
            return;
        }

        // Prepare request data
        const formData = new URLSearchParams();
        formData.append('action_type', 'DELETEPDF');
        formData.append('file', file.name);

        try {
            const response = await axios.post('../_inc/upload.php', formData);
            if (response.data.valid) {
                // Swal.fire({
                //     text: response.data.msg,
                //     icon: "success",
                // });
                return;
            } else {
                return;
            }
        } catch (error) {
            console.error("Error:", error);
            // Swal.fire({
            //     title: "Error!",
            //     text: error.response?.data?.errorMsg || "Something went wrong!",
            //     icon: "error",
            // });
            return;
        }
    });

    myDropzone.on("complete", function (file) {
        // console.log(file.xhr.response);
        if (file.status == "success") {
            if (file.xhr.response == "Successfully Uploaded..!!") {
                file.previewElement.querySelector(".success").innerHTML = file.xhr.response;
            } else {
                file.previewElement.querySelector(".error").innerHTML = file.xhr.response;
            }
        };
    });
    document.querySelector("#actions .start").onclick = function () {
        myDropzone.enqueueFiles(myDropzone.getFilesWithStatus(Dropzone.ADDED));
    };

});