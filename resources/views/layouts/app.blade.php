<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Estoque Claro' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/app.css?v=2">
    <link rel="stylesheet" href="/compact.css?v=11">
</head>
<body>
<aside class="sidebar"><div class="brand"><span class="brand-mark">✦</span><span>Estoque<br><em>Claro</em></span></div><div class="workspace"><span class="avatar">MC</span><span>Mercearia Central<br><small>Operação local</small></span><span class="dot"></span></div><nav><a @class(['active' => request()->routeIs('inventory.index')]) href="{{ route('inventory.index') }}"><span>▦</span> Visão geral</a><a href="{{ route('inventory.create') }}"><span>＋</span> Novo produto</a><a href="{{ route('inventory.index', ['q' => '']) }}#baixo"><span>◒</span> Estoque baixo</a><a @class(['active' => request()->routeIs('inventory.bulk-edit')]) href="{{ route('inventory.bulk-edit') }}"><span>✎</span> Edição em lote</a><details class="reports-menu" @if(request()->routeIs('reports.*')) open @endif><summary><span>▤</span> Relatórios</summary><div class="nav-submenu"><a href="{{ route('reports.missing-sale-price') }}" @class(['active' => request()->routeIs('reports.missing-sale-price')])>Sem preço de venda</a><a href="{{ route('reports.out-of-stock') }}" @class(['active' => request()->routeIs('reports.out-of-stock')])>Sem estoque</a><a href="{{ route('reports.export-form') }}" @class(['active' => request()->routeIs('reports.export-form')])>Exportar Excel</a></div></details></nav><div class="sidebar-tip"><span>✦</span><strong>Uma operação<br>mais leve.</strong><small>Controle simples para<br>vender melhor todos os dias.</small></div><div class="sidebar-footer">v1.0 · Sem login por enquanto</div></aside>
<main class="main"><header class="topbar"><div><p class="eyebrow">{{ now()->translatedFormat('l, d \d\e F') }}</p><h1>{{ $heading ?? 'Visão geral' }}</h1></div><a href="{{ route('inventory.create') }}" class="button primary">＋ &nbsp;Novo produto</a></header>
@if(session('success'))<div class="flash success">✓ {{ session('success') }}</div>@endif
@if($errors->any())<div class="flash error">{{ $errors->first() }}</div>@endif
@yield('content')
</main>
<script src="/inventory-search.js?v=2"></script>
<script src="/uppercase-product.js"></script>
<script src="/product-autocomplete.js"></script>
@stack('scripts')
<script>
document.querySelectorAll('.flash.success').forEach((message) => {
    window.setTimeout(() => message.remove(), 5000);
});
</script>
</body></html>
