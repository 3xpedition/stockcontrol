(() => {
    const form = document.querySelector('.search');
    if (form?.classList.contains('bulk-search')) return;
    const input = form?.querySelector('input[name="q"]');
    const table = document.querySelector('.table-wrap');
    const empty = document.querySelector('.empty');
    let tbody = table?.querySelector('tbody');
    const target = table || empty;
    if (!form || !input || !target) return;

    let timer;
    let controller;
    const number = (value, decimals = 0) => Number(value || 0).toLocaleString('pt-BR', {
        minimumFractionDigits: decimals, maximumFractionDigits: decimals,
    });
    const escapeHtml = value => String(value ?? '').replace(/[&<>'"]/g, char => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;',
    }[char]));

    const render = data => {
        if (!data.products.length) {
            if (!tbody) { target.innerHTML = `<div class="empty"><div class="empty-icon">⌕</div><h3>Nenhum produto encontrado</h3><p>Não encontramos nada para “${escapeHtml(data.query)}”. <a href="${data.create_url}">Cadastrar agora</a></p></div>`; return; }
            tbody.innerHTML = `<tr><td colspan="6" class="no-data">Nenhum produto encontrado para “${escapeHtml(data.query)}”. <a href="${data.create_url}">Cadastrar agora</a></td></tr>`;
            return;
        }

        if (!tbody) {
            target.innerHTML = '<div class="table-wrap"><table><thead><tr><th>Produto</th><th>Código</th><th>Estoque</th><th>Preço de venda</th><th>Status</th><th></th></tr></thead><tbody></tbody></table></div>';
            tbody = target.querySelector('tbody');
        }

        tbody.innerHTML = data.products.map(product => `<tr>
            <td><a class="product-name" href="${product.edit_url}">${escapeHtml(product.name)}</a><small>${escapeHtml(product.unit)} · ${escapeHtml(product.barcode || 'sem código de barras')}</small></td>
            <td class="muted">${escapeHtml(product.code || '—')}</td>
            <td><strong>${number(product.quantity)}</strong> <span class="muted">${escapeHtml(product.unit)}</span></td>
            <td>R$ ${number(product.sale_price, 2)}</td>
            <td><span class="status ${product.low_stock ? 'low' : 'ok'}">● ${product.low_stock ? 'Estoque baixo' : 'Em dia'}</span></td>
            <td><a class="more" href="${product.edit_url}">···</a></td>
        </tr>`).join('');
    };

    const search = async () => {
        const term = input.value.trim();
        form.classList.add('is-loading');
        controller?.abort();
        controller = new AbortController();
        try {
            const response = await fetch(`/produtos/busca?q=${encodeURIComponent(term)}`, {
                headers: { Accept: 'application/json' }, signal: controller.signal,
            });
            if (!response.ok) throw new Error('search');
            render(await response.json());
            history.replaceState(null, '', term ? `/?q=${encodeURIComponent(term)}` : '/');
        } catch (error) {
            if (error.name !== 'AbortError') tbody.innerHTML = '<tr><td colspan="6" class="no-data">Não foi possível pesquisar agora. Tente novamente.</td></tr>';
        } finally { form.classList.remove('is-loading'); }
    };

    input.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(search, 250); });
    form.addEventListener('submit', event => { event.preventDefault(); clearTimeout(timer); search(); });
})();
