export function deleteRecord(encoded, url, elementId = "modal_delete") {
    const decoded = JSON.parse(atob(encoded));
    const modal = document.getElementById(elementId);
    const span = modal.querySelector("#span_text");

    if (span) {
        if (decoded.text) {
            span.style.fontWeight = "bold";
            span.innerHTML = '"' + decoded.text + '"';
        } else {
            span.style.fontWeight = "normal";
            span.innerHTML = "tersebut";
        }
    }

    const btn = modal.querySelector(".btn[data-id]");

    if (btn) {
        btn.setAttribute("data-id", decoded.id);
    } else {
        modal.querySelector("form").action = url + "/" + decoded.id;
    }

    modal.classList.add("is-visible");
}

export function pagination(elem, url, total, current) {
    return new Pagination(elem, { url, total, current });
}

export function eventDeleteChecked(isLivewire, url = null) {
    if (document.getElementById("btn_delete")) {
        document
            .getElementById("btn_delete")
            .addEventListener("click", function () {
                if (
                    document.querySelectorAll(".check-item:checked").length > 0
                ) {
                    document
                        .getElementById("modal_delete_checked")
                        .classList.add("is-visible");
                } else {
                    document
                        .getElementById("modal_alert")
                        .classList.add("is-visible");
                }
            });
    }

    if (isLivewire) {
        document
            .getElementById("btn_delete_checked")
            .addEventListener("click", function () {
                const ids = [];
                document
                    .querySelectorAll("[name='group[]']:checked")
                    .forEach(function (check) {
                        ids.push(check.value);
                    });

                Livewire.dispatch("destroy-checked", {
                    ids,
                });
            });
    } else {
        document
            .getElementById("btn_delete_checked")
            .addEventListener("click", function () {
                const form = document.getElementById("form_list");
                const method = form.querySelector("[name='_method']");

                form.action = url + "/delete";
                if (method) {
                    method.value = "POST";
                }
                form.submit();
            });
    }
}

export function eventFilter(url) {
    const filters = document.querySelectorAll(
        ".box-table__header .select-default, .box-table__header .select-search"
    );

    filters.forEach(function (select) {
        select.addEventListener("change", function () {
            const filterQuery = [];
            filters.forEach(function (filter) {
                if (filter.value) {
                    filterQuery.push(
                        "filter[" + filter.name + "]=" + filter.value
                    );
                }
            });

            window.location.href = url.replace(
                "filter=__",
                filterQuery.join("&")
            );
        });
    });
}

export function eventSearch(urlSearch, urlClear) {
    if (document.querySelector("[type='search']")) {
        document
            .querySelector("[type='search']")
            .addEventListener("keyup", function (e) {
                if (e.key === "Enter") {
                    window.location.href = urlSearch.replace(
                        "__",
                        e.target.value
                    );
                }
            });
    }

    if (document.querySelector("[data-clear='input']")) {
        document
            .querySelector("[data-clear='input']")
            .addEventListener("click", function () {
                window.location.href = urlClear;
            });
    }
}

export function eventSort(url) {
    const cells = Array.from(document.getElementsByClassName("cell-sorting"));

    for (const i in cells) {
        cells[i].addEventListener("click", function (e) {
            const sortDesc = e.target.classList.contains(
                "cell-sorting_active-asc"
            );

            window.location.href = url
                .replace("__sort__", e.target.getAttribute("data-no"))
                .replace("__desc__", sortDesc ? 1 : 0);
        });
    }
}

export function eventSync(url) {
    document.getElementById("btn_sync").addEventListener("click", function () {
        document.getElementById("modal_sync").classList.add("is-visible");
    });

    // submit form
    document
        .getElementById("btn_sync_checked")
        .addEventListener("click", function () {
            // hide modal
            document
                .getElementById("modal_sync")
                .classList.remove("is-visible");
            // run loading
            showLoader();
            const form = document.getElementById("form_list");

            form.action = url + "/sync";
            form.submit();
        });
}

export function eventFillingGetData(url) {
    document
        .getElementById("btn_filling_getdata")
        .addEventListener("click", function () {
            document
                .getElementById("modal_filling_getdata")
                .classList.add("is-visible");
        });

    // submit form
    document
        .getElementById("btn_filling_getdata_checked")
        .addEventListener("click", function () {
            // init form
            const form = document.getElementById("form_list");

            // if has select_idkurikulum, then check if it's not empty
            const selectIdKurikulum =
                document.getElementById("select_idkurikulum");
            if (selectIdKurikulum) {
                if (selectIdKurikulum.value === "") {
                    const spanErrorSelect = document.getElementById(
                        "select_idkurikulum_error"
                    );
                    spanErrorSelect.classList.remove("util_d-none");
                    spanErrorSelect.innerHTML = "Tahun Kurikulum harus diisi.";
                    return false;
                } else {
                    const inputKey = form.querySelector("input[name=key]");
                    inputKey.value = selectIdKurikulum.value;
                }
            }

            // hide modal
            document
                .getElementById("modal_filling_getdata")
                .classList.remove("is-visible");
            // run loading
            showLoader();
            // change action to url
            form.action = url;
            // change method to PUT
            const name = form.querySelector("input[name=_method]");
            if (name) name.value = "PUT";
            form.target = "";

            // find key input
            const inputAct = form.querySelector("input[name=act]");
            inputAct.value = "fillinggetdata";
            form.submit();
        });
}

// FIXME: Belum fix core report
export function showReport(url) {
    var method;

    const form = document.getElementById("form_list");
    // find key input
    const action = form.getAttribute("action");
    // // change method to POST
    const name = form.querySelector("input[name=_method]");
    if (name) {
        method = name.value;
        name.value = "POST";
    }
    form.action = url;
    form.target = "_blank";
    form.submit();

    // reset action
    form.action = action;
    form.target = "";
    if (name) {
        name.value = method;
    }
}

function showLoader() {
    document.querySelector(".full-page-loader").classList.remove("util_d-none");
}

function hideLoader() {
    document.querySelector(".full-page-loader").classList.add("util_d-none");
}
