<div class="mx-auto flex min-h-screen w-full max-w-7xl items-center justify-center px-4 py-12">
    <div class="grid w-full overflow-hidden rounded-[2rem] border border-zinc-200 bg-white shadow-sm lg:grid-cols-[1.05fr_0.95fr]">
        <div class="flex flex-col justify-between gap-10 bg-gradient-to-br from-[#157fcc] to-[#1268a7] p-8 text-white lg:p-12">
            <div class="space-y-4">
                <span class="inline-flex items-center rounded-full border border-white/20 px-3 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-white/70">
                    Workspace do aplicativo
                </span>
                <h1 class="max-w-xl text-4xl font-bold tracking-tight">
                    Acesso interno ao workspace do aplicativo.
                </h1>
                <p class="max-w-lg text-sm leading-6 text-white/75">
                    A interface segue a lógica modular do frontend e libera apenas perfis autorizados com nível hierárquico adequado.
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-3">
                <div class="rounded-2xl border border-white/15 bg-white/10 p-4">
                    <p class="text-xs uppercase tracking-[0.24em] text-white/60">N1</p>
                    <p class="mt-2 text-sm text-white/90">Monitoramento operacional</p>
                </div>
                <div class="rounded-2xl border border-white/15 bg-white/10 p-4">
                    <p class="text-xs uppercase tracking-[0.24em] text-white/60">N2</p>
                    <p class="mt-2 text-sm text-white/90">Cadastros e operação avançada</p>
                </div>
                <div class="rounded-2xl border border-white/15 bg-white/10 p-4">
                    <p class="text-xs uppercase tracking-[0.24em] text-white/60">N3</p>
                    <p class="mt-2 text-sm text-white/90">Permissões e administração</p>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-center p-8 lg:p-12">
            <div class="w-full max-w-md space-y-8">
                <div class="space-y-2">
                    <h2 class="text-2xl font-bold tracking-tight text-zinc-900">Entrar no workspace</h2>
                    <p class="text-sm text-zinc-500">Use o e-mail do perfil configurado no sistema.</p>
                </div>

                <form wire:submit.prevent="login" class="space-y-5">
                    <div class="space-y-2">
                        <label for="email" class="text-sm font-medium text-zinc-700">E-mail</label>
                        <input
                            id="email"
                            type="email"
                            wire:model="email"
                            class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-zinc-400"
                            placeholder="admin@example.com"
                        >
                        @error('email') <p class="text-sm text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="password" class="text-sm font-medium text-zinc-700">Senha</label>
                        <input
                            id="password"
                            type="password"
                            wire:model="password"
                            class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-zinc-400"
                            placeholder="••••••••"
                        >
                        @error('password') <p class="text-sm text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <label class="flex items-center gap-3 text-sm text-zinc-600">
                        <input type="checkbox" wire:model="remember" class="h-4 w-4 rounded border-zinc-300 text-[#157fcc] focus:ring-[#157fcc]">
                        Manter sessão ativa
                    </label>

                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center rounded-2xl bg-[#157fcc] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#1268a7]"
                    >
                        Acessar workspace
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
