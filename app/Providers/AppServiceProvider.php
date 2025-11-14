<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Repositories
use App\Repositories\{
    CicloRepository,
    EstadoRepository,
    AreaRepository,
    AlcanceRepository,
    CargoRepository,
    CoreRepository,
    CuotaRepository,
    EstructuraRepository,
    FranquiciaRepository,
    LineaRepository,
    MercadoRepository,
    MixtaRepository,
    PromocionRepository,
    UneRepository,
    ZonaRepository,
    GeosegmentoRepository,
    MarcaRepository,
    MarcaMktRepository,
    UneFranqRepository,
    FranqLineaRepository,
    MarcFranUneRepository,
    ZonaEmpRepository,
    ZonaGeoRepository,
    EmpleadoRepository,
    ProductoRepository,
    FuerzaVentaRepository,
    UbigeoRepository
};

// Services
use App\Services\{
    CicloService,
    EstadoService,
    AreaService,
    AlcanceService,
    CargoService,
    CoreService,
    CuotaService,
    EstructuraService,
    FranquiciaService,
    LineaService,
    MercadoService,
    MixtaService,
    PromocionService,
    UneService,
    ZonaService,
    GeosegmentoService,
    MarcaService,
    MarcaMktService,
    UneFranqService,
    FranqLineaService,
    MarcFranUneService,
    ZonaEmpService,
    ZonaGeoService,
    EmpleadoService,
    ProductoService,
    FuerzaVentaService,
    UbigeoService,
    SSOTokenService
};

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ==================== REPOSITORIES ====================
        
        // Ciclo
        $this->app->singleton(CicloRepository::class);
        
        // Estado
        $this->app->singleton(EstadoRepository::class);
        
        // Area
        $this->app->singleton(AreaRepository::class);
        
        // Alcance
        $this->app->singleton(AlcanceRepository::class);
        
        // Cargo
        $this->app->singleton(CargoRepository::class);
        
        // Core
        $this->app->singleton(CoreRepository::class);
        
        // Cuota
        $this->app->singleton(CuotaRepository::class);
        
        // Estructura
        $this->app->singleton(EstructuraRepository::class);
        
        // Franquicia
        $this->app->singleton(FranquiciaRepository::class);
        
        // Linea
        $this->app->singleton(LineaRepository::class);
        
        // Mercado
        $this->app->singleton(MercadoRepository::class);
        
        // Mixta
        $this->app->singleton(MixtaRepository::class);
        
        // Promocion
        $this->app->singleton(PromocionRepository::class);
        
        // Une
        $this->app->singleton(UneRepository::class);
        
        // Zona
        $this->app->singleton(ZonaRepository::class);
        
        // Geosegmento
        $this->app->singleton(GeosegmentoRepository::class);
        
        // Marca
        $this->app->singleton(MarcaRepository::class);
        
        // MarcaMkt
        $this->app->singleton(MarcaMktRepository::class);
        
        // UneFranq
        $this->app->singleton(UneFranqRepository::class);
        
        // FranqLinea
        $this->app->singleton(FranqLineaRepository::class);
        
        // MarcFranUne
        $this->app->singleton(MarcFranUneRepository::class);
        
        // ZonaEmp
        $this->app->singleton(ZonaEmpRepository::class);
        
        // ZonaGeo
        $this->app->singleton(ZonaGeoRepository::class);
        
        // Empleado
        $this->app->singleton(EmpleadoRepository::class);
        
        // Producto
        $this->app->singleton(ProductoRepository::class);
        
        // FuerzaVenta
        $this->app->singleton(FuerzaVentaRepository::class);
        
        // Ubigeo
        $this->app->singleton(UbigeoRepository::class);

        // ==================== SERVICES ====================
        
        // Ciclo Service
        $this->app->singleton(CicloService::class, function ($app) {
            return new CicloService($app->make(CicloRepository::class));
        });
        
        // Estado Service
        $this->app->singleton(EstadoService::class, function ($app) {
            return new EstadoService($app->make(EstadoRepository::class));
        });
        
        // Area Service
        $this->app->singleton(AreaService::class, function ($app) {
            return new AreaService($app->make(AreaRepository::class));
        });
        
        // Alcance Service
        $this->app->singleton(AlcanceService::class, function ($app) {
            return new AlcanceService($app->make(AlcanceRepository::class));
        });
        
        // Cargo Service
        $this->app->singleton(CargoService::class, function ($app) {
            return new CargoService($app->make(CargoRepository::class));
        });
        
        // Core Service
        $this->app->singleton(CoreService::class, function ($app) {
            return new CoreService($app->make(CoreRepository::class));
        });
        
        // Cuota Service
        $this->app->singleton(CuotaService::class, function ($app) {
            return new CuotaService($app->make(CuotaRepository::class));
        });
        
        // Estructura Service
        $this->app->singleton(EstructuraService::class, function ($app) {
            return new EstructuraService($app->make(EstructuraRepository::class));
        });
        
        // Franquicia Service
        $this->app->singleton(FranquiciaService::class, function ($app) {
            return new FranquiciaService($app->make(FranquiciaRepository::class));
        });
        
        // Linea Service
        $this->app->singleton(LineaService::class, function ($app) {
            return new LineaService($app->make(LineaRepository::class));
        });
        
        // Mercado Service
        $this->app->singleton(MercadoService::class, function ($app) {
            return new MercadoService($app->make(MercadoRepository::class));
        });
        
        // Mixta Service
        $this->app->singleton(MixtaService::class, function ($app) {
            return new MixtaService($app->make(MixtaRepository::class));
        });
        
        // Promocion Service
        $this->app->singleton(PromocionService::class, function ($app) {
            return new PromocionService($app->make(PromocionRepository::class));
        });
        
        // Une Service
        $this->app->singleton(UneService::class, function ($app) {
            return new UneService($app->make(UneRepository::class));
        });
        
        // Zona Service
        $this->app->singleton(ZonaService::class, function ($app) {
            return new ZonaService($app->make(ZonaRepository::class));
        });
        
        // Geosegmento Service
        $this->app->singleton(GeosegmentoService::class, function ($app) {
            return new GeosegmentoService($app->make(GeosegmentoRepository::class));
        });
        
        // Marca Service
        $this->app->singleton(MarcaService::class, function ($app) {
            return new MarcaService($app->make(MarcaRepository::class));
        });
        
        // MarcaMkt Service
        $this->app->singleton(MarcaMktService::class, function ($app) {
            return new MarcaMktService($app->make(MarcaMktRepository::class));
        });
        
        // UneFranq Service
        $this->app->singleton(UneFranqService::class, function ($app) {
            return new UneFranqService($app->make(UneFranqRepository::class));
        });
        
        // FranqLinea Service
        $this->app->singleton(FranqLineaService::class, function ($app) {
            return new FranqLineaService($app->make(FranqLineaRepository::class));
        });
        
        // MarcFranUne Service
        $this->app->singleton(MarcFranUneService::class, function ($app) {
            return new MarcFranUneService($app->make(MarcFranUneRepository::class));
        });
        
        // ZonaEmp Service
        $this->app->singleton(ZonaEmpService::class, function ($app) {
            return new ZonaEmpService($app->make(ZonaEmpRepository::class));
        });
        
        // ZonaGeo Service
        $this->app->singleton(ZonaGeoService::class, function ($app) {
            return new ZonaGeoService($app->make(ZonaGeoRepository::class));
        });
        
        // Empleado Service
        $this->app->singleton(EmpleadoService::class, function ($app) {
            return new EmpleadoService($app->make(EmpleadoRepository::class));
        });
        
        // Producto Service
        $this->app->singleton(ProductoService::class, function ($app) {
            return new ProductoService($app->make(ProductoRepository::class));
        });
        
        // FuerzaVenta Service
        $this->app->singleton(FuerzaVentaService::class, function ($app) {
            return new FuerzaVentaService($app->make(FuerzaVentaRepository::class));
        });
        
        // Ubigeo Service
        $this->app->singleton(UbigeoService::class, function ($app) {
            return new UbigeoService($app->make(UbigeoRepository::class));
        });
        
        // SSO Token Service
        $this->app->singleton(SSOTokenService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
