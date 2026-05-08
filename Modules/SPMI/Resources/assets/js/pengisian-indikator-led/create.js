(() => {
    // get elemets li.dropdown vanila javascript
    var dropdowns = document.querySelectorAll("a.link-sidebar-tree");
    // click event
    dropdowns.forEach((dropdown) => {
        dropdown.addEventListener("click", (e) => {
            let id = dropdown.getAttribute("data-id");
            // find ul child
            let ul = document.querySelector("ul#" + id);

            // add class open
            if (ul.classList.contains("open")) {
                dropdown.classList.remove("open");
                ul.classList.remove("open");
            } else {
                ul.classList.add("open");
                dropdown.classList.add("open");
            }
        });
    });

    // on clik button data-type=editip on vanila js
    var editip = document.querySelectorAll("button[data-type=editip]");
    editip.forEach((btn) => {
        btn.addEventListener("click", (e) => {
            // get data id
            let id = btn.getAttribute("data-id");

            // add param _get to url
            let url = window.location.href;
            let param = url.split("?");

            let params = param[1].split("&");
            // search param _get
            let search_id = params.find((s) => {
                return s.indexOf("id_record") > -1;
            });

            if (!search_id) {
                // create param _get
                search_id = "id_record=" + id;
                params.push(search_id);
            } else {
                // replace param _get
                let index = params.indexOf(search_id);
                params[index] = "id_record=" + id;
            }

            // join param _get
            params = params.join("&");

            // join
            url = param[0] + "?" + params;

            // redirect url
            window.location.href = url;
        });
    });

    // on clik button data-type=cancelip on vanila js
    var cancelip = document.querySelectorAll("button[data-type=cancelip]");
    cancelip.forEach((btn) => {
        btn.addEventListener("click", (e) => {
            // get data id
            let id = btn.getAttribute("data-id");

            // add param _get to url
            let url = window.location.href;
            let param = url.split("?");

            let params = param[1].split("&");
            // search param _get
            let search_id = params.find((s) => {
                return s.indexOf("id_record") > -1;
            });

            if (search_id) {
                // remove param _get
                let index = params.indexOf(search_id);
                params.splice(index, 1);
            }

            // join param _get
            params = params.join("&");

            // join
            url = param[0] + "?" + params;

            // redirect url
            window.location.href = url;
        });
    });

    // on click button data-type=deleteip on vanila js
    var deleteip = document.querySelectorAll("button[data-type=deleteip]");
    deleteip.forEach((btn) => {
        btn.addEventListener("click", (e) => {
            // show modal
            const modal = document.getElementById("modal_delete");

            // open modal
            modal.classList.add("is-visible");

            // get data id
            let id = btn.getAttribute("data-id");

            // add to input name=iddelete
            let input = modal.querySelector("input[name=iddelete]");
            input.value = id;
        });
    });

    // on click button data-type=saveip on vanila js
    var saveip = document.querySelectorAll("button[data-type=saveip]");
    saveip.forEach((btn) => {
        btn.addEventListener("click", (e) => {
            // get data id
            let id = btn.getAttribute("data-id");
            // get data act
            let act = btn.getAttribute("data-act");

            // get data key
            let inputKey = document.querySelector("input[name=key]");
            inputKey.value = id;
            // get data act
            let inputAct = document.querySelector("input[name=act]");
            inputAct.value = act;

            // submit form
            document.querySelector("form#form-ip").submit();
        });
    });

    // #btn_add append form to table.tb-poin tbody
    var btn_add = document.querySelector("#btn_add");
    if (btn_add) {
        btn_add.addEventListener("click", (e) => {
            // get table
            let table = document.querySelector("table.tb-poin");
            // get tbody
            let tbody = table.querySelector("tbody");
            // get tr
            let tr = tbody.querySelector("tr.template-form-keypoint");
            // clone tr
            let clone = tr.cloneNode(true);
            // clone delete class
            clone.classList.remove("template-form-keypoint");
            // append to tbody
            tbody.appendChild(clone);
        });
    }

    document.addEventListener("click", (e) => {
        // get class .btn_delete and delete element closest tr
        if (e.target && e.target.classList.contains("btn_delete")) {
            e.target.closest("tr").remove();
        }
    });

    // FIXME: masih inline quill js disini

    const wysiwyg = document.querySelector(".text-editor");
    if (wysiwyg) {
        // var BaseImageFormat = Quill.import('formats/image');
        // const ImageFormatAttributesList = [
        //     'alt',
        //     'height',
        //     'width',
        //     'style'
        // ];

        // class ImageFormat extends BaseImageFormat {
        //     static formats(domNode) {
        //         return ImageFormatAttributesList.reduce(function(formats, attribute) {
        //         if (domNode.hasAttribute(attribute)) {
        //             formats[attribute] = domNode.getAttribute(attribute);
        //         }
        //         return formats;
        //         }, {});
        //     }
        //     format(name, value) {
        //         if (ImageFormatAttributesList.indexOf(name) > -1) {
        //         if (value) {
        //             this.domNode.setAttribute(name, value);
        //         } else {
        //             this.domNode.removeAttribute(name);
        //         }
        //         } else {
        //         super.format(name, value);
        //         }
        //     }
        // }

        // limit image size on quill
        // Quill.register(ImageFormat, true);

        const qnQuillToolbarOptions = [
            [{ size: ["small", false, "large", "huge"] }], // custom dropdown

            ["bold", "italic", "underline"], // toggled buttons

            [{ list: "ordered" }, { list: "bullet" }, { align: [] }],

            ["link"],
        ];

        var quill = new Quill(".text-editor", {
            modules: {
                toolbar: qnQuillToolbarOptions,
            },
            theme: "snow",
        });

        // image handler
        // quill.getModule('toolbar').addHandler('image', function() {
        //     const input = document.createElement('input');
        //     input.setAttribute('type', 'file');
        //     input.setAttribute('accept', 'image/*');
        //     input.click();

        //     // Handle file selection
        //     input.onchange = async () => {
        //         const file = input.files[0];
        //         const fileSizeMB = file.size / (1024 * 1024); // Convert bytes to MB
        //         const maxSizeMB = 2; // Set your maximum size limit in MB

        //         if (fileSizeMB > maxSizeMB) {
        //             alert('Image size exceeds the maximum limit of 2MB.');
        //             return;
        //         }

        //         const reader = new FileReader();

        //         reader.onload = () => {
        //             const base64Image = reader.result;
        //             const range = this.quill.getSelection(true);

        //             if (range) {
        //                 this.quill.insertEmbed(range.index, 'image', base64Image, Quill.sources.USER);
        //             } else {
        //                 console.error('Unable to insert image at cursor position.');
        //             }
        //         };

        //         reader.readAsDataURL(file);
        //     };
        // });

        setText();
        quill.on("text-change", function (delta, oldDelta, source) {
            setText();
        });

        function setText() {
            var html = quill.root.innerHTML;
            var input = document.querySelector("input[name=uraian]");
            input.value = html;
        }
    }

    // event click collapse
    var collapse = document.querySelectorAll(".panel-group .panel-heading");
    collapse.forEach((el) => {
        el.addEventListener("click", (e) => {
            // get id from href
            let id = el.getAttribute("data-target");
            // get element by id
            let element = document.querySelector(id);
            // get icon from element
            let icon = el.querySelector("i.indicator-icon");
            // add class open
            if (element.classList.contains("collapsed")) {
                icon.classList.remove("in");
                element.classList.remove("collapsed");
            } else {
                icon.classList.add("in");
                element.classList.add("collapsed");
            }
        });
    });
    // END
})();
