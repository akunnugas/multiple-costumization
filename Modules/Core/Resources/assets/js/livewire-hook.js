import { initDatePicker } from "./app";

document.addEventListener("livewire:initialized", (data) => {
    Livewire.hook("element.init", ({ el }) => {
        if (el.tagName == "SELECT") {
            hookChoices(el);
        }

        if (el.tagName == "SELECT" && el.hasAttribute("multiple") && !el.classList.contains("select-multiple-v2")) {
            hookMultipleChoices(el);
        }

        if (el.getAttribute("input-format") == "date") {
            initDatePicker(el);
        }
    });

    Livewire.hook("morph.updated", ({ el }) => {
        hookUncheck(el);
    });

    document.querySelectorAll("select[multiple]:not(.select-multiple-v2)").forEach((el) => {
        hookMultipleChoices(el);
    });
});

function hookChoices(el) {
    if (el.classList.contains("select-default")) {
        new Choices(el, {
            allowHTML: true,
            searchEnabled: false,
            removeItemButton: false,
            shouldSort: false,
        });
    }

    if (el.classList.contains("select-search")) {
        new Choices(el, {
            allowHTML: true,
            searchEnabled: true,
            removeItemButton: false,
            shouldSort: false,
            searchResultLimit: 1000,
        });
    }

    if (el.classList.contains("select-multiple")) {
        new Choices(el, {
            allowHTML: true,
            shouldSort: false,
            delimiter: ",",
            editItems: true,
            removeItemButton: true,
        });
    }
}

function hookUncheck(el) {
    if ((el.name == "group-all" || el.name == "group[]") && el.checked) {
        el.checked = false;
    }
}

const hookMultipleChoices = (el) => {
    const listeners = {};

    const initMultipleSelect = () => {
        const name = el.getAttribute("name");
        const element = document.body.querySelector("#" + name);

        if (!element) {
            delete listeners[name];
            return;
        }

        const onChange = async () => {
            var dataSelected = [];
            document
                .getElementById(name)
                .querySelectorAll("option")
                .forEach((option) => {
                    if (option.selected) {
                        dataSelected.push(option.value);
                    }
                });

            Livewire.dispatch("updated-multiple-select", {
                name: name,
                values: dataSelected,
            });
        };

        if (!listeners[name]) {
            element.addEventListener("change", onChange, true);

            listeners[name] = onChange;
        } else {
            element.removeEventListener("change", listeners[name], true);
            delete listeners[name];
            initMultipleSelect();
        }
    };

    initMultipleSelect();
};
