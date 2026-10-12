(() => {
    if (!window.location.pathname.endsWith('/produtos/novo')) return;

    const escapeHtml = value => String(value ?? '').replace(/[&<>'"]/g, char => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;',
    }[char]));
    let timer;
    let controller;

    document.querySelectorAll('input[name="name"]').forEach(input => {
        const label = input.closest('label');
        if (!label) return;
        label.classList.add('autocomplete-field');
        const menu = document.createElement('div');
        menu.className = 'product-autocomplete';
        menu.setAttribute('role', 'listbox');
        label.appendChild(menu);

        const close = () => { menu.innerHTML = ''; menu.classList.remove('visible'); };
        const search = async () => {
            const term = input.value.trim();
            if (term.length < 2) { close(); return; }
            controller?.abort();
            controller = new AbortController();
            try {
                const response = await fetch(`/produtos/busca?q=${encodeURIComponent(term)}`, {
                    headers: { Accept: 'application/json' }, signal: controller.signal,
                });
                if (!response.ok) throw new Error('autocomplete');
                const data = await response.json();
                if (!data.products.length) { close(); return; }
                menu.innerHTML = `<div class="autocomplete-label">Produto já cadastrado</div>${data.products.map(product => `<a class="autocomplete-item" role="option" href="${product.edit_url}"><span class="autocomplete-name">${escapeHtml(product.name)}</span><small>${escapeHtml(product.unit)} · Estoque: ${Number(product.quantity || 0).toLocaleString('pt-BR')}</small><span class="autocomplete-arrow">→</span></a>`).join('')}`;
                menu.classList.add('visible');
            } catch (error) { if (error.name !== 'AbortError') close(); }
        };
        input.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(search, 250); });
        input.addEventListener('focus', () => { if (input.value.trim().length >= 2) search(); });
        document.addEventListener('click', event => { if (!label.contains(event.target)) close(); });
    });
})();
