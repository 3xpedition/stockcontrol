<section class="quick-entry card" aria-labelledby="quick-entry-title">
    <div class="quick-entry-heading">
        <span class="quick-entry-icon">✦</span>
        <div>
            <span class="pill">Atalho de lançamento</span>
            <h3 id="quick-entry-title">Cadastre em poucos segundos</h3>
            <p>Preencha o essencial e use <kbd>Tab</kbd> ou <kbd>Enter</kbd> para avançar.</p>
        </div>
    </div>
    <form class="quick-entry-form" method="post" action="{{ route('inventory.store') }}">
        @csrf
        <input type="hidden" name="unit" value="UN">
        <label>Nome do produto *<input autofocus required name="name" value="{{ old('name', request('name')) }}" placeholder="Ex.: Café torrado 500g"></label>
        <label>Quantidade *<input required type="number" step="0.001" min="0" name="quantity" value="{{ old('quantity', 0) }}"></label>
        <label>Preço de venda *<input required type="number" step="0.01" min="0" name="sale_price" value="{{ old('sale_price') }}" placeholder="0,00"></label>
        <button class="button primary" type="submit">Cadastrar <span>→</span></button>
    </form>
</section>
<script>
(() => {
    const form = document.querySelector('.quick-entry-form');
    if (!form) return;
    const fields = [...form.querySelectorAll('input:not([type="hidden"])')];
    fields.forEach((field, index) => field.addEventListener('keydown', event => {
        if (event.key !== 'Enter') return;
        event.preventDefault();
        const next = fields[index + 1];
        if (next) next.focus(); else form.requestSubmit();
    }));
})();
</script>
