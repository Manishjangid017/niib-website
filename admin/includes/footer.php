    </main>
    <footer class="admin-footer">
        <small>&copy; <?= date('Y') ?> NIIB Institute Admin Panel</small>
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Rich text editor (TinyMCE 6 GPL self-hosted — free, no API key required) -->
<script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.3/tinymce.min.js" referrerpolicy="origin"></script>
<script>
(function initRichEditor(){
    function boot(){
        if (!window.tinymce) { return; }
        var nodes = document.querySelectorAll('textarea.rich-editor');
        if (!nodes.length) return;
        tinymce.init({
            selector: 'textarea.rich-editor',
            height: 560,
            menubar: 'edit insert view format table',
            branding: false,
            promotion: false,
            plugins: 'code lists link image table autolink advlist searchreplace visualblocks fullscreen wordcount preview help',
            toolbar: 'undo redo | blocks fontsize | bold italic underline strikethrough | forecolor backcolor | bullist numlist | link image table | alignleft aligncenter alignright | removeformat | code preview fullscreen',
            toolbar_mode: 'sliding',
            block_formats: 'Paragraph=p; Heading 2=h2; Heading 3=h3; Heading 4=h4; Blockquote=blockquote',
            font_size_formats: '11px 12px 13px 14px 15px 16px 18px 20px 24px 28px 32px 36px 48px',
            content_style: "body{font-family:Manrope,system-ui,-apple-system,Segoe UI,Roboto,sans-serif;font-size:15px;color:#170338;line-height:1.7;padding:16px}h1,h2,h3,h4{font-family:'Bodoni Moda',Georgia,serif;color:#170338}",
            convert_urls: false,
            relative_urls: false,
            valid_elements: '*[*]',
            extended_valid_elements: 'details[open|class|style|id],summary[class|style|id],section[class|style|id],span[class|style|id],div[class|style|id],i[class|style|id],strong[class|style|id]'
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
</script>

<script>
// Confirm delete
document.querySelectorAll('a.confirm-delete, button.confirm-delete').forEach(function(a){
    a.addEventListener('click', function(e){
        if(!confirm('Are you sure you want to delete this?')) e.preventDefault();
    });
});
// Image preview
document.querySelectorAll('input[type="file"][data-preview]').forEach(function(inp){
    inp.addEventListener('change', function(){
        var prev = document.getElementById(inp.dataset.preview);
        if(prev && inp.files[0]){
            prev.src = URL.createObjectURL(inp.files[0]);
            prev.style.display = 'block';
        }
    });
});
// auto slug
document.querySelectorAll('input[data-slug-source]').forEach(function(inp){
    var src = document.getElementById(inp.dataset.slugSource);
    if(!src) return;
    src.addEventListener('input', function(){
        if(inp.dataset.autoSlug !== '0' && !inp.dataset.manual){
            inp.value = src.value.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');
        }
    });
    inp.addEventListener('input', function(){ inp.dataset.manual = '1'; });
});
</script>
</body>
</html>
