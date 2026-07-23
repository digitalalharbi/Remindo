<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /** Shared system categories (organization_id = null) available to everyone. */
    public function run(): void
    {
        $categories = [
            ['slug' => 'contracts', 'color' => '#4F6BED', 'icon' => 'file-text',
                'name' => ['en' => 'Contracts', 'ar' => 'العقود', 'es' => 'Contratos', 'tr' => 'Sözleşmeler']],
            ['slug' => 'licenses', 'color' => '#0EA5A4', 'icon' => 'badge-check',
                'name' => ['en' => 'Licenses', 'ar' => 'التراخيص', 'es' => 'Licencias', 'tr' => 'Lisanslar']],
            ['slug' => 'insurance', 'color' => '#8B5CF6', 'icon' => 'shield',
                'name' => ['en' => 'Insurance', 'ar' => 'التأمين', 'es' => 'Seguros', 'tr' => 'Sigorta']],
            ['slug' => 'subscriptions', 'color' => '#F59E0B', 'icon' => 'repeat',
                'name' => ['en' => 'Subscriptions', 'ar' => 'الاشتراكات', 'es' => 'Suscripciones', 'tr' => 'Abonelikler']],
            ['slug' => 'documents', 'color' => '#64748B', 'icon' => 'folder',
                'name' => ['en' => 'Documents', 'ar' => 'الوثائق', 'es' => 'Documentos', 'tr' => 'Belgeler']],
            ['slug' => 'rentals', 'color' => '#10B981', 'icon' => 'home',
                'name' => ['en' => 'Rentals', 'ar' => 'الإيجارات', 'es' => 'Alquileres', 'tr' => 'Kiralamalar']],
            ['slug' => 'warranties', 'color' => '#EF4444', 'icon' => 'wrench',
                'name' => ['en' => 'Warranties', 'ar' => 'الضمانات', 'es' => 'Garantías', 'tr' => 'Garantiler']],
            ['slug' => 'certificates', 'color' => '#3B82F6', 'icon' => 'award',
                'name' => ['en' => 'Certificates', 'ar' => 'الشهادات', 'es' => 'Certificados', 'tr' => 'Sertifikalar']],
            ['slug' => 'maintenance', 'color' => '#A855F7', 'icon' => 'settings',
                'name' => ['en' => 'Maintenance', 'ar' => 'الصيانة', 'es' => 'Mantenimiento', 'tr' => 'Bakım']],
        ];

        foreach ($categories as $category) {
            Category::withoutGlobalScope('organization')->updateOrCreate(
                ['organization_id' => null, 'slug' => $category['slug']],
                array_merge($category, ['is_system' => true]),
            );
        }
    }
}
