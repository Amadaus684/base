<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings\SettingsCluster;
use App\Services\SettingService;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class Api extends Page
{
    protected string $view = 'filament.clusters.settings.pages.api';

    protected static ?string $cluster = SettingsCluster::class;

    protected static ?string $title = 'API';

    protected static ?int $navigationSort = 10;

    protected static string|\BackedEnum|null $navigationIcon =
        \Filament\Support\Icons\Heroicon::OutlinedGlobeAlt;

    public ?array $data = [];

    public function mount(): void
    {
        $settings = app(SettingService::class);

        $this->form->fill([
            // 'enabled' => $settings->get('api.enabled', true),
            'base_url' => $settings->get('api.base_url', ''),
            'token' => '',
            'timeout' => $settings->get('api.timeout', ''),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('API Connection')
                    ->description('Configure the connection to the external application.')
                    ->schema([
                        // Toggle::make('enabled')
                        //     ->label('Enable API'),

                        TextInput::make('base_url')
                            ->label('Base URL')
                            ->url()
                            ->required(fn ($get) => $get('enabled'))
                            ->placeholder('https://example.com/api'),

                        TextInput::make('token')
                            ->label('API Token')
                            ->password()
                            ->revealable()
                            ->placeholder('Enter a new token only when changing it')
                            ->helperText('The token is stored encrypted.'),

                        TextInput::make('timeout')
                            ->label('Timeout')
                            ->placeholder(30)
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(300)
                            ->helperText('time limit for waiting responds from api, max value is 300.')
                            ->suffix('seconds')
                            ->default(30)
                            ->required(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Changes')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $settings = app(SettingService::class);

        // $settings->set(
        //     'api.enabled',
        //     $data['enabled'] ?? false,
        //     'boolean',
        //     'api'
        // );

        $settings->set(
            'api.base_url',
            $data['base_url'] ?? '',
            'string',
            'api'
        );

        $settings->set(
            'api.timeout',
            $data['timeout'] ?? 30,
            'integer',
            'api'
        );

        // Only replace the existing token when a new one is provided.
        if (filled($data['token'] ?? null)) {
            $settings->set(
                'api.token',
                $data['token'],
                'encrypted',
                'api'
            );
        }

        Notification::make()
            ->title('API settings saved')
            ->success()
            ->send();
    }

    public function testConnection(): void
    {
        $settings = app(SettingService::class);

        $baseUrl = $settings->get('api.base_url');
        $token = $settings->get('api.token');
        $timeout = $settings->get('api.timeout', 30);

        if (!$baseUrl) {
            Notification::make()
                ->title('Connection failed')
                ->body('API Base URL has not been configured.')
                ->danger()
                ->send();

            return;
        }

        if (!$token) {
            Notification::make()
                ->title('Connection failed')
                ->body('API token has not been configured.')
                ->danger()
                ->send();

            return;
        }

        try {
            $start = microtime(true);

            $response = \Illuminate\Support\Facades\Http::withToken($token)
                ->acceptJson()
                ->timeout((int) $timeout)
                ->get(rtrim($baseUrl, '/') . '/user');

            $duration = round((microtime(true) - $start) * 1000);

            if ($response->successful()) {
                $user = $response->json('user');

                Notification::make()
                    ->title('Connection successful')
                    ->body(
                        'Terhubung sebagai ' .
                        ($user['name'] ?? 'User') .
                        " • {$duration} ms"
                    )
                    ->success()
                    ->send();

                return;
            }

            Notification::make()
                ->title('Connection failed')
                ->body("The API returned HTTP {$response->status()}.")
                ->danger()
                ->send();

        } catch (\Throwable $e) {
            Notification::make()
                ->title('Connection failed')
                ->body('Unable to connect to the external API.')
                ->danger()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('testConnection')
                ->label('Test Connection')
                ->icon(\Filament\Support\Icons\Heroicon::OutlinedSignal)
                ->action('testConnection'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('settings.view') ?? false;
    }
}