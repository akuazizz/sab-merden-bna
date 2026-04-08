<?php

namespace App\Modules\Shared\Contracts;

use App\Modules\Shared\Events\EventPublisher as LaravelEventPublisher;
use App\Modules\Shared\Repositories\BaseRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Base ServiceProvider untuk semua modul.
 *
 * Menyediakan helper registerRepository() dan registerService()
 * agar pendaftaran DI di child provider lebih konsisten dan DRY.
 */
abstract class BaseModuleServiceProvider extends ServiceProvider
{
    /**
     * Daftar binding [Interface => Concrete] untuk repository.
     * Override di child class.
     *
     * @var array<class-string, class-string>
     */
    protected array $repositories = [];

    /**
     * Daftar binding [Interface => Concrete] untuk service.
     * Override di child class.
     *
     * @var array<class-string, class-string>
     */
    protected array $services = [];

    public function register(): void
    {
        $this->registerRepositories();
        $this->registerServices();
        $this->registerModuleBindings();
    }

    /**
     * Override untuk registrasi binding tambahan yang spesifik per modul.
     */
    protected function registerModuleBindings(): void {}

    private function registerRepositories(): void
    {
        foreach ($this->repositories as $interface => $concrete) {
            $this->app->bind($interface, $concrete);
        }
    }

    private function registerServices(): void
    {
        foreach ($this->services as $interface => $concrete) {
            $this->app->bind($interface, $concrete);
        }
    }
}
