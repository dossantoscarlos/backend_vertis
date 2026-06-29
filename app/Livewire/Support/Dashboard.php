<?php

namespace App\Livewire\Support;

use App\Models\User;
use App\Models\Campaign;
use App\Models\CampaignLocation;
use App\Models\Partner;
use App\Models\Region;
use App\Support\SupportAccess;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use Livewire\Component;
use Symfony\Component\Process\Process;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Throwable;

class Dashboard extends Component
{
    public string $activeTab = 'dashboard';

    public array $openTabs = [];

    // Visual preferences state
    public string $profileName = '';
    public string $profileEmail = '';
    public string $profilePhone = '(11) 99999-9999';
    public string $profileTheme = 'triton';
    public string $profileMode = 'light';
    public string $profileActiveSubTab = 'dados';

    // Support tab state variables (original)
    public array $databases = [];
    public array $tables = [];
    public array $tableColumns = [];
    public array $tablePreview = [];
    public ?string $selectedDatabase = null;
    public ?string $selectedTable = null;
    public string $sqlQuery = '';
    public array $sqlColumns = [];
    public array $sqlRows = [];
    public ?string $sqlMessage = null;
    public ?string $sqlError = null;
    public int $sqlAffectedRows = 0;
    public string $debugCommand = '';
    public string $debugOutput = '';
    public ?int $debugExitCode = null;
    public ?string $debugError = null;
    public string $tailPath = '';
    public int $tailLines = 80;
    public string $tailOutput = '';
    public ?string $tailError = null;
    public array $terminalHistory = [];
    public array $queryHistory = [];
    public array $logFiles = [];

    // Campaigns screen states
    public string $campaignId = '';
    public string $campaignName = '';
    public string $campaignType = 'door-to-door';
    public string $campaignRegionId = '';
    public string $campaignStartDate = '';
    public string $campaignEndDate = '';
    public string $campaignStatus = 'planejada';
    public string $campaignDescription = '';
    public int $campaignCurrentStep = 0;
    public bool $campaignShowForm = false;
    public ?string $campaignDeleteId = null;
    public string $campaignResponsible = 'Administrador';

    // Surveys screen states
    public string $surveyId = '';
    public string $surveyName = '';
    public string $surveyDescription = '';
    public string $surveyStartDate = '';
    public string $surveyEndDate = '';
    public string $surveyType = 'porta'; // online, porta
    public string $surveyResponsible = '';
    public string $surveyTargetAudience = '';
    public string $surveyLink = '';
    public bool $surveyShowForm = false;
    public ?string $surveyDeleteId = null;

    // Users screen states
    public string $userId = '';
    public string $userName = '';
    public string $userEmail = '';
    public string $userRoleId = '';
    public string $userStatus = 'ativo';
    public bool $userShowForm = false;
    public ?string $userDeleteId = null;

    // Partners screen states
    public string $partnerId = '';
    public string $partnerName = '';
    public string $partnerType = 'fornecedor';
    public string $partnerContact = '';
    public string $partnerPhone = '';
    public string $partnerRegionId = '';
    public string $partnerStatus = 'ativo';
    public bool $partnerShowForm = false;
    public ?string $partnerDeleteId = null;

    // Locations screen states
    public string $locationId = '';
    public string $locationName = '';
    public string $locationAddress = '';
    public string $locationRegionId = '';
    public string $locationType = 'comitê';
    public int $locationCapacity = 10;
    public string $locationResponsible = '';
    public bool $locationShowForm = false;
    public ?string $locationDeleteId = null;

    // Regions screen states
    public string $regionId = '';
    public string $regionName = '';
    public string $regionUf = 'SP';
    public int $regionMunicipalities = 1;
    public int $regionPopulation = 0;
    public string $regionCoordinator = '';
    public bool $regionShowForm = false;
    public ?string $regionDeleteId = null;

    // Permissions screen states
    public string $roleId = '';
    public string $roleName = '';
    public string $roleDescription = '';
    public array $rolePermissions = [];
    public bool $roleShowForm = false;

    // TRE Search screen states
    public string $treNome = '';
    public string $treUf = 'SP';
    public string $treCargo = 'Deputado Federal';
    public array $treResults = [];
    public bool $treSearched = false;
    public ?array $treDetail = null;
    public array $treCompareIds = [];

    // Reports screen states
    public string $reportFilter = 'all';
    public ?string $selectedReportId = null;
    public string $reportPeriod = '2026-Q2';
    public ?array $reportResult = null;
    public bool $reportLoading = false;

    // Finance screen states
    public string $financeId = '';
    public string $financeType = 'despesa';
    public string $financeTransactionDate = '';
    public string $financeCompetencyDate = '';
    public string $financeProjectedCost = '';
    public string $financeFinalCost = '';
    public string $financeEntityType = 'campanha';
    public string $financeEntityExternalId = '';
    public string $financeResponsible = '';
    public string $financeApprover = '';
    public bool $financeShowForm = false;
    public ?string $financeDeleteId = null;
    public string $financeFilter = 'todos'; // todos, receber, pagar

    public function mount(): void
    {
        $this->ensureAccess();
        $this->refreshCatalogs();

        // Initialize visual preferences from current user settings
        $user = $this->currentUser();
        $this->profileName = $user->name;
        $this->profileEmail = $user->email;

        // Default open tabs: home and dashboard (if visible)
        $this->openTabs = [
            [
                'id' => 'home',
                'title' => 'Área de Trabalho',
                'icon' => '💻',
                'closable' => false,
            ]
        ];

        if (auth()->user()->can('dashboard:visualizar')) {
            $this->openTabs[] = [
                'id' => 'dashboard',
                'title' => 'Dashboard',
                'icon' => '📊',
                'closable' => false,
            ];
            $this->activeTab = 'dashboard';
        } else {
            $this->activeTab = 'home';
        }

        $this->tailPath = $this->defaultTailPath();
        $this->refreshTail();

        $this->financeTransactionDate = now()->toDateString();
        $this->financeCompetencyDate = now()->toDateString();

        // Initialize foreign key defaults
        $regions = Region::query()->oldest('id')->get();
        if ($regions->isNotEmpty()) {
            $this->campaignRegionId = $regions->first()->external_id;
            $this->partnerRegionId = $regions->first()->external_id;
            $this->locationRegionId = $regions->first()->external_id;
        }

        $roles = Role::query()->oldest('id')->get();
        if ($roles->isNotEmpty()) {
            $this->userRoleId = $roles->first()->external_id;
        }
    }

    public function render(): View
    {
        return view('livewire.support.dashboard', [
            'dbRegions' => Region::query()->oldest('id')->get(),
            'dbCampaigns' => Campaign::query()->oldest('id')->get(),
            'dbPartners' => Partner::query()->oldest('id')->get(),
            'dbLocations' => CampaignLocation::query()->oldest('id')->get(),
            'dbUsers' => User::query()->with('roles')->oldest('id')->get(),
            'dbRoles' => Role::query()->with('permissions')->oldest('id')->get(),
            'dbAvailablePermissions' => config('acl.permissions', []),
            'dbFinances' => \App\Models\FinancialTransaction::query()->latest('id')->get(),
            'dbSurveys' => \App\Models\Survey::query()->latest('id')->get(),
        ])->layout('layouts.support');
    }

    public function logout(): mixed
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function toJSON(mixed $value = null): void
    {
        // Defensive no-op for stale Livewire payloads.
    }

    public function supportProfile(): array
    {
        $user = $this->currentUser();
        $supportAccess = $this->supportAccess();

        return [
            'name' => $user->name,
            'email' => $user->email,
            'level' => $supportAccess->supportLevelLabel($supportAccess->primaryRoleId($user)) ?? 'SUPORTE',
            'rank' => $this->supportRank(),
        ];
    }

    public function currentConnectionLabel(): string
    {
        $connection = DB::connection();
        return sprintf(
            '%s · %s',
            strtoupper($connection->getDriverName()),
            $this->displayDatabaseName($connection->getDatabaseName())
        );
    }

    public function displayDatabaseName(?string $database): string
    {
        if ($database === null || $database === '') {
            return '—';
        }
        if ($database === ':memory:') {
            return ':memory:';
        }
        $name = basename(str_replace('\\', '/', $database));
        return $name !== '' ? $name : $database;
    }

    // Support diagnostic tabs (original API)
    public function visibleTabs(): array
    {
        $tabs = [
            ['id' => 'schema', 'label' => 'Schema', 'icon' => '🗂', 'rank' => 1],
            ['id' => 'sql', 'label' => 'SQL', 'icon' => '⌨', 'rank' => 2],
            ['id' => 'debug', 'label' => 'Debug', 'icon' => '⚙', 'rank' => 3],
        ];

        $rank = $this->supportRank();

        return array_values(array_filter(
            $tabs,
            static fn (array $tab): bool => $rank >= $tab['rank']
        ));
    }

    public function isTabVisible(string $tab): bool
    {
        foreach ($this->visibleTabs() as $visibleTab) {
            if ($visibleTab['id'] === $tab) {
                return true;
            }
        }
        return false;
    }

    public function setTab(string $tab): void
    {
        if (! $this->isTabVisible($tab)) {
            return;
        }
        $this->activeTab = $tab;
        $this->openTab($tab);
    }

    // --- TAB MANAGEMENT MECHANICS ---

    public function openTab(string $tabId): void
    {
        $menuItems = [
            'dashboard' => ['title' => 'Dashboard', 'icon' => '📊', 'permission' => 'dashboard:visualizar'],
            'campanhas' => ['title' => 'Gestão de Campanhas', 'icon' => '📣', 'permission' => 'campanhas:gerenciar'],
            'financeiro' => ['title' => 'Área Financeira', 'icon' => '💰', 'permission' => 'financeiro:gerenciar'],
            'usuarios' => ['title' => 'Equipe Operacional', 'icon' => '👤', 'permission' => 'usuarios:gerenciar'],
            'parceiros' => ['title' => 'Gestão de Parceiros', 'icon' => '🤝', 'permission' => 'parceiros:gerenciar'],
            'locais' => ['title' => 'Locais e Comitês', 'icon' => '📍', 'permission' => 'locais:gerenciar'],
            'regioes' => ['title' => 'Regiões Eleitorais', 'icon' => '🗺', 'permission' => 'regioes:gerenciar'],
            'relatorios' => ['title' => 'Relatórios e Estatísticas', 'icon' => '📊', 'permission' => 'relatorios:visualizar'],
            'tre' => ['title' => 'Consulta Oficial TRE', 'icon' => '⚖', 'permission' => 'tre:consultar'],
            'perfil' => ['title' => 'Configurações de Perfil', 'icon' => '👤', 'permission' => 'perfil:visualizar'],
            'permissoes' => ['title' => 'Matriz de Permissões', 'icon' => '🔐', 'permission' => 'permissoes:gerenciar'],
            'pesquisas' => ['title' => 'Gestão de Pesquisas', 'icon' => '🔍', 'permission' => 'pesquisas:visualizar'],
            'schema' => ['title' => 'Schema Ativo', 'icon' => '🗂', 'rank' => 1],
            'sql' => ['title' => 'Editor SQL', 'icon' => '⌨', 'rank' => 2],
            'debug' => ['title' => 'Terminal Debug', 'icon' => '⚙', 'rank' => 3],
        ];

        if ($tabId === 'home') {
            $tab = ['title' => 'Área de Trabalho', 'icon' => '💻'];
        } else {
            $tab = $menuItems[$tabId] ?? null;
        }

        if ($tab) {
            if (isset($tab['permission']) && !auth()->user()->can($tab['permission'])) {
                $this->dispatch('toast', message: 'Acesso Negado: Você não possui permissão para acessar este módulo.', type: 'error');
                return;
            }
            if (isset($tab['rank']) && $this->supportRank() < $tab['rank']) {
                $this->dispatch('toast', message: 'Acesso Negado: Rank de suporte insuficiente.', type: 'error');
                return;
            }
        }

        if (!$tab) return;

        $exists = false;
        foreach ($this->openTabs as $openTab) {
            if ($openTab['id'] === $tabId) {
                $exists = true;
                break;
            }
        }

        if (!$exists) {
            $this->openTabs[] = [
                'id' => $tabId,
                'title' => $tab['title'],
                'icon' => $tab['icon'],
                'closable' => !in_array($tabId, ['home', 'dashboard']),
            ];
        }

        $this->activeTab = $tabId;
    }

    public function closeTab(string $tabId): void
    {
        if (in_array($tabId, ['home', 'dashboard'])) return;

        $index = -1;
        foreach ($this->openTabs as $i => $openTab) {
            if ($openTab['id'] === $tabId) {
                $index = $i;
                break;
            }
        }

        if ($index !== -1) {
            array_splice($this->openTabs, $index, 1);
            if ($this->activeTab === $tabId) {
                $this->activeTab = $this->openTabs[count($this->openTabs) - 1]['id'] ?? 'home';
            }
        }
    }

    public function selectTab(string $tabId): void
    {
        $this->activeTab = $tabId;
    }

    // --- CAMPAIGNS OPERATIONS ---

    public function saveCampaign(): void
    {
        $this->validate([
            'campaignName' => 'required|string|max:255',
            'campaignType' => 'required|string|max:255',
            'campaignRegionId' => 'required|string|exists:regions,external_id',
            'campaignStartDate' => 'required|date',
            'campaignEndDate' => 'required|date|after_or_equal:campaignStartDate',
            'campaignStatus' => 'required|string|max:255',
            'campaignDescription' => 'nullable|string',
            'campaignResponsible' => 'required|string|max:255',
        ]);

        if ($this->campaignId) {
            $campaign = Campaign::query()->where('external_id', $this->campaignId)->firstOrFail();
            $campaign->update([
                'name' => $this->campaignName,
                'type' => $this->campaignType,
                'region_external_id' => $this->campaignRegionId,
                'start_date' => $this->campaignStartDate,
                'end_date' => $this->campaignEndDate,
                'status' => $this->campaignStatus,
                'description' => $this->campaignDescription ?? '',
                'responsible' => $this->campaignResponsible,
            ]);
            $this->dispatch('toast', message: 'Campanha atualizada.');
        } else {
            Campaign::query()->create([
                'external_id' => 'cam-' . time() . rand(100, 999),
                'name' => $this->campaignName,
                'type' => $this->campaignType,
                'region_external_id' => $this->campaignRegionId,
                'start_date' => $this->campaignStartDate,
                'end_date' => $this->campaignEndDate,
                'status' => $this->campaignStatus,
                'description' => $this->campaignDescription ?? '',
                'responsible' => $this->campaignResponsible,
            ]);
            $this->dispatch('toast', message: 'Campanha criada com sucesso.');
        }

        $this->resetCampaignForm();
    }

    public function editCampaign(string $externalId): void
    {
        $campaign = Campaign::query()->where('external_id', $externalId)->firstOrFail();
        $this->campaignId = $campaign->external_id;
        $this->campaignName = $campaign->name;
        $this->campaignType = $campaign->type;
        $this->campaignRegionId = $campaign->region_external_id;
        $this->campaignStartDate = $campaign->start_date->toDateString();
        $this->campaignEndDate = $campaign->end_date->toDateString();
        $this->campaignStatus = $campaign->status;
        $this->campaignDescription = $campaign->description ?? '';
        $this->campaignResponsible = $campaign->responsible ?? 'Administrador';
        $this->campaignShowForm = true;
        $this->campaignCurrentStep = 0;
    }

    public function deleteCampaign(string $externalId): void
    {
        Campaign::query()->where('external_id', $externalId)->delete();
        $this->dispatch('toast', message: 'Campanha excluída.');
        if ($this->campaignDeleteId === $externalId) {
            $this->campaignDeleteId = null;
        }
    }

    public function advanceCampaign(string $externalId): void
    {
        $campaign = Campaign::query()->where('external_id', $externalId)->firstOrFail();
        $statusSequence = ['planejada', 'em andamento', 'concluída'];
        $currentIndex = array_search($campaign->status, $statusSequence);
        if ($currentIndex !== false && isset($statusSequence[$currentIndex + 1])) {
            $nextStatus = $statusSequence[$currentIndex + 1];
            $campaign->update(['status' => $nextStatus]);
            $this->dispatch('toast', message: 'Campanha avançou para: ' . $nextStatus . '.');
        }
    }

    public function resetCampaignForm(): void
    {
        $this->campaignId = '';
        $this->campaignName = '';
        $this->campaignType = 'door-to-door';
        $regions = Region::query()->oldest('id')->get();
        $this->campaignRegionId = $regions->isNotEmpty() ? $regions->first()->external_id : '';
        $this->campaignStartDate = '';
        $this->campaignEndDate = '';
        $this->campaignStatus = 'planejada';
        $this->campaignDescription = '';
        $this->campaignResponsible = 'Administrador';
        $this->campaignCurrentStep = 0;
        $this->campaignShowForm = false;
        $this->campaignDeleteId = null;
    }

    // --- USERS OPERATIONS ---

    public function saveUser(): void
    {
        $this->validate([
            'userName' => 'required|string|max:255',
            'userEmail' => 'required|email|max:255',
            'userRoleId' => 'required|string|exists:roles,external_id',
            'userStatus' => 'required|string|max:255',
        ]);

        if ($this->userId) {
            $user = User::query()->where('external_id', $this->userId)->firstOrFail();
            $user->update([
                'name' => $this->userName,
                'email' => $this->userEmail,
                'status' => $this->userStatus,
            ]);
            $role = Role::query()->where('external_id', $this->userRoleId)->firstOrFail();
            $user->syncRoles([$role->name]);
            $this->dispatch('toast', message: 'Usuário atualizado com sucesso.');
        } else {
            $user = User::query()->create([
                'external_id' => 'user-' . time() . rand(100, 999),
                'name' => $this->userName,
                'email' => $this->userEmail,
                'status' => $this->userStatus,
                'password' => bcrypt('password123'),
            ]);
            $role = Role::query()->where('external_id', $this->userRoleId)->firstOrFail();
            $user->syncRoles([$role->name]);
            $this->dispatch('toast', message: 'Usuário cadastrado com sucesso.');
        }

        $this->resetUserForm();
    }

    public function editUser(string $externalId): void
    {
        $user = User::query()->where('external_id', $externalId)->firstOrFail();
        $this->userId = $user->external_id;
        $this->userName = $user->name;
        $this->userEmail = $user->email;
        $role = $user->roles->first();
        $this->userRoleId = $role ? $role->external_id : '';
        $this->userStatus = $user->status;
        $this->userShowForm = true;
    }

    public function deleteUser(string $externalId): void
    {
        if ($externalId === '1') {
            $this->dispatch('toast', message: 'Não é possível excluir o administrador principal.', type: 'error');
            return;
        }
        User::query()->where('external_id', $externalId)->delete();
        $this->dispatch('toast', message: 'Usuário removido.');
        if ($this->userDeleteId === $externalId) {
            $this->userDeleteId = null;
        }
    }

    public function resetUserForm(): void
    {
        $this->userId = '';
        $this->userName = '';
        $this->userEmail = '';
        $roles = Role::query()->oldest('id')->get();
        $this->userRoleId = $roles->isNotEmpty() ? $roles->first()->external_id : '';
        $this->userStatus = 'ativo';
        $this->userShowForm = false;
        $this->userDeleteId = null;
    }

    // --- PARTNERS OPERATIONS ---

    public function savePartner(): void
    {
        $this->validate([
            'partnerName' => 'required|string|max:255',
            'partnerType' => 'required|string|max:255',
            'partnerContact' => 'required|email|max:255',
            'partnerPhone' => 'required|string|max:255',
            'partnerRegionId' => 'required|string|exists:regions,external_id',
            'partnerStatus' => 'required|string|max:255',
        ]);

        if ($this->partnerId) {
            $partner = Partner::query()->where('external_id', $this->partnerId)->firstOrFail();
            $partner->update([
                'name' => $this->partnerName,
                'type' => $this->partnerType,
                'contact' => $this->partnerContact,
                'phone' => $this->partnerPhone,
                'region_external_id' => $this->partnerRegionId,
                'status' => $this->partnerStatus,
            ]);
            $this->dispatch('toast', message: 'Parceiro atualizado.');
        } else {
            Partner::query()->create([
                'external_id' => 'par-' . time() . rand(100, 999),
                'name' => $this->partnerName,
                'type' => $this->partnerType,
                'contact' => $this->partnerContact,
                'phone' => $this->partnerPhone,
                'region_external_id' => $this->partnerRegionId,
                'status' => $this->partnerStatus,
            ]);
            $this->dispatch('toast', message: 'Parceiro criado.');
        }

        $this->resetPartnerForm();
    }

    public function editPartner(string $externalId): void
    {
        $partner = Partner::query()->where('external_id', $externalId)->firstOrFail();
        $this->partnerId = $partner->external_id;
        $this->partnerName = $partner->name;
        $this->partnerType = $partner->type;
        $this->partnerContact = $partner->contact;
        $this->partnerPhone = $partner->phone;
        $this->partnerRegionId = $partner->region_external_id;
        $this->partnerStatus = $partner->status;
        $this->partnerShowForm = true;
    }

    public function deletePartner(string $externalId): void
    {
        Partner::query()->where('external_id', $externalId)->delete();
        $this->dispatch('toast', message: 'Parceiro removido.');
        if ($this->partnerDeleteId === $externalId) {
            $this->partnerDeleteId = null;
        }
    }

    public function resetPartnerForm(): void
    {
        $this->partnerId = '';
        $this->partnerName = '';
        $this->partnerType = 'fornecedor';
        $this->partnerContact = '';
        $this->partnerPhone = '';
        $regions = Region::query()->oldest('id')->get();
        $this->partnerRegionId = $regions->isNotEmpty() ? $regions->first()->external_id : '';
        $this->partnerStatus = 'ativo';
        $this->partnerShowForm = false;
        $this->partnerDeleteId = null;
    }

    // --- LOCATIONS OPERATIONS ---

    public function saveLocation(): void
    {
        $this->validate([
            'locationName' => 'required|string|max:255',
            'locationAddress' => 'required|string|max:255',
            'locationRegionId' => 'required|string|exists:regions,external_id',
            'locationType' => 'required|string|max:255',
            'locationCapacity' => 'required|integer|min:1',
            'locationResponsible' => 'required|string|max:255',
        ]);

        if ($this->locationId) {
            $location = CampaignLocation::query()->where('external_id', $this->locationId)->firstOrFail();
            $location->update([
                'name' => $this->locationName,
                'address' => $this->locationAddress,
                'region_external_id' => $this->locationRegionId,
                'type' => $this->locationType,
                'capacity' => $this->locationCapacity,
                'responsible' => $this->locationResponsible,
            ]);
            $this->dispatch('toast', message: 'Local atualizado.');
        } else {
            CampaignLocation::query()->create([
                'external_id' => 'loc-' . time() . rand(100, 999),
                'name' => $this->locationName,
                'address' => $this->locationAddress,
                'region_external_id' => $this->locationRegionId,
                'type' => $this->locationType,
                'capacity' => $this->locationCapacity,
                'responsible' => $this->locationResponsible,
            ]);
            $this->dispatch('toast', message: 'Local criado.');
        }

        $this->resetLocationForm();
    }

    public function editLocation(string $externalId): void
    {
        $location = CampaignLocation::query()->where('external_id', $externalId)->firstOrFail();
        $this->locationId = $location->external_id;
        $this->locationName = $location->name;
        $this->locationAddress = $location->address;
        $this->locationRegionId = $location->region_external_id;
        $this->locationType = $location->type;
        $this->locationCapacity = $location->capacity;
        $this->locationResponsible = $location->responsible;
        $this->locationShowForm = true;
    }

    public function deleteLocation(string $externalId): void
    {
        CampaignLocation::query()->where('external_id', $externalId)->delete();
        $this->dispatch('toast', message: 'Local removido.');
        if ($this->locationDeleteId === $externalId) {
            $this->locationDeleteId = null;
        }
    }

    public function resetLocationForm(): void
    {
        $this->locationId = '';
        $this->locationName = '';
        $this->locationAddress = '';
        $regions = Region::query()->oldest('id')->get();
        $this->locationRegionId = $regions->isNotEmpty() ? $regions->first()->external_id : '';
        $this->locationType = 'comitê';
        $this->locationCapacity = 10;
        $this->locationResponsible = '';
        $this->locationShowForm = false;
        $this->locationDeleteId = null;
    }

    // --- REGIONS OPERATIONS ---

    public function saveRegion(): void
    {
        $this->validate([
            'regionName' => 'required|string|max:255',
            'regionUf' => 'required|string|max:2',
            'regionMunicipalities' => 'required|integer|min:1',
            'regionPopulation' => 'required|integer|min:0',
            'regionCoordinator' => 'required|string|max:255',
        ]);

        if ($this->regionId) {
            $region = Region::query()->where('external_id', $this->regionId)->firstOrFail();
            $region->update([
                'name' => $this->regionName,
                'uf' => strtoupper($this->regionUf),
                'municipalities' => $this->regionMunicipalities,
                'population' => $this->regionPopulation,
                'coordinator' => $this->regionCoordinator,
            ]);
            $this->dispatch('toast', message: 'Região atualizada.');
        } else {
            Region::query()->create([
                'external_id' => 'reg-' . time() . rand(100, 999),
                'name' => $this->regionName,
                'uf' => strtoupper($this->regionUf),
                'municipalities' => $this->regionMunicipalities,
                'population' => $this->regionPopulation,
                'coordinator' => $this->regionCoordinator,
                'vote_goal' => 0,
                'votes_projected' => 0,
            ]);
            $this->dispatch('toast', message: 'Região cadastrada.');
        }

        $this->resetRegionForm();
    }

    public function editRegion(string $externalId): void
    {
        $region = Region::query()->where('external_id', $externalId)->firstOrFail();
        $this->regionId = $region->external_id;
        $this->regionName = $region->name;
        $this->regionUf = $region->uf;
        $this->regionMunicipalities = $region->municipalities;
        $this->regionPopulation = $region->population;
        $this->regionCoordinator = $region->coordinator;
        $this->regionShowForm = true;
    }

    public function deleteRegion(string $externalId): void
    {
        Region::query()->where('external_id', $externalId)->delete();
        $this->dispatch('toast', message: 'Região removida.');
        if ($this->regionDeleteId === $externalId) {
            $this->regionDeleteId = null;
        }
    }

    public function resetRegionForm(): void
    {
        $this->regionId = '';
        $this->regionName = '';
        $this->regionUf = 'SP';
        $this->regionMunicipalities = 1;
        $this->regionPopulation = 0;
        $this->regionCoordinator = '';
        $this->regionShowForm = false;
        $this->regionDeleteId = null;
    }

    // --- FINANCE OPERATIONS ---

    public function saveFinance(): void
    {
        $this->financeCompetencyDate = $this->financeTransactionDate;

        $this->validate([
            'financeType' => 'required|string|in:receita,despesa',
            'financeTransactionDate' => 'required|date',
            'financeCompetencyDate' => 'required|date',
            'financeProjectedCost' => 'required|numeric|min:0',
            'financeFinalCost' => 'required|numeric|min:0',
            'financeEntityType' => 'required|string|in:campanha,locais,eventos',
            'financeEntityExternalId' => 'required|string|max:255',
            'financeResponsible' => 'required|string|max:255',
            'financeApprover' => 'required|string|max:255',
        ]);

        if ($this->financeId) {
            $tx = \App\Models\FinancialTransaction::query()->where('external_id', $this->financeId)->firstOrFail();
            $tx->update([
                'type' => $this->financeType,
                'transaction_date' => $this->financeTransactionDate,
                'competency_date' => $this->financeCompetencyDate,
                'projected_cost' => $this->financeProjectedCost,
                'final_cost' => $this->financeFinalCost,
                'entity_type' => $this->financeEntityType,
                'entity_external_id' => $this->financeEntityExternalId,
                'responsible' => $this->financeResponsible,
                'approver' => $this->financeApprover,
            ]);
            $this->dispatch('toast', message: 'Lançamento financeiro atualizado.');
        } else {
            \App\Models\FinancialTransaction::query()->create([
                'external_id' => 'fin-' . time() . rand(100, 999),
                'type' => $this->financeType,
                'transaction_date' => $this->financeTransactionDate,
                'competency_date' => $this->financeCompetencyDate,
                'projected_cost' => $this->financeProjectedCost,
                'final_cost' => $this->financeFinalCost,
                'entity_type' => $this->financeEntityType,
                'entity_external_id' => $this->financeEntityExternalId,
                'responsible' => $this->financeResponsible,
                'approver' => $this->financeApprover,
            ]);
            $this->dispatch('toast', message: 'Lançamento financeiro criado.');
        }

        $this->resetFinanceForm();
    }

    public function editFinance(string $externalId): void
    {
        $tx = \App\Models\FinancialTransaction::query()->where('external_id', $externalId)->firstOrFail();
        $this->financeId = $tx->external_id;
        $this->financeType = $tx->type;
        $this->financeTransactionDate = $tx->transaction_date->toDateString();
        $this->financeCompetencyDate = $tx->competency_date->toDateString();
        $this->financeProjectedCost = $tx->projected_cost;
        $this->financeFinalCost = $tx->final_cost;
        $this->financeEntityType = $tx->entity_type;
        $this->financeEntityExternalId = $tx->entity_external_id;
        $this->financeResponsible = $tx->responsible;
        $this->financeApprover = $tx->approver ?? '';
        $this->financeShowForm = true;
    }

    public function deleteFinance(string $externalId): void
    {
        \App\Models\FinancialTransaction::query()->where('external_id', $externalId)->delete();
        $this->dispatch('toast', message: 'Lançamento financeiro removido.');
        if ($this->financeDeleteId === $externalId) {
            $this->financeDeleteId = null;
        }
    }

    public function resetFinanceForm(): void
    {
        $this->financeId = '';
        $this->financeType = 'despesa';
        $this->financeTransactionDate = now()->toDateString();
        $this->financeCompetencyDate = now()->toDateString();
        $this->financeProjectedCost = '';
        $this->financeFinalCost = '';
        $this->financeEntityType = 'campanha';
        $this->financeEntityExternalId = '';
        $this->financeResponsible = '';
        $this->financeApprover = '';
        $this->financeShowForm = false;
        $this->financeDeleteId = null;
    }

    public function updatedFinanceEntityExternalId(string $value): void
    {
        $this->syncResponsibleFromEntity($value);
    }

    public function updatedFinanceEntityType(string $value): void
    {
        $this->financeEntityExternalId = '';
        $this->financeResponsible = '';
    }

    private function syncResponsibleFromEntity(string $entityId): void
    {
        if ($this->financeEntityType === 'campanha') {
            $campaign = Campaign::query()->where('external_id', $entityId)->first();
            if ($campaign) {
                $this->financeResponsible = $campaign->responsible;
            }
        } elseif ($this->financeEntityType === 'locais') {
            $location = CampaignLocation::query()->where('external_id', $entityId)->first();
            if ($location) {
                $this->financeResponsible = $location->responsible;
            }
        }
    }

    // --- SURVEY OPERATIONS ---

    public function saveSurvey(): void
    {
        $this->validate([
            'surveyName' => 'required|string|max:255',
            'surveyDescription' => 'nullable|string',
            'surveyStartDate' => 'required|date',
            'surveyEndDate' => 'required|date|after_or_equal:surveyStartDate',
            'surveyType' => 'required|string|in:online,porta',
            'surveyResponsible' => 'required|string|max:255',
            'surveyTargetAudience' => 'required|string|max:255',
            'surveyLink' => 'required_if:surveyType,online|nullable|string|max:1000',
        ]);

        $attributes = [
            'name' => $this->surveyName,
            'description' => $this->surveyDescription ?? '',
            'start_date' => $this->surveyStartDate,
            'end_date' => $this->surveyEndDate,
            'type' => $this->surveyType,
            'responsible' => $this->surveyResponsible,
            'target_audience' => $this->surveyTargetAudience,
            'link' => $this->surveyType === 'online' ? ($this->surveyLink ?: null) : null,
        ];

        if ($this->surveyId) {
            $survey = \App\Models\Survey::query()->where('external_id', $this->surveyId)->firstOrFail();
            $survey->update($attributes);
            $this->dispatch('toast', message: 'Pesquisa atualizada com sucesso.');
        } else {
            $attributes['external_id'] = 'srv-' . time() . rand(100, 999);
            \App\Models\Survey::query()->create($attributes);
            $this->dispatch('toast', message: 'Pesquisa cadastrada com sucesso.');
        }

        $this->resetSurveyForm();
    }

    public function editSurvey(string $externalId): void
    {
        $survey = \App\Models\Survey::query()->where('external_id', $externalId)->firstOrFail();
        $this->surveyId = $survey->external_id;
        $this->surveyName = $survey->name;
        $this->surveyDescription = $survey->description ?? '';
        $this->surveyStartDate = $survey->start_date->toDateString();
        $this->surveyEndDate = $survey->end_date->toDateString();
        $this->surveyType = $survey->type;
        $this->surveyResponsible = $survey->responsible;
        $this->surveyTargetAudience = $survey->target_audience;
        $this->surveyLink = $survey->link ?? '';
        $this->surveyShowForm = true;
    }

    public function deleteSurvey(string $externalId): void
    {
        \App\Models\Survey::query()->where('external_id', $externalId)->delete();
        $this->dispatch('toast', message: 'Pesquisa removida com sucesso.');
        if ($this->surveyDeleteId === $externalId) {
            $this->surveyDeleteId = null;
        }
    }

    public function resetSurveyForm(): void
    {
        $this->surveyId = '';
        $this->surveyName = '';
        $this->surveyDescription = '';
        $this->surveyStartDate = '';
        $this->surveyEndDate = '';
        $this->surveyType = 'porta';
        $users = User::all();
        $this->surveyResponsible = $users->isNotEmpty() ? $users->first()->name : '';
        $this->surveyTargetAudience = '';
        $this->surveyLink = '';
        $this->surveyShowForm = false;
        $this->surveyDeleteId = null;
    }

    // --- PROFILE SETTINGS ---

    public function saveProfilePersonal(): void
    {
        $this->validate([
            'profileName' => 'required|string|max:255',
        ]);
        $user = $this->currentUser();
        $user->update(['name' => $this->profileName]);
        $this->dispatch('toast', message: 'Dados pessoais atualizados com sucesso.');
    }

    public function saveProfileContacts(): void
    {
        $this->validate([
            'profileEmail' => 'required|email|max:255',
        ]);
        $user = $this->currentUser();
        $user->update(['email' => $this->profileEmail]);
        $this->dispatch('toast', message: 'Contatos salvos.');
    }

    public function saveProfilePreferences(): void
    {
        // Theme is stored reactively on current component state.
        $this->dispatch('toast', message: 'Preferências salvas.');
    }

    // --- ROLE & PERMISSION SETTINGS ---

    public function saveRole(): void
    {
        $this->validate([
            'roleName' => 'required|string|max:255',
            'roleDescription' => 'nullable|string|max:255',
        ]);

        if ($this->roleId) {
            $role = Role::query()->where('external_id', $this->roleId)->firstOrFail();
            $role->update([
                'name' => $this->roleName,
                'description' => $this->roleDescription ?? '',
            ]);
            $permissions = Permission::query()->whereIn('name', $this->rolePermissions)->pluck('id');
            $role->permissions()->sync($permissions);
            $this->dispatch('toast', message: 'Perfil atualizado.');
        } else {
            $role = Role::query()->create([
                'name' => $this->roleName,
                'guard_name' => 'web',
            ]);
            $role->forceFill([
                'external_id' => 'role-' . time() . rand(100, 999),
                'description' => $this->roleDescription ?? '',
            ])->save();
            $permissions = Permission::query()->whereIn('name', $this->rolePermissions)->pluck('id');
            $role->permissions()->sync($permissions);
            $this->dispatch('toast', message: 'Perfil criado.');
        }

        $this->resetRoleForm();
    }

    public function editRole(string $externalId): void
    {
        $role = Role::query()->with('permissions')->where('external_id', $externalId)->firstOrFail();
        $this->roleId = $role->external_id;
        $this->roleName = $role->name;
        $this->roleDescription = $role->description ?? '';
        $this->rolePermissions = $role->permissions->pluck('name')->all();
        $this->roleShowForm = true;
    }

    public function toggleRolePermission(string $permissionId): void
    {
        if (in_array($permissionId, $this->rolePermissions)) {
            $this->rolePermissions = array_values(array_filter($this->rolePermissions, fn($p) => $p !== $permissionId));
        } else {
            $this->rolePermissions[] = $permissionId;
        }
    }

    public function resetRoleForm(): void
    {
        $this->roleId = '';
        $this->roleName = '';
        $this->roleDescription = '';
        $this->rolePermissions = [];
        $this->roleShowForm = false;
    }

    // --- TRE CONSULTA ---

    public function searchTre(): void
    {
        $candidates = [
            [
                'id' => 'tre-1', 'nome' => 'Roberto Almeida Ferreira', 'nomeUrna' => 'ROBERTO FERREIRA', 'numero' => 45,
                'partido' => 'Partido da Renovação Democrática', 'siglaPartido' => 'PRD', 'cargo' => 'Deputado Federal',
                'uf' => 'SP', 'municipio' => 'São Paulo', 'situacao' => 'deferido', 'votos' => 187432, 'intencaoVoto' => 28.5,
                'genero' => 'Masculino', 'ocupacao' => 'Advogado', 'coligacao' => 'SP pelo Futuro', 'grauInstrucao' => 'Superior completo',
                'dataNascimento' => '1978-03-15', 'bensDeclarados' => 485000,
            ],
            [
                'id' => 'tre-2', 'nome' => 'Helena Martins Costa', 'nomeUrna' => 'HELENA COSTA', 'numero' => 13,
                'partido' => 'Partido do Trabalho Popular', 'siglaPartido' => 'PTP', 'cargo' => 'Deputado Federal',
                'uf' => 'SP', 'municipio' => 'São Paulo', 'situacao' => 'deferido', 'votos' => 156890, 'intencaoVoto' => 24.2,
                'genero' => 'Feminino', 'ocupacao' => 'Professora', 'coligacao' => 'Frente Popular SP', 'grauInstrucao' => 'Superior completo',
                'dataNascimento' => '1982-11-22', 'bensDeclarados' => 210000,
            ],
            [
                'id' => 'tre-3', 'nome' => 'Carlos Eduardo Souza', 'nomeUrna' => 'CARLOS SOUZA', 'numero' => 22,
                'partido' => 'Partido Liberal Conservador', 'siglaPartido' => 'PLC', 'cargo' => 'Deputado Federal',
                'uf' => 'SP', 'municipio' => 'São Paulo', 'situacao' => 'deferido', 'votos' => 134567, 'intencaoVoto' => 19.8,
                'genero' => 'Masculino', 'ocupacao' => 'Empresário', 'coligacao' => 'União pelo Brasil',
            ],
            [
                'id' => 'tre-4', 'nome' => 'Fernanda Lima Rocha', 'nomeUrna' => 'FERNANDA ROCHA', 'numero' => 55,
                'partido' => 'Partido Verde Social', 'siglaPartido' => 'PVS', 'cargo' => 'Deputado Federal',
                'uf' => 'SP', 'municipio' => 'São Paulo', 'situacao' => 'deferido', 'votos' => 89234, 'intencaoVoto' => 12.1,
                'genero' => 'Feminino', 'ocupacao' => 'Médica', 'coligacao' => 'SP Sustentável',
            ],
            [
                'id' => 'tre-5', 'nome' => 'Marcos Antônio Vieira', 'nomeUrna' => 'MARCOS VIEIRA', 'numero' => 77,
                'partido' => 'Partido Democrático Nacional', 'siglaPartido' => 'PDN', 'cargo' => 'Deputado Federal',
                'uf' => 'SP', 'municipio' => 'São Paulo', 'situacao' => 'deferido', 'votos' => 67890, 'intencaoVoto' => 8.4,
                'genero' => 'Masculino', 'ocupacao' => 'Servidor Público', 'coligacao' => 'Frente Popular SP',
            ],
            [
                'id' => 'tre-6', 'nome' => 'Juliana Pereira Santos', 'nomeUrna' => 'JULIANA SANTOS', 'numero' => 40,
                'partido' => 'Partido da Renovação Democrática', 'siglaPartido' => 'PRD', 'cargo' => 'Deputado Estadual',
                'uf' => 'SP', 'municipio' => 'Campinas', 'situacao' => 'deferido', 'votos' => 45678, 'intencaoVoto' => 15.3,
                'genero' => 'Feminino', 'ocupacao' => 'Engenheira', 'coligacao' => 'SP pelo Futuro',
            ],
        ];

        $this->treResults = array_filter($candidates, function($c) {
            if ($this->treNome) {
                $q = strtolower($this->treNome);
                if (!str_contains(strtolower($c['nome']), $q) && !str_contains(strtolower($c['nomeUrna']), $q)) {
                    return false;
                }
            }
            if ($this->treUf && $c['uf'] !== $this->treUf) {
                return false;
            }
            if ($this->treCargo && strtolower($c['cargo']) !== strtolower($this->treCargo)) {
                return false;
            }
            return true;
        });

        $this->treSearched = true;
        $this->treDetail = null;
        $this->treCompareIds = [];
    }

    public function selectCandidateDetail(string $id): void
    {
        foreach ($this->treResults as $c) {
            if ($c['id'] === $id) {
                $this->treDetail = $c;
                break;
            }
        }
    }

    public function toggleTreCompare(string $id): void
    {
        if (in_array($id, $this->treCompareIds)) {
            $this->treCompareIds = array_values(array_filter($this->treCompareIds, fn($x) => $x !== $id));
        } else {
            if (count($this->treCompareIds) < 4) {
                $this->treCompareIds[] = $id;
            }
        }
    }

    // --- INTELIGENCIA E RELATORIOS ---

    public function selectReport(string $id): void
    {
        $this->selectedReportId = $id;
        $this->reportResult = null;
        $this->reportLoading = false;
        
        // Open report tab dynamically
        $templates = [
            'rep-1' => ['title' => 'Desempenho por Região', 'icon' => '🗺'],
            'rep-2' => ['title' => 'Gastos de Campanha', 'icon' => '💰'],
            'rep-3' => ['title' => 'Eleitores por Comitê', 'icon' => '👥'],
            'rep-4' => ['title' => 'Comparativo de Adversários', 'icon' => '⚔'],
            'rep-5' => ['title' => 'Atividades de Campo', 'icon' => '📋'],
            'rep-6' => ['title' => 'Engajamento Digital', 'icon' => '📱'],
        ];

        $rep = $templates[$id] ?? null;
        if ($rep) {
            $tabId = 'relatorio-' . $id;
            // Check if already open
            $exists = false;
            foreach ($this->openTabs as $openTab) {
                if ($openTab['id'] === $tabId) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $this->openTabs[] = [
                    'id' => $tabId,
                    'title' => $rep['title'],
                    'icon' => $rep['icon'],
                    'closable' => true,
                ];
            }
            $this->activeTab = $tabId;
        }
    }

    public function executeReport(): void
    {
        $this->reportLoading = true;
        
        $mockByCategory = [
            'rep-1' => [
                'columns' => ['Região', 'Apoiadores', 'Meta (%)', 'Campanhas'],
                'rows' => [
                    ['Região' => 'Zona Norte', 'Apoiadores' => 12400, 'Meta (%)' => 78, 'Campanhas' => 2],
                    ['Região' => 'Capital', 'Apoiadores' => 18900, 'Meta (%)' => 92, 'Campanhas' => 3],
                    ['Região' => 'Interior Oeste', 'Apoiadores' => 6800, 'Meta (%)' => 54, 'Campanhas' => 1],
                ]
            ],
            'rep-2' => [
                'columns' => ['Categoria', 'Valor (R$)', 'Fornecedor', 'Status'],
                'rows' => [
                    ['Categoria' => 'Mídia', 'Valor (R$)' => 45000, 'Fornecedor' => 'Rádio Interior FM', 'Status' => 'Pago'],
                    ['Categoria' => 'Material', 'Valor (R$)' => 12800, 'Fornecedor' => 'Gráfica Central', 'Status' => 'Pendente'],
                    ['Categoria' => 'Eventos', 'Valor (R$)' => 22000, 'Fornecedor' => 'Locação Praça', 'Status' => 'Aprovado'],
                ]
            ],
            'rep-3' => [
                'columns' => ['Comitê', 'Contatos', 'Zona', 'Conversão (%)'],
                'rows' => [
                    ['Comitê' => 'Comitê Santana', 'Contatos' => 3200, 'Zona' => 127, 'Conversão (%)' => 18],
                    ['Comitê' => 'Sede Capital', 'Contatos' => 5100, 'Zona' => '045', 'Conversão (%)' => 24],
                ]
            ],
            'rep-4' => [
                'columns' => ['Candidato', 'Intenção (%)', 'Votos 2022', 'Tendência'],
                'rows' => [
                    ['Candidato' => 'ROBERTO FERREIRA', 'Intenção (%)' => 28.5, 'Votos 2022' => 187432, 'Tendência' => '↑'],
                    ['Candidato' => 'HELENA COSTA', 'Intenção (%)' => 24.2, 'Votos 2022' => 156890, 'Tendência' => '→'],
                    ['Candidato' => 'CARLOS SOUZA', 'Intenção (%)' => 19.8, 'Votos 2022' => 134567, 'Tendência' => '↓'],
                ]
            ],
            'rep-5' => [
                'columns' => ['Agente', 'Visitas', 'Panfletos', 'Eventos'],
                'rows' => [
                    ['Agente' => 'João Santos', 'Visitas' => 340, 'Panfletos' => 1200, 'Eventos' => 4],
                    ['Agente' => 'Maria Silva', 'Visitas' => 280, 'Panfletos' => 900, 'Eventos' => 6],
                ]
            ],
            'rep-6' => [
                'columns' => ['Campanha', 'Impressões', 'Cliques', 'CTR (%)'],
                'rows' => [
                    ['Campanha' => '#FuturoSP', 'Impressões' => 450000, 'Cliques' => 12400, 'CTR (%)' => 2.8],
                    ['Campanha' => 'Interior Digital', 'Impressões' => 120000, 'Cliques' => 3100, 'CTR (%)' => 2.6],
                ]
            ]
        ];

        $this->reportResult = $mockByCategory[$this->selectedReportId] ?? null;
        $this->reportLoading = false;
        $this->dispatch('toast', message: 'Relatório executado.');
    }

    // --- SCHEMA / DIAGNOSTICS LOGIC (ORIGINAL) ---

    public function selectDatabase(string $database): void
    {
        if (! $this->databaseExists($database)) {
            return;
        }
        $this->selectedDatabase = $database;
        $this->refreshTablesForSelectedDatabase();
        $this->syncSelectedTable();
        $this->refreshSelectedTableDetails();
    }

    public function selectTable(string $table): void
    {
        if (! $this->tableExists($table)) {
            return;
        }
        $this->selectedTable = $table;
        $this->sqlQuery = sprintf('select * from %s limit 25;', $table);
        $this->refreshSelectedTableDetails();
    }

    public function executeQuery(): void
    {
        $this->sqlError = null;
        $this->sqlMessage = null;
        $this->sqlRows = [];
        $this->sqlColumns = [];
        $this->sqlAffectedRows = 0;

        if (! $this->canUseSql()) {
            $this->sqlError = 'Seu perfil não pode executar consultas SQL.';
            return;
        }

        $sql = trim($this->sqlQuery);
        if ($sql === '') {
            $this->sqlError = 'Informe uma consulta SQL.';
            return;
        }

        $sql = $this->normalizeSql($sql);
        if ($sql === '') {
            $this->sqlError = 'Informe uma consulta SQL.';
            return;
        }

        if ($this->containsMultipleStatements($sql)) {
            $this->sqlError = 'A interface aceita apenas uma instrução por vez.';
            return;
        }

        $connection = $this->connectionForSelectedDatabase();

        try {
            if ($this->isReadOnlySql($sql) || $this->supportRank() >= 3) {
                if (! $this->isReadOnlySql($sql) && $this->supportRank() < 3) {
                    $this->sqlError = 'Seu perfil só pode executar consultas de leitura.';
                    return;
                }

                $rows = $connection->select($sql);
                $this->sqlRows = array_map(
                    static fn (object|array $row): array => (array) $row,
                    $rows
                );
                $this->sqlColumns = array_values(array_keys($this->sqlRows[0] ?? []));
                $this->sqlAffectedRows = count($this->sqlRows);
                $this->sqlMessage = $this->sqlAffectedRows > 0
                    ? sprintf('%d linha(s) retornada(s).', $this->sqlAffectedRows)
                    : 'Consulta executada sem linhas retornadas.';
            } else {
                $affected = $connection->affectingStatement($sql);
                $this->sqlAffectedRows = $affected;
                $this->sqlMessage = sprintf('%d linha(s) afetada(s).', $affected);
            }

            $this->pushQueryHistory($sql, $this->sqlMessage ?? 'Consulta executada.');
            $this->refreshSelectedTableDetails();
        } catch (Throwable $throwable) {
            $this->sqlError = $throwable->getMessage();
            $this->pushQueryHistory($sql, $this->sqlError, true);
        }
    }

    public function runTerminal(): void
    {
        $this->debugError = null;
        $this->debugOutput = '';
        $this->debugExitCode = null;

        if (! $this->canUseDebug()) {
            $this->debugError = 'Seu perfil não pode usar o terminal de debug.';
            return;
        }

        $command = trim($this->debugCommand);
        if ($command === '') {
            $this->debugError = 'Informe um comando para executar.';
            return;
        }

        try {
            $process = Process::fromShellCommandline($command, base_path(), null, null, 30);
            $process->run();

            $this->debugExitCode = $process->getExitCode();
            $this->debugOutput = trim($process->getOutput() . $process->getErrorOutput());

            if (! $process->isSuccessful()) {
                $this->debugError = sprintf('Comando finalizado com código %s.', (string) $this->debugExitCode);
            }

            $this->pushTerminalHistory($command, $this->debugOutput, $this->debugExitCode);
        } catch (Throwable $throwable) {
            $this->debugError = $throwable->getMessage();
            $this->pushTerminalHistory($command, $this->debugError, null);
        }
    }

    public function downloadTail(): mixed
    {
        if (! $this->canUseDebug()) {
            abort(403);
        }
        $path = trim($this->tailPath);
        abort_unless($path !== '' && File::exists($path), 404);
        return response()->download($path, basename($path));
    }

    public function refreshTail(): void
    {
        $this->tailError = null;
        $this->tailOutput = '';

        if (! $this->canUseDebug()) {
            $this->tailError = 'Seu perfil não pode usar tails.';
            return;
        }

        $path = trim($this->tailPath);
        if ($path === '') {
            $this->tailError = 'Informe um arquivo de log.';
            return;
        }

        if (! File::exists($path)) {
            $this->tailError = 'Arquivo não encontrado.';
            return;
        }

        $content = (string) File::get($path);
        $lines = preg_split('/\R/u', rtrim($content, "\r\n")) ?: [];
        $lines = array_slice($lines, -max(1, $this->tailLines));
        $this->tailOutput = implode(PHP_EOL, $lines);
    }

    public function useLatestLog(): void
    {
        if (! $this->canUseDebug()) {
            return;
        }
        $latest = $this->latestLogFile();
        if ($latest === null) {
            return;
        }
        $this->tailPath = $latest;
        $this->refreshTail();
    }

    public function selectTail(string $path): void
    {
        if (! $this->canUseDebug()) {
            return;
        }
        $this->tailPath = $path;
        $this->refreshTail();
    }

    public function refreshCatalogs(): void
    {
        $this->databases = $this->catalogDatabases();
        $this->logFiles = $this->catalogLogFiles();
        $this->selectedDatabase ??= $this->defaultDatabaseName();

        if (! $this->databaseExists($this->selectedDatabase)) {
            $this->selectedDatabase = $this->databases[0]['name'] ?? null;
        }

        $this->refreshTablesForSelectedDatabase();
        $this->syncSelectedTable();
        $this->refreshSelectedTableDetails();

        if ($this->sqlQuery === '') {
            $this->sqlQuery = $this->defaultSqlQuery();
        }
    }

    public function supportRank(): int
    {
        $user = $this->currentUser();
        $supportAccess = $this->supportAccess();
        return $supportAccess->supportRank($supportAccess->primaryRoleId($user));
    }

    public function canUseSql(): bool
    {
        return $this->supportRank() >= 2;
    }

    public function canUseDebug(): bool
    {
        return $this->supportRank() >= 3;
    }

    public function formatValue(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_scalar($value)) {
            return (string) $value;
        }
        $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return $encoded !== false ? $encoded : '';
    }

    private function ensureAccess(): void
    {
        $user = $this->currentUser();
        abort_unless($this->supportAccess()->canAccess($user, 1), 403);
    }

    private function supportAccess(): SupportAccess
    {
        return app(SupportAccess::class);
    }

    private function currentUser(): User
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 403);
        return $user;
    }

    private function defaultDatabaseName(): ?string
    {
        return DB::connection()->getDatabaseName();
    }

    private function defaultSqlQuery(): string
    {
        if ($this->selectedTable !== null) {
            return sprintf('select * from %s limit 25;', $this->selectedTable);
        }
        return 'select 1 as example;';
    }

    private function normalizeSql(string $sql): string
    {
        $sql = trim($sql);
        return preg_replace('/;\s*$/', '', $sql) ?? $sql;
    }

    private function containsMultipleStatements(string $sql): bool
    {
        return str_contains($sql, ';');
    }

    private function isReadOnlySql(string $sql): bool
    {
        return (bool) preg_match('/^(select|show|describe|desc|explain|pragma|with)\b/i', ltrim($sql));
    }

    private function connectionForSelectedDatabase(): Connection
    {
        $current = DB::connection();
        $selectedDatabase = $this->selectedDatabase;

        if ($selectedDatabase === null || $selectedDatabase === '' || $selectedDatabase === $current->getDatabaseName()) {
            return $current;
        }

        $config = config('database.connections.' . DB::getDefaultConnection(), []);
        if ($config === []) {
            return $current;
        }
        $config['database'] = $selectedDatabase;
        return DB::build($config);
    }

    private function refreshTablesForSelectedDatabase(): void
    {
        $this->tables = [];
        if ($this->selectedDatabase === null) {
            return;
        }

        try {
            $connection = $this->connectionForSelectedDatabase();
            $driver = $connection->getDriverName();
            $database = $connection->getDatabaseName();

            $tables = match ($driver) {
                'sqlite' => $this->sqliteTables($connection),
                'mysql', 'mariadb' => $this->mysqlTables($connection, $database),
                'pgsql' => $this->pgsqlTables($connection, $database),
                'sqlsrv' => $this->sqlServerTables($connection, $database),
                default => [],
            };
            $this->tables = $tables;
        } catch (Throwable $throwable) {
            $this->tables = [];
            $this->tableColumns = [];
            $this->tablePreview = [];
            $this->sqlError = $throwable->getMessage();
        }
    }

    private function syncSelectedTable(): void
    {
        if ($this->selectedTable !== null && $this->tableExists($this->selectedTable)) {
            return;
        }
        $this->selectedTable = $this->tables[0]['name'] ?? null;
    }

    private function refreshSelectedTableDetails(): void
    {
        $this->tableColumns = [];
        $this->tablePreview = [];

        if ($this->selectedDatabase === null || $this->selectedTable === null) {
            return;
        }

        try {
            $connection = $this->connectionForSelectedDatabase();
            $this->tableColumns = $this->describeTable($connection, $this->selectedTable);
            $this->tablePreview = $this->previewTableRows($connection, $this->selectedTable);

            if ($this->sqlQuery === '' || $this->sqlQuery === 'select 1 as example;') {
                $this->sqlQuery = sprintf('select * from %s limit 25;', $this->selectedTable);
            }
        } catch (Throwable $throwable) {
            $this->tableColumns = [];
            $this->tablePreview = [];
            $this->sqlError = $throwable->getMessage();
        }
    }

    private function catalogDatabases(): array
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();
        $current = $connection->getDatabaseName();

        try {
            $rows = match ($driver) {
                'sqlite' => $this->sqliteDatabases($current),
                'mysql', 'mariadb' => $this->rowsToCatalog(
                    $connection->select('select schema_name as name from information_schema.schemata order by schema_name')
                ),
                'pgsql' => $this->rowsToCatalog(
                    $connection->select('select datname as name from pg_database where datistemplate = false order by datname')
                ),
                'sqlsrv' => $this->rowsToCatalog(
                    $connection->select('select name from sys.databases where state = 0 order by name')
                ),
                default => [],
            };
        } catch (Throwable) {
            $rows = [];
        }

        if ($rows === []) {
            $rows[] = [
                'name' => $current ?? 'default',
                'label' => $this->displayDatabaseName($current),
                'current' => true,
            ];
        }

        return array_map(function (array $row) use ($current): array {
            $name = (string) ($row['name'] ?? '');
            return [
                'name' => $name,
                'label' => $row['label'] ?? $this->displayDatabaseName($name),
                'current' => $current !== null && $name === $current,
            ];
        }, $rows);
    }

    private function rowsToCatalog(array $rows): array
    {
        return array_map(static function (object|array $row): array {
            $data = (array) $row;
            return [
                'name' => (string) ($data['name'] ?? array_values($data)[0] ?? ''),
            ];
        }, $rows);
    }

    private function sqliteDatabases(?string $currentDatabase): array
    {
        return [[
            'name' => $currentDatabase ?? 'main',
            'label' => $this->displayDatabaseName($currentDatabase),
            'current' => true,
        ]];
    }

    private function catalogLogFiles(): array
    {
        $paths = glob(storage_path('logs/*.log')) ?: [];
        usort($paths, static function (string $left, string $right): int {
            return (filemtime($right) ?: 0) <=> (filemtime($left) ?: 0);
        });

        return array_map(function (string $path): array {
            return [
                'path' => $path,
                'label' => basename($path),
            ];
        }, array_slice($paths, 0, 8));
    }

    private function latestLogFile(): ?string
    {
        return $this->logFiles[0]['path'] ?? null;
    }

    private function defaultTailPath(): string
    {
        return $this->latestLogFile() ?? storage_path('logs/laravel.log');
    }

    private function databaseExists(?string $database): bool
    {
        if ($database === null) {
            return false;
        }
        foreach ($this->databases as $item) {
            if (($item['name'] ?? null) === $database) {
                return true;
            }
        }
        return false;
    }

    private function tableExists(string $table): bool
    {
        foreach ($this->tables as $item) {
            if (($item['name'] ?? null) === $table) {
                return true;
            }
        }
        return false;
    }

    private function sqliteTables(Connection $connection): array
    {
        $rows = $connection->select(
            "select name from sqlite_master where type in ('table', 'view') and name not like 'sqlite_%' order by name"
        );
        return array_map(static function (object|array $row): array {
            $data = (array) $row;
            return [
                'name' => (string) ($data['name'] ?? ''),
                'label' => (string) ($data['name'] ?? ''),
            ];
        }, $rows);
    }

    private function mysqlTables(Connection $connection, ?string $database): array
    {
        $rows = $connection->select(
            'select table_name as name, table_type as type from information_schema.tables where table_schema = ? order by table_name',
            [$database]
        );
        return $this->rowsToTableCatalog($rows);
    }

    private function pgsqlTables(Connection $connection, ?string $database): array
    {
        $rows = $connection->select(
            "select table_name as name, table_type as type from information_schema.tables where table_catalog = ? and table_schema not in ('information_schema', 'pg_catalog') order by table_name",
            [$database]
        );
        return $this->rowsToTableCatalog($rows);
    }

    private function sqlServerTables(Connection $connection, ?string $database): array
    {
        $rows = $connection->select(
            'select table_name as name, table_type as type from information_schema.tables where table_catalog = ? order by table_name',
            [$database]
        );
        return $this->rowsToTableCatalog($rows);
    }

    private function rowsToTableCatalog(array $rows): array
    {
        return array_map(static function (object|array $row): array {
            $data = (array) $row;
            $name = (string) ($data['name'] ?? array_values($data)[0] ?? '');
            return [
                'name' => $name,
                'label' => $name,
                'type' => (string) ($data['type'] ?? ''),
            ];
        }, $rows);
    }

    private function describeTable(Connection $connection, string $table): array
    {
        return match ($connection->getDriverName()) {
            'sqlite' => $this->describeSqliteTable($connection, $table),
            'mysql', 'mariadb' => $this->describeGenericTable(
                $connection,
                'select column_name as name, data_type as type, is_nullable as nullable, column_default as default_value, extra from information_schema.columns where table_schema = ? and table_name = ? order by ordinal_position',
                [$connection->getDatabaseName(), $table]
            ),
            'pgsql' => $this->describeGenericTable(
                $connection,
                'select column_name as name, data_type as type, is_nullable as nullable, column_default as default_value from information_schema.columns where table_catalog = ? and table_name = ? order by ordinal_position',
                [$connection->getDatabaseName(), $table]
            ),
            'sqlsrv' => $this->describeGenericTable(
                $connection,
                'select column_name as name, data_type as type, is_nullable as nullable, column_default as default_value from information_schema.columns where table_catalog = ? and table_name = ? order by ordinal_position',
                [$connection->getDatabaseName(), $table]
            ),
            default => [],
        };
    }

    private function describeSqliteTable(Connection $connection, string $table): array
    {
        $rows = $connection->select(sprintf('pragma table_info(%s)', $this->quoteSqliteIdentifier($table)));
        return array_map(static function (object|array $row): array {
            $data = (array) $row;
            return [
                'name' => (string) ($data['name'] ?? ''),
                'type' => (string) ($data['type'] ?? ''),
                'nullable' => (int) ($data['notnull'] ?? 0) === 0 ? 'yes' : 'no',
                'default_value' => $data['dflt_value'] ?? null,
                'primary' => (int) ($data['pk'] ?? 0) === 1 ? 'yes' : 'no',
            ];
        }, $rows);
    }

    private function describeGenericTable(Connection $connection, string $sql, array $bindings): array
    {
        $rows = $connection->select($sql, $bindings);
        return array_map(static function (object|array $row): array {
            $data = (array) $row;
            return [
                'name' => (string) ($data['name'] ?? ''),
                'type' => (string) ($data['type'] ?? ''),
                'nullable' => (string) ($data['nullable'] ?? ''),
                'default_value' => $data['default_value'] ?? null,
                'extra' => $data['extra'] ?? null,
            ];
        }, $rows);
    }

    private function previewTableRows(Connection $connection, string $table): array
    {
        try {
            $rows = $connection->table($table)->limit(25)->get();
            return array_map(static fn (object|array $row): array => (array) $row, $rows->all());
        } catch (Throwable) {
            return [];
        }
    }

    private function quoteSqliteIdentifier(string $identifier): string
    {
        return '"' . str_replace('"', '""', $identifier) . '"';
    }

    private function pushQueryHistory(string $sql, string $message, bool $failed = false): void
    {
        array_unshift($this->queryHistory, [
            'sql' => $sql,
            'message' => $message,
            'failed' => $failed,
            'at' => now()->format('H:i:s'),
        ]);
        $this->queryHistory = array_slice($this->queryHistory, 0, 5);
    }

    private function pushTerminalHistory(string $command, string $output, ?int $exitCode): void
    {
        array_unshift($this->terminalHistory, [
            'command' => $command,
            'output' => $output,
            'exitCode' => $exitCode,
            'at' => now()->format('H:i:s'),
        ]);
        $this->terminalHistory = array_slice($this->terminalHistory, 0, 5);
    }
}
