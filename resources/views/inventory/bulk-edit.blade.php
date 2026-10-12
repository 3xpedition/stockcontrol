@extends('layouts.app')

@section('content')
<section class="content-head report-head">
    <div>
        <span class="pill">Atualização em lote</span>
        <h3>Editar NCM e CFOP</h3>
        <p>Pesquise, confira os produtos e selecione os itens que receberão os novos valores.</p>
    </div>
</section>

<form class="search report-search bulk-search" method="get">
    <span>⌕</span>
    <input name="q" value="{{ $term }}" placeholder="Digite nome, código ou código de barras..." autofocus>
    <button type="submit">Listar produtos</button>
</form>

@if($term === '')
    <div class="empty bulk-empty"><div class="empty-icon">⌕</div><h3>Pesquise um produto para começar</h3><p>Você pode buscar pelo nome, código ou código de barras.</p></div>
@elseif($products->isEmpty())
    <div class="empty bulk-empty"><div class="empty-icon">⌕</div><h3>Nenhum produto encontrado</h3><p>Não encontramos produtos para “{{ $term }}”.</p></div>
@else
    <form method="post" action="{{ route('inventory.bulk-update') }}">
        @csrf
        @method('PUT')
        <div class="bulk-apply-card card" aria-labelledby="bulk-apply-title">
            <div>
                <span class="pill">Atalho de edição</span>
                <h3 id="bulk-apply-title">Aplicar dados fiscais em lote</h3>
                <p>Informe NCM e CFOP uma vez para atualizar todos os produtos marcados abaixo.</p>
            </div>
            <div class="grid two">
                <label>NCM<input name="ncm" maxlength="20" placeholder="Ex.: 22030000"></label>
                <label>CFOP<input type="number" step="1" min="0" max="9999" name="cfop" placeholder="Ex.: 5405"></label>
            </div>
            <small class="bulk-cest-note">O CEST dos produtos selecionados será mantido somente quando o CFOP informado for 5405. Caso contrário, será limpo.</small>
        </div>

        <div class="bulk-toolbar"><div><strong><span class="selected-count">0</span> produto(s) selecionado(s)</strong><span>Marque os produtos que receberão os valores acima.</span></div><button class="button primary bulk-submit" type="submit" disabled>Aplicar alterações</button></div>
        <div class="table-wrap"><table class="bulk-table"><thead><tr><th><label class="bulk-selector"><input class="bulk-check-all" type="checkbox" data-select-all aria-label="Selecionar todos"><span>Todos</span></label></th><th>Produto</th><th>Estoque</th><th>NCM atual</th><th>CFOP atual</th></tr></thead><tbody>
        @foreach($products as $product)
            <tr>
                <td><label class="bulk-selector"><input class="bulk-check" type="checkbox" name="selected_tokens[]" value="{{ $product->getRouteKey() }}" aria-label="Selecionar {{ $product->name }}"><span>Selecionar</span></label></td>
                <td><a class="product-name" href="{{ route('inventory.edit', $product) }}">{{ $product->name }}</a><small>{{ $product->code ?: 'sem código' }} · {{ $product->barcode ?: 'sem código de barras' }}</small></td>
                <td><strong>{{ number_format($product->quantity, 0, ',', '.') }}</strong> <span class="muted">{{ $product->unit }}</span></td>
                <td class="muted">{{ $product->ncm ?: '—' }}</td>
                <td class="muted">{{ $product->cfop ?: '—' }}</td>
            </tr>
        @endforeach
        </tbody></table></div>
        <div class="bulk-footer"><span>Somente os produtos selecionados serão alterados.</span><button class="button primary bulk-submit" type="submit" disabled>Aplicar alterações</button></div>
    </form>
@endif
@endsection

@push('scripts')
<script>
const bulkChecks = [...document.querySelectorAll('.bulk-check')];
const selectAll = document.querySelector('[data-select-all]');
const selectedCount = document.querySelector('.selected-count');
const submitButtons = [...document.querySelectorAll('.bulk-submit')];
function updateSelectedCount() {
    const count = bulkChecks.filter((checkbox) => checkbox.checked).length;
    if (selectedCount) selectedCount.textContent = count;
    if (selectAll) selectAll.checked = bulkChecks.length > 0 && count === bulkChecks.length;
    submitButtons.forEach((button) => button.disabled = count === 0);
}
bulkChecks.forEach((checkbox) => checkbox.addEventListener('change', updateSelectedCount));
selectAll?.addEventListener('change', () => {
    bulkChecks.forEach((checkbox) => checkbox.checked = selectAll.checked);
    updateSelectedCount();
});
updateSelectedCount();
</script>
@endpush
