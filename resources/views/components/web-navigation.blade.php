@props(['drawer' => false])
@php($navigation = \App\Support\WebNavigation::for(request()))
@php($links = $navigation->links())

@if($drawer)
    <header class="sticky top-0 z-40 flex h-16 items-center justify-between bg-[#17392a] px-4 text-white lg:hidden">
        <a href="{{ route('inicio') }}" class="font-fraunces text-2xl" aria-label="Ajudae — início">Ajud<span class="text-[#e3a62f]">ae</span></a>
        <button type="button" id="btn-abrir-menu-ong" aria-controls="sidebar-ong" aria-expanded="false" aria-label="Abrir menu" class="h-11 w-11 rounded-xl bg-white/10">☰</button>
    </header>
    <div id="overlay-menu-ong" class="fixed inset-0 z-40 hidden bg-black/45 lg:hidden" aria-hidden="true"></div>
    <aside id="sidebar-ong" aria-label="Menu da área de trabalho" class="fixed left-0 top-0 z-50 flex h-dvh w-[min(280px,90vw)] -translate-x-full flex-col overflow-y-auto bg-[#17392a] p-5 text-white transition-transform lg:w-[230px] lg:translate-x-0">
        <button type="button" id="btn-fechar-menu-ong" class="self-end rounded-lg px-2 py-1 lg:hidden" aria-label="Fechar menu">Fechar ×</button>
        <a href="{{ route('inicio') }}" class="mb-5 font-fraunces text-2xl" aria-label="Ajudae — início">Ajud<span class="text-[#e3a62f]">ae</span></a>
        @include('components.web-navigation-links', ['linkClass' => 'block rounded-xl px-3 py-2 hover:bg-white/10 aria-[current=page]:bg-white/15', 'vertical' => true, 'showName' => true])
    </aside>
@else
    <header class="fixed left-0 top-0 z-50 w-full border-b border-[#e7e2d8] bg-[#f8f6f0]/95 backdrop-blur">
        <div class="mx-auto flex h-[68px] max-w-[1440px] items-center justify-between gap-4 px-5 sm:px-8">
            <a aria-label="Ajudae — início" data-brand-home href="{{ route('inicio') }}" class="shrink-0 font-fraunces text-[26px] font-semibold text-[#17392a]">Ajud<span class="text-[#df9729]">ae</span></a>
            <div class="hidden min-w-0 text-[#17392a] lg:block">
                @include('components.web-navigation-links', ['linkClass' => 'rounded-lg px-2 py-2 hover:bg-[#eee9dd] aria-[current=page]:font-semibold aria-[current=page]:underline', 'vertical' => false, 'showName' => true])
            </div>
            <button type="button" id="btn-menu-publico" class="h-10 w-10 rounded-xl border border-[#ddd8ce] text-[#17392a] lg:hidden" aria-label="Abrir menu" aria-controls="menu-publico-mobile" aria-expanded="false" data-menu-breakpoint="1023">☰</button>
        </div>
        <div id="menu-publico-mobile" class="hidden max-h-[calc(100dvh-68px)] overflow-y-auto border-t border-[#e7e2d8] bg-[#f8f6f0] px-5 py-4 text-[#17392a] lg:hidden">
            @include('components.web-navigation-links', ['linkClass' => 'block rounded-xl px-3 py-2 hover:bg-[#eee9dd] aria-[current=page]:bg-[#eee9dd]', 'vertical' => true, 'showName' => true])
        </div>
    </header>
@endif
