<div class="mx-auto flex min-h-screen w-full max-w-7xl items-center justify-center px-4 py-12">
    <div class="grid w-full overflow-hidden rounded-[2rem] border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-950 lg:grid-cols-[1.1fr_0.9fr]">
        <div class="flex flex-col justify-between gap-10 bg-zinc-950 p-8 text-zinc-50 lg:p-12">
            <div class="space-y-4">
                <span class="inline-flex items-center rounded-full border border-zinc-800 px-3 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-zinc-400">
                    Painel de suporte
                </span>
                <h1 class="max-w-xl text-4xl font-bold tracking-tight">
                    Acesso interno para operação e jobs do Laravel.
                </h1>
                <p class="max-w-lg text-sm leading-6 text-zinc-400">
                    A interface segue a lógica modular do frontend e libera apenas perfis de suporte com nível hierárquico adequado.
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-3">
                <div class="rounded-2xl border border-zinc-800 bg-zinc-900/80 p-4">
                    <p class="text-xs uppercase tracking-[0.24em] text-zinc-500">N1</p>
                    <p class="mt-2 text-sm text-zinc-200">Monitoramento operacional</p>
                </div>
                <div class="rounded-2xl border border-zinc-800 bg-zinc-900/80 p-4">
                    <p class="text-xs uppercase tracking-[0.24em] text-zinc-500">N2</p>
                    <p class="mt-2 text-sm text-zinc-200">Cadastros e suporte avançado</p>
                </div>
                <div class="rounded-2xl border border-zinc-800 bg-zinc-900/80 p-4">
                    <p class="text-xs uppercase tracking-[0.24em] text-zinc-500">N3</p>
                    <p class="mt-2 text-sm text-zinc-200">Permissões e administração</p>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-center p-8 lg:p-12">
            <div class="w-full max-w-md space-y-8">
                <div class="space-y-2">
                    <h2 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50">Entrar no suporte</h2>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Use o e-mail do perfil de suporte configurado no sistema.</p>
                </div>

                <form wire:submit.prevent="login" class="space-y-5">
                    <div class="space-y-2">
                        <label for="email" class="text-sm font-medium text-zinc-700 dark:text-zinc-300">E-mail</label>
                        <input
                            id="email"
                            type="email"
                            wire:model="email"
                            class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-zinc-400 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-100"
                            placeholder="suporte.n1@vertis.com.local"
                        >
                        @error('email') <p class="text-sm text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="password" class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Senha</label>
                        <input
                            id="password"
                            type="password"
                            wire:model="password"
                            class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-zinc-400 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-100"
                            placeholder="••••••••"
                        >
                        @error('password') <p class="text-sm text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <label class="flex items-center gap-3 text-sm text-zinc-600 dark:text-zinc-400">
                        <input type="checkbox" wire:model="remember" class="h-4 w-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-900">
                        Manter sessão ativa
                    </label>

                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center rounded-2xl bg-zinc-950 px-4 py-3 text-sm font-semibold text-white transition hover:bg-zinc-800 dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-200"
                    >
                        Acessar painel
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
