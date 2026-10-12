@extends('layouts.app')

@section('content')
<section class="content-head report-head">
    <div>
        <span class="pill">Relatório de atenção</span>
        <h3>Estoque com preço de venda pendente</h3>
        <p>Produtos com quantidade disponível, mas sem preço de venda cadastrado.</p>
    </div>
    <span class="report-count">{{ $products->total() }} {{ $products->total() === 1 ? 'produto' : 'produtos' }}</span>
</section>

<form class="search report-search" method="get">
    <span>⌕</span>
    <input name="q" value="{{ $term }}" placeholder="Pesquisar produto no relatório...">
    <button type="submit">Buscar</button>
</form>

@if($products->isEmpty())
    <div class="empty">
        <div class="empty-icon">✓</div>
        <h3>Nenhum produto pendente</h3>
        <p>Todos os produtos com estoque disponível já têm preço de venda cadastrado.</p>
        <a class="button primary" href="{{ route('inventory.index') }}">Voltar ao estoque</a>
    </div>
@else
    <div class="table-wrap"><table><thead><tr><th>Produto</th><th>Código</th><th>Estoque</th><th>Preço de venda</th><th>Status</th><th></th></tr></thead><tbody>
    @foreach($products as $product)
        <tr>
            <td><a class="product-name" href="{{ route('inventory.edit', $product) }}">{{ $product->name }}</a><small>{{ $product->unit }} · {{ $product->barcode ?: 'sem código de barras' }}</small></td>
            <td class="muted">{{ $product->code ?: '—' }}</td>
            <td><strong>{{ number_format($product->quantity, 0, ',', '.') }}</strong> <span class="muted">{{ $product->unit }}</span></td>
            <td class="muted">Não informado</td>
            <td><span class="status low">● Cadastrar preço</span></td>
            <td><a class="button primary report-edit" href="{{ route('inventory.edit', $product) }}">Editar</a></td>
        </tr>
    @endforeach
    </tbody></table></div>
    <div class="pagination">{{ $products->links() }}</div>
@endif
@endsection
