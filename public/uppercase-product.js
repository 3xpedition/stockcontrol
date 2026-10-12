(() => {
    document.querySelectorAll('input[name="name"], input[name="q"]').forEach(input => {
        input.addEventListener('input', () => {
            const start = input.selectionStart;
            input.value = input.value.toLocaleUpperCase('pt-BR');
            input.setSelectionRange(start, start);
        });
    });
})();
