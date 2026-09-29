    <div class="pg-footer">
        <span><?=htmlspecialchars($fixUtf8(trim(implode(', ', array_filter([$empresa->calle ?? '', $empresa->ciudad ?? '', $empresa->estado ?? ''])))))?></span>
        <span class="pg-num">Hoja 1 de 1</span>
    </div>
</div>
</div><!-- #pdfDocument -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" crossorigin="anonymous"></script>
<script>
(function() {
    var btn = document.getElementById('btnDescargarPdf');
    var filename = '<?=$folio?>_<?=preg_replace('/[^a-zA-Z0-9]+/', '_', $titulo)?>.pdf';
    function descargar() {
        if (typeof html2pdf === 'undefined') { window.print(); return; }
        btn.disabled = true; btn.textContent = 'Generando…';
        html2pdf().set({
            margin: [8, 8, 8, 8], filename: filename,
            image: { type: 'jpeg', quality: 0.95 },
            html2canvas: { scale: 2, useCORS: true, scrollY: 0 },
            jsPDF: { unit: 'mm', format: 'letter', orientation: 'portrait' },
            pagebreak: { mode: ['css', 'legacy'] }
        }).from(document.getElementById('pdfDocument')).save().catch(function(){ window.print(); }).finally(function(){ btn.disabled = false; btn.textContent = 'Descargar PDF'; });
    }
    btn.addEventListener('click', descargar);
    if (new URLSearchParams(window.location.search).get('auto') === '1') { window.addEventListener('load', function(){ setTimeout(descargar, 800); }); }
})();
</script>
</body>
</html>
