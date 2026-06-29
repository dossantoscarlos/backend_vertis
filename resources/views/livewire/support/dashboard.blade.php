@php
$themeStyles = [
    'triton' => [
        'headerBg' => 'from-[#157fcc] to-[#1268a7] dark:from-[#1b2f42] dark:to-[#111e2a]',
        'headerBorder' => 'border-[#115b94] dark:border-[#1e3d59]',
        'activeTabBorder' => 'border-t-[#157fcc] dark:border-t-blue-500',
        'textHighlight' => 'text-[#157fcc] dark:text-blue-400',
        'sidebarActiveNode' => 'bg-[#e2eff8] dark:bg-[#1e2f42] text-[#157fcc] dark:text-blue-400 border-l-4 border-l-[#157fcc] dark:border-l-blue-400',
        'badge' => 'bg-[#157fcc] text-white',
    ],
    'neptune' => [
        'headerBg' => 'from-[#0f766e] to-[#0d5c56] dark:from-[#1a3835] dark:to-[#102220]',
        'headerBorder' => 'border-[#0b4d48] dark:border-[#122826]',
        'activeTabBorder' => 'border-t-[#0f766e] dark:border-t-teal-500',
        'textHighlight' => 'text-[#0f766e] dark:text-teal-400',
        'sidebarActiveNode' => 'bg-[#e2f2f1] dark:bg-[#152e2c] text-[#0f766e] dark:text-teal-400 border-l-4 border-l-[#0f766e] dark:border-l-teal-400',
        'badge' => 'bg-[#0f766e] text-white',
    ],
    'slate' => [
        'headerBg' => 'from-[#475569] to-[#334155] dark:from-[#242d38] dark:to-[#171d24]',
        'headerBorder' => 'border-[#1e293b] dark:border-[#212933]',
        'activeTabBorder' => 'border-t-[#475569] dark:border-t-slate-500',
        'textHighlight' => 'text-[#475569] dark:text-slate-400',
        'sidebarActiveNode' => 'bg-[#f1f5f9] dark:bg-[#252f3d] text-[#334155] dark:text-slate-450 border-l-4 border-l-[#475569] dark:border-l-slate-450',
        'badge' => 'bg-[#475569] text-white',
    ],
];
$themeConfig = $themeStyles[$profileTheme] ?? $themeStyles['triton'];
$currentUser = auth()->user();
@endphp

<div class="{{ $profileMode === 'dark' ? 'dark' : '' }} h-screen w-screen overflow-hidden" 
     x-data="{ 
        isSidebarCollapsed: false, 
        collapsedGroups: { operacao: false, cadastros: false, inteligencia: false, configuracao: false, suporte: false },
        toastMessage: '',
        toastType: 'success',
        showToast(msg, type = 'success') {
            this.toastMessage = msg;
            this.toastType = type;
            setTimeout(() => { this.toastMessage = '' }, 4000);
        }
     }"
     x-init="
        Livewire.on('toast', event => {
            showToast(event.message, event.type || 'success');
        });
     "
>
    <!-- TOAST POPUP -->
    <div x-show="toastMessage" 
         x-transition
         class="fixed right-4 top-14 z-50 flex items-center gap-2 border px-4 py-3 text-xs font-semibold shadow-md rounded"
         :class="{
            'bg-emerald-50 border-emerald-200 text-emerald-800 dark:bg-emerald-950 dark:border-emerald-800 dark:text-emerald-200': toastType === 'success',
            'bg-rose-50 border-rose-200 text-rose-800 dark:bg-rose-950 dark:border-rose-800 dark:text-rose-200': toastType === 'error'
         }"
         style="display: none;"
    >
        <span x-text="toastType === 'success' ? '✓' : '⚠️'"></span>
        <span x-text="toastMessage"></span>
    </div>

    <!-- MAIN VIEWPORT -->
    <div class="flex h-full w-full flex-col overflow-hidden bg-[#eef2f7] dark:bg-[#0a0f14] font-sans text-xs text-[#333] dark:text-[#ccd3db] select-none antialiased">
        
        <!-- HEADER (NORTH REGION) -->
        <header class="flex h-12 w-full shrink-0 items-center justify-between border-b {{ $themeConfig['headerBorder'] }} bg-gradient-to-r {{ $themeConfig['headerBg'] }} px-4 text-white shadow-md z-20">
            <div class="flex items-center gap-3">
                <div class="flex h-8 w-8 items-center justify-center rounded bg-white/10 dark:bg-white/5 font-bold text-white shadow-inner">
                    🧩
                </div>
                <div>
                    <h1 class="text-sm font-bold tracking-wider uppercase">Vertis Support Workspace</h1>
                    <p class="text-[9px] font-medium opacity-80 uppercase tracking-widest text-[#d8e8f5] dark:text-blue-300">
                        Sencha ExtJS Modern Portal v7.8
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="hidden items-center gap-2 border-r border-white/20 dark:border-white/10 pr-4 text-right sm:flex">
                    <span class="text-[10px] font-semibold text-white">
                        {{ $profileName }}
                    </span>
                    <span class="rounded bg-black/20 dark:bg-black/35 px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider text-blue-200 dark:text-blue-400">
                        {{ $this->supportProfile()['level'] }}
                    </span>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" wire:click="logout" class="flex h-8 items-center justify-center rounded border border-red-700 bg-red-600 dark:bg-red-700 dark:border-red-800 px-3 font-semibold text-white transition hover:bg-red-700 dark:hover:bg-red-800 active:scale-95 text-[10px] uppercase tracking-wide">
                        Sair
                    </button>
                </div>
            </div>
        </header>

        <!-- MAIN LAYOUT WRAPPER (WEST & CENTER) -->
        <div class="flex flex-1 w-full overflow-hidden">
            
            <!-- SIDEBAR (WEST REGION: Accordion tree) -->
            <aside class="flex shrink-0 flex-col border-r border-[#c0c7d0] dark:border-[#2b3e51] bg-[#f5f5f5] dark:bg-[#121c26] transition-all duration-300 z-10"
                   :class="isSidebarCollapsed ? 'w-7' : 'w-60'">
                
                <!-- Collapsed view -->
                <div x-show="isSidebarCollapsed" class="flex flex-1 flex-col items-center py-4 gap-4 bg-[#f0f0f0] dark:bg-[#0f1720]" style="display: none;">
                    <button type="button" @click="isSidebarCollapsed = false" class="flex h-6 w-5 items-center justify-center border border-[#b0b7c0] dark:border-[#2b3e51] bg-white dark:bg-[#1a2836] rounded shadow-sm text-[#157fcc] dark:text-blue-400 hover:bg-[#e6eff8] dark:hover:bg-[#25394f] transition-colors" title="Expandir Painel">
                        ▶
                    </button>
                    <div class="write-vertical text-[#555] dark:text-zinc-500 font-bold uppercase tracking-widest text-[9px] select-none pointer-events-none origin-center rotate-90 whitespace-nowrap mt-12">
                        Navegação de Módulos
                    </div>
                </div>

                <!-- Expanded view -->
                <div x-show="!isSidebarCollapsed" class="flex flex-col flex-1 overflow-hidden">
                    <div class="flex h-8 items-center justify-between border-b border-[#c0c7d0] dark:border-[#2b3e51] bg-[#e6eff8] dark:bg-[#172534] px-3 font-bold text-[#154f85] dark:text-blue-300">
                        <div class="flex items-center gap-1.5">
                            <span class="text-sm">📂</span>
                            <span>MÓDULOS DO SISTEMA</span>
                        </div>
                        <button type="button" @click="isSidebarCollapsed = true" class="flex h-5 w-5 items-center justify-center border border-[#b0b7c0] dark:border-[#2b3e51] bg-white dark:bg-[#1a2836] rounded shadow-sm text-[#157fcc] dark:text-blue-400 hover:bg-[#e6eff8] dark:hover:bg-[#25394f]" title="Recolher Painel">
                            ◀
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto p-1.5 space-y-1">
                        
                        <!-- OPERACOES GROUP -->
                        <div class="border border-[#d0d6de] dark:border-[#2b3e51] bg-white dark:bg-[#16222f] rounded overflow-hidden shadow-xs">
                            <button type="button" @click="collapsedGroups.operacao = !collapsedGroups.operacao" class="flex w-full h-7 items-center justify-between bg-gradient-to-b from-[#f9fbfd] to-[#eaeef3] dark:from-[#1e2d3d] dark:to-[#16222f] px-2 font-bold text-[#3a4f66] dark:text-zinc-300">
                                <div class="flex items-center gap-1.5 text-[10px]">
                                    <span>⚙️</span>
                                    <span>Operações</span>
                                </div>
                                <span class="text-[8px] font-mono text-[#8a99a8]" x-text="collapsedGroups.operacao ? '＋' : '－'"></span>
                            </button>
                            <ul x-show="!collapsedGroups.operacao" class="divide-y divide-[#f2f4f6] dark:divide-[#1f2d3d] p-0.5">
                                @if($currentUser->can('dashboard:visualizar'))
                                <li>
                                    <button type="button" wire:click="openTab('dashboard')" class="flex w-full h-7 items-center gap-2 px-3 transition-colors text-left rounded-sm font-medium {{ $activeTab === 'dashboard' ? $themeConfig['sidebarActiveNode'] : 'text-[#555] dark:text-zinc-400 hover:bg-[#f0f4f8] dark:hover:bg-[#1a2533]' }}">
                                        <span>📊</span>
                                        <span>Dashboard</span>
                                    </button>
                                </li>
                                @endif
                                @if($currentUser->can('campanhas:gerenciar') || $currentUser->can('campanhas:visualizar'))
                                <li>
                                    <button type="button" wire:click="openTab('campanhas')" class="flex w-full h-7 items-center gap-2 px-3 transition-colors text-left rounded-sm font-medium {{ $activeTab === 'campanhas' ? $themeConfig['sidebarActiveNode'] : 'text-[#555] dark:text-zinc-400 hover:bg-[#f0f4f8] dark:hover:bg-[#1a2533]' }}">
                                        <span>📣</span>
                                        <span>Campanhas</span>
                                    </button>
                                </li>
                                @endif
                                @if($currentUser->can('financeiro:gerenciar'))
                                <li>
                                    <button type="button" wire:click="openTab('financeiro')" class="flex w-full h-7 items-center gap-2 px-3 transition-colors text-left rounded-sm font-medium {{ $activeTab === 'financeiro' ? $themeConfig['sidebarActiveNode'] : 'text-[#555] dark:text-zinc-400 hover:bg-[#f0f4f8] dark:hover:bg-[#1a2533]' }}">
                                        <span>💰</span>
                                        <span>Área Financeira</span>
                                    </button>
                                </li>
                                @endif
                            </ul>
                        </div>

                        <!-- CADASTROS GERAIS GROUP -->
                        @if($currentUser->can('usuarios:gerenciar') || $currentUser->can('parceiros:gerenciar') || $currentUser->can('locais:gerenciar') || $currentUser->can('regioes:gerenciar') || $currentUser->can('usuarios:visualizar') || $currentUser->can('parceiros:visualizar') || $currentUser->can('locais:visualizar') || $currentUser->can('regioes:visualizar'))
                        <div class="border border-[#d0d6de] dark:border-[#2b3e51] bg-white dark:bg-[#16222f] rounded overflow-hidden shadow-xs">
                            <button type="button" @click="collapsedGroups.cadastros = !collapsedGroups.cadastros" class="flex w-full h-7 items-center justify-between bg-gradient-to-b from-[#f9fbfd] to-[#eaeef3] dark:from-[#1e2d3d] dark:to-[#16222f] px-2 font-bold text-[#3a4f66] dark:text-zinc-300">
                                <div class="flex items-center gap-1.5 text-[10px]">
                                    <span>📁</span>
                                    <span>Cadastros Gerais</span>
                                </div>
                                <span class="text-[8px] font-mono text-[#8a99a8]" x-text="collapsedGroups.cadastros ? '＋' : '－'"></span>
                            </button>
                            <ul x-show="!collapsedGroups.cadastros" class="divide-y divide-[#f2f4f6] dark:divide-[#1f2d3d] p-0.5">
                                @if($currentUser->can('usuarios:gerenciar') || $currentUser->can('usuarios:visualizar'))
                                <li>
                                    <button type="button" wire:click="openTab('usuarios')" class="flex w-full h-7 items-center gap-2 px-3 transition-colors text-left rounded-sm font-medium {{ $activeTab === 'usuarios' ? $themeConfig['sidebarActiveNode'] : 'text-[#555] dark:text-zinc-400 hover:bg-[#f0f4f8] dark:hover:bg-[#1a2533]' }}">
                                        <span>👤</span>
                                        <span>Usuários</span>
                                    </button>
                                </li>
                                @endif
                                @if($currentUser->can('parceiros:gerenciar') || $currentUser->can('parceiros:visualizar'))
                                <li>
                                    <button type="button" wire:click="openTab('parceiros')" class="flex w-full h-7 items-center gap-2 px-3 transition-colors text-left rounded-sm font-medium {{ $activeTab === 'parceiros' ? $themeConfig['sidebarActiveNode'] : 'text-[#555] dark:text-zinc-400 hover:bg-[#f0f4f8] dark:hover:bg-[#1a2533]' }}">
                                        <span>🤝</span>
                                        <span>Parceiros</span>
                                    </button>
                                </li>
                                @endif
                                @if($currentUser->can('locais:gerenciar') || $currentUser->can('locais:visualizar'))
                                <li>
                                    <button type="button" wire:click="openTab('locais')" class="flex w-full h-7 items-center gap-2 px-3 transition-colors text-left rounded-sm font-medium {{ $activeTab === 'locais' ? $themeConfig['sidebarActiveNode'] : 'text-[#555] dark:text-zinc-400 hover:bg-[#f0f4f8] dark:hover:bg-[#1a2533]' }}">
                                        <span>📍</span>
                                        <span>Locais (Comitê)</span>
                                    </button>
                                </li>
                                @endif
                                @if($currentUser->can('regioes:gerenciar') || $currentUser->can('regioes:visualizar'))
                                <li>
                                    <button type="button" wire:click="openTab('regioes')" class="flex w-full h-7 items-center gap-2 px-3 transition-colors text-left rounded-sm font-medium {{ $activeTab === 'regioes' ? $themeConfig['sidebarActiveNode'] : 'text-[#555] dark:text-zinc-400 hover:bg-[#f0f4f8] dark:hover:bg-[#1a2533]' }}">
                                        <span>🗺</span>
                                        <span>Regiões</span>
                                    </button>
                                </li>
                                @endif
                            </ul>
                        </div>
                        @endif

                        <!-- INTELIGENCIA GROUP -->
                        @if($currentUser->can('relatorios:visualizar') || $currentUser->can('tre:consultar'))
                        <div class="border border-[#d0d6de] dark:border-[#2b3e51] bg-white dark:bg-[#16222f] rounded overflow-hidden shadow-xs">
                            <button type="button" @click="collapsedGroups.inteligencia = !collapsedGroups.inteligencia" class="flex w-full h-7 items-center justify-between bg-gradient-to-b from-[#f9fbfd] to-[#eaeef3] dark:from-[#1e2d3d] dark:to-[#16222f] px-2 font-bold text-[#3a4f66] dark:text-zinc-300">
                                <div class="flex items-center gap-1.5 text-[10px]">
                                    <span>📈</span>
                                    <span>Inteligência</span>
                                </div>
                                <span class="text-[8px] font-mono text-[#8a99a8]" x-text="collapsedGroups.inteligencia ? '＋' : '－'"></span>
                            </button>
                            <ul x-show="!collapsedGroups.inteligencia" class="divide-y divide-[#f2f4f6] dark:divide-[#1f2d3d] p-0.5">
                                @if($currentUser->can('relatorios:visualizar'))
                                <li>
                                    <button type="button" wire:click="openTab('relatorios')" class="flex w-full h-7 items-center gap-2 px-3 transition-colors text-left rounded-sm font-medium {{ $activeTab === 'relatorios' ? $themeConfig['sidebarActiveNode'] : 'text-[#555] dark:text-zinc-400 hover:bg-[#f0f4f8] dark:hover:bg-[#1a2533]' }}">
                                        <span>📊</span>
                                        <span>Relatórios</span>
                                    </button>
                                </li>
                                @endif
                                @if($currentUser->can('tre:consultar'))
                                <li>
                                    <button type="button" wire:click="openTab('tre')" class="flex w-full h-7 items-center gap-2 px-3 transition-colors text-left rounded-sm font-medium {{ $activeTab === 'tre' ? $themeConfig['sidebarActiveNode'] : 'text-[#555] dark:text-zinc-400 hover:bg-[#f0f4f8] dark:hover:bg-[#1a2533]' }}">
                                        <span>⚖</span>
                                        <span>Consulta TRE</span>
                                    </button>
                                </li>
                                @endif
                                @if($currentUser->can('pesquisas:visualizar'))
                                <li>
                                    <button type="button" wire:click="openTab('pesquisas')" class="flex w-full h-7 items-center gap-2 px-3 transition-colors text-left rounded-sm font-medium {{ $activeTab === 'pesquisas' ? $themeConfig['sidebarActiveNode'] : 'text-[#555] dark:text-zinc-400 hover:bg-[#f0f4f8] dark:hover:bg-[#1a2533]' }}">
                                        <span>🔍</span>
                                        <span>Pesquisas</span>
                                    </button>
                                </li>
                                @endif
                            </ul>
                        </div>
                        @endif

                        <!-- CONFIGURACOES GROUP -->
                        <div class="border border-[#d0d6de] dark:border-[#2b3e51] bg-white dark:bg-[#16222f] rounded overflow-hidden shadow-xs">
                            <button type="button" @click="collapsedGroups.configuracao = !collapsedGroups.configuracao" class="flex w-full h-7 items-center justify-between bg-gradient-to-b from-[#f9fbfd] to-[#eaeef3] dark:from-[#1e2d3d] dark:to-[#16222f] px-2 font-bold text-[#3a4f66] dark:text-zinc-300">
                                <div class="flex items-center gap-1.5 text-[10px]">
                                    <span>⚙️</span>
                                    <span>Configurações</span>
                                </div>
                                <span class="text-[8px] font-mono text-[#8a99a8]" x-text="collapsedGroups.configuracao ? '＋' : '－'"></span>
                            </button>
                            <ul x-show="!collapsedGroups.configuracao" class="divide-y divide-[#f2f4f6] dark:divide-[#1f2d3d] p-0.5">
                                <li>
                                    <button type="button" wire:click="openTab('perfil')" class="flex w-full h-7 items-center gap-2 px-3 transition-colors text-left rounded-sm font-medium {{ $activeTab === 'perfil' ? $themeConfig['sidebarActiveNode'] : 'text-[#555] dark:text-zinc-400 hover:bg-[#f0f4f8] dark:hover:bg-[#1a2533]' }}">
                                        <span>👤</span>
                                        <span>Meu Perfil</span>
                                    </button>
                                </li>
                                @if($currentUser->can('permissoes:gerenciar'))
                                <li>
                                    <button type="button" wire:click="openTab('permissoes')" class="flex w-full h-7 items-center gap-2 px-3 transition-colors text-left rounded-sm font-medium {{ $activeTab === 'permissoes' ? $themeConfig['sidebarActiveNode'] : 'text-[#555] dark:text-zinc-400 hover:bg-[#f0f4f8] dark:hover:bg-[#1a2533]' }}">
                                        <span>🔐</span>
                                        <span>Permissões</span>
                                    </button>
                                </li>
                                @endif
                            </ul>
                        </div>

                        <!-- SUPORTE DIAGNOSTIC GROUP -->
                        <div class="border border-[#d0d6de] dark:border-[#2b3e51] bg-white dark:bg-[#16222f] rounded overflow-hidden shadow-xs">
                            <button type="button" @click="collapsedGroups.suporte = !collapsedGroups.suporte" class="flex w-full h-7 items-center justify-between bg-gradient-to-b from-[#f9fbfd] to-[#eaeef3] dark:from-[#1e2d3d] dark:to-[#16222f] px-2 font-bold text-[#3a4f66] dark:text-zinc-300">
                                <div class="flex items-center gap-1.5 text-[10px]">
                                    <span>🛠️</span>
                                    <span>Suporte</span>
                                </div>
                                <span class="text-[8px] font-mono text-[#8a99a8]" x-text="collapsedGroups.suporte ? '＋' : '－'"></span>
                            </button>
                            <ul x-show="!collapsedGroups.suporte" class="divide-y divide-[#f2f4f6] dark:divide-[#1f2d3d] p-0.5">
                                @if($this->supportRank() >= 1)
                                <li>
                                    <button type="button" wire:click="openTab('schema')" class="flex w-full h-7 items-center gap-2 px-3 transition-colors text-left rounded-sm font-medium {{ $activeTab === 'schema' ? $themeConfig['sidebarActiveNode'] : 'text-[#555] dark:text-zinc-400 hover:bg-[#f0f4f8] dark:hover:bg-[#1a2533]' }}">
                                        <span>🗂</span>
                                        <span>Schema</span>
                                    </button>
                                </li>
                                @endif
                                @if($this->supportRank() >= 2)
                                <li>
                                    <button type="button" wire:click="openTab('sql')" class="flex w-full h-7 items-center gap-2 px-3 transition-colors text-left rounded-sm font-medium {{ $activeTab === 'sql' ? $themeConfig['sidebarActiveNode'] : 'text-[#555] dark:text-zinc-400 hover:bg-[#f0f4f8] dark:hover:bg-[#1a2533]' }}">
                                        <span>⌨</span>
                                        <span>Editor SQL</span>
                                    </button>
                                </li>
                                @endif
                                @if($this->supportRank() >= 3)
                                <li>
                                    <button type="button" wire:click="openTab('debug')" class="flex w-full h-7 items-center gap-2 px-3 transition-colors text-left rounded-sm font-medium {{ $activeTab === 'debug' ? $themeConfig['sidebarActiveNode'] : 'text-[#555] dark:text-zinc-400 hover:bg-[#f0f4f8] dark:hover:bg-[#1a2533]' }}">
                                        <span>⚙</span>
                                        <span>Terminal Debug</span>
                                    </button>
                                </li>
                                @endif
                            </ul>
                        </div>

                    </div>
                </div>
            </aside>

            <!-- CENTER REGION (TAB PANEL & BODY) -->
            <main class="flex flex-1 flex-col overflow-hidden bg-[#eef2f7] dark:bg-[#0a0f14] p-2">
                
                <!-- Tab Strip Header -->
                <div class="flex w-full border-b border-[#c0c7d0] dark:border-[#2b3e51] px-1 flex-wrap gap-0.5 items-end h-8 shrink-0">
                    @foreach($openTabs as $tab)
                    @php $isActive = ($activeTab === $tab['id']) || (str_starts_with($activeTab, 'relatorio-') && $tab['id'] === $activeTab); @endphp
                    <div wire:click="selectTab('{{ $tab['id'] }}')" 
                         class="group flex h-7 items-center gap-2 px-3 border-t rounded-t cursor-pointer transition-all {{ $isActive ? 'bg-white dark:bg-zinc-950 border-t-2 ' . $themeConfig['activeTabBorder'] . ' border-x border-x-[#c0c7d0] dark:border-x-[#2b3e51] font-bold ' . $themeConfig['textHighlight'] . ' z-10 -mb-[1px]' : 'bg-[#e1e5eb] dark:bg-[#131b24] border-t border-t-[#c8cfd6] dark:border-t-[#2b3e51] border-x border-x-[#c8cfd6] dark:border-x-[#2b3e51] text-[#555] dark:text-zinc-400 hover:bg-[#f0f2f5] dark:hover:bg-[#1a2530] hover:text-[#111] dark:hover:text-zinc-200 -mb-[1px]' }}"
                         style="min-width: 100px; max-width: 200px;">
                        <span class="text-xs shrink-0">{{ $tab['icon'] }}</span>
                        <span class="truncate text-[10px] select-none flex-1">{{ $tab['title'] }}</span>
                        @if($tab['closable'])
                        <button type="button" wire:click.stop="closeTab('{{ $tab['id'] }}')" class="flex h-3.5 w-3.5 items-center justify-center rounded-full text-[9px] text-[#9a9fa6] hover:bg-[#e0565b] hover:text-white" title="Fechar Aba">
                            ×
                        </button>
                        @endif
                    </div>
                    @endforeach
                </div>

                <!-- Active Tab Body -->
                <div class="flex-1 w-full overflow-hidden border-x border-b border-[#c0c7d0] dark:border-[#2b3e51] bg-white dark:bg-zinc-950 shadow-sm flex flex-col z-0">
                    <div class="flex-1 overflow-auto p-4 bg-white dark:bg-zinc-950">

                        <!-- HOME TAB (💻 AREA DE TRABALHO) -->
                        @if($activeTab === 'home')
                        <div class="flex flex-col gap-5 h-full" x-data="{ currentSlide: 0 }">
                            <div class="grid gap-4 md:grid-cols-3">
                                
                                <!-- Portal Column 1 (News & Carousel) -->
                                <div class="md:col-span-2 flex flex-col gap-4">
                                    
                                    <!-- Carousel Container -->
                                    <div class="border border-[#c0c7d0] dark:border-zinc-800 rounded bg-[#fafafa] dark:bg-zinc-900 shadow-xs overflow-hidden flex flex-col h-64">
                                        <div class="bg-[#e9eef4] dark:bg-[#1a2d3e] border-b border-[#c0c7d0] dark:border-[#2b3e51] px-3 py-1.5 font-bold text-[#2c3e50] dark:text-zinc-300 text-[10px] uppercase flex justify-between items-center">
                                            <span>📢 Destaques Eleitorais e Urnas Eletrônicas</span>
                                            <span class="bg-[#157fcc] dark:bg-blue-600 text-white font-bold rounded-sm px-1.5 py-0.5 text-[8px]"
                                                  x-text="currentSlide === 0 ? 'Segurança de Votação' : (currentSlide === 1 ? 'Eleições 2026' : 'Transparência')">
                                            </span>
                                        </div>

                                        <div class="flex-1 p-5 flex items-start gap-4 transition-all duration-500 relative">
                                            <div class="text-3xl p-3 bg-white dark:bg-zinc-950 rounded border border-[#cbd5e1] dark:border-zinc-850 shadow-xs shrink-0 select-none"
                                                 x-text="currentSlide === 0 ? '🔒' : (currentSlide === 1 ? '📅' : '🛡️')">
                                            </div>
                                            <div class="flex flex-col gap-2">
                                                <h3 class="text-xs font-bold text-[#154f85] dark:text-blue-400"
                                                    x-text="currentSlide === 0 ? 'Urnas Eletrônicas 2026: Segurança e Isolamento Absoluto' : (currentSlide === 1 ? 'Calendário Eleitoral 2026: Programe os Prazos Críticos' : 'Auditoria de Códigos-Fonte e Teste Público')">
                                                </h3>
                                                <p class="text-[11px] text-[#555] dark:text-zinc-350 leading-relaxed"
                                                   x-text="currentSlide === 0 ? 'As urnas eletrônicas brasileiras operam sem qualquer conexão à internet, Wi-Fi ou Bluetooth. A ausência de conexões de rede inviabiliza invasões ou interferências cibernéticas externas.' : (currentSlide === 1 ? 'Fique atento ao cronograma oficial do TSE para 2026: as eleições gerais acontecem dia 04 de Outubro (1º turno) e 25 de Outubro (2º turno).' : 'O código-fonte das urnas e sistemas eleitorais fica disponível para auditoria pelas entidades fiscalizadoras muito antes das eleições.')">
                                                </p>
                                            </div>
                                        </div>

                                        <div class="h-8 border-t border-[#cbd5e1] dark:border-zinc-800 bg-white dark:bg-zinc-950 flex items-center justify-between px-4">
                                            <div class="flex gap-1">
                                                <button @click="currentSlide = 0" class="h-2 w-2 rounded-full" :class="currentSlide === 0 ? 'bg-[#157fcc] w-4' : 'bg-[#cbd5e1]'"></button>
                                                <button @click="currentSlide = 1" class="h-2 w-2 rounded-full" :class="currentSlide === 1 ? 'bg-[#157fcc] w-4' : 'bg-[#cbd5e1]'"></button>
                                                <button @click="currentSlide = 2" class="h-2 w-2 rounded-full" :class="currentSlide === 2 ? 'bg-[#157fcc] w-4' : 'bg-[#cbd5e1]'"></button>
                                            </div>
                                            <div class="flex gap-2">
                                                <button type="button" @click="currentSlide = (currentSlide - 1 + 3) % 3" class="px-2 py-0.5 text-[9px] font-bold border border-[#cbd5e1] dark:border-zinc-700 bg-white dark:bg-zinc-900 rounded">Anterior</button>
                                                <button type="button" @click="currentSlide = (currentSlide + 1) % 3" class="px-2 py-0.5 text-[9px] font-bold border border-[#cbd5e1] dark:border-zinc-700 bg-white dark:bg-zinc-900 rounded">Próximo</button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- News Central list -->
                                    <div class="border border-[#c0c7d0] dark:border-zinc-800 rounded bg-white dark:bg-zinc-950 shadow-xs flex flex-col">
                                        <div class="bg-[#e9eef4] dark:bg-[#1a2d3e] border-b border-[#c0c7d0] dark:border-[#2b3e51] px-3 py-1.5 font-bold text-[#2c3e50] dark:text-zinc-300 text-[10px] uppercase">
                                            📰 Central de Notícias e Informativos
                                        </div>
                                        <div class="p-4 flex flex-col gap-4 divide-y divide-[#eaeded] dark:divide-zinc-850">
                                            <div class="pt-0 flex flex-col gap-1.5">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-[9px] font-bold text-[#95a5a6]">14 Jun 2026 · 09:00</span>
                                                    <span class="bg-[#eef2f7] dark:bg-[#1a2c3a] text-[#154f85] dark:text-blue-300 font-bold px-1.5 py-0.5 rounded text-[8px]">Segurança</span>
                                                </div>
                                                <h4 class="text-[11px] font-bold text-[#2c3e50] dark:text-zinc-200">TSE conclui a homologação técnica dos novos modelos de Urna Eletrônica UE2026</h4>
                                                <p class="text-[10px] text-[#7f8c8d] dark:text-zinc-400">A nova versão traz hardware criptográfico atualizado e processadores mais rápidos, mantendo o consagrado isolamento total de redes.</p>
                                            </div>
                                            <div class="pt-4 flex flex-col gap-1.5">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-[9px] font-bold text-[#95a5a6]">11 Jun 2026 · 14:30</span>
                                                    <span class="bg-[#eef2f7] dark:bg-[#1a2c3a] text-[#154f85] dark:text-blue-300 font-bold px-1.5 py-0.5 rounded text-[8px]">Calendário</span>
                                                </div>
                                                <h4 class="text-[11px] font-bold text-[#2c3e50] dark:text-zinc-200">Prazo para convenções partidárias tem início em 20 de julho de 2026</h4>
                                                <p class="text-[10px] text-[#7f8c8d] dark:text-zinc-400">Legendas terão até o dia 5 de agosto para oficializar candidaturas a deputados federais, senadores e governadores.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Portal Column 2 (Shortcuts & Campaign Execution status) -->
                                <div class="flex flex-col gap-4">
                                    <!-- Campanhas em Execução -->
                                    <div class="border border-[#c0c7d0] dark:border-[#2b3e51] rounded bg-[#fafafa] dark:bg-zinc-900 shadow-xs">
                                        <div class="bg-[#e9eef4] dark:bg-[#1a2d3e] border-b border-[#c0c7d0] dark:border-[#2b3e51] px-3 py-1.5 font-bold text-[#2c3e50] dark:text-zinc-300 text-[10px] uppercase">
                                            ⚡ Campanhas em Execução
                                        </div>
                                        <div class="p-3">
                                            @php $activeCampaigns = $dbCampaigns->filter(fn($c) => $c->status === 'em andamento'); @endphp
                                            @if($activeCampaigns->isEmpty())
                                                <p class="text-[11px] text-[#7f8c8d] italic">Nenhuma campanha ativa no momento.</p>
                                            @else
                                                <ul class="divide-y divide-[#eaeded] dark:divide-zinc-850 space-y-1.5">
                                                    @foreach($activeCampaigns as $camp)
                                                    <li class="pt-1.5 first:pt-0">
                                                        <p class="font-bold text-[#2c3e50] dark:text-zinc-200 truncate">{{ $camp->name }}</p>
                                                        <p class="text-[9px] text-[#7f8c8d] dark:text-zinc-450">Tipo: {{ $camp->type }}</p>
                                                        <div class="mt-1 flex items-center gap-1.5">
                                                            <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                                                            <span class="text-[9px] uppercase tracking-wider font-bold text-amber-700">Em andamento</span>
                                                        </div>
                                                    </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Atalhos Rápidos -->
                                    <div class="border border-[#c0c7d0] dark:border-[#2b3e51] rounded bg-white dark:bg-zinc-950 shadow-xs">
                                        <div class="bg-[#e9eef4] dark:bg-[#1a2d3e] border-b border-[#c0c7d0] dark:border-[#2b3e51] px-3 py-1.5 font-bold text-[#2c3e50] dark:text-zinc-300 text-[10px] uppercase">
                                            🔌 Atalhos Rápidos
                                        </div>
                                        <div class="p-3 grid grid-cols-2 gap-2">
                                            <button type="button" wire:click="openTab('dashboard')" class="flex flex-col items-center justify-center p-2 border border-[#e2e8f0] dark:border-zinc-800 rounded bg-white dark:bg-zinc-950 hover:bg-[#f1f5f9] dark:hover:bg-zinc-900 transition">
                                                <span class="text-xl">📊</span>
                                                <span class="text-[9px] font-bold mt-1">Dashboard</span>
                                            </button>
                                            <button type="button" wire:click="openTab('campanhas')" class="flex flex-col items-center justify-center p-2 border border-[#e2e8f0] dark:border-zinc-800 rounded bg-white dark:bg-zinc-950 hover:bg-[#f1f5f9] dark:hover:bg-zinc-900 transition">
                                                <span class="text-xl">📣</span>
                                                <span class="text-[9px] font-bold mt-1">Campanhas</span>
                                            </button>
                                            <button type="button" wire:click="openTab('relatorios')" class="flex flex-col items-center justify-center p-2 border border-[#e2e8f0] dark:border-zinc-800 rounded bg-white dark:bg-zinc-950 hover:bg-[#f1f5f9] dark:hover:bg-zinc-900 transition">
                                                <span class="text-xl">📊</span>
                                                <span class="text-[9px] font-bold mt-1">Relatórios</span>
                                            </button>
                                            <button type="button" wire:click="openTab('schema')" class="flex flex-col items-center justify-center p-2 border border-[#e2e8f0] dark:border-zinc-800 rounded bg-white dark:bg-zinc-950 hover:bg-[#f1f5f9] dark:hover:bg-zinc-900 transition">
                                                <span class="text-xl">🗂</span>
                                                <span class="text-[9px] font-bold mt-1">Schema</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                        @endif

                        <!-- DASHBOARD PANEL -->
                        @if($activeTab === 'dashboard')
                        <div class="flex flex-col gap-6">
                            <div class="flex items-end justify-between border-b border-zinc-200 pb-3 dark:border-zinc-800">
                                <div>
                                    <h1 class="text-base font-bold text-zinc-900 dark:text-zinc-50">Dashboard Operacional</h1>
                                    <p class="text-[10px] text-zinc-400 font-bold uppercase tracking-wider">Visão consolidada · Colaboradores, comitês, regiões, pesquisas e campanhas</p>
                                </div>
                            </div>

                            <!-- KPIs grid -->
                            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                <div class="border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-950 p-4 rounded shadow-xs relative overflow-hidden">
                                    <div class="flex justify-between items-start">
                                        <div class="p-2 bg-indigo-50 dark:bg-indigo-950 rounded text-lg">👥</div>
                                        <span class="text-[10px] font-bold text-emerald-500">↑ {{ $dbUsers->filter(fn($u)=>$u->status==='ativo')->count() }} ativos</span>
                                    </div>
                                    <p class="text-xl font-extrabold text-zinc-900 dark:text-zinc-50 mt-2">{{ $dbUsers->count() }}</p>
                                    <p class="text-[9px] uppercase tracking-wider font-bold text-zinc-400">Colaboradores</p>
                                </div>
                                <div class="border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-950 p-4 rounded shadow-xs relative overflow-hidden">
                                    <div class="flex justify-between items-start">
                                        <div class="p-2 bg-violet-50 dark:bg-violet-950 rounded text-lg">🏛️</div>
                                    </div>
                                    <p class="text-xl font-extrabold text-zinc-900 dark:text-zinc-50 mt-2">{{ $dbLocations->filter(fn($l)=>in_array($l->type, ['comitê', 'sede']))->count() }}</p>
                                    <p class="text-[9px] uppercase tracking-wider font-bold text-zinc-400">Comitês / Locais</p>
                                </div>
                                <div class="border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-950 p-4 rounded shadow-xs relative overflow-hidden">
                                    <div class="flex justify-between items-start">
                                        <div class="p-2 bg-cyan-50 dark:bg-cyan-950 rounded text-lg">🗺️</div>
                                    </div>
                                    <p class="text-xl font-extrabold text-zinc-900 dark:text-zinc-50 mt-2">{{ $dbRegions->count() }}</p>
                                    <p class="text-[9px] uppercase tracking-wider font-bold text-zinc-400">Regiões Alcançadas</p>
                                </div>
                                <div class="border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-950 p-4 rounded shadow-xs relative overflow-hidden">
                                    <div class="flex justify-between items-start">
                                        <div class="p-2 bg-emerald-50 dark:bg-emerald-950 rounded text-lg">📋</div>
                                        <span class="text-[10px] font-bold text-indigo-500">5 total</span>
                                    </div>
                                    <p class="text-xl font-extrabold text-zinc-900 dark:text-zinc-50 mt-2">3</p>
                                    <p class="text-[9px] uppercase tracking-wider font-bold text-zinc-400">Pesquisas Feitas</p>
                                </div>
                            </div>

                            <!-- Simple visual layout for charts -->
                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-950 p-4 rounded shadow-xs">
                                    <h3 class="font-bold text-[#154f85] dark:text-blue-400 border-b border-[#f1f5f9] dark:border-zinc-800 pb-2 mb-3">Campanhas por Status</h3>
                                    <div class="space-y-2">
                                        <div>
                                            <div class="flex justify-between text-[10px] mb-1">
                                                <span>Em Andamento</span>
                                                <span class="font-bold">{{ $dbCampaigns->filter(fn($c)=>$c->status==='em andamento')->count() }}</span>
                                            </div>
                                            <div class="h-2 w-full bg-zinc-100 dark:bg-zinc-800 rounded-full">
                                                <div class="h-full bg-amber-500 rounded-full" style="width: 50%;"></div>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="flex justify-between text-[10px] mb-1">
                                                <span>Planejadas</span>
                                                <span class="font-bold">{{ $dbCampaigns->filter(fn($c)=>$c->status==='planejada')->count() }}</span>
                                            </div>
                                            <div class="h-2 w-full bg-zinc-100 dark:bg-zinc-800 rounded-full">
                                                <div class="h-full bg-indigo-500 rounded-full" style="width: 30%;"></div>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="flex justify-between text-[10px] mb-1">
                                                <span>Concluídas</span>
                                                <span class="font-bold">{{ $dbCampaigns->filter(fn($c)=>$c->status==='concluída')->count() }}</span>
                                            </div>
                                            <div class="h-2 w-full bg-zinc-100 dark:bg-zinc-800 rounded-full">
                                                <div class="h-full bg-emerald-500 rounded-full" style="width: 20%;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-950 p-4 rounded shadow-xs">
                                    <h3 class="font-bold text-[#154f85] dark:text-blue-400 border-b border-[#f1f5f9] dark:border-zinc-800 pb-2 mb-3">Pesquisas por Status</h3>
                                    <div class="space-y-2">
                                        <div>
                                            <div class="flex justify-between text-[10px] mb-1">
                                                <span>Concluídas</span>
                                                <span class="font-bold">3</span>
                                            </div>
                                            <div class="h-2 w-full bg-zinc-100 dark:bg-zinc-800 rounded-full">
                                                <div class="h-full bg-emerald-500 rounded-full" style="width: 60%;"></div>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="flex justify-between text-[10px] mb-1">
                                                <span>Em andamento</span>
                                                <span class="font-bold">2</span>
                                            </div>
                                            <div class="h-2 w-full bg-zinc-100 dark:bg-zinc-800 rounded-full">
                                                <div class="h-full bg-indigo-500 rounded-full" style="width: 40%;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- FINANCEIRO TAB -->
                        @if($activeTab === 'financeiro')
                        <div class="flex flex-col gap-4">
                            <div class="flex items-center justify-between border-b border-zinc-200 pb-3 dark:border-zinc-800">
                                <div>
                                    <h2 class="text-sm font-bold text-[#154f85] dark:text-blue-400">Área Financeira</h2>
                                    <p class="text-[10px] text-zinc-400">Gestão de despesas e receitas da campanha</p>
                                </div>
                                @if($currentUser->can('financeiro:gerenciar') && !$financeShowForm)
                                <button type="button" wire:click="$set('financeShowForm', true)" class="px-3 py-1.5 bg-[#157fcc] text-white font-semibold rounded hover:bg-[#115b94] transition active:scale-95 text-[10px] uppercase">
                                    Nova transação
                                </button>
                                @endif
                            </div>

                            <!-- Finance Balance Metrics -->
                            @php
                                $totalReceitas = $dbFinances->filter(fn($t) => $t->type === 'receita')->sum('final_cost');
                                $totalDespesas = $dbFinances->filter(fn($t) => $t->type === 'despesa')->sum('final_cost');
                                $balance = $totalReceitas - $totalDespesas;
                            @endphp
                            <div class="grid gap-3 sm:grid-cols-3">
                                <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-950 shadow-xs relative overflow-hidden">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">Total Receitas (Final)</p>
                                    <p class="text-xl font-black text-emerald-600 mt-1">
                                        R$ {{ number_format($totalReceitas, 2, ',', '.') }}
                                    </p>
                                    <span class="absolute right-4 bottom-2 text-2xl opacity-15">📈</span>
                                </div>

                                <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-950 shadow-xs relative overflow-hidden">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">Total Despesas (Final)</p>
                                    <p class="text-xl font-black text-rose-600 mt-1">
                                        R$ {{ number_format($totalDespesas, 2, ',', '.') }}
                                    </p>
                                    <span class="absolute right-4 bottom-2 text-2xl opacity-15">📉</span>
                                </div>

                                <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-950 shadow-xs relative overflow-hidden">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">Saldo Líquido</p>
                                    <p class="text-xl font-black mt-1 @if($balance >= 0) text-indigo-600 @else text-amber-600 @endif">
                                        R$ {{ number_format($balance, 2, ',', '.') }}
                                    </p>
                                    <span class="absolute right-4 bottom-2 text-2xl opacity-15">⚖️</span>
                                </div>
                            </div>

                            @if($financeShowForm)
                            <form wire:submit.prevent="saveFinance" class="border border-[#cbd5e1] dark:border-zinc-800 rounded p-4 bg-[#f8fbff] dark:bg-[#111c26] space-y-4">
                                <h3 class="font-bold text-[#154f85] dark:text-blue-400 text-xs">
                                    {{ $financeId ? 'Editar Movimentação Financeira' : 'Registrar Movimentação Financeira' }}
                                </h3>

                                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                    <div class="flex flex-col gap-1">
                                        <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-1">Tipo de Lançamento</label>
                                        <select wire:model.live="financeType" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100">
                                            <option value="despesa">Despesa (Saída)</option>
                                            <option value="receita">Receita (Entrada)</option>
                                        </select>
                                    </div>

                                    <div class="flex flex-col gap-1">
                                        <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-1">Data do Lançamento</label>
                                        <input type="date" wire:model="financeTransactionDate" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100" required>
                                    </div>

                                    <div class="flex flex-col gap-1">
                                        <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-1">Valor Previsto (R$)</label>
                                        <input type="number" step="0.01" min="0" wire:model="financeProjectedCost" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100" placeholder="0,00" required>
                                    </div>

                                    <div class="flex flex-col gap-1">
                                        <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-1">Valor Final (R$)</label>
                                        <input type="number" step="0.01" min="0" wire:model="financeFinalCost" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100" placeholder="0,00" required>
                                    </div>

                                    <div class="flex flex-col gap-1">
                                        <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-1">Entidade Tipo</label>
                                        <select wire:model.live="financeEntityType" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100">
                                            <option value="campanha">Campanha</option>
                                            <option value="locais">Locais</option>
                                            <option value="eventos">Eventos</option>
                                        </select>
                                    </div>

                                    <div class="flex flex-col gap-1">
                                        <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-1">Referente a (Código/ID)</label>
                                        @if($financeEntityType === 'campanha')
                                        <select wire:model.live="financeEntityExternalId" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100">
                                            <option value="">Selecione a Campanha</option>
                                            @foreach($dbCampaigns as $c)
                                            <option value="{{ $c->external_id }}">{{ $c->name }}</option>
                                            @endforeach
                                        </select>
                                        @elseif($financeEntityType === 'locais')
                                        <select wire:model.live="financeEntityExternalId" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100">
                                            <option value="">Selecione o Local</option>
                                            @foreach($dbLocations as $l)
                                            <option value="{{ $l->external_id }}">{{ $l->name }}</option>
                                            @endforeach
                                        </select>
                                        @else
                                        <input type="text" wire:model.live="financeEntityExternalId" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100" placeholder="Código do evento" required>
                                        @endif
                                    </div>

                                    <div class="flex flex-col gap-1">
                                        <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-1">Responsável</label>
                                        @if($financeEntityType === 'eventos')
                                        <select wire:model="financeResponsible" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100" required>
                                            <option value="">Selecione o Responsável</option>
                                            @foreach($dbUsers as $user)
                                            <option value="{{ $user->name }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                        @else
                                        <input type="text" wire:model="financeResponsible" class="w-full border border-[#cbd5e1] bg-zinc-100 dark:bg-zinc-800 px-3 py-1.5 text-xs rounded text-zinc-500 dark:text-zinc-400 cursor-not-allowed" readonly required placeholder="Preenchido automaticamente">
                                        @endif
                                    </div>

                                    <div class="flex flex-col gap-1">
                                        <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-1">Aprovador</label>
                                        <select wire:model="financeApprover" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100" required>
                                            <option value="">Selecione o Aprovador</option>
                                            @foreach($dbUsers as $user)
                                            <option value="{{ $user->name }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="flex gap-2 justify-end">
                                    <button type="button" wire:click="resetFinanceForm" class="px-3 py-1.5 bg-zinc-400 text-white rounded text-[10px] font-bold hover:bg-zinc-500 transition">
                                        Cancelar
                                    </button>
                                    <button type="submit" class="px-3 py-1.5 bg-[#157fcc] text-white rounded text-[10px] font-bold">
                                        Salvar Lançamento
                                    </button>
                                </div>
                            </form>
                            @endif

                            <!-- Sub-tabs segment buttons for Contas a Receber / Contas a Pagar -->
                            <div class="flex border-b border-[#cbd5e1] dark:border-zinc-800 pb-1 gap-2">
                                <button type="button" wire:click="$set('financeFilter', 'todos')" class="px-3 py-1.5 text-[10px] font-bold rounded uppercase transition {{ $financeFilter === 'todos' ? 'bg-[#154f85] text-white' : 'text-zinc-550 dark:text-zinc-400 hover:bg-[#eef3f9] dark:hover:bg-[#1a2d3e]' }}">
                                    Todos os Lançamentos
                                </button>
                                <button type="button" wire:click="$set('financeFilter', 'receber')" class="px-3 py-1.5 text-[10px] font-bold rounded uppercase transition {{ $financeFilter === 'receber' ? 'bg-emerald-600 text-white' : 'text-zinc-550 dark:text-zinc-400 hover:bg-[#eef3f9] dark:hover:bg-[#1a2d3e]' }}">
                                    Contas a Receber (Receitas)
                                </button>
                                <button type="button" wire:click="$set('financeFilter', 'pagar')" class="px-3 py-1.5 text-[10px] font-bold rounded uppercase transition {{ $financeFilter === 'pagar' ? 'bg-rose-600 text-white' : 'text-zinc-550 dark:text-zinc-400 hover:bg-[#eef3f9] dark:hover:bg-[#1a2d3e]' }}">
                                    Contas a Pagar (Despesas)
                                </button>
                            </div>

                            <div class="overflow-x-auto border border-[#cbd5e1] dark:border-zinc-800">
                                <table class="w-full text-left text-xs bg-white dark:bg-zinc-950">
                                    <thead class="border-b border-[#cbd5e1] bg-[#eef3f9] dark:bg-[#1a2d3e] text-[10px] font-bold uppercase text-[#154f85] dark:text-blue-300">
                                        <tr>
                                            <th class="px-3 py-2">Tipo</th>
                                            <th class="px-3 py-2">Lançamento</th>
                                            <th class="px-3 py-2">Previsto / Final</th>
                                            <th class="px-3 py-2">Referente a</th>
                                            <th class="px-3 py-2">Resp. / Aprov.</th>
                                            <th class="px-3 py-2">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-[#cbd5e1] dark:divide-zinc-800">
                                        @forelse($dbFinances->filter(fn($t) => $financeFilter === 'todos' || ($financeFilter === 'receber' && $t->type === 'receita') || ($financeFilter === 'pagar' && $t->type === 'despesa')) as $t)
                                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50">
                                            <td class="px-3 py-2">
                                                <span class="text-[10px] font-bold uppercase tracking-wider @if($t->type === 'receita') text-emerald-600 dark:text-emerald-400 @else text-rose-600 dark:text-rose-400 @endif">
                                                    {{ $t->type === 'receita' ? 'Receita' : 'Despesa' }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-2 text-[11px] font-mono text-zinc-900 dark:text-zinc-100">
                                                {{ $t->transaction_date->toDateString() }}
                                            </td>
                                            <td class="px-3 py-2 font-mono text-[11px]">
                                                <div class="flex flex-col">
                                                    <span>P: R$ {{ number_format($t->projected_cost, 2, ',', '.') }}</span>
                                                    <span class="font-bold text-zinc-950 dark:text-zinc-50">F: R$ {{ number_format($t->final_cost, 2, ',', '.') }}</span>
                                                </div>
                                            </td>
                                            <td class="px-3 py-2">
                                                <div class="flex flex-col">
                                                    <span class="font-bold text-zinc-900 dark:text-zinc-50 capitalize">{{ $t->entity_type }}</span>
                                                    <span class="text-[10px] text-zinc-400 font-mono">ID: {{ $t->entity_external_id }}</span>
                                                </div>
                                            </td>
                                            <td class="px-3 py-2 text-[11px]">
                                                <div class="flex flex-col">
                                                    <span>Resp: {{ $t->responsible }}</span>
                                                    <span class="text-zinc-400 text-[10px]">Aprov: {{ $t->approver ?? '—' }}</span>
                                                </div>
                                            </td>
                                            <td class="px-3 py-2">
                                                <div class="flex gap-2">
                                                    <button type="button" wire:click="editFinance('{{ $t->external_id }}')" class="text-zinc-500 hover:underline">Editar</button>
                                                    <button type="button" wire:click="deleteFinance('{{ $t->external_id }}')" class="text-red-600 hover:underline">Excluir</button>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="px-3 py-4 text-center text-zinc-400">Nenhuma movimentação financeira registrada.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif

                        <!-- CAMPANHAS TAB -->
                        @if($activeTab === 'campanhas')
                        <div class="flex flex-col gap-4">
                            <div class="flex items-center justify-between border-b border-zinc-200 pb-3 dark:border-zinc-800">
                                <div>
                                    <h2 class="text-sm font-bold text-[#154f85] dark:text-blue-400">Gestão de Campanhas</h2>
                                    <p class="text-[10px] text-zinc-400">Gerenciamento e controle de atividades</p>
                                </div>
                                @if($currentUser->can('campanhas:gerenciar') && !$campaignShowForm)
                                <button type="button" wire:click="$set('campaignShowForm', true)" class="px-3 py-1.5 bg-[#157fcc] text-white font-semibold rounded hover:bg-[#115b94] transition active:scale-95 text-[10px] uppercase">
                                    Nova campanha
                                </button>
                                @endif
                            </div>

                            @if($campaignShowForm)
                            <div class="border border-[#cbd5e1] dark:border-zinc-800 rounded p-4 bg-[#f8fbff] dark:bg-[#111c26]">
                                <h3 class="font-bold text-[#154f85] dark:text-blue-400 mb-3">Nova Campanha - Wizard Etapa {{ $campaignCurrentStep + 1 }}/4</h3>
                                
                                <div class="flex gap-2 mb-4">
                                    <button type="button" wire:click="$set('campaignCurrentStep', 0)" class="px-3 py-1 text-[10px] font-bold rounded {{ $campaignCurrentStep === 0 ? 'bg-indigo-600 text-white' : 'bg-zinc-200 text-zinc-700' }}">1. Básico</button>
                                    <button type="button" wire:click="$set('campaignCurrentStep', 1)" class="px-3 py-1 text-[10px] font-bold rounded {{ $campaignCurrentStep === 1 ? 'bg-indigo-600 text-white' : 'bg-zinc-200 text-zinc-700' }}">2. Territorial</button>
                                    <button type="button" wire:click="$set('campaignCurrentStep', 2)" class="px-3 py-1 text-[10px] font-bold rounded {{ $campaignCurrentStep === 2 ? 'bg-indigo-600 text-white' : 'bg-zinc-200 text-zinc-700' }}">3. Cronograma</button>
                                    <button type="button" wire:click="$set('campaignCurrentStep', 3)" class="px-3 py-1 text-[10px] font-bold rounded {{ $campaignCurrentStep === 3 ? 'bg-indigo-600 text-white' : 'bg-zinc-200 text-zinc-700' }}">4. Detalhes</button>
                                </div>

                                @if($campaignCurrentStep === 0)
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-1">Nome da Campanha</label>
                                        <input type="text" wire:model="campaignName" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100" placeholder="Digite o nome da campanha">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-1">Tipo de Atividade</label>
                                        <select wire:model="campaignType" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100">
                                            <option value="door-to-door">Porta a Porta</option>
                                            <option value="comício">Comício</option>
                                            <option value="digital">Digital</option>
                                            <option value="rádio">Rádio</option>
                                        </select>
                                    </div>
                                </div>
                                @elseif($campaignCurrentStep === 1)
                                <div>
                                    <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-1">Região Alvo</label>
                                    <select wire:model="campaignRegionId" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100">
                                        @foreach($dbRegions as $reg)
                                            <option value="{{ $reg->external_id }}">{{ $reg->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @elseif($campaignCurrentStep === 2)
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-1">Data de Início</label>
                                        <input type="date" wire:model="campaignStartDate" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-1">Data de Término</label>
                                        <input type="date" wire:model="campaignEndDate" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100">
                                    </div>
                                </div>
                                @elseif($campaignCurrentStep === 3)
                                <div class="grid gap-3">
                                    <div>
                                        <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-1">Responsável</label>
                                        <select wire:model="campaignResponsible" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100" required>
                                            @foreach($dbUsers as $user)
                                                <option value="{{ $user->name }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-1">Descrição</label>
                                        <textarea wire:model="campaignDescription" rows="4" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100" placeholder="Detalhes..."></textarea>
                                    </div>
                                </div>
                                @endif

                                <div class="mt-4 flex gap-2 justify-end">
                                    <button type="button" wire:click="resetCampaignForm" class="px-3 py-1.5 bg-zinc-400 text-white rounded text-[10px] font-bold">Cancelar</button>
                                    @if($campaignCurrentStep < 3)
                                    <button type="button" wire:click="$set('campaignCurrentStep', {{ $campaignCurrentStep + 1 }})" class="px-3 py-1.5 bg-indigo-600 text-white rounded text-[10px] font-bold">Avançar</button>
                                    @else
                                    <button type="button" wire:click="saveCampaign" class="px-3 py-1.5 bg-[#157fcc] text-white rounded text-[10px] font-bold">Salvar Campanha</button>
                                    @endif
                                </div>
                            </div>
                            @endif

                            <div class="overflow-x-auto border border-[#cbd5e1] dark:border-zinc-800">
                                <table class="w-full text-left text-xs bg-white dark:bg-zinc-950">
                                    <thead class="border-b border-[#cbd5e1] bg-[#eef3f9] dark:bg-[#1a2d3e] text-[10px] font-bold uppercase text-[#154f85] dark:text-blue-300">
                                        <tr>
                                            <th class="px-3 py-2">Campanha</th>
                                            <th class="px-3 py-2">Tipo</th>
                                            <th class="px-3 py-2">Região</th>
                                            <th class="px-3 py-2">Responsável</th>
                                            <th class="px-3 py-2">Período</th>
                                            <th class="px-3 py-2">Status</th>
                                            <th class="px-3 py-2">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-[#cbd5e1] dark:divide-zinc-800">
                                        @forelse($dbCampaigns as $c)
                                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50">
                                            <td class="px-3 py-2 font-bold">{{ $c->name }}</td>
                                            <td class="px-3 py-2 font-mono text-[10px]">{{ $c->type }}</td>
                                            <td class="px-3 py-2">{{ $dbRegions->firstWhere('external_id', $c->region_external_id)->name ?? $c->region_external_id }}</td>
                                            <td class="px-3 py-2">{{ $c->responsible ?? 'Administrador' }}</td>
                                            <td class="px-3 py-2">{{ $c->start_date->toDateString() }} - {{ $c->end_date->toDateString() }}</td>
                                            <td class="px-3 py-2">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $c->status === 'em andamento' ? 'bg-amber-100 text-amber-800' : ($c->status === 'concluída' ? 'bg-emerald-100 text-emerald-800' : 'bg-zinc-100 text-zinc-800') }}">
                                                    {{ $c->status }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-2">
                                                <div class="flex gap-2">
                                                    @if($c->status !== 'concluída' && $currentUser->can('campanhas:gerenciar'))
                                                    <button type="button" wire:click="advanceCampaign('{{ $c->external_id }}')" class="text-blue-600 hover:underline">Avançar</button>
                                                    @endif
                                                    <button type="button" wire:click="editCampaign('{{ $c->external_id }}')" class="text-zinc-500 hover:underline">Editar</button>
                                                    <button type="button" wire:click="deleteCampaign('{{ $c->external_id }}')" class="text-red-600 hover:underline">Excluir</button>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="px-3 py-4 text-center text-zinc-400">Nenhuma campanha cadastrada.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif

                        <!-- PESQUISAS TAB -->
                        @if($activeTab === 'pesquisas' && $currentUser->can('pesquisas:visualizar'))
                        <div class="flex flex-col gap-4">
                            <div class="flex items-center justify-between border-b border-zinc-200 pb-3 dark:border-zinc-800">
                                <div>
                                    <h2 class="text-sm font-bold text-[#154f85] dark:text-blue-400">Gestão de Pesquisas</h2>
                                    <p class="text-[10px] text-zinc-400">Pesquisas de opinião e intenções de voto</p>
                                </div>
                                @if($currentUser->can('pesquisas:gerenciar') && !$surveyShowForm)
                                <button type="button" wire:click="$set('surveyShowForm', true)" class="px-3 py-1.5 bg-[#157fcc] text-white font-semibold rounded hover:bg-[#115b94] transition active:scale-95 text-[10px] uppercase">
                                    Nova pesquisa
                                </button>
                                @endif
                            </div>

                            @if($surveyShowForm)
                            <form wire:submit.prevent="saveSurvey" class="border border-[#cbd5e1] dark:border-zinc-800 rounded p-4 bg-[#f8fbff] dark:bg-[#111c26] space-y-4">
                                <h3 class="font-bold text-[#154f85] dark:text-blue-400 text-xs">
                                    {{ $surveyId ? 'Editar Pesquisa' : 'Cadastrar Pesquisa' }}
                                </h3>

                                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                    <div class="flex flex-col gap-1">
                                        <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-1">Nome da Pesquisa</label>
                                        <input type="text" wire:model="surveyName" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100" placeholder="Digite o nome da pesquisa" required>
                                    </div>

                                    <div class="flex flex-col gap-1">
                                        <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-1">Tipo de Pesquisa</label>
                                        <select wire:model.live="surveyType" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100" required>
                                            <option value="porta">Porta a Porta (Física)</option>
                                            <option value="online">Online (Formulário Web)</option>
                                        </select>
                                    </div>

                                    <div class="flex flex-col gap-1">
                                        <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-1">Responsável</label>
                                        <select wire:model="surveyResponsible" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100" required>
                                            <option value="">Selecione o Responsável</option>
                                            @foreach($dbUsers as $user)
                                            <option value="{{ $user->name }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="flex flex-col gap-1">
                                        <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-1">Público Alvo</label>
                                        <input type="text" wire:model="surveyTargetAudience" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100" placeholder="Ex: Moradores do bairro Santana" required>
                                    </div>

                                    <div class="flex flex-col gap-1">
                                        <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-1">Data de Início</label>
                                        <input type="date" wire:model="surveyStartDate" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100" required>
                                    </div>

                                    <div class="flex flex-col gap-1">
                                        <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-1">Data de Término</label>
                                        <input type="date" wire:model="surveyEndDate" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100" required>
                                    </div>

                                    @if($surveyType === 'online')
                                    <div class="flex flex-col gap-1 sm:col-span-2 lg:col-span-3">
                                        <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-1">Link do Formulário (Obrigatório se Online)</label>
                                        <input type="url" wire:model="surveyLink" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100" placeholder="https://forms.gle/..." required>
                                    </div>
                                    @endif

                                    <div class="flex flex-col gap-1 sm:col-span-2 lg:col-span-3">
                                        <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-1">Descrição / Observações</label>
                                        <textarea wire:model="surveyDescription" rows="2" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100" placeholder="Informações adicionais da pesquisa..."></textarea>
                                    </div>
                                </div>

                                <div class="flex gap-2 justify-end">
                                    <button type="button" wire:click="resetSurveyForm" class="px-3 py-1.5 bg-zinc-400 text-white rounded text-[10px] font-bold hover:bg-zinc-500 transition">
                                        Cancelar
                                    </button>
                                    <button type="submit" class="px-3 py-1.5 bg-[#157fcc] text-white rounded text-[10px] font-bold hover:bg-[#115b94] transition">
                                        Salvar Pesquisa
                                    </button>
                                </div>
                            </form>
                            @endif

                            <div class="overflow-x-auto border border-[#cbd5e1] dark:border-zinc-800">
                                <table class="w-full text-left text-xs bg-white dark:bg-zinc-950">
                                    <thead class="border-b border-[#cbd5e1] bg-[#eef3f9] dark:bg-[#1a2d3e] text-[10px] font-bold uppercase text-[#154f85] dark:text-blue-300">
                                        <tr>
                                            <th class="px-3 py-2">Pesquisa</th>
                                            <th class="px-3 py-2">Tipo</th>
                                            <th class="px-3 py-2">Período</th>
                                            <th class="px-3 py-2">Responsável</th>
                                            <th class="px-3 py-2">Público Alvo</th>
                                            <th class="px-3 py-2">Link</th>
                                            <th class="px-3 py-2">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-[#cbd5e1] dark:divide-zinc-800">
                                        @forelse($dbSurveys as $s)
                                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50">
                                            <td class="px-3 py-2 font-bold">{{ $s->name }}</td>
                                            <td class="px-3 py-2 font-semibold capitalize">{{ $s->type }}</td>
                                            <td class="px-3 py-2 font-mono text-[10px]">{{ $s->start_date->toDateString() }} a {{ $s->end_date->toDateString() }}</td>
                                            <td class="px-3 py-2">{{ $s->responsible }}</td>
                                            <td class="px-3 py-2 text-zinc-500 dark:text-zinc-400">{{ $s->target_audience }}</td>
                                            <td class="px-3 py-2">
                                                @if($s->link)
                                                <a href="{{ $s->link }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline truncate max-w-xs block font-mono text-[10px]">{{ $s->link }}</a>
                                                @else
                                                <span class="text-zinc-400 italic">N/A</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2">
                                                <div class="flex gap-2">
                                                    @if($currentUser->can('pesquisas:gerenciar'))
                                                    <button type="button" wire:click="editSurvey('{{ $s->external_id }}')" class="text-zinc-500 hover:underline">Editar</button>
                                                    <button type="button" wire:click="deleteSurvey('{{ $s->external_id }}')" class="text-red-650 hover:underline">Excluir</button>
                                                    @else
                                                    <span class="text-zinc-400 italic">Leitura</span>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="px-3 py-4 text-center text-zinc-400">Nenhuma pesquisa cadastrada.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif

                        <!-- USUARIOS TAB -->
                        @if($activeTab === 'usuarios')
                        <div class="flex flex-col gap-4">
                            <div class="flex items-center justify-between border-b border-zinc-200 pb-3 dark:border-zinc-800">
                                <div>
                                    <h2 class="text-sm font-bold text-[#154f85] dark:text-blue-400">Equipe Operacional</h2>
                                    <p class="text-[10px] text-zinc-400">Usuários autorizados no sistema</p>
                                </div>
                                @if($currentUser->can('usuarios:gerenciar') && !$userShowForm)
                                <button type="button" wire:click="$set('userShowForm', true)" class="px-3 py-1.5 bg-[#157fcc] text-white font-semibold rounded hover:bg-[#115b94] transition active:scale-95 text-[10px] uppercase">
                                    Novo usuário
                                </button>
                                @endif
                            </div>

                            @if($userShowForm)
                            <form wire:submit.prevent="saveUser" class="border border-[#cbd5e1] dark:border-zinc-800 rounded p-4 bg-[#f8fbff] dark:bg-[#111c26]">
                                <h3 class="font-bold text-[#154f85] dark:text-blue-400 mb-3">{{ $userId ? 'Editar Usuário' : 'Novo Usuário' }}</h3>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-1">Nome</label>
                                        <input type="text" wire:model="userName" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100" required>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-1">E-mail</label>
                                        <input type="email" wire:model="userEmail" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100" required>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-1">Perfil</label>
                                        <select wire:model="userRoleId" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100">
                                            @foreach($dbRoles as $role)
                                                <option value="{{ $role->external_id }}">{{ $role->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-1">Status</label>
                                        <select wire:model="userStatus" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100">
                                            <option value="ativo">Ativo</option>
                                            <option value="inativo">Inativo</option>
                                            <option value="pendente">Pendente</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mt-4 flex gap-2 justify-end">
                                    <button type="button" wire:click="resetUserForm" class="px-3 py-1.5 bg-zinc-400 text-white rounded text-[10px] font-bold">Cancelar</button>
                                    <button type="submit" class="px-3 py-1.5 bg-[#157fcc] text-white rounded text-[10px] font-bold">Salvar</button>
                                </div>
                            </form>
                            @endif

                            <div class="overflow-x-auto border border-[#cbd5e1] dark:border-zinc-800">
                                <table class="w-full text-left text-xs bg-white dark:bg-zinc-950">
                                    <thead class="border-b border-[#cbd5e1] bg-[#eef3f9] dark:bg-[#1a2d3e] text-[10px] font-bold uppercase text-[#154f85] dark:text-blue-300">
                                        <tr>
                                            <th class="px-3 py-2">Nome</th>
                                            <th class="px-3 py-2">E-mail</th>
                                            <th class="px-3 py-2">Perfil</th>
                                            <th class="px-3 py-2">Status</th>
                                            <th class="px-3 py-2">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-[#cbd5e1] dark:divide-zinc-800">
                                        @forelse($dbUsers as $u)
                                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50">
                                            <td class="px-3 py-2 font-bold">{{ $u->name }}</td>
                                            <td class="px-3 py-2 font-mono">{{ $u->email }}</td>
                                            <td class="px-3 py-2">{{ $u->roles->first()?->name ?? '—' }}</td>
                                            <td class="px-3 py-2">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $u->status === 'ativo' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ $u->status }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-2">
                                                <div class="flex gap-2">
                                                    @if($currentUser->can('usuarios:gerenciar'))
                                                    <button type="button" wire:click="editUser('{{ $u->external_id }}')" class="text-zinc-500 hover:underline">Editar</button>
                                                    @if($u->external_id !== '1')
                                                    <button type="button" wire:click="deleteUser('{{ $u->external_id }}')" class="text-red-600 hover:underline">Excluir</button>
                                                    @endif
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="px-3 py-4 text-center text-zinc-400">Nenhum usuário cadastrado.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif

                        <!-- PARCEIROS TAB -->
                        @if($activeTab === 'parceiros')
                        <div class="flex flex-col gap-4">
                            <div class="flex items-center justify-between border-b border-zinc-200 pb-3 dark:border-zinc-800">
                                <div>
                                    <h2 class="text-sm font-bold text-[#154f85] dark:text-blue-400">Gestão de Parceiros</h2>
                                    <p class="text-[10px] text-zinc-400">Entidades parceiras cadastradas</p>
                                </div>
                                @if($currentUser->can('parceiros:gerenciar') && !$partnerShowForm)
                                <button type="button" wire:click="$set('partnerShowForm', true)" class="px-3 py-1.5 bg-[#157fcc] text-white font-semibold rounded hover:bg-[#115b94] transition active:scale-95 text-[10px] uppercase">
                                    Novo parceiro
                                </button>
                                @endif
                            </div>

                            @if($partnerShowForm)
                            <form wire:submit.prevent="savePartner" class="border border-[#cbd5e1] dark:border-zinc-800 rounded p-4 bg-[#f8fbff] dark:bg-[#111c26]">
                                <h3 class="font-bold text-[#154f85] dark:text-blue-400 mb-3">{{ $partnerId ? 'Editar Parceiro' : 'Novo Parceiro' }}</h3>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-1">Nome</label>
                                        <input type="text" wire:model="partnerName" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100" required>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-1">Tipo</label>
                                        <select wire:model="partnerType" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100">
                                            <option value="fornecedor">Fornecedor</option>
                                            <option value="mídia">Mídia</option>
                                            <option value="institucional">Institucional</option>
                                            <option value="voluntário">Voluntário</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-1">Contato (E-mail)</label>
                                        <input type="email" wire:model="partnerContact" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100" required>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-1">Telefone</label>
                                        <input type="text" wire:model="partnerPhone" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100" required>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-1">Região Vinculada</label>
                                        <select wire:model="partnerRegionId" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100">
                                            @foreach($dbRegions as $reg)
                                                <option value="{{ $reg->external_id }}">{{ $reg->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-1">Status</label>
                                        <select wire:model="partnerStatus" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100">
                                            <option value="ativo">Ativo</option>
                                            <option value="pendente">Pendente</option>
                                            <option value="inativo">Inativo</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mt-4 flex gap-2 justify-end">
                                    <button type="button" wire:click="resetPartnerForm" class="px-3 py-1.5 bg-zinc-400 text-white rounded text-[10px] font-bold">Cancelar</button>
                                    <button type="submit" class="px-3 py-1.5 bg-[#157fcc] text-white rounded text-[10px] font-bold">Salvar</button>
                                </div>
                            </form>
                            @endif

                            <div class="overflow-x-auto border border-[#cbd5e1] dark:border-zinc-800">
                                <table class="w-full text-left text-xs bg-white dark:bg-zinc-950">
                                    <thead class="border-b border-[#cbd5e1] bg-[#eef3f9] dark:bg-[#1a2d3e] text-[10px] font-bold uppercase text-[#154f85] dark:text-blue-300">
                                        <tr>
                                            <th class="px-3 py-2">Nome</th>
                                            <th class="px-3 py-2">Tipo</th>
                                            <th class="px-3 py-2">Contato</th>
                                            <th class="px-3 py-2">Região</th>
                                            <th class="px-3 py-2">Status</th>
                                            <th class="px-3 py-2">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-[#cbd5e1] dark:divide-zinc-800">
                                        @forelse($dbPartners as $p)
                                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50">
                                            <td class="px-3 py-2 font-bold">{{ $p->name }}</td>
                                            <td class="px-3 py-2 uppercase font-mono text-[10px]">{{ $p->type }}</td>
                                            <td class="px-3 py-2">{{ $p->contact }} ({{ $p->phone }})</td>
                                            <td class="px-3 py-2">{{ $dbRegions->firstWhere('external_id', $p->region_external_id)->name ?? $p->region_external_id }}</td>
                                            <td class="px-3 py-2">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $p->status === 'ativo' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                                    {{ $p->status }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-2">
                                                <div class="flex gap-2">
                                                    @if($currentUser->can('parceiros:gerenciar'))
                                                    <button type="button" wire:click="editPartner('{{ $p->external_id }}')" class="text-zinc-500 hover:underline">Editar</button>
                                                    <button type="button" wire:click="deletePartner('{{ $p->external_id }}')" class="text-red-600 hover:underline">Excluir</button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="px-3 py-4 text-center text-zinc-400">Nenhum parceiro cadastrado.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif

                        <!-- LOCAIS TAB -->
                        @if($activeTab === 'locais')
                        <div class="flex flex-col gap-4">
                            <div class="flex items-center justify-between border-b border-zinc-200 pb-3 dark:border-zinc-800">
                                <div>
                                    <h2 class="text-sm font-bold text-[#154f85] dark:text-blue-400">Locais e Comitês</h2>
                                    <p class="text-[10px] text-zinc-400">Infraestrutura eleitoral física</p>
                                </div>
                                @if($currentUser->can('locais:gerenciar') && !$locationShowForm)
                                <button type="button" wire:click="$set('locationShowForm', true)" class="px-3 py-1.5 bg-[#157fcc] text-white font-semibold rounded hover:bg-[#115b94] transition active:scale-95 text-[10px] uppercase">
                                    Novo local
                                </button>
                                @endif
                            </div>

                            @if($locationShowForm)
                            <form wire:submit.prevent="saveLocation" class="border border-[#cbd5e1] dark:border-zinc-800 rounded p-4 bg-[#f8fbff] dark:bg-[#111c26]">
                                <h3 class="font-bold text-[#154f85] dark:text-blue-400 mb-3">{{ $locationId ? 'Editar Local' : 'Novo Local' }}</h3>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-1">Nome</label>
                                        <input type="text" wire:model="locationName" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100" required>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-1">Tipo</label>
                                        <select wire:model="locationType" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100">
                                            <option value="sede">Sede</option>
                                            <option value="comitê">Comitê</option>
                                            <option value="ponto de apoio">Ponto de Apoio</option>
                                        </select>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-1">Endereço</label>
                                        <input type="text" wire:model="locationAddress" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100" required>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-1">Região Vinculada</label>
                                        <select wire:model="locationRegionId" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100">
                                            @foreach($dbRegions as $reg)
                                                <option value="{{ $reg->external_id }}">{{ $reg->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-1">Capacidade</label>
                                        <input type="number" wire:model="locationCapacity" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100" required>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-1">Responsável</label>
                                        <input type="text" wire:model="locationResponsible" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100" required>
                                    </div>
                                </div>
                                <div class="mt-4 flex gap-2 justify-end">
                                    <button type="button" wire:click="resetLocationForm" class="px-3 py-1.5 bg-zinc-400 text-white rounded text-[10px] font-bold">Cancelar</button>
                                    <button type="submit" class="px-3 py-1.5 bg-[#157fcc] text-white rounded text-[10px] font-bold">Salvar</button>
                                </div>
                            </form>
                            @endif

                            <div class="overflow-x-auto border border-[#cbd5e1] dark:border-zinc-800">
                                <table class="w-full text-left text-xs bg-white dark:bg-zinc-950">
                                    <thead class="border-b border-[#cbd5e1] bg-[#eef3f9] dark:bg-[#1a2d3e] text-[10px] font-bold uppercase text-[#154f85] dark:text-blue-300">
                                        <tr>
                                            <th class="px-3 py-2">Nome</th>
                                            <th class="px-3 py-2">Tipo</th>
                                            <th class="px-3 py-2">Endereço</th>
                                            <th class="px-3 py-2">Capacidade</th>
                                            <th class="px-3 py-2">Responsável</th>
                                            <th class="px-3 py-2">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-[#cbd5e1] dark:divide-zinc-800">
                                        @forelse($dbLocations as $l)
                                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50">
                                            <td class="px-3 py-2 font-bold">{{ $l->name }}</td>
                                            <td class="px-3 py-2 uppercase font-mono text-[10px]">{{ $l->type }}</td>
                                            <td class="px-3 py-2 text-zinc-500 truncate max-w-xs">{{ $l->address }}</td>
                                            <td class="px-3 py-2 font-bold">{{ $l->capacity }} pessoas</td>
                                            <td class="px-3 py-2">{{ $l->responsible }}</td>
                                            <td class="px-3 py-2">
                                                <div class="flex gap-2">
                                                    @if($currentUser->can('locais:gerenciar'))
                                                    <button type="button" wire:click="editLocation('{{ $l->external_id }}')" class="text-zinc-500 hover:underline">Editar</button>
                                                    <button type="button" wire:click="deleteLocation('{{ $l->external_id }}')" class="text-red-600 hover:underline">Excluir</button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="px-3 py-4 text-center text-zinc-400">Nenhum local cadastrado.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif

                        <!-- REGIOES TAB -->
                        @if($activeTab === 'regioes')
                        <div class="flex flex-col gap-4">
                            <div class="flex items-center justify-between border-b border-zinc-200 pb-3 dark:border-zinc-800">
                                <div>
                                    <h2 class="text-sm font-bold text-[#154f85] dark:text-blue-400">Regiões Eleitorais</h2>
                                    <p class="text-[10px] text-zinc-400">Territórios e estatísticas demográficas</p>
                                </div>
                                @if($currentUser->can('regioes:gerenciar') && !$regionShowForm)
                                <button type="button" wire:click="$set('regionShowForm', true)" class="px-3 py-1.5 bg-[#157fcc] text-white font-semibold rounded hover:bg-[#115b94] transition active:scale-95 text-[10px] uppercase">
                                    Nova região
                                </button>
                                @endif
                            </div>

                            @if($regionShowForm)
                            <form wire:submit.prevent="saveRegion" class="border border-[#cbd5e1] dark:border-zinc-800 rounded p-4 bg-[#f8fbff] dark:bg-[#111c26]">
                                <h3 class="font-bold text-[#154f85] dark:text-blue-400 mb-3">{{ $regionId ? 'Editar Região' : 'Nova Região' }}</h3>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-1">Nome da região</label>
                                        <input type="text" wire:model="regionName" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100" required placeholder="Ex.: Zona Central">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-1">UF</label>
                                        <input type="text" wire:model="regionUf" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100" required maxlength="2" placeholder="SP">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-1">Municípios</label>
                                        <input type="number" wire:model="regionMunicipalities" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100" required min="1">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-1">População</label>
                                        <input type="number" wire:model="regionPopulation" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100" required min="0">
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-1">Coordenador</label>
                                        <input type="text" wire:model="regionCoordinator" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100" required placeholder="Nome do coordenador regional">
                                    </div>
                                </div>
                                <div class="mt-4 flex gap-2 justify-end">
                                    <button type="button" wire:click="resetRegionForm" class="px-3 py-1.5 bg-zinc-400 text-white rounded text-[10px] font-bold">Cancelar</button>
                                    <button type="submit" class="px-3 py-1.5 bg-[#157fcc] text-white rounded text-[10px] font-bold">Salvar</button>
                                </div>
                            </form>
                            @endif

                            <div class="overflow-x-auto border border-[#cbd5e1] dark:border-zinc-800">
                                <table class="w-full text-left text-xs bg-white dark:bg-zinc-950">
                                    <thead class="border-b border-[#cbd5e1] bg-[#eef3f9] dark:bg-[#1a2d3e] text-[10px] font-bold uppercase text-[#154f85] dark:text-blue-300">
                                        <tr>
                                            <th class="px-3 py-2">Região</th>
                                            <th class="px-3 py-2">UF</th>
                                            <th class="px-3 py-2">Municípios</th>
                                            <th class="px-3 py-2">População</th>
                                            <th class="px-3 py-2">Coordenador</th>
                                            <th class="px-3 py-2">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-[#cbd5e1] dark:divide-zinc-800">
                                        @forelse($dbRegions as $r)
                                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50">
                                            <td class="px-3 py-2 font-bold">{{ $r->name }}</td>
                                            <td class="px-3 py-2">{{ $r->uf }}</td>
                                            <td class="px-3 py-2 font-mono text-[10px]">{{ $r->municipalities }}</td>
                                            <td class="px-3 py-2">{{ number_format($r->population, 0, ',', '.') }}</td>
                                            <td class="px-3 py-2">{{ $r->coordinator }}</td>
                                            <td class="px-3 py-2">
                                                <div class="flex gap-2">
                                                    @if($currentUser->can('regioes:gerenciar'))
                                                    <button type="button" wire:click="editRegion('{{ $r->external_id }}')" class="text-zinc-500 hover:underline">Editar</button>
                                                    <button type="button" wire:click="deleteRegion('{{ $r->external_id }}')" class="text-red-600 hover:underline">Excluir</button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="px-3 py-4 text-center text-zinc-400">Nenhuma região cadastrada.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif

                        <!-- INTELIGENCIA E RELATORIOS TAB -->
                        @if($activeTab === 'relatorios')
                        <div class="flex flex-col gap-6">
                            <div class="flex items-center justify-between border-b border-zinc-200 pb-3 dark:border-zinc-800">
                                <div>
                                    <h2 class="text-sm font-bold text-[#154f85] dark:text-blue-400">Inteligência e Relatórios</h2>
                                    <p class="text-[10px] text-zinc-400">Modelos analíticos de consulta rápida</p>
                                </div>
                            </div>

                            <!-- Categories Pills -->
                            <div class="flex flex-wrap gap-2">
                                <button type="button" wire:click="$set('reportFilter', 'all')" class="rounded-full px-3 py-1 text-[10px] font-bold uppercase transition {{ $reportFilter === 'all' ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800' }}">Todos</button>
                                <button type="button" wire:click="$set('reportFilter', 'Territorial')" class="rounded-full px-3 py-1 text-[10px] font-bold uppercase transition {{ $reportFilter === 'Territorial' ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800' }}">Territorial</button>
                                <button type="button" wire:click="$set('reportFilter', 'Financeiro')" class="rounded-full px-3 py-1 text-[10px] font-bold uppercase transition {{ $reportFilter === 'Financeiro' ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800' }}">Financeiro</button>
                                <button type="button" wire:click="$set('reportFilter', 'Base Eleitoral')" class="rounded-full px-3 py-1 text-[10px] font-bold uppercase transition {{ $reportFilter === 'Base Eleitoral' ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800' }}">Base Eleitoral</button>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                @php
                                    $allReports = [
                                        ['id' => 'rep-1', 'title' => 'Desempenho por Região', 'category' => 'Territorial', 'description' => 'Metas atingidas e apoiadores por região.', 'icon' => '🗺'],
                                        ['id' => 'rep-2', 'title' => 'Gastos de Campanha', 'category' => 'Financeiro', 'description' => 'Resumo de contas por despesa e fornecedor.', 'icon' => '💰'],
                                        ['id' => 'rep-3', 'title' => 'Eleitores por Comitê', 'category' => 'Base Eleitoral', 'description' => 'Apoiadores vinculados aos comitês.', 'icon' => '👥'],
                                        ['id' => 'rep-4', 'title' => 'Comparativo de Adversários', 'category' => 'Inteligência', 'description' => 'Intenção de voto histórico eleitoral.', 'icon' => '⚔'],
                                    ];
                                @endphp
                                @foreach($allReports as $rep)
                                @if($reportFilter === 'all' || $rep['category'] === $reportFilter)
                                <button type="button" wire:click="selectReport('{{ $rep['id'] }}')" class="group flex flex-col rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-950 p-4 text-left transition hover:border-indigo-300 hover:shadow-md">
                                    <div class="flex items-start justify-between w-full">
                                        <span class="text-2xl">{{ $rep['icon'] }}</span>
                                        <span class="rounded bg-zinc-100 dark:bg-[#1a2d3e] text-[#154f85] dark:text-blue-300 font-bold px-1.5 py-0.5 text-[8px] uppercase">{{ $rep['category'] }}</span>
                                    </div>
                                    <p class="font-bold text-zinc-900 dark:text-zinc-50 mt-3 group-hover:text-indigo-600 transition-colors">{{ $rep['title'] }}</p>
                                    <p class="text-[11px] text-zinc-500 mt-1">{{ $rep['description'] }}</p>
                                    <span class="text-[10px] font-bold text-indigo-500 mt-3 opacity-0 group-hover:opacity-100 transition-opacity">Abrir relatório →</span>
                                </button>
                                @endif
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- DYNAMIC REPORT VIEWER TAB -->
                        @if(str_starts_with($activeTab, 'relatorio-'))
                        <div class="flex flex-col gap-4">
                            <h2 class="text-sm font-bold text-[#154f85] dark:text-blue-400">Visualizador de Relatório</h2>
                            <p class="text-[11px] text-zinc-500">Configurações e pré-visualização dos dados calculados.</p>

                            <div class="flex flex-wrap items-end gap-3 rounded bg-zinc-50 dark:bg-[#111c26] border border-zinc-200 dark:border-zinc-800 p-4">
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">Período</label>
                                    <select wire:model="reportPeriod" class="border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100">
                                        <option value="2026-Q1">2026 — 1º Trimestre</option>
                                        <option value="2026-Q2">2026 — 2º Trimestre</option>
                                        <option value="2026-eleicao">Eleição 2026</option>
                                    </select>
                                </div>
                                <button type="button" wire:click="executeReport" class="px-4 py-2 bg-[#157fcc] text-white font-semibold text-xs rounded hover:bg-[#115b94] transition">
                                    {{ $reportLoading ? 'Carregando...' : 'Executar relatório' }}
                                </button>
                            </div>

                            @if($reportResult)
                            <div class="mt-4 border border-[#cbd5e1] dark:border-zinc-800 rounded overflow-hidden">
                                <table class="w-full text-left text-xs bg-white dark:bg-zinc-950">
                                    <thead class="border-b border-[#cbd5e1] bg-[#eef3f9] dark:bg-[#1a2d3e] text-[10px] font-bold uppercase text-[#154f85] dark:text-blue-300">
                                        <tr>
                                            @foreach($reportResult['columns'] as $col)
                                                <th class="px-3 py-2">{{ $col }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-[#cbd5e1] dark:divide-zinc-800">
                                        @foreach($reportResult['rows'] as $row)
                                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50">
                                            @foreach($reportResult['columns'] as $col)
                                                <td class="px-3 py-2">
                                                    {{ is_numeric($row[$col]) ? number_format($row[$col], 0, ',', '.') : $row[$col] }}
                                                </td>
                                            @endforeach
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif
                        </div>
                        @endif

                        <!-- CONSULTA TRE TAB -->
                        @if($activeTab === 'tre')
                        <div class="flex flex-col gap-4">
                            <h2 class="text-sm font-bold text-[#154f85] dark:text-blue-400">Consulta Oficial TRE</h2>
                            <p class="text-[10px] text-zinc-400">Informações oficiais de registro de candidaturas do TSE</p>

                            <form wire:submit.prevent="searchTre" class="rounded border border-zinc-200 bg-zinc-50/50 p-4 dark:border-zinc-800 dark:bg-zinc-900/30">
                                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                    <div class="flex flex-col gap-1">
                                        <label class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">Nome</label>
                                        <input type="text" wire:model="treNome" class="border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100" placeholder="Ex: Roberto">
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <label class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">UF</label>
                                        <select wire:model="treUf" class="border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100">
                                            <option value="SP">SP</option>
                                            <option value="RJ">RJ</option>
                                            <option value="MG">MG</option>
                                        </select>
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <label class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">Cargo</label>
                                        <select wire:model="treCargo" class="border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100">
                                            <option value="Deputado Federal">Deputado Federal</option>
                                            <option value="Deputado Estadual">Deputado Estadual</option>
                                        </select>
                                    </div>
                                    <div class="flex flex-col justify-end">
                                        <button type="submit" class="px-4 py-2 bg-[#157fcc] text-white font-semibold text-xs rounded hover:bg-[#115b94] transition">Consultar TRE</button>
                                    </div>
                                </div>
                            </form>

                            @if($treSearched)
                            <div class="overflow-x-auto border border-[#cbd5e1] dark:border-zinc-800">
                                <table class="w-full text-left text-xs bg-white dark:bg-zinc-950">
                                    <thead class="border-b border-[#cbd5e1] bg-[#eef3f9] dark:bg-[#1a2d3e] text-[10px] font-bold uppercase text-[#154f85] dark:text-blue-300">
                                        <tr>
                                            <th class="px-3 py-2">Candidato</th>
                                            <th class="px-3 py-2">Nº</th>
                                            <th class="px-3 py-2">Partido</th>
                                            <th class="px-3 py-2">Situação</th>
                                            <th class="px-3 py-2">Intenção</th>
                                            <th class="px-3 py-2">Comp.</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-[#cbd5e1] dark:divide-zinc-800">
                                        @forelse($treResults as $cand)
                                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50">
                                            <td class="px-3 py-2">
                                                <button type="button" wire:click="selectCandidateDetail('{{ $cand['id'] }}')" class="font-bold text-blue-600 hover:underline text-left">{{ $cand['nomeUrna'] }}</button>
                                            </td>
                                            <td class="px-3 py-2 font-mono">{{ $cand['numero'] }}</td>
                                            <td class="px-3 py-2">{{ $cand['siglaPartido'] }}</td>
                                            <td class="px-3 py-2">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $cand['situacao'] === 'deferido' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ $cand['situacao'] }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-2 font-bold">{{ $cand['intencaoVoto'] }}%</td>
                                            <td class="px-3 py-2">
                                                <input type="checkbox" wire:click="toggleTreCompare('{{ $cand['id'] }}')" @if(in_array($cand['id'], $treCompareIds)) checked @endif class="rounded border-[#cbd5e1]">
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="px-3 py-4 text-center text-zinc-400">Nenhum candidato localizado.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @endif

                            @if($treDetail)
                            <div class="rounded border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-4 mt-4">
                                <h3 class="font-bold border-b border-[#cbd5e1] dark:border-zinc-800 pb-2 mb-2">{{ $treDetail['nomeUrna'] }}</h3>
                                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 text-[11px]">
                                    <div>
                                        <p class="font-bold text-zinc-400 uppercase">Nome Completo</p>
                                        <p class="font-medium text-zinc-900 dark:text-zinc-50">{{ $treDetail['nome'] }}</p>
                                    </div>
                                    <div>
                                        <p class="font-bold text-zinc-400 uppercase">Partido / Coligação</p>
                                        <p class="font-medium text-zinc-900 dark:text-zinc-50">{{ $treDetail['siglaPartido'] }} — {{ $treDetail['coligacao'] ?? 'Nenhuma' }}</p>
                                    </div>
                                    <div>
                                        <p class="font-bold text-zinc-400 uppercase">UF / Município</p>
                                        <p class="font-medium text-zinc-900 dark:text-zinc-50">{{ $treDetail['uf'] }} — {{ $treDetail['municipio'] }}</p>
                                    </div>
                                    <div>
                                        <p class="font-bold text-zinc-400 uppercase">Votos 2022</p>
                                        <p class="font-medium text-zinc-900 dark:text-zinc-50">{{ number_format($treDetail['votos'], 0, ',', '.') }}</p>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Comparative visual chart emulation if 2+ selected -->
                            @if(count($treCompareIds) >= 2)
                            <div class="grid gap-4 md:grid-cols-2 mt-4">
                                <div class="border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-950 p-4 rounded shadow-xs">
                                    <h3 class="font-bold text-[#154f85] dark:text-blue-400 border-b border-[#f1f5f9] dark:border-zinc-800 pb-2 mb-3">Intenção de voto (%)</h3>
                                    <div class="space-y-3">
                                        @foreach($treResults as $c)
                                        @if(in_array($c['id'], $treCompareIds))
                                        <div>
                                            <div class="flex justify-between text-[10px] mb-1">
                                                <span>{{ $c['nomeUrna'] }}</span>
                                                <span class="font-bold">{{ $c['intencaoVoto'] }}%</span>
                                            </div>
                                            <div class="h-3 w-full bg-zinc-100 dark:bg-zinc-800 rounded">
                                                <div class="h-full bg-indigo-600 rounded" style="width: {{ $c['intencaoVoto'] * 2 }}%"></div>
                                            </div>
                                        </div>
                                        @endif
                                        @endforeach
                                    </div>
                                </div>

                                <div class="border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-950 p-4 rounded shadow-xs">
                                    <h3 class="font-bold text-[#154f85] dark:text-blue-400 border-b border-[#f1f5f9] dark:border-zinc-800 pb-2 mb-3">Votos (k)</h3>
                                    <div class="space-y-3">
                                        @foreach($treResults as $c)
                                        @if(in_array($c['id'], $treCompareIds))
                                        <div>
                                            <div class="flex justify-between text-[10px] mb-1">
                                                <span>{{ $c['nomeUrna'] }}</span>
                                                <span class="font-bold">{{ round($c['votos'] / 1000) }}k</span>
                                            </div>
                                            <div class="h-3 w-full bg-zinc-100 dark:bg-zinc-800 rounded">
                                                <div class="h-full bg-violet-600 rounded" style="width: {{ ($c['votos'] / 200000) * 100 }}%"></div>
                                            </div>
                                        </div>
                                        @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                        @endif

                        <!-- MEU PERFIL TAB -->
                        @if($activeTab === 'perfil')
                        <div class="flex flex-col gap-4 max-w-2xl">
                            <h2 class="text-sm font-bold text-[#154f85] dark:text-blue-400">Preferências de Perfil</h2>
                            <p class="text-[11px] text-zinc-500">Personalize as informações do seu usuário.</p>

                            <div class="flex border-b border-[#cbd5e1] dark:border-zinc-855 gap-1 items-end h-8 shrink-0">
                                <button type="button" wire:click="$set('profileActiveSubTab', 'dados')" class="flex h-7 items-center gap-1.5 px-3 border-t rounded-t cursor-pointer text-[10px] font-bold {{ $profileActiveSubTab === 'dados' ? 'bg-white dark:bg-zinc-950 border-t-2 border-t-[#157fcc] dark:border-t-blue-500 border-x border-x-[#cbd5e1] dark:border-x-zinc-800 text-[#157fcc] dark:text-blue-400 -mb-[1px]' : 'bg-[#eef2f7] dark:bg-[#141e28] text-[#555] hover:bg-[#e2e8f0]' }}">Dados Pessoais</button>
                                <button type="button" wire:click="$set('profileActiveSubTab', 'contatos')" class="flex h-7 items-center gap-1.5 px-3 border-t rounded-t cursor-pointer text-[10px] font-bold {{ $profileActiveSubTab === 'contatos' ? 'bg-white dark:bg-zinc-950 border-t-2 border-t-[#157fcc] dark:border-t-blue-500 border-x border-x-[#cbd5e1] dark:border-x-zinc-800 text-[#157fcc] dark:text-blue-400 -mb-[1px]' : 'bg-[#eef2f7] dark:bg-[#141e28] text-[#555] hover:bg-[#e2e8f0]' }}">Contatos</button>
                                <button type="button" wire:click="$set('profileActiveSubTab', 'preferencias')" class="flex h-7 items-center gap-1.5 px-3 border-t rounded-t cursor-pointer text-[10px] font-bold {{ $profileActiveSubTab === 'preferencias' ? 'bg-white dark:bg-zinc-950 border-t-2 border-t-[#157fcc] dark:border-t-blue-500 border-x border-x-[#cbd5e1] dark:border-x-zinc-800 text-[#157fcc] dark:text-blue-400 -mb-[1px]' : 'bg-[#eef2f7] dark:bg-[#141e28] text-[#555] hover:bg-[#e2e8f0]' }}">Preferências</button>
                            </div>

                            <div class="border border-t-0 border-[#cbd5e1] dark:border-zinc-800 rounded-b p-4 bg-white dark:bg-zinc-950">
                                @if($profileActiveSubTab === 'dados')
                                <form wire:submit.prevent="saveProfilePersonal" class="flex flex-col gap-4">
                                    <h3 class="text-xs font-bold text-[#154f85] dark:text-blue-400 border-b border-[#f1f5f9] dark:border-zinc-800 pb-2">Informações de Identificação Pessoal</h3>
                                    <div class="flex flex-col gap-1.5">
                                        <label class="text-[10px] font-bold text-zinc-400 uppercase">Nome de Exibição</label>
                                        <input type="text" wire:model="profileName" class="border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100" required>
                                    </div>
                                    <div class="flex justify-end">
                                        <button type="submit" class="px-4 py-2 bg-[#157fcc] text-white font-semibold text-xs rounded hover:bg-[#115b94] transition">Salvar Dados Pessoais</button>
                                    </div>
                                </form>
                                @endif

                                @if($profileActiveSubTab === 'contatos')
                                <form wire:submit.prevent="saveProfileContacts" class="flex flex-col gap-4">
                                    <h3 class="text-xs font-bold text-[#154f85] dark:text-blue-400 border-b border-[#f1f5f9] dark:border-zinc-800 pb-2">Canais de Comunicação e Contato</h3>
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <div class="flex flex-col gap-1.5">
                                            <label class="text-[10px] font-bold text-zinc-400 uppercase">E-mail de Acesso</label>
                                            <input type="email" wire:model="profileEmail" class="border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100" required>
                                        </div>
                                        <div class="flex flex-col gap-1.5">
                                            <label class="text-[10px] font-bold text-zinc-400 uppercase">Telefone de Contato</label>
                                            <input type="text" wire:model="profilePhone" class="border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100">
                                        </div>
                                    </div>
                                    <div class="flex justify-end">
                                        <button type="submit" class="px-4 py-2 bg-[#157fcc] text-white font-semibold text-xs rounded hover:bg-[#115b94] transition">Salvar Contatos</button>
                                    </div>
                                </form>
                                @endif

                                @if($profileActiveSubTab === 'preferencias')
                                <form wire:submit.prevent="saveProfilePreferences" class="flex flex-col gap-4">
                                    <h3 class="text-xs font-bold text-[#154f85] dark:text-blue-400 border-b border-[#f1f5f9] dark:border-zinc-800 pb-2">Personalização Visual e Interface</h3>
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <div class="flex flex-col gap-1.5">
                                            <label class="text-[10px] font-bold text-zinc-400 uppercase">Tema do Workspace</label>
                                            <select wire:model="profileTheme" class="border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100">
                                                <option value="triton">Triton Classic (Blue)</option>
                                                <option value="neptune">Neptune (Teal)</option>
                                                <option value="slate">Slate Modern (Charcoal)</option>
                                            </select>
                                        </div>
                                        <div class="flex flex-col gap-1.5">
                                            <label class="text-[10px] font-bold text-zinc-400 uppercase">Modo de Exibição</label>
                                            <select wire:model="profileMode" class="border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100">
                                                <option value="light">Claro (Light)</option>
                                                <option value="dark">Escuro (Dark)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="flex justify-end">
                                        <button type="submit" class="px-4 py-2 bg-[#157fcc] text-white font-semibold text-xs rounded hover:bg-[#115b94] transition">Salvar Preferências</button>
                                    </div>
                                </form>
                                @endif
                            </div>
                        </div>
                        @endif

                        <!-- PERMISSOES TAB -->
                        @if($activeTab === 'permissoes')
                        <div class="flex flex-col gap-4">
                            <div class="flex items-center justify-between border-b border-zinc-200 pb-3 dark:border-zinc-800">
                                <div>
                                    <h2 class="text-sm font-bold text-[#154f85] dark:text-blue-400">Configurações de Acesso</h2>
                                    <p class="text-[10px] text-zinc-400">Controle de perfis e matriz de permissões</p>
                                </div>
                                @if(!$roleShowForm)
                                <button type="button" wire:click="$set('roleShowForm', true)" class="px-3 py-1.5 bg-[#157fcc] text-white font-semibold rounded hover:bg-[#115b94] transition active:scale-95 text-[10px] uppercase">
                                    Novo perfil
                                </button>
                                @endif
                            </div>

                            @if($roleShowForm)
                            <form wire:submit.prevent="saveRole" class="border border-[#cbd5e1] dark:border-zinc-800 rounded p-4 bg-[#f8fbff] dark:bg-[#111c26]">
                                <h3 class="font-bold text-[#154f85] dark:text-blue-400 mb-3">{{ $roleId ? 'Editar Perfil' : 'Novo Perfil' }}</h3>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-1">Nome do perfil</label>
                                        <input type="text" wire:model="roleName" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100" required>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-1">Descrição</label>
                                        <input type="text" wire:model="roleDescription" class="w-full border border-[#cbd5e1] bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs rounded text-zinc-900 dark:text-zinc-100">
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <label class="block text-[10px] font-bold text-zinc-400 uppercase mb-2">Permissões</label>
                                    <div class="grid gap-2 sm:grid-cols-3 max-h-48 overflow-y-auto">
                                        @foreach($dbAvailablePermissions as $perm)
                                        <label class="flex cursor-pointer items-center gap-2 rounded border border-zinc-100 bg-zinc-50/50 px-2 py-1.5 text-xs hover:bg-zinc-100">
                                            <input type="checkbox" wire:click="toggleRolePermission('{{ $perm['id'] }}')" @if(in_array($perm['id'], $rolePermissions)) checked @endif class="rounded border-zinc-300">
                                            <span class="truncate">{{ $perm['label'] }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="mt-4 flex gap-2 justify-end">
                                    <button type="button" wire:click="resetRoleForm" class="px-3 py-1.5 bg-zinc-400 text-white rounded text-[10px] font-bold">Cancelar</button>
                                    <button type="submit" class="px-3 py-1.5 bg-[#157fcc] text-white rounded text-[10px] font-bold">Salvar perfil</button>
                                </div>
                            </form>
                            @endif

                            <div class="overflow-x-auto border border-[#cbd5e1] dark:border-zinc-800">
                                <table class="w-full text-left text-xs bg-white dark:bg-zinc-950">
                                    <thead class="border-b border-[#cbd5e1] bg-[#eef3f9] dark:bg-[#1a2d3e] text-[10px] font-bold uppercase text-[#154f85] dark:text-blue-300">
                                        <tr>
                                            <th class="px-3 py-2">Perfil</th>
                                            <th class="px-3 py-2">Descrição</th>
                                            <th class="px-3 py-2">Capacidades</th>
                                            <th class="px-3 py-2">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-[#cbd5e1] dark:divide-zinc-800">
                                        @foreach($dbRoles as $role)
                                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50">
                                            <td class="px-3 py-2 font-bold">{{ $role->name }}</td>
                                            <td class="px-3 py-2 text-zinc-500">{{ $role->description ?? '—' }}</td>
                                            <td class="px-3 py-2 text-[10px] font-bold text-zinc-500 uppercase">{{ $role->permissions->count() }} regras ativas</td>
                                            <td class="px-3 py-2">
                                                <button type="button" wire:click="editRole('{{ $role->external_id }}')" class="text-zinc-650 hover:underline">Configurar</button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif

                        <!-- DIAGNOSTICS: SCHEMA TAB -->
                        @if($activeTab === 'schema')
                        <div class="flex flex-col gap-4">
                            <div class="grid gap-4 lg:grid-cols-[280px_1fr]">
                                <!-- Selectors Sidebar (matches original left bar) -->
                                <div class="flex flex-col gap-3">
                                    <div class="border border-[#cbd5e1] dark:border-zinc-800 bg-white dark:bg-zinc-950">
                                        <div class="bg-[#eef3f9] dark:bg-[#1a2d3e] px-3 py-2 text-[10px] font-bold uppercase tracking-wider text-[#154f85] dark:text-blue-300 border-b border-[#cbd5e1] dark:border-zinc-850">
                                            Bases de dados
                                        </div>
                                        <div class="p-1 flex flex-col">
                                            @foreach($databases as $db)
                                            <button type="button" wire:click="selectDatabase('{{ $db['name'] }}')" class="flex justify-between items-center px-2 py-1.5 text-left text-xs rounded hover:bg-zinc-100 {{ $selectedDatabase === $db['name'] ? 'bg-indigo-50 font-bold text-indigo-700' : 'text-zinc-600' }}">
                                                <span class="truncate">{{ $db['label'] }}</span>
                                                @if($db['current'] ?? false)
                                                <span class="text-[8px] uppercase font-bold text-indigo-500">atual</span>
                                                @endif
                                            </button>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="border border-[#cbd5e1] dark:border-zinc-800 bg-white dark:bg-zinc-950">
                                        <div class="bg-[#eef3f9] dark:bg-[#1a2d3e] px-3 py-2 text-[10px] font-bold uppercase tracking-wider text-[#154f85] dark:text-blue-300 border-b border-[#cbd5e1] dark:border-zinc-850">
                                            Tabelas
                                        </div>
                                        <div class="p-1 flex flex-col max-h-60 overflow-y-auto">
                                            @foreach($tables as $tbl)
                                            <button type="button" wire:click="selectTable('{{ $tbl['name'] }}')" class="flex items-center gap-1.5 px-2 py-1.5 text-left text-xs rounded hover:bg-zinc-100 {{ $selectedTable === $tbl['name'] ? 'bg-indigo-50 font-bold text-indigo-700' : 'text-zinc-600' }}">
                                                <span>◫</span>
                                                <span class="truncate">{{ $tbl['label'] }}</span>
                                            </button>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <!-- Schema columns & preview -->
                                <div class="flex flex-col gap-4 min-w-0">
                                    <div class="border border-[#c0c7d0] dark:border-zinc-800 bg-white dark:bg-zinc-950 p-4">
                                        <h3 class="font-bold text-[#154f85] dark:text-blue-400 border-b border-[#cbd5e1] dark:border-zinc-850 pb-2 mb-3">Colunas da Tabela: {{ $selectedTable ?? 'Nenhuma' }}</h3>
                                        <div class="overflow-x-auto">
                                            <table class="w-full text-left text-xs">
                                                <thead class="border-b border-[#cbd5e1] text-[10px] font-bold uppercase text-zinc-400">
                                                    <tr>
                                                        <th class="px-3 py-2">Nome</th>
                                                        <th class="px-3 py-2">Tipo</th>
                                                        <th class="px-3 py-2">Nulo</th>
                                                        <th class="px-3 py-2">Default</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-[#cbd5e1] dark:divide-zinc-800">
                                                    @forelse($tableColumns as $col)
                                                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50">
                                                        <td class="px-3 py-2 font-bold">{{ $col['name'] }}</td>
                                                        <td class="px-3 py-2 font-mono text-[11px]">{{ $col['type'] }}</td>
                                                        <td class="px-3 py-2 uppercase">{{ $col['nullable'] }}</td>
                                                        <td class="px-3 py-2 text-zinc-500 font-mono">{{ $col['default_value'] ?? '—' }}</td>
                                                    </tr>
                                                    @empty
                                                    <tr>
                                                        <td colspan="4" class="px-3 py-4 text-center text-zinc-400">Selecione uma tabela para ver colunas.</td>
                                                    </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="border border-[#c0c7d0] dark:border-zinc-800 bg-white dark:bg-zinc-950 p-4">
                                        <h3 class="font-bold text-[#154f85] dark:text-blue-400 border-b border-[#cbd5e1] dark:border-zinc-850 pb-2 mb-3">Preview dos Dados</h3>
                                        <div class="overflow-x-auto">
                                            <table class="w-full text-left text-xs">
                                                <thead class="border-b border-[#cbd5e1] text-[10px] font-bold uppercase text-zinc-400">
                                                    <tr>
                                                        @foreach(array_keys($tablePreview[0] ?? []) as $col)
                                                            <th class="px-3 py-2">{{ $col }}</th>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-[#cbd5e1] dark:divide-zinc-800">
                                                    @forelse($tablePreview as $row)
                                                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50">
                                                        @foreach($row as $val)
                                                            <td class="px-3 py-2 text-zinc-700 dark:text-zinc-300 font-mono">{{ $this->formatValue($val) }}</td>
                                                        @endforeach
                                                    </tr>
                                                    @empty
                                                    <tr>
                                                        <td class="px-3 py-4 text-center text-zinc-400" colspan="{{ max(1, count($tablePreview[0] ?? [])) }}">Sem dados.</td>
                                                    </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- DIAGNOSTICS: SQL TAB -->
                        @if($activeTab === 'sql')
                        <div class="grid gap-4 lg:grid-cols-2">
                            <!-- SQL Editor -->
                            <div class="border border-[#c0c7d0] bg-white dark:bg-zinc-950 p-4">
                                <h3 class="font-bold text-[#154f85] dark:text-blue-400 border-b border-[#cbd5e1] pb-2 mb-3">Editor SQL</h3>
                                <form wire:submit.prevent="executeQuery" class="space-y-4">
                                    <textarea wire:model.defer="sqlQuery" rows="12" class="w-full border border-[#cbd5e1] bg-[#fbfdff] dark:bg-zinc-900 px-3 py-2 font-mono text-[12px] text-zinc-900 dark:text-zinc-100 rounded focus:border-[#157fcc] outline-none" placeholder="select * from users limit 25;"></textarea>
                                    <div class="flex gap-2">
                                        <button type="submit" class="px-4 py-2 bg-[#1f2937] text-white font-semibold text-xs rounded hover:bg-black transition">Executar</button>
                                        <button type="button" wire:click="$refresh" class="border border-[#cbd5e1] px-4 py-2 text-xs font-semibold rounded hover:bg-zinc-50">Recarregar</button>
                                    </div>
                                </form>
                                @if($sqlMessage)
                                    <div class="mt-3 border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-800 rounded">{{ $sqlMessage }}</div>
                                @endif
                                @if($sqlError)
                                    <div class="mt-3 border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-800 rounded">{{ $sqlError }}</div>
                                @endif
                            </div>

                            <!-- SQL Results -->
                            <div class="border border-[#c0c7d0] bg-white dark:bg-zinc-950 p-4 flex flex-col min-w-0">
                                <h3 class="font-bold text-[#154f85] dark:text-blue-400 border-b border-[#cbd5e1] pb-2 mb-3">Resultado</h3>
                                <div class="overflow-x-auto flex-1">
                                    <table class="w-full text-left text-xs bg-white dark:bg-zinc-950">
                                        <thead class="border-b border-[#cbd5e1] text-[10px] font-bold uppercase text-zinc-400">
                                            <tr>
                                                @foreach($sqlColumns as $col)
                                                    <th class="px-3 py-2">{{ $col }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-[#cbd5e1] dark:divide-zinc-800">
                                            @forelse($sqlRows as $row)
                                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50">
                                                @foreach($sqlColumns as $col)
                                                    <td class="px-3 py-2 font-mono text-[#444] dark:text-zinc-300">{{ $this->formatValue($row[$col] ?? null) }}</td>
                                                @endforeach
                                            </tr>
                                            @empty
                                            <tr>
                                                <td class="px-3 py-4 text-center text-zinc-400" colspan="{{ max(1, count($sqlColumns)) }}">Nenhum resultado.</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mt-4 pt-4 border-t border-[#cbd5e1]">
                                    <h4 class="text-[10px] font-bold uppercase text-zinc-400 mb-2">Histórico de Consultas</h4>
                                    <div class="space-y-2 max-h-40 overflow-y-auto">
                                        @forelse($queryHistory as $entry)
                                        <div class="border border-zinc-200 p-2 text-[10px] rounded bg-[#fafafa]">
                                            <div class="flex justify-between font-mono text-[9px] text-zinc-400">
                                                <span>{{ $entry['at'] }}</span>
                                                <span class="{{ $entry['failed'] ? 'text-rose-600' : 'text-emerald-600' }}">{{ $entry['failed'] ? 'erro' : 'ok' }}</span>
                                            </div>
                                            <p class="truncate font-mono font-semibold text-zinc-800 mt-1">{{ $entry['sql'] }}</p>
                                        </div>
                                        @empty
                                        <p class="text-zinc-400 italic text-[10px]">Nenhuma consulta no histórico.</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- DIAGNOSTICS: DEBUG TAB -->
                        @if($activeTab === 'debug')
                        <div class="flex flex-col gap-4">
                            <div class="grid gap-4 lg:grid-cols-2">
                                <div class="border border-[#c0c7d0] bg-white dark:bg-zinc-950 p-4">
                                    <h3 class="font-bold text-[#154f85] dark:text-blue-400 border-b border-[#cbd5e1] pb-2 mb-3">Terminal de Comando</h3>
                                    <form wire:submit.prevent="runTerminal" class="space-y-4">
                                        <input type="text" wire:model.defer="debugCommand" class="w-full border border-[#cbd5e1] bg-[#fbfdff] dark:bg-zinc-900 px-3 py-2 font-mono text-[12px] text-zinc-900 dark:text-zinc-100 rounded focus:border-[#157fcc] outline-none" placeholder="php artisan queue:work --once">
                                        <div class="flex gap-2">
                                            <button type="submit" class="px-4 py-2 bg-[#1f2937] text-white font-semibold text-xs rounded hover:bg-black transition">Executar</button>
                                            <button type="button" wire:click="useLatestLog" class="border border-[#cbd5e1] px-4 py-2 text-xs font-semibold rounded hover:bg-zinc-50">Último log</button>
                                            <button type="button" wire:click="downloadTail" class="px-4 py-2 bg-[#eef6ff] text-[#154f85] font-semibold border border-[#154f85] text-xs rounded hover:bg-[#dfeeff]">Baixar arquivo completo</button>
                                        </div>
                                    </form>
                                    @if($debugError)
                                        <div class="mt-3 border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-800 rounded">{{ $debugError }}</div>
                                    @endif
                                </div>

                                <div class="border border-[#c0c7d0] bg-white dark:bg-zinc-950 p-4 flex flex-col">
                                    <h3 class="font-bold text-[#154f85] dark:text-blue-400 border-b border-[#cbd5e1] pb-2 mb-3">Logs Recentes</h3>
                                    <div class="flex flex-wrap gap-2">
                                        @forelse($logFiles as $lf)
                                        <button type="button" wire:click="selectTail('{{ $lf['path'] }}')" class="border border-[#cbd5e1] bg-white dark:bg-zinc-900 hover:bg-zinc-50 px-2 py-1 text-[10px] rounded truncate max-w-xs {{ $tailPath === $lf['path'] ? 'border-indigo-500 text-indigo-700 bg-indigo-50' : 'text-zinc-600' }}">
                                            {{ $lf['label'] }}
                                        </button>
                                        @empty
                                        <p class="text-zinc-400 italic">Nenhum log encontrado.</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            <div class="border border-[#c0c7d0] bg-white dark:bg-zinc-950 p-4">
                                <h3 class="font-bold text-[#154f85] dark:text-blue-400 border-b border-[#cbd5e1] pb-2 mb-3">Saída do Terminal</h3>
                                <pre class="min-h-56 bg-zinc-900 text-emerald-400 p-4 font-mono text-[11px] overflow-auto rounded">{{ $debugOutput ?: 'Nenhuma saída ainda.' }}</pre>
                            </div>
                        </div>
                        @endif

                    </div>
                </div>

                <!-- FOOTER (SOUTH REGION) -->
                <footer class="flex h-6 w-full shrink-0 items-center justify-between border-t border-[#c0c7d0] dark:border-[#2b3e51] bg-[#f0f0f0] dark:bg-[#111a24] px-3 text-[#555] dark:text-zinc-400 shadow-inner text-[10px] z-10">
                    <div class="flex items-center gap-1.5">
                        <span class="inline-block h-2 w-2 rounded-full bg-green-500 animate-pulse"></span>
                        <span>Status: Pronto</span>
                    </div>

                    <div class="hidden border-x border-[#c0c7d0] dark:border-[#2b3e51] px-4 sm:block">
                        <span>Usuário Conectado: <strong>{{ $profileEmail }}</strong></span>
                    </div>

                    <div>
                        <span>{{ strtoupper($activeTab) }}</span>
                    </div>
                </footer>

            </main>

        </div>

    </div>
</div>
