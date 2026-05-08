const fileValidation = (elm, formatted, maxSize) => {
    let fileSize = elm.files.item(0).size;
    let file = Math.round(fileSize / 1024);
    let regexFile = new RegExp(`(.*?).(${formatted})$`);

    const { parentElement } = elm.parentElement.parentElement;
    const label = parentElement.querySelector(".form-control__label")?.innerText;

    if (!regexFile.test(elm.value)) {
        parentElement.querySelector(".custom-error")?.remove();
        // jika lebih dari 2mb
        const errorComponent = document.createElement("div");
        // add wire:ignore to avoid livewire re-render
        errorComponent.classList.add("custom-error");
        errorComponent.innerHTML = `
            <p class="error" style="color: #e84118">Format file tidak didukung</p>
        `;
        parentElement.appendChild(errorComponent);
        elm.value = null;

        if (document.getElementById("page_loading")) {
            setTimeout(() => {
                document.getElementById("page_loading").style.display = "none";
            }, 500);
        }

        return;
    }

    if (file >= maxSize) {
        parentElement.querySelector(".custom-error")?.remove();
        // jika lebih dari 2mb
        const errorComponent = document.createElement("div");
        errorComponent.classList.add("custom-error");
        errorComponent.innerHTML = `
            <p class="error" style="color: #e84118">${label} maksimal berukuran ${maxSize} kb</p>
        `;
        parentElement.appendChild(errorComponent);
        elm.value = null;

        if (document.getElementById("page_loading")) {
            setTimeout(() => {
                document.getElementById("page_loading").style.display = "none";
            }, 500);
        }

        return;
    }

    parentElement.querySelector(".custom-error")?.remove();

    return
};
