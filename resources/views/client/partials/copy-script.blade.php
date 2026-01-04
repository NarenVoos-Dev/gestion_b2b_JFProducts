<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        const toast = document.createElement('div');
        toast.className = 'fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
        toast.textContent = 'Número de cuenta copiado';
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 2000);
    }).catch(function(err) {
        console.error('Error al copiar:', err);
    });
}
</script>
