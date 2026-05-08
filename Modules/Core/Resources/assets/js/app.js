import * as List from "./app-list";
import AirDatepicker from "air-datepicker";
import localeID from "air-datepicker/locale/id";

window.List = List;

export const initDatePicker = (input) => {
    new AirDatepicker(input, {
        autoClose: true,
        dateFormat: "dd/MM/yyyy",
        locale: localeID,
        onSelect: (fd, d, picker) => {
            input.dispatchEvent(new Event("input"));

            // reset quantum required checking
            document
                .querySelectorAll("form [type='submit']")
                .forEach((buttonSubmit) => {
                    let form = buttonSubmit.closest("form");
                    if (form) {
                        let status = false;
                        form.querySelectorAll(
                            "input:required, select:required, textarea:required"
                        ).forEach((input) => {
                            if (input.value == "") {
                                status = true;
                            }
                        });
                        buttonSubmit.disabled = status;
                    }
                });
        },
    });

    input.addEventListener("keydown", (e) => {
        e.preventDefault();
    });

    if (input.value) {
        if (input.value.includes("-")) {
            let arrDate = input.value.split("-");
            let formattedDate =
                arrDate[2] + "/" + arrDate[1] + "/" + arrDate[0];
            input.value = formattedDate;
            input.dispatchEvent(new Event("input"));
        }
    }
};

document.addEventListener("DOMContentLoaded", function () {
    // on datepicker listener
    const inputDate = document.querySelectorAll('input[input-format="date"]');
    inputDate.forEach((input) => {
        initDatePicker(input);
    });

    // on data-clear="input" listener
    document.querySelectorAll('[data-clear="input"]').forEach((element) => {
        element.addEventListener("click", (e) => {
            let input = element.closest("div").querySelector("input");
            input.value = "";
            input.dispatchEvent(new Event("input"));
        });
    });

    // validasi utk input number (handle min, max, dan float)
    function validateNumberInput(input, limit, comparator, makeFloat) {
        // validasi input kosong atau bukan angka
        if (input === "" || !/^\d+(\.\d+)?$/.test(input)) {
            return null;
        }

        // Parse input sebagai float atau integer berdasarkan nilai makeFloat
        let number = makeFloat ? parseFloat(input) : parseInt(input, 10);
        limit = parseFloat(limit);

        // Jika input melebihi batas, set input ke nilai batas
        if (comparator(number, limit)) {
            number = limit;
        }

        return makeFloat ? number.toFixed(2) : number.toString();
    }

    // query selector all for type number & has data-number-max
    document
        .querySelectorAll('input[type="number"][data-number-max]')
        .forEach(function (element) {
            element.addEventListener("change", function () {
                let maxNumber = this.getAttribute("data-number-max");
                let value = this.value.trim();
                let isFloat = this.hasAttribute("data-number-float");
                this.value = validateNumberInput(
                    value,
                    maxNumber,
                    function (number, limit) {
                        return number > limit;
                    },
                    isFloat
                );
            });
        });

    // query selector all for type number & has data-number-min
    document
        .querySelectorAll('input[type="number"][data-number-min]')
        .forEach(function (element) {
            element.addEventListener("change", function () {
                let minNumber = this.getAttribute("data-number-min");
                let value = this.value.trim();
                let isFloat = this.hasAttribute("data-number-float");
                this.value = validateNumberInput(
                    value,
                    minNumber,
                    function (number, limit) {
                        return number < limit;
                    },
                    isFloat
                );
            });
        });
});
