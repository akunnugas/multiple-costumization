// TODO: Perlu disesuaikan dengan Quantum-3

export function deleteRecord(encoded, url, elementId = "modal_delete") {
    const decoded = JSON.parse(atob(encoded));

    const modal = document.getElementById(elementId);

    const modalInstance = new bootstrap.Modal(modal);

    modalInstance.show();

    const btn = modal.querySelector(".btn[data-id]");

    if (btn) {
        btn.setAttribute("data-id", decoded.id);
    } else {
        modal.querySelector("form").action = url + "/" + decoded.id;
    }
}

export function eventPagination(url) {
    document.querySelectorAll("button[data-column='page']").forEach((elem) => {
        if (!elem) return;
        elem.addEventListener("click", (e) => {
            window.location.href = url.replace(
                "__page__",
                elem.getAttribute("data-value")
            );
        });
    });
}
export function eventSync(url) {
    document.getElementById("btn_sync").addEventListener("click", function () {
        const modal = document.getElementById("modal_sync");
        const modalInstance = new bootstrap.Modal(modal);
        modalInstance.show();
    });

    document
        .getElementById("btn_sync_checked")
        .addEventListener("click", function () {
            // Show loading spinner
            document
                .querySelector(".full-page-loader")
                .classList.remove("util_d-none");

            const form = document.getElementById("form_list");
            form.action = url + "/sync";
            form.submit();
        });
}
export function eventPerPagePagination(url) {
    document
        .querySelector("#select-navigation")
        .addEventListener("change", (e) => {
            window.location.href = url.replace("__perPage__", e.target.value);
        });
}

// TODO: Perlu disesuaikan dengan Quantum-3
export function eventDeleteChecked(isLivewire, url = null) {
    document
        .getElementById("button_delete")
        .addEventListener("click", function (event) {
            const checkedItems = document.querySelectorAll(
                '[name="group[]"]:checked'
            );
            if (checkedItems.length < 1) {
                const modal = document.getElementById("modal_checked");
                const modalInstance = new bootstrap.Modal(modal);
                modalInstance.show();
            } else {
                const modal = document.getElementById("modal_delete_checked");
                const modalInstance = new bootstrap.Modal(modal);
                modalInstance.show();
                document
                    .getElementById("delete-many-button")
                    .addEventListener("click", function () {
                        const form = document.getElementById("form_list");
                        form.action = url + "/delete";
                        form.submit();
                    });
            }
        });
}

export function eventFilter(url) {
    const filters = document.querySelectorAll(".select-filter");

    filters.forEach(function (select) {
        $(select).on('select2:select', function () {
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
        // select.addEventListener("change", function () {
        //     const filterQuery = [];
        //     filters.forEach(function (filter) {
        //         if (filter.value) {
        //             filterQuery.push(
        //                 "filter[" + filter.name + "]=" + filter.value
        //             );
        //         }
        //     });

        //     console.log(filterQuery)

        //     window.location.href = url.replace(
        //         "filter=__",
        //         filterQuery.join("&")
        //     );
        // });
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
            const tagName = e.target.tagName.toLowerCase();
            let thElement = e.target;
            if (tagName === "i" || tagName === "div") {
                thElement = e.target.closest("th");
            }
            const sortDesc = thElement.classList.contains(
                "cell-sorting_active-asc"
            );

            window.location.href = url
                .replace("__sort__", thElement.getAttribute("data-no"))
                .replace("__desc__", sortDesc ? 1 : 0);
        });
    }
}

function showLoader() {
    document.querySelector(".full-page-loader").classList.remove("util_d-none");
}

function hideLoader() {
    document.querySelector(".full-page-loader").classList.add("util_d-none");
}

document.addEventListener("alpine:init", () => {
    Alpine.store("checkbox", {
        checkAll: false,
        selectCheck: [],
        toggleCheckAll() {
            const checkgroup = document.querySelectorAll(
                "input[name='group[]']"
            );
            this.selectCheck = this.checkAll
                ? [...checkgroup].map((item) => item.value)
                : [];
        },
        toggleCheck(state) {
            if (this.selectCheck.includes(state)) {
                this.selectCheck = this.selectCheck.filter(
                    (id) => id !== state
                );
            } else {
                this.selectCheck.push(state);
            }
        },
    });
});
