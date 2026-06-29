<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\CampaignLocation;
use App\Models\Partner;
use App\Models\Region;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissionNames = collect(config('acl.permissions', []))->pluck('id');

        foreach ($permissionNames as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        foreach (config('acl.roles', []) as $roleId => $roleConfig) {
            $role = Role::findOrCreate($roleConfig['name'], 'web');
            $role->forceFill([
                'external_id' => $roleId,
                'description' => $roleConfig['description'] ?? '',
            ])->save();
            $rolePermissions = Permission::query()
                ->where('guard_name', 'web')
                ->whereIn('name', $roleConfig['permissions'])
                ->pluck('id');

            $role->permissions()->sync($rolePermissions);
        }

        $this->seedDashboardData();

        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $admin->assignRole('Administrador');

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function seedDashboardData(): void
    {
        foreach ($this->regions() as $region) {
            Region::query()->updateOrCreate(
                ['external_id' => $region['external_id']],
                $region,
            );
        }

        foreach ($this->campaigns() as $campaign) {
            Campaign::query()->updateOrCreate(
                ['external_id' => $campaign['external_id']],
                $campaign,
            );
        }

        foreach ($this->partners() as $partner) {
            Partner::query()->updateOrCreate(
                ['external_id' => $partner['external_id']],
                $partner,
            );
        }

        foreach ($this->locations() as $location) {
            CampaignLocation::query()->updateOrCreate(
                ['external_id' => $location['external_id']],
                $location,
            );
        }

        foreach ($this->users() as $userData) {
            $roleId = $userData['role_id'];
            unset($userData['role_id']);

            $user = User::query()->firstOrNew(['email' => $userData['email']]);
            $user->forceFill($userData)->save();
            $role = Role::query()->where('external_id', $roleId)->firstOrFail();
            $user->syncRoles([$role->name]);
        }

        foreach ($this->surveys() as $survey) {
            \App\Models\Survey::query()->updateOrCreate(
                ['external_id' => $survey['external_id']],
                $survey,
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function regions(): array
    {
        return [
            ['external_id' => 'reg-1', 'name' => 'Zona Norte', 'uf' => 'SP', 'municipalities' => 12, 'population' => 850000, 'coordinator' => 'Maria Silva', 'vote_goal' => 95000, 'votes_projected' => 74200],
            ['external_id' => 'reg-2', 'name' => 'Zona Sul', 'uf' => 'SP', 'municipalities' => 8, 'population' => 620000, 'coordinator' => 'Carlos Mendes', 'vote_goal' => 72000, 'votes_projected' => 51800],
            ['external_id' => 'reg-3', 'name' => 'Interior Oeste', 'uf' => 'SP', 'municipalities' => 24, 'population' => 430000, 'coordinator' => 'Fernanda Lima', 'vote_goal' => 68000, 'votes_projected' => 42100],
            ['external_id' => 'reg-4', 'name' => 'Capital', 'uf' => 'SP', 'municipalities' => 1, 'population' => 1200000, 'coordinator' => 'Roberto Alves', 'vote_goal' => 265000, 'votes_projected' => 198400],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function campaigns(): array
    {
        return [
            ['external_id' => 'cam-1', 'name' => 'Mutirão Zona Norte', 'type' => 'door-to-door', 'region_external_id' => 'reg-1', 'start_date' => '2026-06-15', 'end_date' => '2026-06-20', 'status' => 'em andamento', 'description' => 'Visita porta a porta nos bairros de Santana e Tucuruvi', 'responsible' => 'Maria Silva'],
            ['external_id' => 'cam-2', 'name' => 'Comício Praça da Sé', 'type' => 'comício', 'region_external_id' => 'reg-4', 'start_date' => '2026-07-01', 'end_date' => '2026-07-01', 'status' => 'planejada', 'description' => 'Grande comício com presença de lideranças regionais', 'responsible' => 'Administrador'],
            ['external_id' => 'cam-3', 'name' => 'Campanha Digital #FuturoSP', 'type' => 'digital', 'region_external_id' => 'reg-4', 'start_date' => '2026-05-01', 'end_date' => '2026-10-05', 'status' => 'em andamento', 'description' => 'Anúncios segmentados em redes sociais por região', 'responsible' => 'Administrador'],
            ['external_id' => 'cam-4', 'name' => 'Programa Rádio Interior', 'type' => 'rádio', 'region_external_id' => 'reg-3', 'start_date' => '2026-06-01', 'end_date' => '2026-08-30', 'status' => 'planejada', 'description' => 'Inserções em rádios do interior oeste', 'responsible' => 'Maria Silva'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function partners(): array
    {
        return [
            ['external_id' => 'par-1', 'name' => 'Gráfica União', 'type' => 'fornecedor', 'contact' => 'contato@graficauniao.com.br', 'phone' => '(11) 3456-7890', 'region_external_id' => 'reg-4', 'status' => 'ativo'],
            ['external_id' => 'par-2', 'name' => 'Rádio Cidade FM', 'type' => 'mídia', 'contact' => 'comercial@radiocidade.com.br', 'phone' => '(11) 2345-6789', 'region_external_id' => 'reg-1', 'status' => 'ativo'],
            ['external_id' => 'par-3', 'name' => 'Sindicato dos Comerciários', 'type' => 'institucional', 'contact' => 'presidencia@sindcomercio.org.br', 'phone' => '(11) 4567-8901', 'region_external_id' => 'reg-2', 'status' => 'ativo'],
            ['external_id' => 'par-4', 'name' => 'Rede de Voluntários Unidos', 'type' => 'voluntário', 'contact' => 'coordenacao@rvu.org', 'phone' => '(11) 5678-9012', 'region_external_id' => 'reg-3', 'status' => 'pendente'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function locations(): array
    {
        return [
            ['external_id' => 'loc-1', 'name' => 'Comitê Central', 'address' => 'Av. Paulista, 1000 - Bela Vista, São Paulo/SP', 'region_external_id' => 'reg-4', 'type' => 'sede', 'capacity' => 80, 'responsible' => 'Administrador'],
            ['external_id' => 'loc-2', 'name' => 'Comitê Zona Norte', 'address' => 'Rua Voluntários da Pátria, 500 - Santana, São Paulo/SP', 'region_external_id' => 'reg-1', 'type' => 'comitê', 'capacity' => 40, 'responsible' => 'Maria Silva'],
            ['external_id' => 'loc-3', 'name' => 'Ponto de Apoio Sul', 'address' => 'Rua Domingos de Morais, 200 - Vila Mariana, São Paulo/SP', 'region_external_id' => 'reg-2', 'type' => 'ponto de apoio', 'capacity' => 20, 'responsible' => 'Carlos Mendes'],
            ['external_id' => 'loc-4', 'name' => 'Comitê Interior', 'address' => 'Praça da Matriz, 50 - Centro, Campinas/SP', 'region_external_id' => 'reg-3', 'type' => 'comitê', 'capacity' => 35, 'responsible' => 'Fernanda Lima'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function users(): array
    {
        return [
            ['external_id' => '1', 'name' => 'Administrador', 'email' => 'admin@example.com', 'email_verified_at' => now(), 'password' => Hash::make('password123'), 'status' => 'ativo', 'role_id' => 'role-admin', 'created_at' => '2025-01-15 00:00:00', 'updated_at' => now()],
            ['external_id' => '2', 'name' => 'Maria Silva', 'email' => 'maria.silva@campanha.br', 'email_verified_at' => now(), 'password' => Hash::make('password'), 'status' => 'ativo', 'role_id' => 'role-coordenador', 'created_at' => '2025-02-01 00:00:00', 'updated_at' => now()],
            ['external_id' => '3', 'name' => 'João Santos', 'email' => 'joao.santos@campanha.br', 'email_verified_at' => now(), 'password' => Hash::make('password'), 'status' => 'ativo', 'role_id' => 'role-agente', 'created_at' => '2025-02-10 00:00:00', 'updated_at' => now()],
            ['external_id' => '4', 'name' => 'Ana Costa', 'email' => 'ana.costa@campanha.br', 'email_verified_at' => now(), 'password' => Hash::make('password'), 'status' => 'pendente', 'role_id' => 'role-analista', 'created_at' => '2025-03-05 00:00:00', 'updated_at' => now()],
            ['external_id' => 'support-n1', 'name' => 'Suporte N1', 'email' => 'suporte.n1@vertis.com.local', 'email_verified_at' => now(), 'password' => Hash::make('password123'), 'status' => 'ativo', 'role_id' => 'role-suporte-n1', 'created_at' => '2026-06-27 00:00:00', 'updated_at' => now()],
            ['external_id' => 'support-n2', 'name' => 'Suporte N2', 'email' => 'suporte.n2@vertis.com.local', 'email_verified_at' => now(), 'password' => Hash::make('password123'), 'status' => 'ativo', 'role_id' => 'role-suporte-n2', 'created_at' => '2026-06-27 00:00:00', 'updated_at' => now()],
            ['external_id' => 'support-n3', 'name' => 'Suporte N3', 'email' => 'suporte.n3@vertis.com.local', 'email_verified_at' => now(), 'password' => Hash::make('password123'), 'status' => 'ativo', 'role_id' => 'role-suporte-n3', 'created_at' => '2026-06-27 00:00:00', 'updated_at' => now()],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function surveys(): array
    {
        return [
            [
                'external_id' => 'srv-1',
                'name' => 'Pesquisa de Intenção de Voto - Santana',
                'description' => 'Mapeamento de intenção de voto espontânea e estimulada no bairro de Santana.',
                'start_date' => '2026-06-01',
                'end_date' => '2026-06-10',
                'type' => 'porta',
                'responsible' => 'Maria Silva',
                'target_audience' => 'Moradores do distrito de Santana (maiores de 16 anos)',
                'link' => null,
            ],
            [
                'external_id' => 'srv-2',
                'name' => 'Pesquisa Temática - Saúde e Segurança',
                'description' => 'Avaliação dos serviços de saúde e percepção de segurança nas capitais.',
                'start_date' => '2026-06-15',
                'end_date' => '2026-07-15',
                'type' => 'online',
                'responsible' => 'Administrador',
                'target_audience' => 'Eleitores da Região Metropolitana',
                'link' => 'https://forms.gle/exemploSaudeSeguranca',
            ],
        ];
    }
}
