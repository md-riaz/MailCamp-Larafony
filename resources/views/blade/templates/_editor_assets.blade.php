<?php
$basePath = rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/');
$editorUploadUrl = $basePath . '/templates/upload-image';
?>
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
(function () {
    const textarea = document.querySelector('#html_content');
    if (!textarea || typeof ClassicEditor === 'undefined') {
        return;
    }

    class SimpleUploadAdapter {
        constructor(loader) {
            this.loader = loader;
        }

        upload() {
            return this.loader.file.then(file => new Promise((resolve, reject) => {
                const data = new FormData();
                data.append('upload', file);

                fetch('<?= htmlspecialchars($editorUploadUrl, ENT_QUOTES, 'UTF-8') ?>', {
                    method: 'POST',
                    body: data,
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(result => {
                    if (result.error) {
                        reject(result.error.message || 'Upload failed');
                        return;
                    }

                    resolve({ default: result.url });
                })
                .catch(error => reject(error?.message || 'Upload failed'));
            }));
        }

        abort() {}
    }

    function UploadAdapterPlugin(editor) {
        editor.plugins.get('FileRepository').createUploadAdapter = loader => new SimpleUploadAdapter(loader);
    }

    ClassicEditor
        .create(textarea, {
            extraPlugins: [UploadAdapterPlugin],
            toolbar: [
                'undo', 'redo', '|',
                'heading', '|',
                'bold', 'italic', 'link', '|',
                'bulletedList', 'numberedList', '|',
                'insertTable', 'blockQuote', 'imageUpload', '|',
                'htmlEmbed'
            ],
            table: {
                contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells']
            }
        })
        .then(editor => {
            const variableButtons = document.querySelectorAll('[data-insert-variable]');
            variableButtons.forEach(button => {
                button.addEventListener('click', () => {
                    editor.model.change(writer => {
                        editor.model.insertContent(writer.createText(button.getAttribute('data-insert-variable') || ''));
                    });
                });
            });
        })
        .catch(error => console.error('CKEditor initialization failed', error));
})();
</script>
