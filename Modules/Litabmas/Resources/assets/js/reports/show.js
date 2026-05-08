(() => {

    // click id #goSubmitReport
    const goSubmitReport = document.getElementById("goSubmitReport");
    if (goSubmitReport) {
        goSubmitReport.addEventListener("click", function () {
            const form = document.getElementById("form_list");
            const pageback = form.querySelector('input[name="pageback"]');
            pageback.value = "1";
            form.target = '';
            goSubmit();
        });
    }

    // click id #goSubmitBlank
    const goSubmitBlank = document.getElementById("goSubmitBlankReport");
    if (goSubmitBlank) {
        goSubmitBlank.addEventListener("click", function () {
            const form = document.getElementById("form_list");
            // find input pageback
            const pageback = form.querySelector('input[name="pageback"]');
            pageback.value = "0";
            if (validateForm()) {
                form.target = '_blank';
            } else {
                form.target = '';
            }
            goSubmit();
        });
    }

})();

function goSubmit() {
    const form = document.getElementById("form_list");
    form.submit();
}

function validateForm() {
    // status true if input mandatory is fully filled
    var isValidated = true;
    const form = document.getElementById("form_list");
    // get all inputs inside form name=[];
    form.querySelectorAll("input:required, select:required, textarea:required").forEach((input) => {
        if (input.value === "") {
            isValidated = false;
        }
    });

    if (isValidated) {
        // remove alert 
        const alert = document.querySelectorAll(".form-control__helper.error");
        alert.forEach((el) => {
            el.remove();
        });

    }

    return isValidated;
}
