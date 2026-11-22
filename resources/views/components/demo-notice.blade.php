@if(request()->boolean('inspecao'))
{{-- Shared demo context. Never treat fixture messages as API confirmations. --}}
<aside data-ajudae-demo role="status" aria-label="Modo demonstração"
       style="position:fixed;right:12px;bottom:12px;z-index:100;max-width:320px;border:1px solid #e8920a;border-radius:12px;background:#fff8e8;color:#183c2b;padding:12px 36px 12px 14px;font:13px/1.45 sans-serif;box-shadow:0 4px 16px #183c2b22">
    <button type="button" aria-label="Fechar aviso de demonstração" onclick="this.parentElement.remove()"
            style="position:absolute;right:10px;top:8px;background:none;border:0;font-size:20px;color:#183c2b;cursor:pointer">×</button>
    <strong>Modo demonstração</strong>
    <p>Dados fictícios. Nenhuma operação real ou autenticação é realizada. Use somente dados de exemplo.</p>
    @foreach (['success', 'sucesso', 'cadastro_sucesso', 'demo_notice'] as $key)
        @if(session($key))<p>{{ session($key) }}</p>@endif
    @endforeach
</aside>

@endif
