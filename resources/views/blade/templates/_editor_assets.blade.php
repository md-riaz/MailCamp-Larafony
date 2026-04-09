<?php
$basePath = rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/');
$editorUploadUrl = $basePath . '/templates/upload-image';
?>
<style>
.merge-variable-chip {
    border: 1px solid #d0d7e2;
    background: #f8fafc;
    color: #334155;
    border-radius: 999px;
    padding: 4px 10px;
    font-size: 12px;
    line-height: 1.2;
    font-weight: 600;
}

.merge-variable-chip:hover {
    background: #eef2ff;
    border-color: #c7d2fe;
    color: #312e81;
}

.editor-toolbar-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 8px;
}

.editor-helper-text {
    color: #64748b;
    font-size: 12px;
}

.cke_contents {
    min-height: 520px;
}
</style>
<script src="/js/ckeditor_fullpage_build.js"></script>
<script>
(function () {
    const textarea = document.querySelector('#html_content');
    if (!textarea || typeof CKEDITOR === 'undefined') {
        return;
    }

    const label = document.querySelector('label[for="html_content"]');
    if (label && !document.getElementById('editor-helper-text')) {
        const toolbarRow = document.createElement('div');
        toolbarRow.className = 'editor-toolbar-row';
        toolbarRow.id = 'editor-helper-text';

        const left = document.createElement('div');
        left.className = 'editor-helper-text';
        left.textContent = 'Full-document HTML editor with source mode, image upload, and merge-variable insert. Paste complete HTML including <html>, <head>, and <body>.';

        toolbarRow.appendChild(left);
        label.parentElement.insertAdjacentElement('afterend', toolbarRow);
    }

    CKEDITOR.dtd.$removeEmpty.span = false;
    CKEDITOR.dtd.$removeEmpty.i = false;
    CKEDITOR.config.versionCheck = false;

    const editor = CKEDITOR.replace('html_content', {
        versionCheck: false,
        height: 520,
        fullPage: true,
        allowedContent: true,
        extraAllowedContent: '*(*);*{*}',
        removeButtons: '',
        extraPlugins: 'codesnippet,colorbutton,font,justify,fullpage',
        removePlugins: 'exportpdf,scayt,wsc,uploadimage,image2',
        toolbar: [
            { name: 'document', items: ['Source', '-', 'Preview'] },
            { name: 'clipboard', items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo'] },
            { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', '-', 'RemoveFormat'] },
            { name: 'colors', items: ['TextColor', 'BGColor'] },
            { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'] },
            { name: 'links', items: ['Link', 'Unlink'] },
            { name: 'insert', items: ['Image', 'Table', 'HorizontalRule', 'SpecialChar', 'CodeSnippet'] },
            { name: 'styles', items: ['Format', 'Font', 'FontSize'] },
            { name: 'tools', items: ['Maximize'] }
        ],
        codeSnippet_theme: 'monokai_sublime',
        filebrowserUploadUrl: '<?= htmlspecialchars($editorUploadUrl, ENT_QUOTES, 'UTF-8') ?>',
        filebrowserImageUploadUrl: '<?= htmlspecialchars($editorUploadUrl, ENT_QUOTES, 'UTF-8') ?>',
        uploadUrl: '<?= htmlspecialchars($editorUploadUrl, ENT_QUOTES, 'UTF-8') ?>'
    });

    window.mailcampEditor = editor;

    const variableButtons = document.querySelectorAll('[data-insert-variable]');
    variableButtons.forEach(button => {
        button.addEventListener('click', () => {
            const token = button.getAttribute('data-insert-variable') || '';
            editor.focus();
            editor.insertText(token);
        });
    });
})();
</script>
