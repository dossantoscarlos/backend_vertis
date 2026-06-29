<div class="min-h-screen bg-zinc-100 text-zinc-900 dark:bg-zinc-950 dark:text-zinc-50">
    <div class="mx-auto flex min-h-screen w-full max-w-[1600px] flex-col gap-4 px-4 py-4 lg:px-6">
        <header class="rounded-[2rem] border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="space-y-2">
                    <p class="text-xs uppercase tracking-[0.28em] text-zinc-500">Painel de suporte</p>
                    <div class="flex flex-wrap items-center gap-3">
                        <h1 class="text-3xl font-bold tracking-tight">Workspace ExtJS</h1>
                        <span class="rounded-full border border-zinc-200 px-3 py-1 text-xs font-semibold dark:border-zinc-800">
                            {{ $this->supportProfile['role'] }}@if($this->supportProfile['level']) · {{ $this->supportProfile['level'] }}@endif
                        </span>
                    </div>
                    <p class="max-w-3xl text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                        Interface em abas, com painel inicial, módulos por nível e leitura rápida da operação de suporte.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <button
                        type="button"
                        wire:click="$refresh"
                        class="rounded-2xl border border-zinc-200 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-300 dark:hover:bg-zinc-900"
                    >
                        Atualizar
                    </button>
                    <button
                        type="button"
                        wire:click="restartQueueWorkers"
                        class="rounded-2xl bg-zinc-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-zinc-800 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-200"
                    >
                        Reiniciar workers
                    </button>
                    <button
                        type="button"
                        wire:click="logout"
                        class="rounded-2xl border border-zinc-200 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-300 dark:hover:bg-zinc-900"
                    >
                        Sair
                    </button>
                </div>
            </div>

            @if (session('status'))
                <div class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900/40 dark:bg-emerald-950/30 dark:text-emerald-300">
                    {{ session('status') }}
                </div>
            @endif
        </header>

        <section class="rounded-[2rem] border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
            <div class="flex flex-wrap items-end gap-1 border-b border-zinc-200 px-2 pt-2 dark:border-zinc-800">
                @foreach($this->availableTabs as $tab)
                    <button
                        type="button"
                        wire:click="setTab('{{ $tab['id'] }}')"
                        @class([
                            'group flex h-10 items-center gap-2 rounded-t-2xl border border-transparent px-4 text-sm font-medium transition',
                            'bg-white text-zinc-900 -mb-px border-zinc-200 border-b-white shadow-sm dark:bg-zinc-950 dark:text-zinc-50 dark:border-zinc-800 dark:border-b-zinc-950' => $this->activeTab === $tab['id'],
                            'bg-zinc-100 text-zinc-600 hover:bg-zinc-50 dark:bg-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800/80' => $this->activeTab !== $tab['id'],
                        ])
                    >
                        <span class="text-xs">{{ $tab['icon'] }}</span>
                        <span>{{ $tab['label'] }}</span>
                    </button>
                @endforeach
            </div>

            <div class="p-4 lg:p-6">
                @if ($this->activeTab === 'home')
                    <div class="grid gap-4 xl:grid-cols-[1.2fr_0.8fr]">
                        <div class="grid gap-4">
                            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                                <x-support.module-block title="Jobs" icon="⟳" subtitle="Pendentes na fila do Laravel.">
                                    <p class="text-3xl font-bold">{{ $this->stats['jobs'] }}</p>
                                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Itens aguardando processamento</p>
                                </x-support.module-block>

                                <x-support.module-block title="Falhas" icon="⚠" subtitle="Controle das últimas exceções.">
                                    <p class="text-3xl font-bold">{{ $this->stats['failedJobs'] }}</p>
                                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Jobs com erro registrados</p>
                                </x-support.module-block>

                                <x-support.module-block title="Usuários" icon="▣" subtitle="Base operacional carregada.">
                                    <p class="text-3xl font-bold">{{ $this->stats['users'] }}</p>
                                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Cadastros ativos na base</p>
                                </x-support.module-block>

                                <x-support.module-block title="ACL" icon="✦" subtitle="Perfis disponíveis no workspace.">
                                    <p class="text-3xl font-bold">{{ $this->stats['roles'] }}</p>
                                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Perfis carregados</p>
                                </x-support.module-block>
                            </div>

                            <x-support.module-block title="Atalhos" icon="⌘" subtitle="Abra qualquer área direto pelas abas.">
                                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                                    @foreach($this->availableTabs as $tab)
                                        @if ($tab['id'] !== 'home')
                                            <button
                                                type="button"
                                                wire:click="setTab('{{ $tab['id'] }}')"
                                                class="flex items-start gap-3 rounded-2xl border border-zinc-200 bg-zinc-50 p-4 text-left transition hover:bg-zinc-100 dark:border-zinc-800 dark:bg-zinc-900/50 dark:hover:bg-zinc-900"
                                            >
                                                <span class="mt-0.5 text-xl">{{ $tab['icon'] }}</span>
                                                <span class="min-w-0">
                                                    <span class="block font-semibold text-zinc-900 dark:text-zinc-50">{{ $tab['label'] }}</span>
                                                    <span class="mt-1 block text-xs leading-5 text-zinc-500 dark:text-zinc-400">
                                                        {{ $tab['description'] }}
                                                    </span>
                                                </span>
                                            </button>
                                        @endif
                                    @endforeach
                                </div>
                            </x-support.module-block>
                        </div>

                        <div class="grid gap-4">
                            <x-support.module-block title="Perfil atual" icon="👤" subtitle="Resumo do acesso autenticado.">
                                <div class="space-y-3">
                                    <div>
                                        <p class="text-lg font-bold">{{ $this->supportProfile['name'] }}</p>
                                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $this->supportProfile['email'] }}</p>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <span class="rounded-full border border-zinc-200 px-3 py-1 text-xs font-semibold dark:border-zinc-800">
                                            {{ $this->supportProfile['role'] }}
                                        </span>
                                        <span class="rounded-full border border-zinc-200 px-3 py-1 text-xs font-semibold dark:border-zinc-800">
                                            N{{ $this->supportProfile['rank'] }}
                                        </span>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        @forelse(array_slice($this->supportProfile['permissions'], 0, 8) as $permission)
                                            <span class="rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1 text-xs text-zinc-600 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300">
                                                {{ $permission }}
                                            </span>
                                        @empty
                                            <p class="text-sm text-zinc-500">Sem permissões carregadas.</p>
                                        @endforelse
                                    </div>
                                </div>
                            </x-support.module-block>

                            <x-support.module-block title="Módulos ativos" icon="▤" subtitle="Abas disponíveis nesta sessão.">
                                <div class="space-y-3">
                                    @foreach($this->availableTabs as $tab)
                                        <div class="flex items-center justify-between gap-3 rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-800 dark:bg-zinc-900/40">
                                            <div class="min-w-0">
                                                <p class="font-semibold">{{ $tab['label'] }}</p>
                                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $tab['description'] }}</p>
                                            </div>
                                            <button
                                                type="button"
                                                wire:click="setTab('{{ $tab['id'] }}')"
                                                class="rounded-full border border-zinc-200 px-3 py-1.5 text-xs font-semibold uppercase tracking-[0.2em] text-zinc-600 transition hover:bg-zinc-100 dark:border-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-900"
                                            >
                                                Abrir
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </x-support.module-block>
                        </div>
                    </div>
                @elseif ($this->activeTab === 'jobs')
                    <div class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
                        <x-support.module-block title="Fila de jobs" icon="⟳" subtitle="Últimos itens pendentes na fila do Laravel.">
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-left text-sm">
                                    <thead class="border-b border-zinc-200 text-zinc-500 dark:border-zinc-800 dark:text-zinc-400">
                                        <tr>
                                            <th class="px-4 py-3 font-medium">Job</th>
                                            <th class="px-4 py-3 font-medium">Fila</th>
                                            <th class="px-4 py-3 font-medium">Tentativas</th>
                                            <th class="px-4 py-3 font-medium">Disponível</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                        @forelse($this->queueJobs as $job)
                                            <tr class="transition hover:bg-zinc-50 dark:hover:bg-zinc-900/40">
                                                <td class="px-4 py-3">
                                                    <p class="font-medium">{{ $job['name'] }}</p>
                                                    <p class="text-xs text-zinc-500">ID {{ $job['id'] }}</p>
                                                </td>
                                                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $job['queue'] }}</td>
                                                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $job['attempts'] }}</td>
                                                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $job['availableAt'] ?? '—' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="px-4 py-8 text-center text-zinc-500" colspan="4">Nenhum job pendente.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </x-support.module-block>

                        <x-support.module-block title="Falhas recentes" icon="⚠" subtitle="Retry e limpeza de falhas.">
                            <div class="space-y-3">
                                <div class="flex gap-3">
                                    <button
                                        type="button"
                                        wire:click="flushFailedJobs"
                                        class="rounded-2xl border border-zinc-200 px-4 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-900"
                                    >
                                        Limpar falhas
                                    </button>
                                </div>

                                <div class="space-y-3">
                                    @forelse($this->failedJobs as $failedJob)
                                        <article class="rounded-2xl border border-zinc-200 p-4 dark:border-zinc-800">
                                            <div class="flex items-start justify-between gap-4">
                                                <div class="space-y-1">
                                                    <p class="font-semibold">{{ $failedJob['name'] }}</p>
                                                    <p class="text-xs text-zinc-500">Fila {{ $failedJob['queue'] }} · {{ $failedJob['failedAt'] ?? '—' }}</p>
                                                </div>
                                                <div class="flex gap-2">
                                                    <button
                                                        type="button"
                                                        wire:click="retryFailedJob({{ $failedJob['id'] }})"
                                                        class="rounded-full bg-zinc-950 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-zinc-800 dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-200"
                                                    >
                                                        Reenviar
                                                    </button>
                                                    <button
                                                        type="button"
                                                        wire:click="forgetFailedJob({{ $failedJob['id'] }})"
                                                        class="rounded-full border border-zinc-200 px-3 py-1.5 text-xs font-semibold text-zinc-600 transition hover:bg-zinc-50 dark:border-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-900"
                                                    >
                                                        Remover
                                                    </button>
                                                </div>
                                            </div>
                                            <p class="mt-3 line-clamp-3 text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                                                {{ $failedJob['exception'] }}
                                            </p>
                                        </article>
                                    @empty
                                        <p class="rounded-2xl border border-dashed border-zinc-300 p-6 text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                                            Nenhuma falha registrada.
                                        </p>
                                    @endforelse
                                </div>
                            </div>
                        </x-support.module-block>
                    </div>
                @elseif ($this->activeTab === 'logs')
                    <div class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
                        <x-support.module-block title="Logs por dia" icon="☰" subtitle="Eventos agrupados por data a partir de `storage/logs`.">
                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <label for="selectedLogDay" class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                        Dia
                                    </label>
                                    <select
                                        id="selectedLogDay"
                                        wire:model.live="selectedLogDay"
                                        class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-zinc-400 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-100"
                                    >
                                        @foreach($this->logDays as $day)
                                            <option value="{{ $day['date'] }}">
                                                {{ $day['label'] }} · {{ $day['count'] }} registros · {{ $day['level'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="space-y-2">
                                    <p class="text-xs uppercase tracking-[0.24em] text-zinc-500">Dias disponíveis</p>
                                    <div class="flex flex-wrap gap-2">
                                        @forelse($this->logDays as $day)
                                            <button
                                                type="button"
                                                wire:click="$set('selectedLogDay', '{{ $day['date'] }}')"
                                                class="rounded-full border px-3 py-1.5 text-xs font-semibold transition {{ $this->selectedLogDay === $day['date'] ? 'border-zinc-950 bg-zinc-950 text-white dark:border-white dark:bg-white dark:text-zinc-950' : 'border-zinc-200 bg-zinc-50 text-zinc-600 hover:bg-zinc-100 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800' }}"
                                            >
                                                {{ $day['label'] }}
                                            </button>
                                        @empty
                                            <p class="text-sm text-zinc-500">Nenhum arquivo de log encontrado.</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </x-support.module-block>

                        <x-support.module-block title="Eventos do dia" icon="▣" subtitle="Últimos eventos registrados no dia selecionado.">
                            <div class="space-y-4">
                                <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-900/50">
                                    <p class="text-xs uppercase tracking-[0.24em] text-zinc-500">Data selecionada</p>
                                    <p class="mt-1 text-lg font-semibold">
                                        {{ \Illuminate\Support\Carbon::parse($this->selectedLogDay)->format('d/m/Y') }}
                                    </p>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ count($this->logEntriesForSelectedDay) }} eventos encontrados
                                    </p>
                                </div>

                                <div class="space-y-3">
                                    @forelse($this->logEntriesForSelectedDay as $entry)
                                        <article class="rounded-2xl border border-zinc-200 p-4 dark:border-zinc-800">
                                            <div class="flex flex-wrap items-center justify-between gap-3">
                                                <div class="flex items-center gap-3">
                                                    <span class="rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1 text-xs font-semibold text-zinc-600 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300">
                                                        {{ $entry['time'] }}
                                                    </span>
                                                    <span class="rounded-full border border-zinc-200 px-3 py-1 text-xs font-semibold text-zinc-600 dark:border-zinc-800 dark:text-zinc-300">
                                                        {{ $entry['level'] }}
                                                    </span>
                                                </div>
                                                <p class="text-xs text-zinc-500">{{ $entry['channel'] }}</p>
                                            </div>
                                            <p class="mt-3 whitespace-pre-wrap text-sm leading-6 text-zinc-700 dark:text-zinc-300">
                                                {{ $entry['headline'] }}
                                            </p>
                                            @if ($entry['message'] !== $entry['headline'])
                                                <details class="mt-3">
                                                    <summary class="cursor-pointer text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500">
                                                        Ver detalhes
                                                    </summary>
                                                    <pre class="mt-3 overflow-x-auto rounded-2xl bg-zinc-950 p-4 text-xs leading-6 text-zinc-100 dark:bg-zinc-900">{{ $entry['message'] }}</pre>
                                                </details>
                                            @endif
                                        </article>
                                    @empty
                                        <p class="rounded-2xl border border-dashed border-zinc-300 p-6 text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                                            Nenhum evento encontrado para este dia.
                                        </p>
                                    @endforelse
                                </div>
                            </div>
                        </x-support.module-block>
                    </div>
                @elseif ($this->activeTab === 'campaigns')
                    <x-support.module-block title="Campanhas" icon="◆" subtitle="Visão operacional em modo de leitura.">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-sm">
                                <thead class="border-b border-zinc-200 text-zinc-500 dark:border-zinc-800 dark:text-zinc-400">
                                    <tr>
                                        <th class="px-4 py-3 font-medium">Nome</th>
                                        <th class="px-4 py-3 font-medium">Tipo</th>
                                        <th class="px-4 py-3 font-medium">Região</th>
                                        <th class="px-4 py-3 font-medium">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                    @foreach($this->catalogs['campaigns'] as $campaign)
                                        <tr class="transition hover:bg-zinc-50 dark:hover:bg-zinc-900/40">
                                            <td class="px-4 py-3 font-medium">{{ $campaign['name'] }}</td>
                                            <td class="px-4 py-3">{{ $campaign['type'] }}</td>
                                            <td class="px-4 py-3">{{ $campaign['regionId'] }}</td>
                                            <td class="px-4 py-3">{{ $campaign['status'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </x-support.module-block>
                @elseif ($this->activeTab === 'partners')
                    <x-support.module-block title="Parceiros" icon="◉" subtitle="Cadastros e vínculos em leitura.">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-sm">
                                <thead class="border-b border-zinc-200 text-zinc-500 dark:border-zinc-800 dark:text-zinc-400">
                                    <tr>
                                        <th class="px-4 py-3 font-medium">Nome</th>
                                        <th class="px-4 py-3 font-medium">Tipo</th>
                                        <th class="px-4 py-3 font-medium">Contato</th>
                                        <th class="px-4 py-3 font-medium">Região</th>
                                        <th class="px-4 py-3 font-medium">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                    @foreach($this->catalogs['partners'] as $partner)
                                        <tr class="transition hover:bg-zinc-50 dark:hover:bg-zinc-900/40">
                                            <td class="px-4 py-3 font-medium">{{ $partner['name'] }}</td>
                                            <td class="px-4 py-3">{{ $partner['type'] }}</td>
                                            <td class="px-4 py-3">{{ $partner['contact'] }}</td>
                                            <td class="px-4 py-3">{{ $partner['regionId'] }}</td>
                                            <td class="px-4 py-3">{{ $partner['status'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </x-support.module-block>
                @elseif ($this->activeTab === 'locations')
                    <x-support.module-block title="Locais" icon="⌂" subtitle="Comitês, sedes e pontos de apoio.">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-sm">
                                <thead class="border-b border-zinc-200 text-zinc-500 dark:border-zinc-800 dark:text-zinc-400">
                                    <tr>
                                        <th class="px-4 py-3 font-medium">Nome</th>
                                        <th class="px-4 py-3 font-medium">Tipo</th>
                                        <th class="px-4 py-3 font-medium">Região</th>
                                        <th class="px-4 py-3 font-medium">Capacidade</th>
                                        <th class="px-4 py-3 font-medium">Responsável</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                    @foreach($this->catalogs['locations'] as $location)
                                        <tr class="transition hover:bg-zinc-50 dark:hover:bg-zinc-900/40">
                                            <td class="px-4 py-3 font-medium">{{ $location['name'] }}</td>
                                            <td class="px-4 py-3">{{ $location['type'] }}</td>
                                            <td class="px-4 py-3">{{ $location['regionId'] }}</td>
                                            <td class="px-4 py-3">{{ $location['capacity'] }}</td>
                                            <td class="px-4 py-3">{{ $location['responsible'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </x-support.module-block>
                @elseif ($this->activeTab === 'users')
                    <x-support.module-block title="Usuários" icon="▣" subtitle="Equipe operacional e vínculos de perfil.">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-sm">
                                <thead class="border-b border-zinc-200 text-zinc-500 dark:border-zinc-800 dark:text-zinc-400">
                                    <tr>
                                        <th class="px-4 py-3 font-medium">Nome</th>
                                        <th class="px-4 py-3 font-medium">E-mail</th>
                                        <th class="px-4 py-3 font-medium">Perfil</th>
                                        <th class="px-4 py-3 font-medium">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                    @foreach($this->catalogs['users'] as $user)
                                        <tr class="transition hover:bg-zinc-50 dark:hover:bg-zinc-900/40">
                                            <td class="px-4 py-3 font-medium">{{ $user['name'] }}</td>
                                            <td class="px-4 py-3">{{ $user['email'] }}</td>
                                            <td class="px-4 py-3">{{ $user['roleId'] }}</td>
                                            <td class="px-4 py-3">{{ $user['status'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </x-support.module-block>
                @elseif ($this->activeTab === 'roles')
                    <x-support.module-block title="Perfis e permissões" icon="✦" subtitle="Leitura da matriz de ACL, com edição reservada ao fluxo administrativo do frontend.">
                        <div class="grid gap-4 xl:grid-cols-2">
                            @foreach($this->catalogs['roles'] as $role)
                                <article class="rounded-2xl border border-zinc-200 p-4 dark:border-zinc-800">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <h3 class="font-semibold">{{ $role['name'] }}</h3>
                                            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $role['description'] }}</p>
                                        </div>
                                        <span class="rounded-full border border-zinc-200 px-3 py-1 text-xs text-zinc-500 dark:border-zinc-800 dark:text-zinc-400">
                                            {{ count($role['permissions']) }} permissões
                                        </span>
                                    </div>
                                    <div class="mt-4 flex flex-wrap gap-2">
                                        @foreach($role['permissions'] as $permission)
                                            <span class="rounded-full bg-zinc-100 px-3 py-1 text-xs text-zinc-600 dark:bg-zinc-900 dark:text-zinc-300">
                                                {{ $permission }}
                                            </span>
                                        @endforeach
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </x-support.module-block>
                @endif
            </div>
        </section>
    </div>
</div>
