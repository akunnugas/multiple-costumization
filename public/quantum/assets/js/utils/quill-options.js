(() => {
    // Init Toolbar Quill
    const qnQuillToolbarOptions = [
        [{ 'size': ['small', false, 'large', 'huge'] }],  // custom dropdown
        ['bold', 'italic', 'underline'],                  // toggled buttons
        [{ 'list': 'ordered'}, { 'list': 'bullet' }, { 'align': [] }],
        ['link', 'image', 'video']
    ];

    const disableQuillToolbar = (editorElement) => {
        const toolbarElement = editorElement.previousElementSibling; // Assuming toolbar comes before editor

        if (toolbarElement && toolbarElement.classList.contains('ql-toolbar')) {
            // Disable all toolbar buttons
            toolbarElement.querySelectorAll('button').forEach(function (button) {
                button.disabled = true;
            });
            // Disable all input fields
            toolbarElement.querySelectorAll('input').forEach(function (input) {
                input.disabled = true;
            });
            // Disable all selects
            toolbarElement.querySelectorAll('select').forEach(function (select) {
                select.disabled = true;
            });
            // Disable all textareas
            toolbarElement.querySelectorAll('textarea').forEach(function (textarea) {
                textarea.disabled = true;
            });
        }
    }

    const setEditorElement = (editorElement) => {
        if  (!editorElement.classList.contains('ql-container')) {
            const qnQuill = new Quill(editorElement, {
                modules: {
                    toolbar: qnQuillToolbarOptions
                },
                theme: 'snow'
            });
            
            editorElement.qnQuill = qnQuill;

            if (editorElement.classList.contains('ql-disabled')) {
                disableQuillToolbar(editorElement);
            }
        }
    }

    const setAllEditorElements = () => {
        document.querySelectorAll('.text-editor').forEach( (editorElement) => {
            setEditorElement(editorElement);
            
        });
    }

    document.addEventListener("DOMContentLoaded", () => {
        setAllEditorElements();
    });

    const observerTextEditor = new MutationObserver((mutationsList) => {
        mutationsList.forEach((mutation) => {
            if (mutation.type === 'childList') {
                const targetElement = mutation.target;
                const innerTextEditors = targetElement.querySelectorAll('.text-editor:not(.ql-container)');
                if (targetElement.classList.contains('text-editor') && !targetElement.classList.contains('ql-container')) {
                    setEditorElement(targetElement);
                }
                if (innerTextEditors.length > 0) {
                    innerTextEditors.forEach((innerTextEditor) => {
                        setEditorElement(innerTextEditor);
                    })
                }
            }
        });
    });

    observerTextEditor.observe(document.body, { 
        childList: true,
        subtree: true, 
    });
})();
