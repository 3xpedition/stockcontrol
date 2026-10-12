@extends('layouts.app')

@section('content')
<section class="content-head report-head">
    <div>
        <span class="pill">Exportação personalizada</span>
        <h3>Exportar produtos para Excel</h3>
        <p>Selecione as colunas que deseja incluir no arquivo .xls.</p>
    </div>
</section>

<form class="card export-card" method="post" action="{{ route('reports.export') }}">
    @csrf
    <div class="export-toolbar">
        <div>
            <h3>Colunas do arquivo</h3>
            <p>O arquivo será gerado com todos os produtos cadastrados.</p>
        </div>
        <button type="button" class="button secondary" data-select-all>Selecionar todas</button>
    </div>

    <div class="export-columns">
        @foreach($columns as $key => $label)
            <label class="export-column"><input type="checkbox" name="columns[]" value="{{ $key }}" checked><span>{{ $label }}</span></label>
        @endforeach
    </div>

    <div class="export-actions"><a class="button" href="{{ route('inventory.index') }}">Cancelar</a><button class="button primary" type="submit">↓ &nbsp;Baixar arquivo .xls</button></div>
</form>
@endsection

@push('scripts')
<script>
document.querySelector('[data-select-all]')?.addEventListener('click', function () {
    const checkboxes = document.querySelectorAll('.export-column input');
    const shouldCheck = [...checkboxes].some((checkbox) => !checkbox.checked);
    checkboxes.forEach((checkbox) => checkbox.checked = shouldCheck);
    this.textContent = shouldCheck ? 'Desmarcar todas' : 'Selecionar todas';
});
</script>
@endpush
