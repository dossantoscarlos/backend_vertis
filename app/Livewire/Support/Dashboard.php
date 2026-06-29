<?php

namespace App\Livewire\Support;

use App\Models\Campaign;
use App\Models\CampaignLocation;
use App\Models\Partner;
use App\Models\Region;
use App\Models\User;
use App\Support\DashboardSerializer;
use App\Support\SupportAccess;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class Dashboard extends Component
{
    public string $activeTab = 'home';

    public string $selectedLogDay = '';

    /**
     * @var array<int, array<string, mixed>>|null
     */
    private ?array $logEntriesCache = null;

    public function mount(SupportAccess $supportAccess): void
    {
        $user = Auth::user();

        abort_unless($user instanceof User, 403);
        abort_unless($supportAccess->canAccess($user), 403);

        $this->selectedLogDay = $this->defaultLogDay();
        $this->activeTab = $this->availableTabs()[0]['id'] ?? 'home';
    }

    public function setTab(string $tab): void
    {
        abort_unless(array_key_exists($tab, $this->tabs()), 403);
        abort_unless($this->canSeeTab($tab), 403);

        $this->activeTab = $tab;
    }

    public function setSection(string $section): void
    {
        $this->setTab($section);
    }

    public function restartQueueWorkers(): void
    {
        $this->authorizeJobsAction();
        Artisan::call('queue:restart');
        session()->flash('status', 'Workers reiniciados com sucesso.');
    }

    public function flushFailedJobs(): void
    {
        $this->authorizeJobsAction();
        Artisan::call('queue:flush');
        session()->flash('status', 'Fila de falhas limpa.');
    }

    public function retryFailedJob(int $failedJobId): void
    {
        $this->authorizeJobsAction();
        Artisan::call('queue:retry', ['id' => $failedJobId]);
        session()->flash('status', 'Job reenviado para a fila.');
    }

    public function forgetFailedJob(int $failedJobId): void
    {
        $this->authorizeJobsAction();
        Artisan::call('queue:forget', ['id' => $failedJobId]);
        session()->flash('status', 'Falha removida do histórico.');
    }

    public function logout(): mixed
    {
        Auth::logout();

        if (request()->hasSession()) {
            request()->session()->invalidate();
            request()->session()->regenerateToken();
        }

        return $this->redirectRoute('login', navigate: true);
    }

    #[Computed]
    public function supportProfile(): array
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 403);

        $user->loadMissing('roles.permissions');

        $supportAccess = app(SupportAccess::class);
        $role = $user->roles->first();
        $roleId = $role?->external_id;

        return [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $role?->name ?? 'Sem perfil',
            'roleId' => $roleId,
            'level' => $supportAccess->supportLevelLabel($roleId),
            'rank' => $supportAccess->supportRank($roleId),
            'permissions' => $role?->permissions->pluck('name')->sort()->values()->all() ?? [],
        ];
    }

    #[Computed]
    public function tabs(): array
    {
        return [
            'home' => [
                'label' => 'Área de Trabalho',
                'icon' => '💻',
                'description' => 'Resumo operacional, atalhos e visão geral do suporte.',
                'requiredRank' => 1,
                'closable' => false,
            ],
            'jobs' => [
                'label' => 'Jobs',
                'icon' => '⟳',
                'description' => 'Fila, retry e falhas.',
                'requiredRank' => 1,
                'closable' => false,
            ],
            'logs' => [
                'label' => 'Logs',
                'icon' => '☰',
                'description' => 'Eventos por dia.',
                'requiredRank' => 1,
                'closable' => false,
            ],
            'campaigns' => [
                'label' => 'Campanhas',
                'icon' => '◆',
                'description' => 'Operação e planejamento.',
                'requiredRank' => 2,
                'closable' => true,
            ],
            'partners' => [
                'label' => 'Parceiros',
                'icon' => '◉',
                'description' => 'Cadastros e vínculos.',
                'requiredRank' => 2,
                'closable' => true,
            ],
            'locations' => [
                'label' => 'Locais',
                'icon' => '⌂',
                'description' => 'Comitês e bases.',
                'requiredRank' => 2,
                'closable' => true,
            ],
            'users' => [
                'label' => 'Usuários',
                'icon' => '▣',
                'description' => 'Equipe operacional.',
                'requiredRank' => 2,
                'closable' => true,
            ],
            'roles' => [
                'label' => 'Perfis',
                'icon' => '✦',
                'description' => 'Leitura de ACL.',
                'requiredRank' => 2,
                'closable' => true,
            ],
        ];
    }

    /**
     * @return array<int, array{id: string, label: string, icon: string, description: string, requiredRank: int, closable: bool}>
     */
    #[Computed]
    public function availableTabs(): array
    {
        $profile = $this->supportProfile;
        $rank = (int) ($profile['rank'] ?? 0);

        return array_values(array_filter(
            array_map(
                fn (string $id, array $tab): array => ['id' => $id, ...$tab],
                array_keys($this->tabs()),
                $this->tabs(),
            ),
            fn (array $tab): bool => $rank >= $tab['requiredRank'],
        ));
    }

    #[Computed]
    public function stats(): array
    {
        return [
            'regions' => Region::query()->count(),
            'campaigns' => Campaign::query()->count(),
            'partners' => Partner::query()->count(),
            'locations' => CampaignLocation::query()->count(),
            'users' => User::query()->count(),
            'roles' => Role::query()->count(),
            'jobs' => DB::table('jobs')->count(),
            'failedJobs' => DB::table('failed_jobs')->count(),
        ];
    }

    #[Computed]
    public function modules(): array
    {
        $modules = [
            [
                'key' => 'overview',
                'title' => 'Visão geral',
                'icon' => '◼',
                'description' => 'Resumo operacional do workspace e atalhos principais.',
                'badge' => 'Base',
                'requiredRank' => 1,
            ],
            [
                'key' => 'jobs',
                'title' => 'Jobs',
                'icon' => '⟳',
                'description' => 'Monitoramento e controle da fila do Laravel.',
                'badge' => 'Operação',
                'requiredRank' => 1,
            ],
            [
                'key' => 'logs',
                'title' => 'Logs',
                'icon' => '☰',
                'description' => 'Visualização dos eventos do sistema por dia.',
                'badge' => 'Observabilidade',
                'requiredRank' => 1,
            ],
            [
                'key' => 'campaigns',
                'title' => 'Campanhas',
                'icon' => '◆',
                'description' => 'Módulo comercial com o mesmo modelo do frontend.',
                'badge' => 'N2',
                'requiredRank' => 2,
            ],
            [
                'key' => 'partners',
                'title' => 'Parceiros',
                'icon' => '◉',
                'description' => 'Cadastros e relacionamentos de apoio.',
                'badge' => 'N2',
                'requiredRank' => 2,
            ],
            [
                'key' => 'locations',
                'title' => 'Locais',
                'icon' => '⌂',
                'description' => 'Pontos operacionais, comitês e bases.',
                'badge' => 'N2',
                'requiredRank' => 2,
            ],
            [
                'key' => 'users',
                'title' => 'Usuários',
                'icon' => '▣',
                'description' => 'Usuários e vínculo de perfis.',
                'badge' => 'N2',
                'requiredRank' => 2,
            ],
            [
                'key' => 'roles',
                'title' => 'Perfis',
                'icon' => '✦',
                'description' => 'Leitura da hierarquia de perfis e permissões.',
                'badge' => 'ACL',
                'requiredRank' => 2,
            ],
        ];

        $rank = (int) ($this->supportProfile['rank'] ?? 0);

        return array_values(array_filter($modules, fn (array $module): bool => $rank >= $module['requiredRank']));
    }

    #[Computed]
    public function queueJobs(): array
    {
        return DB::table('jobs')
            ->orderByDesc('id')
            ->limit(10)
            ->get()
            ->map(fn (object $job): array => $this->normalizeQueueJob($job))
            ->all();
    }

    #[Computed]
    public function failedJobs(): array
    {
        return DB::table('failed_jobs')
            ->orderByDesc('id')
            ->limit(10)
            ->get()
            ->map(fn (object $job): array => $this->normalizeFailedJob($job))
            ->all();
    }

    #[Computed]
    public function logDays(): array
    {
        return collect($this->logEntries())
            ->groupBy('date')
            ->map(fn ($entries, string $date): array => [
                'date' => $date,
                'label' => Carbon::parse($date)->format('d/m/Y'),
                'count' => $entries->count(),
                'level' => $this->highestSeverity($entries->all()),
            ])
            ->sortByDesc('date')
            ->values()
            ->all();
    }

    #[Computed]
    public function logEntriesForSelectedDay(): array
    {
        return collect($this->logEntries())
            ->filter(fn (array $entry): bool => $entry['date'] === $this->selectedLogDay)
            ->sortByDesc('timestamp')
            ->values()
            ->all();
    }

    #[Computed]
    public function catalogs(): array
    {
        $serializer = app(DashboardSerializer::class);

        return [
            'regions' => Region::query()->oldest('id')->get()->map(fn (Region $region): array => $serializer->region($region))->values()->all(),
            'campaigns' => Campaign::query()->oldest('id')->get()->map(fn (Campaign $campaign): array => $serializer->campaign($campaign))->values()->all(),
            'partners' => Partner::query()->oldest('id')->get()->map(fn (Partner $partner): array => $serializer->partner($partner))->values()->all(),
            'locations' => CampaignLocation::query()->oldest('id')->get()->map(fn (CampaignLocation $location): array => $serializer->location($location))->values()->all(),
            'users' => User::query()->with('roles.permissions')->oldest('id')->get()->map(fn (User $user): array => $serializer->user($user))->values()->all(),
            'roles' => Role::query()->with('permissions')->oldest('id')->get()->map(fn (Role $role): array => $serializer->role($role))->values()->all(),
        ];
    }

    public function render(): View
    {
        return view('livewire.support.dashboard')->layout('layouts.support');
    }

    public function updatedSelectedLogDay(): void
    {
        if (! in_array($this->selectedLogDay, $this->availableLogDays(), true)) {
            $this->selectedLogDay = $this->defaultLogDay();
        }
    }

    private function authorizeJobsAction(): void
    {
        abort_unless($this->canSeeSection('jobs'), 403);
    }

    private function canSeeTab(string $tab): bool
    {
        $supportAccess = app(SupportAccess::class);
        $user = Auth::user();

        if (! $user instanceof User) {
            return false;
        }

        $rank = $supportAccess->supportRank($supportAccess->primaryRoleId($user));
        $requiredRank = $this->tabs()[$tab]['requiredRank'] ?? 999;

        return $rank >= $requiredRank;
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeQueueJob(object $job): array
    {
        $payload = json_decode((string) $job->payload, true);
        $data = is_array($payload) ? $payload : [];

        return [
            'id' => (int) $job->id,
            'queue' => (string) $job->queue,
            'name' => (string) ($data['displayName'] ?? $data['job'] ?? 'Job da fila'),
            'attempts' => (int) $job->attempts,
            'availableAt' => $this->formatTimestamp($job->available_at),
            'reservedAt' => $this->formatTimestamp($job->reserved_at),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeFailedJob(object $job): array
    {
        $payload = json_decode((string) $job->payload, true);
        $data = is_array($payload) ? $payload : [];

        return [
            'id' => (int) $job->id,
            'uuid' => (string) $job->uuid,
            'queue' => (string) $job->queue,
            'name' => (string) ($data['displayName'] ?? $data['job'] ?? 'Job com falha'),
            'failedAt' => $this->formatDateTime($job->failed_at),
            'exception' => str((string) $job->exception)->limit(140)->toString(),
        ];
    }

    private function formatTimestamp(mixed $timestamp): ?string
    {
        if (! is_numeric($timestamp) || (int) $timestamp <= 0) {
            return null;
        }

        return now()->setTimestamp((int) $timestamp)->format('d/m/Y H:i');
    }

    private function formatDateTime(mixed $dateTime): ?string
    {
        if (! is_string($dateTime) || $dateTime === '') {
            return null;
        }

        return Carbon::parse($dateTime)->format('d/m/Y H:i');
    }

    /**
     * @return array<int, string>
     */
    private function availableLogDays(): array
    {
        return collect($this->logEntries())
            ->pluck('date')
            ->unique()
            ->sortDesc()
            ->values()
            ->all();
    }

    private function defaultLogDay(): string
    {
        return $this->availableLogDays()[0] ?? now()->toDateString();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function logEntries(): array
    {
        if ($this->logEntriesCache !== null) {
            return $this->logEntriesCache;
        }

        $entries = [];

        foreach ($this->logFiles() as $path) {
            $entries = array_merge($entries, $this->parseLogFile($path));
        }

        usort($entries, static fn (array $left, array $right): int => strcmp($left['timestamp'], $right['timestamp']));

        return $this->logEntriesCache = $entries;
    }

    /**
     * @return array<int, string>
     */
    private function logFiles(): array
    {
        $paths = glob(storage_path('logs/laravel*.log')) ?: [];
        sort($paths);

        return $paths;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function parseLogFile(string $path): array
    {
        if (! File::exists($path)) {
            return [];
        }

        $content = File::get($path);

        if ($content === '') {
            return [];
        }

        preg_match_all(
            '/^\[(?<timestamp>[^\]]+)\]\s+(?<channel>[^:]+):\s+(?<message>.*?)(?=^\[\d{4}-\d{2}-\d{2}|\z)/ms',
            $content,
            $matches,
            PREG_SET_ORDER,
        );

        return array_values(array_filter(array_map(function (array $match): ?array {
            try {
                $timestamp = Carbon::parse($match['timestamp']);
            } catch (\Throwable $throwable) {
                return null;
            }

            $message = trim((string) $match['message']);
            $lines = preg_split('/\R/', $message) ?: [];
            $headline = trim((string) ($lines[0] ?? $message));
            $channel = trim((string) $match['channel']);
            $level = str($channel)->afterLast('.')->toString();

            return [
                'timestamp' => $timestamp->toDateTimeString(),
                'date' => $timestamp->toDateString(),
                'time' => $timestamp->format('H:i'),
                'level' => strtoupper($level),
                'channel' => $channel,
                'headline' => $headline,
                'message' => $message,
            ];
        }, $matches)));
    }

    /**
     * @param  array<int, array<string, mixed>>  $entries
     */
    private function highestSeverity(array $entries): string
    {
        $levels = collect($entries)->pluck('level')->map(fn (mixed $level): string => strtoupper((string) $level));

        return match (true) {
            $levels->contains('EMERGENCY') => 'EMERGENCY',
            $levels->contains('ALERT') => 'ALERT',
            $levels->contains('CRITICAL') => 'CRITICAL',
            $levels->contains('ERROR') => 'ERROR',
            $levels->contains('WARNING') => 'WARNING',
            $levels->contains('NOTICE') => 'NOTICE',
            default => 'INFO',
        };
    }
}
