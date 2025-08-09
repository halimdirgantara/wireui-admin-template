<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class OptimizeForProduction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:optimize-production {--force : Force optimization without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize the application for production deployment with caching and security';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!app()->environment('production') && !$this->option('force')) {
            if (!$this->confirm('This command is intended for production. Continue anyway?')) {
                $this->info('Optimization cancelled.');
                return 0;
            }
        }

        $this->info('🚀 Starting production optimization...');
        $this->newLine();

        // Step 1: Clear all caches
        $this->info('📱 Clearing all caches...');
        $this->clearCaches();

        // Step 2: Optimize configurations
        $this->info('⚙️  Optimizing configurations...');
        $this->optimizeConfigurations();

        // Step 3: Optimize routes
        $this->info('🛣️  Optimizing routes...');
        $this->optimizeRoutes();

        // Step 4: Optimize views
        $this->info('👁️  Optimizing views...');
        $this->optimizeViews();

        // Step 5: Optimize autoloader
        $this->info('📚 Optimizing autoloader...');
        $this->optimizeAutoloader();

        // Step 6: Cache permissions
        $this->info('🔐 Caching permissions...');
        $this->cachePermissions();

        // Step 7: Security recommendations
        $this->info('🛡️  Security recommendations...');
        $this->securityRecommendations();

        $this->newLine();
        $this->info('✅ Production optimization completed successfully!');
        $this->info('🎯 Your application is now optimized for production.');

        return 0;
    }

    /**
     * Clear all application caches
     */
    protected function clearCaches(): void
    {
        $commands = [
            'cache:clear' => 'Application cache',
            'config:clear' => 'Configuration cache',
            'route:clear' => 'Route cache',
            'view:clear' => 'View cache',
            'event:clear' => 'Event cache',
        ];

        foreach ($commands as $command => $description) {
            $this->line("   Clearing {$description}...");
            Artisan::call($command);
        }
    }

    /**
     * Optimize application configurations
     */
    protected function optimizeConfigurations(): void
    {
        $this->line('   Caching configuration...');
        Artisan::call('config:cache');
        
        $this->line('   Caching events...');
        Artisan::call('event:cache');
    }

    /**
     * Optimize application routes
     */
    protected function optimizeRoutes(): void
    {
        $this->line('   Caching routes...');
        
        try {
            Artisan::call('route:cache');
            $this->line('   Routes cached successfully.');
        } catch (\Exception $e) {
            $this->warn('   Route caching failed: ' . $e->getMessage());
            $this->warn('   This might be due to closure-based routes.');
        }
    }

    /**
     * Optimize application views
     */
    protected function optimizeViews(): void
    {
        $this->line('   Caching views...');
        Artisan::call('view:cache');
    }

    /**
     * Optimize composer autoloader
     */
    protected function optimizeAutoloader(): void
    {
        $this->line('   Optimizing composer autoloader...');
        
        $result = shell_exec('composer install --optimize-autoloader --no-dev 2>&1');
        if ($result === null) {
            $this->warn('   Could not run composer optimization. Please run manually:');
            $this->warn('   composer install --optimize-autoloader --no-dev');
        } else {
            $this->line('   Composer autoloader optimized.');
        }
    }

    /**
     * Cache permissions for better performance
     */
    protected function cachePermissions(): void
    {
        $this->line('   Caching Spatie permissions...');
        
        try {
            Artisan::call('permission:cache-reset');
            $this->line('   Permissions cached successfully.');
        } catch (\Exception $e) {
            $this->warn('   Permission caching not available.');
        }
    }

    /**
     * Display security recommendations
     */
    protected function securityRecommendations(): void
    {
        $this->line('   Security checklist:');
        
        $checks = [
            'APP_ENV is set to "production"' => env('APP_ENV') === 'production',
            'APP_DEBUG is set to false' => !env('APP_DEBUG', false),
            'APP_KEY is set' => !empty(env('APP_KEY')),
            'HTTPS is configured' => env('APP_URL', '') !== '' && str_starts_with(env('APP_URL'), 'https://'),
        ];

        foreach ($checks as $check => $passed) {
            $status = $passed ? '✅' : '❌';
            $this->line("     {$status} {$check}");
        }

        if (in_array(false, $checks)) {
            $this->newLine();
            $this->warn('⚠️  Some security recommendations are not met.');
            $this->warn('   Please review your .env file and server configuration.');
        }

        $this->newLine();
        $this->info('📋 Additional production recommendations:');
        $this->line('   • Set up SSL/TLS certificates');
        $this->line('   • Configure proper file permissions (755 for directories, 644 for files)');
        $this->line('   • Set up database backups');
        $this->line('   • Configure log rotation');
        $this->line('   • Set up monitoring and alerting');
        $this->line('   • Configure queue workers with supervisor');
        $this->line('   • Set up Redis for caching and sessions');
    }
}
