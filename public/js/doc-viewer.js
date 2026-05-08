const renderPDF = (container, pdfUrl, scale = 1) => {
    let pdfDoc = null;
    let currentPage = 1;

    if (window.location.pathname.includes("/v2/")) {
        pdfjsLib.GlobalWorkerOptions.workerSrc = "/v2/js/pdf.worker.min.js";
    } else {
        pdfjsLib.GlobalWorkerOptions.workerSrc = "/js/pdf.worker.min.js";
    }

    pdfjsLib.getDocument(pdfUrl).promise.then((pdfDoc_) => {
        pdfDoc = pdfDoc_;
        renderPage(currentPage);
    });

    const renderPage = (pageNum) => {
        if (pageNum > pdfDoc.numPages) {
            return;
        }

        pdfDoc.getPage(pageNum).then((page) => {
            const viewport = page.getViewport({
                scale,
            });

            const canvas = document.createElement("canvas");
            const context = canvas.getContext("2d");
            canvas.height = viewport.height;
            canvas.width = viewport.width;

            const renderContext = {
                canvasContext: context,
                viewport: viewport,
            };

            page.render(renderContext).promise.then(() => {
                const dataUrl = canvas.toDataURL("image/png");
                const img = document.createElement("img");
                img.src = dataUrl;
                img.style.width = "100%";

                const pageDiv = document.createElement("div");
                pageDiv.className = "pdf-page";
                pageDiv.appendChild(img);
                container.appendChild(pageDiv);
                pageDiv.style.width = "100%";
                pageDiv.style.paddingBottom = "32px";
                container.style.height = `0px`;

                currentPage++;
                renderPage(currentPage);
            });
        });
    };
};

const printExternal = (url) => {
    var printWindow = window.open(
        url,
        "Print",
        "left=200, top=80, width=950, height=680, toolbar=0, resizable=0"
    );

    printWindow.addEventListener(
        "load",
        function () {
            if (Boolean(printWindow.chrome)) {
                printWindow.focus();
                printWindow.print();
                setTimeout(function () {
                    printWindow.close();
                }, 500);
            } else {
                printWindow.print();
                printWindow.close();
            }
        },
        true
    );
};

const renderDOCX = async (container, docxUrl) => {
    const response = await fetch(docxUrl);
    const arrayBuffer = await response.arrayBuffer();

    docx.renderAsync(arrayBuffer, container, null, {
        ...docx.defaultOptions,
        renderChanges: true,
    }).then(async (x) => {
        const docxWrapper = document.querySelector(`.docx-wrapper`);
        docxWrapper.style.opacity = "0";
        docxWrapper.style.padding = "0";
        docxWrapper.style.background = "transparent";

        const docx = document.querySelectorAll(`.docx`);

        await new Promise((resolve) => {
            try {
                docx.forEach((element) => {
                    element.style.boxShadow = "none";
                    element.style.width = "100%";
                    element.style.padding = "32px";
                    resolve();
                });
            } catch (error) {
                reject();
            }
        });

        docxWrapper.style.opacity = "1";
    });
};
